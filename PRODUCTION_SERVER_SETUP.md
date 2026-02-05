# 🚀 คู่มือการตั้งค่า Production Server

**Server:** http://203.172.184.47:9000/  
**วันที่:** _______________  
**สถานะ:** Production (ช่วงแรก)

---

## 📋 สารบัญ

1. [Quick Setup](#quick-setup)
2. [Environment Configuration](#environment-configuration)
3. [Security Settings](#security-settings)
4. [Performance Optimization](#performance-optimization)
5. [File Storage Setup](#file-storage-setup)
6. [Testing Checklist](#testing-checklist)
7. [Monitoring](#monitoring)
8. [Troubleshooting](#troubleshooting)

---

## ⚡ Quick Setup

### 1. อัพเดท Environment File

```bash
# บน production server
cd /DATA/AppData/www/nitesa

# Backup .env เดิม
cp .env .env.backup

# Copy production config
cp .env.production.server .env

# แก้ไขค่าที่จำเป็น
nano .env
```

### 2. ตั้งค่าที่สำคัญ

แก้ไขใน `.env`:

```env
APP_NAME="ระบบนิเทศการศึกษา"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://203.172.184.47:9000

# Database (ปรับตาม server จริง)
DB_DATABASE=nitesa
DB_USERNAME=tok
DB_PASSWORD=l6-lyo9N

# Mail (ปรับตาม mail server)
MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

### 3. Generate App Key (ถ้ายังไม่มี)

```bash
php artisan key:generate
```

### 4. Clear และ Cache Configuration

```bash
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. สร้าง Storage Link

```bash
php artisan storage:link
```

### 6. ตั้งค่า Permissions

```bash
# Linux/Unix
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Windows (ถ้าใช้ IIS)
# ตั้งค่า permissions ผ่าน IIS Manager
```

---

## ⚙️ Environment Configuration

### Production Settings

```env
# Application
APP_NAME="ระบบนิเทศการศึกษา"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://203.172.184.47:9000

# Logging (Production)
LOG_CHANNEL=stack
LOG_LEVEL=error  # เปลี่ยนจาก debug เป็น error

# Cache & Session (ช่วงแรกใช้ file)
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Queue (ช่วงแรกใช้ sync - ไม่ต้อง setup worker)
QUEUE_CONNECTION=sync

# File Storage
FILESYSTEM_DISK=public
```

### Mail Configuration

สำหรับช่วงแรกสามารถใช้ Gmail SMTP:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-specific-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@nitesa.go.th"
MAIL_FROM_NAME="ระบบนิเทศการศึกษา"
```

**วิธีสร้าง App Password สำหรับ Gmail:**
1. ไปที่ https://myaccount.google.com/apppasswords
2. เลือก "Mail" และ "Other (Custom name)"
3. ตั้งชื่อ "NITESA Production"
4. คัดลอก password ที่ได้มาใส่ใน `MAIL_PASSWORD`

---

## 🔐 Security Settings

### 1. Disable Debug Mode

```env
APP_DEBUG=false
```

### 2. Trust Proxies (สำหรับ Load Balancer)

ไฟล์ `app/Http/Middleware/TrustProxies.php` ตั้งค่าแล้ว:
```php
protected $proxies = '*'; // Trust all proxies
```

### 3. Session Security

แก้ไข `config/session.php`:
```php
'encrypt' => env('SESSION_ENCRYPT', false),
```

เพิ่มใน `.env`:
```env
SESSION_ENCRYPT=true  # สำหรับ production
```

### 4. HTTPS (ถ้ามี SSL)

ถ้า server มี SSL certificate:

```env
APP_URL=https://203.172.184.47:9000
```

และเพิ่มใน `config/session.php`:
```php
'secure' => env('SESSION_SECURE_COOKIE', true),
'same_site' => 'lax',
```

### 5. File Permissions

```bash
# ตั้งค่า permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```

---

## ⚡ Performance Optimization

### 1. Cache Configuration

```bash
# Cache config, routes, views
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 2. Optimize Autoloader

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Build Frontend Assets

```bash
npm install
npm run build
```

### 4. OPcache (ถ้าใช้ PHP-FPM)

แก้ไข `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

---

## 📁 File Storage Setup

### 1. สร้าง Storage Link

```bash
php artisan storage:link
```

### 2. ตรวจสอบ Symbolic Link

```bash
# Linux/Unix
ls -la public/storage

# ควรเห็น:
# public/storage -> ../storage/app/public
```

### 3. ทดสอบ File Upload

```bash
php artisan tinker
>>> Storage::disk('public')->put('test.txt', 'Hello World');
>>> Storage::disk('public')->exists('test.txt');
```

---

## ✅ Testing Checklist

### Functional Testing

- [ ] **Login/Logout**
  - [ ] Login ด้วย email/password
  - [ ] Login ด้วย Google (ถ้าใช้)
  - [ ] Logout ทำงาน

- [ ] **Dashboard**
  - [ ] แสดงข้อมูลสถิติ
  - [ ] กราฟแสดงผลถูกต้อง
  - [ ] Filter ทำงาน

- [ ] **Supervision**
  - [ ] สร้าง Supervision ใหม่
  - [ ] แก้ไข Supervision
  - [ ] Upload ไฟล์
  - [ ] Download ไฟล์
  - [ ] Workflow (Submit, Approve, Reject, Publish)

- [ ] **School Management**
  - [ ] สร้าง/แก้ไขโรงเรียน
  - [ ] Import จาก Excel

- [ ] **User Management**
  - [ ] สร้าง/แก้ไขผู้ใช้
  - [ ] กำหนด Role

- [ ] **Notifications**
  - [ ] Email ส่งได้ (ถ้า setup แล้ว)
  - [ ] In-app notifications แสดง

- [ ] **Activity Log**
  - [ ] บันทึกกิจกรรม
  - [ ] แสดง log history

### Performance Testing

- [ ] Page load time < 3 seconds
- [ ] API response time < 1 second
- [ ] File upload ทำงาน
- [ ] File download ทำงาน

### Security Testing

- [ ] ไม่แสดง error details (APP_DEBUG=false)
- [ ] CSRF protection ทำงาน
- [ ] Session timeout ทำงาน
- [ ] File permissions ถูกต้อง

---

## 📊 Monitoring

### 1. Application Logs

```bash
# ดู logs
tail -f storage/logs/laravel.log

# ดู errors เท่านั้น
tail -f storage/logs/laravel.log | grep ERROR
```

### 2. Server Resources

```bash
# CPU และ Memory
htop

# Disk Usage
df -h

# PHP-FPM Status (ถ้าใช้)
php-fpm -tt
```

### 3. Database

```bash
# เชื่อมต่อ database
mysql -u root -p nitesa

# ตรวจสอบ connections
SHOW PROCESSLIST;

# ตรวจสอบ table sizes
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'nitesa'
ORDER BY (data_length + index_length) DESC;
```

### 4. Application Status

```bash
# ตรวจสอบ application
php artisan about

# ตรวจสอบ routes
php artisan route:list

# ตรวจสอบ config
php artisan config:show app
```

---

## 🆘 Troubleshooting

### ปัญหา: 500 Internal Server Error

```bash
# 1. ตรวจสอบ logs
tail -f storage/logs/laravel.log

# 2. Clear cache
php artisan optimize:clear

# 3. ตรวจสอบ permissions
ls -la storage bootstrap/cache

# 4. ตรวจสอบ .env
php artisan config:show app
```

### ปัญหา: File Upload ไม่ทำงาน

```bash
# 1. ตรวจสอบ storage link
ls -la public/storage

# 2. ตรวจสอบ permissions
chmod -R 775 storage

# 3. ตรวจสอบ disk space
df -h

# 4. ตรวจสอบ php.ini
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

### ปัญหา: Session ไม่ทำงาน

```bash
# 1. ตรวจสอบ session driver
php artisan config:show session

# 2. Clear session files
rm -rf storage/framework/sessions/*

# 3. ตรวจสอบ permissions
chmod -R 775 storage/framework/sessions
```

### ปัญหา: Email ไม่ส่ง

```bash
# 1. ทดสอบ mail configuration
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });

# 2. ตรวจสอบ mail config
php artisan config:show mail

# 3. ตรวจสอบ queue (ถ้าใช้)
php artisan queue:work --once
```

### ปัญหา: Database Connection Error

```bash
# 1. ทดสอบ connection
php artisan tinker
>>> DB::connection()->getPdo();

# 2. ตรวจสอบ .env
cat .env | grep DB_

# 3. ตรวจสอบ MySQL
mysql -u root -p -e "SHOW DATABASES;"
```

---

## 🔄 Maintenance Tasks

### Daily

- [ ] ตรวจสอบ logs
- [ ] ตรวจสอบ disk space
- [ ] ตรวจสอบ application status

### Weekly

- [ ] Backup database
- [ ] Backup files
- [ ] ตรวจสอบ performance
- [ ] Clear old logs

### Monthly

- [ ] Review security
- [ ] Update dependencies (ถ้าจำเป็น)
- [ ] Performance optimization
- [ ] Documentation update

---

## 📝 Important Notes

### สำหรับช่วงแรก (Initial Production)

1. **Queue:** ใช้ `sync` (ไม่ต้อง setup worker)
   - เปลี่ยนเป็น `database` หรือ `redis` เมื่อพร้อม

2. **Cache:** ใช้ `file` (ไม่ต้อง setup Redis)
   - เปลี่ยนเป็น `redis` เมื่อต้องการ performance ดีขึ้น

3. **Session:** ใช้ `file`
   - เปลี่ยนเป็น `redis` หรือ `database` เมื่อพร้อม

4. **Mail:** ใช้ Gmail SMTP (ง่าย)
   - เปลี่ยนเป็น SendGrid หรือ AWS SES เมื่อพร้อม

5. **Debug:** ปิดแล้ว (`APP_DEBUG=false`)
   - เปิดเฉพาะเมื่อ troubleshoot

### เมื่อพร้อม Scale Up

1. Setup Redis สำหรับ Cache และ Queue
2. Setup Queue Workers (Supervisor)
3. Setup Production Mail Service
4. Setup SSL/HTTPS
5. Setup CDN สำหรับ Static Assets
6. Setup Monitoring Tools

---

## 📞 Support

หากมีปัญหาหรือข้อสงสัย:

1. ตรวจสอบ logs: `storage/logs/laravel.log`
2. ตรวจสอบ documentation: `PRODUCTION_DEPLOYMENT.md`
3. ตรวจสอบ troubleshooting section ด้านบน

---

## ✅ Deployment Checklist

### Pre-Deployment
- [ ] Backup database
- [ ] Backup files
- [ ] Review code changes
- [ ] Test locally

### Deployment
- [ ] Update .env
- [ ] Run migrations
- [ ] Build assets
- [ ] Clear cache
- [ ] Set permissions
- [ ] Test application

### Post-Deployment
- [ ] Functional testing
- [ ] Performance testing
- [ ] Security testing
- [ ] Monitor logs
- [ ] Document issues

---

**🎉 Production Server พร้อมใช้งาน!**

**URL:** http://203.172.184.47:9000/
