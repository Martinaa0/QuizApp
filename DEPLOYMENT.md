# Deployment Guide

This guide covers deploying Quiz App to production environments.

## Prerequisites

- Web server (Apache/Nginx)
- PHP 8.2+ with required extensions
- MySQL/PostgreSQL database
- Node.js 20+ for building frontend
- SSL certificate (recommended)

---

## Backend Deployment

### 1. Server Setup

#### Install PHP Extensions
```bash
sudo apt-get install php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip
```

#### Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Application Setup

```bash
# Clone repository
git clone <repository-url> /var/www/quizapp
cd /var/www/quizapp/backend

# Install dependencies
composer install --optimize-autoloader --no-dev

# Set permissions
sudo chown -R www-data:www-data /var/www/quizapp
sudo chmod -R 755 /var/www/quizapp
sudo chmod -R 775 /var/www/quizapp/backend/storage
sudo chmod -R 775 /var/www/quizapp/backend/bootstrap/cache
```

### 3. Environment Configuration

```bash
# Copy and edit .env
cp .env.example .env
nano .env
```

**Production .env settings:**
```env
APP_NAME="Quiz App"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quizapp_production
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com

# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

```bash
# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Web Server Configuration

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/quizapp/backend/public;

    index index.php;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/quizapp/backend/public

    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key

    <Directory /var/www/quizapp/backend/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/quizapp_error.log
    CustomLog ${APACHE_LOG_DIR}/quizapp_access.log combined
</VirtualHost>
```

### 5. Queue Worker (Optional)

For background jobs, set up queue worker:

```bash
# Install supervisor
sudo apt-get install supervisor

# Create config file
sudo nano /etc/supervisor/conf.d/quizapp-worker.conf
```

```ini
[program:quizapp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/quizapp/backend/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/quizapp/backend/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start quizapp-worker:*
```

---

## Frontend Deployment

### 1. Build for Production

```bash
cd /var/www/quizapp/frontend

# Install dependencies
npm install

# Set production API URL
echo "VITE_API_BASE_URL=https://api.yourdomain.com" > .env.production

# Build
npm run build
```

### 2. Deploy Build

#### Option A: Same Domain (Subdirectory)

```bash
# Copy build to backend public
cp -r dist/* /var/www/quizapp/backend/public/frontend/
```

Update Nginx/Apache to serve frontend files.

#### Option B: Separate Domain/Subdomain

```bash
# Copy to separate web root
cp -r dist/* /var/www/quizapp-frontend/
```

### 3. Web Server Configuration for Frontend

#### Nginx (SPA Routing)

```nginx
server {
    listen 80;
    server_name app.yourdomain.com;
    root /var/www/quizapp/frontend/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api {
        proxy_pass https://api.yourdomain.com;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

---

## Database Setup

### MySQL

```sql
CREATE DATABASE quizapp_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'quizapp_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON quizapp_production.* TO 'quizapp_user'@'localhost';
FLUSH PRIVILEGES;
```

### Run Migrations

```bash
php artisan migrate --force
```

---

## Security Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong database passwords
- [ ] Enable HTTPS/SSL
- [ ] Set secure session configuration
- [ ] Configure CORS properly
- [ ] Set up firewall rules
- [ ] Regular backups
- [ ] Keep dependencies updated
- [ ] Use environment variables for secrets
- [ ] Set proper file permissions

---

## Monitoring & Maintenance

### Logs

```bash
# Laravel logs
tail -f /var/www/quizapp/backend/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### Backup Script

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/quizapp"

# Database backup
mysqldump -u username -p database_name > $BACKUP_DIR/db_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/quizapp/backend/storage

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete
```

### Update Application

```bash
cd /var/www/quizapp

# Pull latest changes
git pull origin main

# Backend
cd backend
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Frontend
cd ../frontend
npm install
npm run build

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

---

## Performance Optimization

### Laravel Optimizations

```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### Frontend Optimizations

- Enable gzip compression
- Use CDN for static assets
- Implement lazy loading
- Optimize images
- Minify CSS/JS

### Database Optimizations

- Add indexes to frequently queried columns
- Use query caching
- Optimize slow queries

---

## Troubleshooting

### 500 Error
- Check Laravel logs: `storage/logs/laravel.log`
- Verify file permissions
- Check `.env` configuration

### CORS Issues
- Verify `SANCTUM_STATEFUL_DOMAINS` in `.env`
- Check CORS middleware configuration

### Storage Issues
- Ensure `storage:link` is created
- Check storage directory permissions
- Verify disk configuration

---

## Support

For deployment issues, check:
1. Laravel logs
2. Web server logs
3. PHP error logs
4. Database connection
