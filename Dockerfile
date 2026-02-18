# Menggunakan image PHP 8.3 FPM Nginx yang stabil
FROM serversideup/php:8.4-fpm-nginx

# Atur direktori kerja
WORKDIR /var/www/html

# Switch ke root untuk instalasi sistem
USER root

# 1. Instal Node.js (untuk menjalankan Vite) dan ekstensi PHP
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && install-php-extensions gd intl

# 2. Copy file project
COPY --chown=www-data:www-data . .

# 3. Jalankan build untuk Frontend (Vite)
RUN npm install && npm run build

# 4. Jalankan build untuk Backend (Composer)
RUN composer install --no-dev --optimize-autoloader

# Copy start script for role switching (and fix line endings for Windows)
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh && \
    sed -i 's/\r$//' /usr/local/bin/start.sh

# Pindahkan kepemilikan kembali ke user www-data
USER www-data

# Environment variable untuk automasi
ENV AUTORUN_ENABLED=true

# Expose port internal Nginx
EXPOSE 8080

# Set cmd to our script
CMD ["/usr/local/bin/start.sh"]