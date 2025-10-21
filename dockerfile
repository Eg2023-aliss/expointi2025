# Dockerfile final para PHP + Apache en Render con FPDF
FROM php:8.2-apache

# Copia todos los archivos del proyecto al directorio web de Apache
COPY . /var/www/html/

# Corrige permisos para Apache (www-data) y configura el archivo de inicio
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 && echo "<Directory /var/www/html/>" >> /etc/apache2/apache2.conf \
 && echo "    DirectoryIndex ax_index.php login_index.php registro_index.php carnet_index.php" >> /etc/apache2/apache2.conf \
 && echo "</Directory>" >> /etc/apache2/apache2.conf

# Habilita mod_rewrite (útil si usas rutas amigables)
RUN a2enmod rewrite

# Expone el puerto 80 para Render
EXPOSE 80

# ✅ Este comando mantiene Apache corriendo en primer plano
CMD ["apache2ctl", "-D", "FOREGROUND"]

# 🔧 Arreglar permisos para Render (Apache)
RUN chmod -R 755 /var/www/html \
 && find /var/www/html -type f -exec chmod 644 {} \; \
 && chown -R www-data:www-data /var/www/html \
 && echo "<Directory /var/www/html>\nAllowOverride All\nRequire all granted\n</Directory>" >> /etc/apache2/apache2.conf

