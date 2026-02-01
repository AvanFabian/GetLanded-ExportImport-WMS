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

# Pindahkan kepemilikan kembali ke user www-data
USER www-data

# Environment variable untuk automasi
ENV AUTORUN_ENABLED=true

# Expose port internal Nginx
EXPOSE 8080