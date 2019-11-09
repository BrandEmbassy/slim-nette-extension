FROM php:7.3-cli-alpine

ENV PROJECT_ROOT="/code"

WORKDIR ${PROJECT_ROOT}
ADD . ${PROJECT_ROOT}

# Install additional packages
RUN apk update \
 && apk upgrade \
 && apk add --no-cache $PHPIZE_DEPS php7-dev bash composer

# Install XDebug
RUN pecl install xdebug \
 && docker-php-ext-enable xdebug \
 && echo xdebug.remote_enable=1 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo xdebug.remote_autostart=0 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo xdebug.remote_connect_back=1 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo xdebug.remote_port=9000 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo xdebug.remote_log=/tmp/php7-xdebug.log >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Composer
RUN composer self-update
RUN composer install

ENTRYPOINT ["/code/.docker/bin/run"]
