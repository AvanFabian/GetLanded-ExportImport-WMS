# Menggunakan image PHP 8.3 FPM Nginx yang stabil
FROM serversideup/php:8.4-fpm-nginx

# Atur direktori kerja
WORKDIR /var/www/html

# Switch ke root untuk instalasi sistem
USER root

# 1. Instal dependencies, Node.js, dan ekstensi PHP
RUN apt-get update && apt-get install -y ca-certificates curl gnupg \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs \
    && install-php-extensions gd intl

# 2. Copy file project
COPY --chown=www-data:www-data . .

# 3. Jalankan build untuk Frontend (Vite)
RUN npm install && npm run build

# 4. Jalankan build untuk Backend (Composer)
RUN composer install --no-dev --optimize-autoloader

# Create entrypoint script to handle role switching and migrations
RUN printf '#!/bin/sh\n\
set -e\n\
role=${CONTAINER_ROLE:-app}\n\
echo "Entrypoint script running for role: $role"\n\
\n\
if [ "$role" = "app" ]; then\n\
    echo "Running migrations and caching..."\n\
    php artisan migrate --force\n\
    php artisan config:cache\n\
    php artisan route:cache\n\
    php artisan view:cache\n\
fi\n\
\n\
if [ "$role" = "worker" ]; then\n\
    echo "Hijacking entrypoint for Queue Worker..."\n\
    exec php artisan queue:work --verbose --tries=3 --timeout=90\n\
elif [ "$role" = "scheduler" ]; then\n\
    echo "Hijacking entrypoint for Scheduler..."\n\
    exec php artisan schedule:work\n\
fi\n' > /etc/entrypoint.d/99-laravel-setup.sh && \
    chmod +x /etc/entrypoint.d/99-laravel-setup.sh

# Environment variable for automation (legacy)
ENV AUTORUN_ENABLED=true

# Expose port internal Nginx (only applicable for app role)
EXPOSE 8080

# Default command handled by the base image entrypoint (which will run our script in /etc/entrypoint.d)
# We do NOT override CMD anymore to avoid breaking Nginx initialization