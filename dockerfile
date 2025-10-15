# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# Copia los archivos de tu proyecto al contenedor
COPY . /var/www/html/

# Expone el puerto 80
EXPOSE 80

# Opcional: instala extensiones de PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql

