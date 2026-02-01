# 🚀 AgroWMS Setup Guide

> **Complete installation guide for VPS and Coolify deployment.**

---

## 📌 Quick Reference

### Demo Credentials (All passwords: `demo1234`)

| Role | Email | Access |
|------|-------|--------|
| **Owner/Admin** | `owner@avandigital.id` | Full system access |
| **Manager** | `manager@avandigital.id` | Operations management |
| **Staff** | `staff@avandigital.id` | Daily operations |
| **Viewer** | `viewer@avandigital.id` | Read-only access |

> All users belong to **AVANDIGITAL** tenant (Company ID: 1).

---

## 🖥️ Requirements

- **PHP**: 8.2+ with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, XML, GD, ZIP
- **MySQL**: 8.0+ or MariaDB 10.5+
- **Node.js**: 18+ (LTS recommended)
- **Composer**: 2.0+
- **Python 3.x + Pandas**: (Optional) Required only for generating stress test data (`pip install pandas openpyxl`)
- **Redis**: Optional (for caching/queues)

---

## ☁️ Deploy with Coolify (Recommended)

### Step 1: Create New Service

1. Go to Coolify Dashboard → **New Resource** → **Laravel**
2. Connect your Git repository
3. Set **Branch**: `staging` (or `main` for production)

### Step 2: Environment Variables

In Coolify's **Environment Variables** section, configure:

```env
# App Configuration
APP_NAME="AgroWMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (use Coolify's MySQL service)
DB_CONNECTION=mysql
DB_HOST=mysql                    # Coolify service name
DB_PORT=3306
DB_DATABASE=agrowms
DB_USERNAME=agrowms
DB_PASSWORD=your_secure_password

# Security
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Logging
LOG_STACK=daily,security
LOG_LEVEL=error
LOG_DAILY_DAYS=30

# Queue (optional)
QUEUE_CONNECTION=database

# Redis (if using Coolify Redis service)
CACHE_DRIVER=redis
REDIS_HOST=redis
```

### Step 3: Build Configuration

**Build Command:**
```bash
composer install --no-dev --optimize-autoloader && \
npm ci && npm run build
```

**Start Command:**
```bash
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
php artisan migrate --force && \
php artisan storage:link
```

### Step 4: Deploy & Seed Demo Data

After first deployment, run via Coolify terminal:
```bash
php artisan db:seed --class=PermissionSeeder --force
php artisan db:seed --class=UserSeeder --force
```

---

## 🖥️ Manual VPS Deployment

### Step 1: Server Setup (Ubuntu 22.04+)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-gd

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install MySQL
sudo apt install -y mysql-server
```

### Step 2: Clone & Configure

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/YourOrg/AgroWMS.git agrowms
sudo chown -R $USER:www-data agrowms
cd agrowms

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Configure environment
cp .env.example .env
nano .env  # Edit database credentials

# Setup application
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan db:seed --class=PermissionSeeder --force
php artisan db:seed --class=DemoUserSeeder --force

# Set permissions
sudo chown -R www-data:www-data /var/www/agrowms
sudo chmod -R 755 /var/www/agrowms
sudo chmod -R 775 storage bootstrap/cache
```

### Step 3: Nginx Configuration

Create `/etc/nginx/sites-available/agrowms`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/agrowms/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable and restart:
```bash
sudo ln -s /etc/nginx/sites-available/agrowms /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### Step 4: SSL Certificate

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

---

## 🔧 Post-Deployment

### Optimize Performance

```bash
php artisan optimize       # Cache config, routes, views
php artisan view:cache
php artisan event:cache
composer dump-autoload --optimize
```

### Clear Cache (if issues)

```bash
php artisan optimize:clear
```

### Maintenance Mode

```bash
php artisan down           # Enable
php artisan up             # Disable
```

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| **500 Error** | `chmod -R 775 storage bootstrap/cache` |
| **404 on routes** | Check document root points to `/public` |
| **Assets not loading** | Run `npm run build` |
| **Database error** | Verify `.env` credentials, run `php artisan migrate:status` |
| **Session issues** | Ensure `SESSION_DRIVER=database` and tables migrated |

### View Logs

```bash
tail -f storage/logs/laravel.log      # Application logs
tail -f storage/logs/security.log     # Security events
```

---

## ✅ Go-Live Checklist

- [ ] `APP_DEBUG=false` 
- [ ] `APP_ENV=production`
- [ ] HTTPS enabled
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] Demo users created
- [ ] Backups configured
- [ ] Monitoring active

---

## 📚 Additional Resources

- [Architecture Guide](ARCHITECTURE.md) - System design patterns
- [Business Logic](BUSINESS_LOGIC.md) - Workflows and rules
- [Contributing](CONTRIBUTING.md) - How to contribute
- [Changelog](CHANGELOG.md) - Version history

---

**Developer:** Avan Digital Consultant  
**Support:** support@avandigital.id
