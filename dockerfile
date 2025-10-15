# Dockerfile para PHP + Apache en Render
FROM php:8.2-apache

# Copia todo el proyecto al directorio web de Apache
COPY . /var/www/html/

# Instala unzip (necesario para descomprimir fpdf_full.zip)
RUN apt-get update && apt-get install -y unzip \
    # Descomprime FPDF si aún existe el zip
 && if [ -f /var/www/html/fpdf_full.zip ]; then unzip -o /var/www/html/fpdf_full.zip -d /var/www/html/; fi \
 && rm -f /var/www/html/fpdf_full.zip \
    # Corrige propietarios/permiso para que Apache pueda leer/ejecutar
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
    # Configura Apache para buscar tus archivos PHP principales por defecto
 && echo "DirectoryIndex ax_index.php login_index.php registro_index.php carnet_index.php index.php index.html" >> /etc/apache2/apache2.conf

# Expone el puerto 80 para Render
EXPOSE 80
