FROM php:8.2-apache

COPY . /var/www/html/

# Instala unzip
RUN apt-get update && apt-get install -y unzip \
    # Descomprime fpdf_full.zip si existe y asegura la ruta correcta
 && if [ -f /var/www/html/fpdf_full.zip ]; then \
      unzip -o /var/www/html/fpdf_full.zip -d /var/www/html/; \
      mv /var/www/html/fpdf/* /var/www/html/fpdf/; \
      rm -rf /var/www/html/__MACOSX /var/www/html/fpdf_full.zip; \
    fi \
    # Corrige permisos
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
    # Configura Apache
 && echo "DirectoryIndex ax_index.php login_index.php registro_index.php carnet_index.php index.php index.html" >> /etc/apache2/apache2.conf

EXPOSE 80
