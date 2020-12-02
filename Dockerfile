FROM php:cli

# apt dependancies
RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y --no-install-recommends \
        git \
        libicu-dev

RUN docker-php-ext-install gettext

# core extensions
RUN docker-php-ext-install \
        intl \
        opcache \
        pcntl

# pecl extensions
RUN pecl channel-update pecl.php.net \
    && pecl install \
        apcu-5.1.18 \
    && docker-php-ext-enable \
        apcu

COPY docker/conf.d /usr/local/etc/php/conf.d

RUN apt-get autoremove --purge -y && apt-get clean

RUN php -r "copy('http://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"

COPY . /app

WORKDIR /app

RUN composer install

ENTRYPOINT ["php", "/app/build.php"]
