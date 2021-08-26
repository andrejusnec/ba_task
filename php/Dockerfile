FROM php:8.0-fpm
RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip curl wget libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

WORKDIR /var/www/ba_task

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony
RUN git config --global user.email "andrejus.necvetnas@gmail.com" \
    && git config --global user.name "andrejusnec"


