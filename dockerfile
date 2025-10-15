# Dockerfile final para PHP + Apache en Render con FPDF
FROM php:8.2-apache

# Copia todos los archivos del proyecto al directorio web de Apache
COPY . /var/www/html/

# Corrige permisos para Apache (www-data) y configura el archivo de inicio
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 && echo "DirectoryIndex ax_index.php login_index.php registro_index.php carnet_index.php" >> /etc/apache2/apache2.conf

# Expone el puerto 80 para Render
EXPOSE 80
