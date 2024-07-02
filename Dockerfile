# deploy/Dockerfile

FROM dragoonis/dagger-workshop-app:latest
# FROM dragoonis/dagger-workshop-build as build

# install composer
COPY --from=composer:2.7.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# copy necessary files and change permissions
COPY . .
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# install php and node.js dependencies
# RUN php -v && exit 1

USER www-data
RUN composer install --dev
# RUN npm install
# RUN npm run prod

# RUN chown -R www-data:www-data /var/www/html/vendor \
#     && chmod -R 775 /var/www/html/vendor

USER root

# stage 2: production stage

# copy files from the build stage
# COPY --from=build /var/www/html /var/www/html
COPY ./deploy/nginx.conf /etc/nginx/http.d/default.conf
COPY ./deploy/php.ini "$PHP_INI_DIR/conf.d/app.ini"

WORKDIR /var/www/html

# add all folders where files are being stored that require persistence. if needed, otherwise remove this line.
VOLUME ["/var/www/html/storage/app"]

CMD ["sh", "-c", "nginx && php-fpm"]

