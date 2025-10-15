FROM php:8.2-apache

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 && echo "DirectoryIndex index.php index.html" >> /etc/apache2/apache2.conf

EXPOSE 80
