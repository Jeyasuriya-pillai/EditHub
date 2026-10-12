
FROM php:8.2-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite \
    && sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:10000>/' /etc/apache2/sites-available/000-default.conf

ENV PORT=10000

COPY . /var/www/html/

EXPOSE 10000
