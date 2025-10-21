# 🚀 Dockerfile final para PHP + Apache + PostgreSQL (Render)
FROM php:8.2-apache

# 🧩 Instalar dependencias necesarias para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql

# Copia todos los archivos del proyecto al directorio web de Apache
COPY . /var/www/html/

# 🔧 Configura permisos y acceso para Apache
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 && find /var/www/html -type f -exec chmod 644 {} \; \
 && echo "<Directory /var/www/html/>" >> /etc/apache2/apache2.conf \
 && echo "    AllowOverride All" >> /etc/apache2/apache2.conf \
 && echo "    Require all granted" >> /etc/apache2/apache2.conf \
 && echo "    DirectoryIndex ax_index.php login_index.php registro_index.php carnet_index.php login.php index.html" >> /etc/apache2/apache2.conf \
 && echo "</Directory>" >> /etc/apache2/apache2.conf

# 🔄 Habilita mod_rewrite
RUN a2enmod rewrite

# 📦 Expone el puerto 80
EXPOSE 80

# ✅ Mantiene Apache corriendo en primer plano
CMD ["apache2ctl", "-D", "FOREGROUND"]
