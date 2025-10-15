# Dockerfile para PHP + Apache en Render
FROM php:8.2-apache

# Copia todo el proyecto al directorio web de Apache
COPY . /var/www/html/

# Corrige propietarios/permiso para que Apache (www-data) pueda leer/ejecutar
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 # Asegura que Apache busque index.php antes que index.html (opcional)
&& echo "DirectoryIndex ax_index.php login_index.php registro_index.php " >> /etc/apache2/apache2.conf

EXPOSE 80
