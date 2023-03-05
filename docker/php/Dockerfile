FROM php:8.1-fpm

RUN export DEBIAN_FRONTEND="noninteractive" \
    && apt-get update -qq \
    && apt-get -qqy install software-properties-common apt-utils locales tzdata \
    && apt-get install -y --no-install-recommends libzip-dev unzip procps inotify.tools

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
    && composer clear-cache

#install git
RUN apt-get install -y git zip unzip

#install git
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

