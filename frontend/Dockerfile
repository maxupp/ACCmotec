FROM php:8-apache
RUN chown -R www-data:www-data /var/www/html
RUN php -i
RUN docker-php-ext-install mysqli