# Dockerfile final para PHP + Apache en Render con FPDF
FROM php:8.2-apache

# Copia todo el proyecto al directorio web de Apache
COPY . /var/www/html/

# Corrige permisos y propiedad de todos los archivos, incluyendo fpdf/
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 # Asegura que Apache busque tus archivos PHP principales por defecto
 && echo "DirectoryIndex ax_index.php login_index.php registro_index.php carnet_index.php index.php index.html" >> /etc/apache2/apache2.conf

# Expone el puerto 80
EXPOSE 80
