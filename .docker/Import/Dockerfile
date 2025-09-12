FROM php:8.4-fpm

ARG gid=1000
ARG uid=1000

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

COPY ./php/php.ini "$PHP_INI_DIR/php.ini"

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y zip unzip git && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_pgsql pgsql zip igbinary xhprof sockets


RUN groupadd -g $uid dev
RUN useradd -u $gid -g dev dev
COPY --chown=dev:dev . /app
USER dev


