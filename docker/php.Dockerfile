FROM php:8.2-apache

RUN docker-php-ext-install mysqli

COPY docker/php-custom.ini /usr/local/etc/php/conf.d/zz-custom.ini

RUN a2enmod rewrite
