# Stage 1: Build Frontend Assets
FROM node:20-slim AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP Application
FROM serversideup/php:8.4-fpm-nginx

# Set working directory
WORKDIR /var/www/html

# Switch to root for system-level setup
USER root

# 1. Install PHP extensions (using the pre-installed helper in serversideup images)
RUN install-php-extensions gd intl

# 2. Copy project files
COPY --chown=www-data:www-data . .

# 3. Copy built assets from the node-builder stage
COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build

# 4. Install Backend dependencies (Composer)
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

# Environment variable for automation
ENV AUTORUN_ENABLED=true

# Expose Nginx port
EXPOSE 8080