FROM php:8-apache
COPY custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY pdo_mysql.ini /usr/local/etc/conf.d/pdo_mysql.ini
RUN php -i
RUN chown www-data /var/www/html
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN docker-php-ext-install pdo_mysql mysqli
