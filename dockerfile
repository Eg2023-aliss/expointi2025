FROM php:8.2-apache

# Copia el contenido de /public a la carpeta web de Apache
COPY public/ /var/www/html/

EXPOSE 80

