FROM php:8.2-apache

# Instalar extensiones necesarias para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pgsql pdo pdo_pgsql

# Copiar los archivos al directorio de Apache
COPY . /var/www/html/

# Exponer el puerto 80
EXPOSE 80
