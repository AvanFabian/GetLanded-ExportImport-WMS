# Menggunakan image PHP 8.3 FPM Nginx yang stabil
FROM serversideup/php:8.3-fpm-nginx

# Atur direktori kerja
WORKDIR /var/www/html

# Switch ke root untuk menginstal ekstensi sistem
USER root

# Instal ekstensi PHP GD dan INTL (Dibutuhkan untuk Excel, QR Code, dan Formating Angka)
RUN install-php-extensions gd intl

# Copy semua file project dengan kepemilikan user www-data
COPY --chown=www-data:www-data . .

# Instal dependencies Laravel
RUN composer install --no-dev --optimize-autoloader

# Pindahkan kepemilikan kembali ke user www-data
USER www-data

# Environment variable untuk menjalankan skrip otomatis
ENV AUTORUN_ENABLED=true

# Tambahkan ini di bagian bawah Dockerfile kamu
EXPOSE 8080