# Menggunakan image PHP 8.3 FPM Nginx yang sangat stabil untuk Laravel
FROM serversideup/php:8.3-fpm-nginx

# Atur direktori kerja
WORKDIR /var/www/html

# Switch ke root untuk pengaturan izin
USER root

# Copy semua file project
COPY --chown=www-data:www-data . .

# Install dependencies (Tanpa dev dependencies)
RUN composer install --no-dev --optimize-autoloader

# Pindahkan kepemilikan kembali ke user www-data
USER www-data

# Environment variable default (Akan ditimpa oleh Coolify)
ENV AUTORUN_ENABLED=true