FROM php:8-apache
RUN mkdir /var/www/html/motec_data
RUN chmod a+rwx /var/www/html/motec_data
RUN php -i
RUN docker-php-ext-install mysqli