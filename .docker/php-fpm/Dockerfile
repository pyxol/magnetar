FROM php:8-fpm

# add in composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

## install system dependencies
#RUN apt-get update && apt-get -y upgrade
#RUN apt-get update && apt-get install -y git curl zip unzip

## install specific command line tools
#RUN apt-get update && apt-get install -y webp pngquant libjpeg-turbo-progs zlib1g-dev libpng-dev

# php: pdo mysql
RUN apt-get update && docker-php-ext-install pdo_mysql

# php: memcache
#RUN apt-get update && apt-get install -y libmemcached-dev \
#	&& git clone https://github.com/php-memcached-dev/php-memcached /usr/src/php/ext/memcached \
#	&& docker-php-ext-configure /usr/src/php/ext/memcached \
#	&& docker-php-ext-install /usr/src/php/ext/memcached \
#	&& rm -rf /usr/src/php/ext/memcached
RUN apt-get update && apt-get install -y libmemcached-dev libssl-dev zlib1g-dev \
	&& pecl install memcached-3.2.0 \
	&& docker-php-ext-enable memcached

## php: imagemagick
#RUN apt-get update && apt-get install -y imagemagick libmagickwand-dev --no-install-recommends \
#	&& pecl install imagick \
#	&& docker-php-ext-enable imagick

# php: mbstring
RUN apt-get update && apt-get install -y libonig-dev \
	&& docker-php-ext-install mbstring

# php: zip
RUN apt-get update && apt-get install -y libzip-dev \
	&& docker-php-ext-install zip

## php: gd
#RUN apt-get update && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
#	&& docker-php-ext-configure gd --with-freetype=/usr/include --with-jpeg=/usr/include \
#	&& docker-php-ext-install gd

# php: exif
RUN apt-get update && docker-php-ext-install exif

# php: sockets
RUN apt-get update && docker-php-ext-install sockets

# clear apt cache
RUN apt-get clean

# working directory
WORKDIR /magnetar
