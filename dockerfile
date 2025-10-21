FROM php:8.2-apache

COPY . /var/www/html/

# Dockerfile
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/fpdf \
    && chmod -R 755 /var/www/html/fpdf

RUN apt-get update && apt-get install -y libpq-dev \
 && docker-php-ext-install pdo_pgsql \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 && echo "<Directory /var/www/html/>" >> /etc/apache2/apache2.conf \
 && echo "    DirectoryIndex ax_index.php login_index.php registro_index.php carnet_index.php" >> /etc/apache2/apache2.conf \
 && echo "</Directory>" >> /etc/apache2/apache2.conf

RUN a2enmod rewrite
EXPOSE 80
CMD ["apache2ctl", "-D", "FOREGROUND"]
