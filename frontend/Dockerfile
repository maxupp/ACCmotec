FROM php:8-apache
COPY custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY pdo_mysql.ini /usr/local/etc/conf.d/pdo_mysql.ini
RUN php -i
RUN chown www-data /var/www/html
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN apt-get update && apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-install zip pdo_mysql mysqli