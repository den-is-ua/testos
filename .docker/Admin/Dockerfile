FROM php:8.4-fpm

ARG gid=1000
ARG uid=1000

# extension installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

# PHP ini
COPY ./php/php.ini "$PHP_INI_DIR/php.ini"

# tools & PHP extensions
RUN apt-get update \
 && apt-get upgrade -y \
 && apt-get install -y zip unzip git curl \
 && install-php-extensions pdo_pgsql pgsql zip igbinary xhprof sockets \
 && rm -rf /var/lib/apt/lists/*

 # Composer (if you want it available inside container)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm -f composer-setup.php

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# create group/user correctly + home dir
RUN groupadd -g ${gid} dev \
 && useradd -m -u ${uid} -g ${gid} -s /bin/bash dev \
 && mkdir -p /home/dev/.config/psysh /app \
 && chown -R dev:dev /home/dev /app

# Make PsySH/Composer paths predictable
ENV HOME=/home/dev \
    XDG_CONFIG_HOME=/home/dev/.config \
    COMPOSER_HOME=/home/dev/.composer \
    PATH="$PATH:/home/dev/.composer/vendor/bin"

ENV NPM_CONFIG_PREFIX=/home/dev/.npm-global \
    npm_config_prefix=/home/dev/.npm-global \
    NPM_CONFIG_CACHE=/home/dev/.npm/_cacache \
    npm_config_cache=/home/dev/.npm/_cacache \
    PATH=$PATH:/home/dev/.npm-global/bin

USER dev
WORKDIR /app

# Your code
COPY --chown=dev:dev . /app
