FROM php:8-apache
COPY custom.ini /usr/local/etc/php/conf.d/custom.ini
RUN php -i
RUN docker-php-ext-install pdo_mysql mysqli