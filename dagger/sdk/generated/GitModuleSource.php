<?php

/**
 * This class has been generated by dagger-php-sdk. DO NOT EDIT.
 */

declare(strict_types=1);

namespace Dagger;

/**
 * Module source originating from a git repo.
 */
class GitModuleSource extends Client\AbstractObject implements Client\IdAble
{
    /**
     * The URL to clone the root of the git repo from
     */
    public function cloneURL(): string
    {
        $leafQueryBuilder = new \Dagger\Client\QueryBuilder('cloneURL');
        return (string)$this->queryLeaf($leafQueryBuilder, 'cloneURL');
    }

    /**
     * The resolved commit of the git repo this source points to.
     */
    public function commit(): string
    {
        $leafQueryBuilder = new \Dagger\Client\QueryBuilder('commit');
        return (string)$this->queryLeaf($leafQueryBuilder, 'commit');
    }

    /**
     * The directory containing everything needed to load load and use the module.
     */
    public function contextDirectory(): Directory
    {
        $innerQueryBuilder = new \Dagger\Client\QueryBuilder('contextDirectory');
        return new \Dagger\Directory($this->client, $this->queryBuilderChain->chain($innerQueryBuilder));
    }

    /**
     * The URL to the source's git repo in a web browser
     */
    public function htmlURL(): string
    {
        $leafQueryBuilder = new \Dagger\Client\QueryBuilder('htmlURL');
        return (string)$this->queryLeaf($leafQueryBuilder, 'htmlURL');
    }

    /**
     * A unique identifier for this GitModuleSource.
     */
    public function id(): GitModuleSourceId
    {
        $leafQueryBuilder = new \Dagger\Client\QueryBuilder('id');
        return new \Dagger\GitModuleSourceId((string)$this->queryLeaf($leafQueryBuilder, 'id'));
    }

    /**
     * The clean module name of the root of the module
     */
    public function root(): string
    {
        $leafQueryBuilder = new \Dagger\Client\QueryBuilder('root');
        return (string)$this->queryLeaf($leafQueryBuilder, 'root');
    }

    /**
     * The path to the root of the module source under the context directory. This directory contains its configuration file. It also contains its source code (possibly as a subdirectory).
     */
    public function rootSubpath(): string
    {
        $leafQueryBuilder = new \Dagger\Client\QueryBuilder('rootSubpath');
        return (string)$this->queryLeaf($leafQueryBuilder, 'rootSubpath');
    }

    /**
     * The specified version of the git repo this source points to.
     */
    public function version(): string
    {
        $leafQueryBuilder = new \Dagger\Client\QueryBuilder('version');
        return (string)$this->queryLeaf($leafQueryBuilder, 'version');
    }
}