# 🚀 คู่มือ Production Deployment

**ระบบ:** NITESA - ระบบนิเทศ ติดตาม และประเมินผลการศึกษา  
**Framework:** Laravel 10.x  
**PHP Version:** 8.1+  
**Database:** MySQL 8.0+

---

## 📋 สารบัญ

1. [Prerequisites](#prerequisites)
2. [Server Requirements](#server-requirements)
3. [Pre-Deployment Checklist](#pre-deployment-checklist)
4. [Environment Configuration](#environment-configuration)
5. [Database Setup](#database-setup)
6. [File Storage Setup](#file-storage-setup)
7. [Queue Worker Setup](#queue-worker-setup)
8. [Web Server Configuration](#web-server-configuration)
9. [SSL/HTTPS Setup](#sslhttps-setup)
10. [Performance Optimization](#performance-optimization)
11. [Security Hardening](#security-hardening)
12. [Monitoring & Logging](#monitoring--logging)
13. [Backup Strategy](#backup-strategy)
14. [Deployment Scripts](#deployment-scripts)
15. [Rollback Procedures](#rollback-procedures)
16. [Post-Deployment Testing](#post-deployment-testing)
17. [Troubleshooting](#troubleshooting)

---

## 📦 Prerequisites

### Software Requirements

- ✅ **PHP 8.1+** (แนะนำ 8.2 หรือ 8.3)
- ✅ **Composer 2.x**
- ✅ **Node.js 18+** และ **npm**
- ✅ **MySQL 8.0+** หรือ **MariaDB 10.6+**
- ✅ **Nginx 1.20+** หรือ **Apache 2.4+**
- ✅ **Redis** (แนะนำสำหรับ Cache และ Queue)
- ✅ **Supervisor** (สำหรับ Queue Workers)
- ✅ **SSL Certificate** (Let's Encrypt)

### PHP Extensions

```bash
php -m | grep -E "pdo|mbstring|xml|curl|zip|gd|fileinfo|openssl|tokenizer|json"
```

**Required Extensions:**
- `pdo_mysql`
- `mbstring`
- `xml`
- `curl`
- `zip`
- `gd` หรือ `imagick`
- `fileinfo`
- `openssl`
- `tokenizer`
- `json`
- `bcmath`

---

## 🖥️ Server Requirements

### Minimum (สำหรับ Production ขนาดเล็ก)

- **CPU:** 2 cores
- **RAM:** 2GB
- **Storage:** 20GB SSD
- **Bandwidth:** 100Mbps

### Recommended (สำหรับ Production ปกติ)

- **CPU:** 4 cores
- **RAM:** 4GB
- **Storage:** 50GB SSD
- **Bandwidth:** 1Gbps

### High Traffic (สำหรับ Production ขนาดใหญ่)

- **CPU:** 8+ cores
- **RAM:** 8GB+
- **Storage:** 100GB+ SSD
- **Bandwidth:** 10Gbps
- **Redis** สำหรับ Cache
- **CDN** สำหรับ Static Assets

---

## ✅ Pre-Deployment Checklist

### Code Quality

- [ ] Code Quality Score ≥ 95%
- [ ] All tests passing
- [ ] No debug code (`dd()`, `dump()`, etc.)
- [ ] Environment variables documented
- [ ] `.env.example` updated

### Security

- [ ] `.env` file not in Git
- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials secure
- [ ] File permissions correct
- [ ] SSL/HTTPS configured

### Performance

- [ ] Assets optimized (`npm run build`)
- [ ] Cache configured (Redis recommended)
- [ ] Database indexes added
- [ ] Queue workers configured
- [ ] CDN configured (optional)

### Documentation

- [ ] Deployment guide reviewed
- [ ] Backup procedures documented
- [ ] Rollback procedures documented
- [ ] Monitoring setup documented

---

## ⚙️ Environment Configuration

### 1. สร้างไฟล์ `.env` สำหรับ Production

```bash
cp .env.example .env
nano .env
```

### 2. ตั้งค่า Environment Variables

```env
# Application
APP_NAME="ระบบนิเทศ NITESA"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://nitesa.go.th

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nitesa_production
DB_USERNAME=nitesa_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Cache & Session (แนะนำใช้ Redis)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Queue (แนะนำใช้ Redis)
QUEUE_CONNECTION=redis

# File Storage
FILESYSTEM_DISK=public

# Mail Configuration (Production)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=YOUR_SENDGRID_API_KEY
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@nitesa.go.th"
MAIL_FROM_NAME="ระบบนิเทศ NITESA"

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Google OAuth (ถ้าใช้)
GOOGLE_CLIENT_ID=YOUR_CLIENT_ID
GOOGLE_CLIENT_SECRET=YOUR_CLIENT_SECRET
GOOGLE_REDIRECT_URI=https://nitesa.go.th/auth/google/callback

# AWS S3 (ถ้าใช้)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=nitesa-storage
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Verify Configuration

```bash
php artisan config:cache
php artisan config:show app
```

---

## 🗄️ Database Setup

### 1. สร้าง Database และ User

```sql
-- เข้าสู่ MySQL
mysql -u root -p

-- สร้าง Database
CREATE DATABASE nitesa_production 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- สร้าง User
CREATE USER 'nitesa_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';

-- Grant Permissions
GRANT ALL PRIVILEGES ON nitesa_production.* TO 'nitesa_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Run Migrations

```bash
php artisan migrate --force
```

### 3. (Optional) Run Seeders

```bash
# เฉพาะข้อมูลพื้นฐาน (ไม่แนะนำ seed users ใน production)
php artisan db:seed --class=DatabaseSeeder
```

### 4. Create Database Indexes

```bash
# ตรวจสอบว่า migration สำหรับ indexes รันแล้ว
php artisan migrate:status
```

---

## 📁 File Storage Setup

### 1. สร้าง Storage Link

```bash
php artisan storage:link
```

### 2. ตั้งค่า Permissions

```bash
# ตั้งค่า ownership
sudo chown -R www-data:www-data storage bootstrap/cache

# ตั้งค่า permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 755 public
```

### 3. ตรวจสอบ File Storage

```bash
# ตรวจสอบ symbolic link
ls -la public/storage

# ทดสอบอัพโหลดไฟล์
php artisan tinker
>>> Storage::disk('public')->put('test.txt', 'Hello World');
>>> Storage::disk('public')->exists('test.txt');
```

---

## 🔄 Queue Worker Setup

### 1. ตั้งค่า Supervisor

สร้างไฟล์ `/etc/supervisor/conf.d/nitesa-worker.conf`:

```ini
[program:nitesa-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/nitesa2/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/nitesa2/storage/logs/worker.log
stopwaitsecs=3600
```

### 2. Reload Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start nitesa-worker:*
```

### 3. ตรวจสอบ Queue Worker

```bash
sudo supervisorctl status
```

### 4. ตรวจสอบ Queue Jobs

```bash
php artisan queue:work --once
```

---

## 🌐 Web Server Configuration

### Nginx Configuration

สร้างไฟล์ `/etc/nginx/sites-available/nitesa`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name nitesa.go.th www.nitesa.go.th;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name nitesa.go.th www.nitesa.go.th;
    
    root /var/www/nitesa2/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/nitesa.go.th/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/nitesa.go.th/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # Logging
    access_log /var/log/nginx/nitesa-access.log;
    error_log /var/log/nginx/nitesa-error.log;
    
    # Max Upload Size
    client_max_body_size 50M;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Cache Static Assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Enable Site

```bash
sudo ln -s /etc/nginx/sites-available/nitesa /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Apache Configuration (Alternative)

สร้างไฟล์ `/etc/apache2/sites-available/nitesa.conf`:

```apache
<VirtualHost *:80>
    ServerName nitesa.go.th
    ServerAlias www.nitesa.go.th
    Redirect permanent / https://nitesa.go.th/
</VirtualHost>

<VirtualHost *:443>
    ServerName nitesa.go.th
    ServerAlias www.nitesa.go.th
    DocumentRoot /var/www/nitesa2/public
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/nitesa.go.th/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/nitesa.go.th/privkey.pem
    
    <Directory /var/www/nitesa2/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/nitesa-error.log
    CustomLog ${APACHE_LOG_DIR}/nitesa-access.log combined
</VirtualHost>
```

---

## 🔒 SSL/HTTPS Setup

### ใช้ Let's Encrypt (Certbot)

```bash
# ติดตั้ง Certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx

# ขอ SSL Certificate
sudo certbot --nginx -d nitesa.go.th -d www.nitesa.go.th

# Auto-renewal (ตั้งค่าแล้วอัตโนมัติ)
sudo certbot renew --dry-run
```

### ตรวจสอบ SSL

```bash
# ตรวจสอบ SSL Certificate
openssl s_client -connect nitesa.go.th:443 -servername nitesa.go.th

# ตรวจสอบ SSL Rating
# ไปที่: https://www.ssllabs.com/ssltest/analyze.html?d=nitesa.go.th
```

---

## ⚡ Performance Optimization

### 1. Optimize Autoloader

```bash
composer install --optimize-autoloader --no-dev
```

### 2. Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3. Optimize Composer

```bash
composer dump-autoload --optimize
```

### 4. Enable OPcache

แก้ไข `/etc/php/8.2/fpm/php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

```bash
sudo systemctl restart php8.2-fpm
```

### 5. Redis Configuration

แก้ไข `/etc/redis/redis.conf`:

```conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

---

## 🔐 Security Hardening

### 1. File Permissions

```bash
# ตั้งค่า ownership
sudo chown -R www-data:www-data /var/www/nitesa2

# ตั้งค่า permissions
find /var/www/nitesa2 -type f -exec chmod 644 {} \;
find /var/www/nitesa2 -type d -exec chmod 755 {} \;
chmod -R 775 /var/www/nitesa2/storage
chmod -R 775 /var/www/nitesa2/bootstrap/cache
```

### 2. Hide Server Information

แก้ไข `config/app.php`:

```php
'debug' => env('APP_DEBUG', false),
```

แก้ไข `.env`:

```env
APP_DEBUG=false
```

### 3. Disable Directory Listing

**Nginx:**
```nginx
autoindex off;
```

**Apache:**
```apache
Options -Indexes
```

### 4. Rate Limiting

แก้ไข `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \Illuminate\Routing\Middleware\ThrottleRequests::class . ':60,1',
    ],
];
```

### 5. CSRF Protection

ตรวจสอบว่า CSRF middleware เปิดอยู่:

```php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        // ...
    ],
];
```

### 6. SQL Injection Prevention

- ✅ ใช้ Eloquent ORM (ป้องกันอัตโนมัติ)
- ✅ ใช้ Query Builder parameters
- ✅ ตรวจสอบ input validation

### 7. XSS Protection

- ✅ Blade templates escape อัตโนมัติ
- ✅ ใช้ `{!! !!}` เฉพาะเมื่อจำเป็น
- ✅ Sanitize user input

---

## 📊 Monitoring & Logging

### 1. Laravel Logging

ตรวจสอบ logs:

```bash
tail -f storage/logs/laravel.log
```

### 2. Queue Monitoring

```bash
# ตรวจสอบ queue status
php artisan queue:monitor redis

# ตรวจสอบ failed jobs
php artisan queue:failed
```

### 3. System Monitoring

**ใช้ tools:**
- **New Relic** - Application Performance Monitoring
- **Sentry** - Error Tracking
- **Laravel Telescope** - Debug Tool (เฉพาะ development)
- **Laravel Pulse** - Real-time Application Monitoring

### 4. Server Monitoring

```bash
# CPU และ Memory
htop

# Disk Usage
df -h

# Network
iftop
```

---

## 💾 Backup Strategy

### 1. Database Backup Script

สร้างไฟล์ `/usr/local/bin/backup-nitesa-db.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/nitesa"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="nitesa_production"
DB_USER="nitesa_user"
DB_PASS="YOUR_PASSWORD"

mkdir -p $BACKUP_DIR

# Backup Database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete

# Backup Files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/nitesa2/storage/app

# Keep only last 30 days
find $BACKUP_DIR -name "files_*.tar.gz" -mtime +30 -delete
```

```bash
chmod +x /usr/local/bin/backup-nitesa-db.sh
```

### 2. Cron Job สำหรับ Backup

```bash
# แก้ไข crontab
crontab -e

# เพิ่มบรรทัดนี้ (backup ทุกวันเวลา 2:00 AM)
0 2 * * * /usr/local/bin/backup-nitesa-db.sh
```

### 3. Manual Backup

```bash
# Database
mysqldump -u nitesa_user -p nitesa_production > backup.sql

# Files
tar -czf storage-backup.tar.gz storage/app
```

---

## 🚀 Deployment Scripts

### 1. Deployment Script

สร้างไฟล์ `deploy.sh`:

```bash
#!/bin/bash
set -e

echo "🚀 Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart services
sudo supervisorctl restart nitesa-worker:*
sudo systemctl reload php8.2-fpm

echo "✅ Deployment completed!"
```

```bash
chmod +x deploy.sh
```

### 2. Quick Deploy Script

สร้างไฟล์ `quick-deploy.sh`:

```bash
#!/bin/bash
git pull
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan optimize
sudo supervisorctl restart nitesa-worker:*
```

---

## 🔄 Rollback Procedures

### 1. Code Rollback

```bash
# ดู commit history
git log --oneline

# Rollback to previous commit
git reset --hard HEAD~1

# หรือ rollback to specific commit
git reset --hard <commit-hash>

# Rebuild assets
npm run build

# Clear cache
php artisan optimize:clear
php artisan optimize
```

### 2. Database Rollback

```bash
# ดู migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Rollback specific steps
php artisan migrate:rollback --step=3
```

### 3. Restore from Backup

```bash
# Restore Database
mysql -u nitesa_user -p nitesa_production < backup.sql

# Restore Files
tar -xzf storage-backup.tar.gz -C /var/www/nitesa2/
```

---

## ✅ Post-Deployment Testing

### 1. Functional Testing

- [ ] Login/Logout ทำงาน
- [ ] Dashboard แสดงข้อมูล
- [ ] สร้าง/แก้ไข Supervision
- [ ] Upload ไฟล์
- [ ] Download ไฟล์
- [ ] ส่ง Email notification
- [ ] Queue jobs ทำงาน
- [ ] API endpoints ทำงาน

### 2. Performance Testing

```bash
# Load testing (ใช้ Apache Bench)
ab -n 1000 -c 10 https://nitesa.go.th/

# หรือใช้ wrk
wrk -t4 -c100 -d30s https://nitesa.go.th/
```

### 3. Security Testing

- [ ] SSL Certificate ถูกต้อง
- [ ] HTTPS redirect ทำงาน
- [ ] Security headers ตั้งค่าแล้ว
- [ ] CSRF protection ทำงาน
- [ ] SQL injection protection
- [ ] XSS protection

### 4. Monitoring

- [ ] Logs ถูกเขียน
- [ ] Queue workers ทำงาน
- [ ] Cache ทำงาน
- [ ] Database connections ถูกต้อง

---

## 🆘 Troubleshooting

### ปัญหาที่พบบ่อย

#### 1. 500 Internal Server Error

```bash
# ตรวจสอบ logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# ตรวจสอบ permissions
ls -la storage bootstrap/cache

# Clear cache
php artisan optimize:clear
```

#### 2. Queue Jobs ไม่ทำงาน

```bash
# ตรวจสอบ supervisor
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart nitesa-worker:*

# ตรวจสอบ queue
php artisan queue:work --once
```

#### 3. File Upload ไม่ทำงาน

```bash
# ตรวจสอบ permissions
ls -la storage/app/public

# ตรวจสอบ symbolic link
ls -la public/storage

# ตรวจสอบ disk space
df -h
```

#### 4. Database Connection Error

```bash
# ทดสอบ connection
php artisan tinker
>>> DB::connection()->getPdo();

# ตรวจสอบ .env
cat .env | grep DB_
```

#### 5. Cache Issues

```bash
# Clear all cache
php artisan optimize:clear

# Rebuild cache
php artisan optimize
```

---

## 📚 เอกสารเพิ่มเติม

- [Laravel Deployment](https://laravel.com/docs/10.x/deployment)
- [Laravel Optimization](https://laravel.com/docs/10.x/optimization)
- [Nginx Configuration](https://www.nginx.com/resources/wiki/start/topics/examples/phpfcgi/)
- [Let's Encrypt](https://letsencrypt.org/)
- [Supervisor Configuration](http://supervisord.org/configuration.html)

---

## ✅ Deployment Checklist

### Pre-Deployment
- [ ] Code tested และผ่าน
- [ ] Environment variables ตั้งค่าแล้ว
- [ ] Database backup สร้างแล้ว
- [ ] SSL certificate ติดตั้งแล้ว
- [ ] Server requirements ครบ

### Deployment
- [ ] Code deployed
- [ ] Dependencies installed
- [ ] Database migrated
- [ ] Assets built
- [ ] Cache optimized
- [ ] Permissions set
- [ ] Queue workers started

### Post-Deployment
- [ ] Functional testing ผ่าน
- [ ] Performance testing ผ่าน
- [ ] Security testing ผ่าน
- [ ] Monitoring setup
- [ ] Backup verified
- [ ] Documentation updated

---

**🎉 Production Deployment พร้อมใช้งาน!**
