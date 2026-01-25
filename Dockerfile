FROM richarvey/php-fpm-laravel:latest

# Atur direktori kerja
WORKDIR /var/www/html

# Copy semua file project
COPY . .

# Konfigurasi environment untuk produksi
ENV APP_ENV=production
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1

# Install dependencies (Optional jika sudah ada vendor)
RUN composer install --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80