<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerArgument;
use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Client;
use Dagger\Container;
use Dagger\Directory;
use Dagger\Service;
use Dagger\Terminal;

#[DaggerObject]
class DaggerLaravelApp
{
    public Client $client;

    #[DaggerFunction('laravel-app-build')]
    public function laravelAppBuild(
        #[DaggerArgument('The source code directory')]
        Directory $source,
    ): Container {

        return $this->client->container()
            ->build($source);
    }


    #[DaggerFunction('laravel-app-env-vars')]
    public function laravelAppEnvVars(
        #[DaggerArgument('The source code directory')]
        Directory $source,
    ): string {

        $container = $this->laravelAppBuild($source);

        $container = $this->loadEnvArgs($container, $source);

        return $container->withExec(['env'])->stdout();
    }

    #[DaggerFunction('run-integration-tests')]
    public function runIntegrationTests(
        #[DaggerArgument('The source code directory')]
        Directory $source,
    ): string {

        $container = $this->laravelAppBuild($source);
        $container = $this->loadContainerEnvVars($container, $source);

        // Attach MariaDB
        $dbService = $this->getDbService($source);
        $container = $container->withServiceBinding('database', $dbService);

        # Run Migrations and Tests
        return $container
            ->withExec($this->cmd('php artisan migrate'))
            ->withExec($this->cmd('php artisan db:seed'))
            ->withExec(['./vendor/bin/phpunit', '--testdox'])
            ->stdout();
    }


    #[DaggerFunction('lint')]
    public function lint(
        #[DaggerArgument('The value to echo')]
        Directory $source,
    ): string {
        return $this->client->container()
            ->from('jakzal/phpqa:latest')
            ->withMountedDirectory('/tmp/app', $source)
            ->withExec(['parallel-lint', '/tmp/app'])
            ->stdout();
    }

    #[DaggerFunction('phpstan')]
    public function phpstan(
        #[DaggerArgument('The value to echo')]
        Directory $source,
    ): string {
        return $this->client->container()
            ->from('jakzal/phpqa:latest')
            ->withMountedDirectory('/tmp/app', $source)
            ->withExec(['phpstan', 'analyse', '/tmp/app'])
            ->stdout();
    }

    #[DaggerFunction('Echo the value to standard output')]
     public function echo(
         #[DaggerArgument('The value to echo')]
         string $value = 'hello world',
     ): string {
         return $this->client->container()
             ->from('alpine:latest')
             ->withExec(['echo', $value])
             ->stdout();
     }

    #[DaggerFunction('Search a directory for lines matching a pattern')]
     public function grepDir(
         #[DaggerArgument('The directory to search')]
         Directory $directory,
         #[DaggerArgument('The pattern to search for')]
         string $pattern
    ): string {
         return $this->client->container()->from('alpine:latest')
             ->withMountedDirectory('/mnt', $directory)
             ->withWorkdir('/mnt')
             ->withExec(["grep", '-R', $pattern, '.'])
             ->stdout();
     }

    #[DaggerFunction('Search a directory for lines matching a pattern')]
    public function terminal(
        #[DaggerArgument('The directory to mount')]
        Directory $directory,
    ): Terminal {
        return $this->client->container()->from('alpine:latest')
            ->withMountedDirectory('/tmp/app', $directory)
            ->withWorkdir('/tmp/app')
            ->terminal();
    }

    #[DaggerFunction('php-base')]
    public function phpBase(): Container {
        return $this->client->container()
            ->from('php:8.3-cli-alpine')
            ->withWorkdir('/tmp/app');
    }

    #[DaggerFunction('Echo the value to standard output')]
    public function phpBase2(): string {
        $container = $this->client->php()->cli('8.2');
        foreach(['pdo-sqlite', 'gd'] as $ext) {
            $container = $this->client->php()->withExtension($container, $ext);
        }

        return $container->withExec(['php', '-m'])
            ->stdout();
    }

    #[DaggerFunction('php-version')]
    public function phpVersion(
    ): string {
        return $this->phpBase()
            ->withExec($this->cmd('php -v'))
            ->stdout();
    }

    #[DaggerFunction('php-terminal')]
    public function phpTerminal(
    ): Terminal {
        return $this->phpBase()
            ->terminal();
    }

    private function cmd(string $cmd): array
    {
        return ["/bin/sh", "-c", $cmd];
    }

    private function loadEnvArgs(Container $container, Directory $source): Container
    {
        $envContents = $source->file('.env')->contents();
        $envVars = parse_ini_string($envContents);

        foreach($envVars as $envVarKey => $envVarValue) {
            $container = $container->withEnvVariable($envVarKey, $envVarValue);
        }

        return $container;
    }


    private function loadContainerEnvVars(Container $container, Directory $source, string $prefix = ''): Container
    {
        $envVars = $this->getEnvVars($source, $prefix);

        foreach($envVars as $envVarKey => $envVarValue) {
            $container = $container->withEnvVariable($envVarKey, $envVarValue);
        }

        return $container;
    }

    private function getEnvVars(Directory $source, string $prefix = ''): array
    {
        $envContents = $source->file('.env')->contents();
        $envVars = parse_ini_string($envContents);
        $return = [];

        foreach($envVars as $envVarKey => $envVarValue) {
            if($prefix === '') {
                $return[$envVarKey] = $envVarValue;
                continue;
            }

            if($prefix !== '' && str_starts_with($envVarKey, $prefix)) {
                $return[$envVarKey] = $envVarValue;
            }
        }

        return $return;
    }

    private function getDbService(
        #[DaggerArgument('The source code directory')]
        Directory $source,
    ): Service {

        $dbEnvVars = $this->getEnvVars($source, 'DB_');

        $service = $this->client->container()->from('mariadb:lts-jammy')
            ->withEnvVariable('MARIADB_DATABASE', $dbEnvVars['DB_DATABASE'])
            ->withEnvVariable('MARIADB_USER', $dbEnvVars['DB_USERNAME'])
            ->withEnvVariable('MARIADB_PASSWORD', $dbEnvVars['DB_PASSWORD'])
            ->withEnvVariable('MARIADB_ROOT_PASSWORD', $dbEnvVars['DB_ROOT_PASSWORD'])
            ->withExposedPort(3306)
            ->asService();

        return $service;
    }
}
