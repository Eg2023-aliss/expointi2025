FROM php:8.2-apache

# Copia todo el proyecto al servidor web
COPY . /var/www/html/

# 🔧 Corrige permisos para Apache
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

# Expone el puerto 80
EXPOSE 80
