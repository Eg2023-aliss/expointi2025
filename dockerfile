# Dockerfile final para PHP + Apache en Render con FPDF
FROM php:8.2-apache

# Copia todos los archivos del proyecto al contenedor
COPY . /var/www/html/

# Corrige permisos y propiedad para que Apache pueda leer/ejecutar todo
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 # Configura Apache para abrir ax_index.php primero
 && echo "DirectoryIndex ax_index.php login_index.php registro_index.php carnet_index.php index.php index.html" >> /etc/apache2/apache2.conf

EXPOSE 80
