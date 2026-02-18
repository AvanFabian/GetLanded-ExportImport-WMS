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

# Create start script inline to avoid Windows line ending issues
RUN printf '#!/bin/sh\nset -e\n\nrole=${CONTAINER_ROLE:-app}\necho "Starting container with role: $role"\n\nif [ "$role" = "app" ]; then\n    echo "Running migrations..."\n    php artisan migrate --force\n    echo "Caching config..."\n    php artisan config:cache\n    php artisan route:cache\n    php artisan view:cache\n    echo "Starting S6 Supervisor (Nginx+FPM)..."\n    exec /init\nelif [ "$role" = "queue" ]; then\n    echo "Running Queue Worker..."\n    exec php artisan queue:work --verbose --tries=3 --timeout=90\nelif [ "$role" = "scheduler" ]; then\n    echo "Running Scheduler..."\n    while [ true ]; do\n      php artisan schedule:run --verbose --no-interaction &\n      sleep 60\n    done\nelse\n    echo "Unknown role: $role"\n    exit 1\nfi\n' > /usr/local/bin/start.sh && \
    chmod +x /usr/local/bin/start.sh

# Pindahkan kepemilikan kembali ke user www-data
USER www-data

# Environment variable untuk automasi
ENV AUTORUN_ENABLED=true

# Expose port internal Nginx
EXPOSE 8080

# Set cmd to our script
CMD ["/usr/local/bin/start.sh"]