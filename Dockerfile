# Menggunakan image PHP 8.3 FPM Nginx yang stabil
FROM serversideup/php:8.3-fpm-nginx

# Atur direktori kerja
WORKDIR /var/www/html

# Switch ke root untuk menginstal ekstensi sistem
USER root

# Instal ekstensi PHP GD (Dibutuhkan untuk Excel & QR Code)
RUN install-php-extensions gd

# Copy semua file project dengan kepemilikan user www-data
COPY --chown=www-data:www-data . .

# Instal dependencies Laravel
RUN composer install --no-dev --optimize-autoloader

# Pindahkan kepemilikan kembali ke user www-data untuk keamanan
USER www-data

# Environment variable untuk menjalankan skrip otomatis
ENV AUTORUN_ENABLED=true