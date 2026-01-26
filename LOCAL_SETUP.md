# 🚀 คู่มือการรันระบบใน Local Environment

## 📋 Prerequisites

ตรวจสอบว่ามี tools ต่อไปนี้ติดตั้งแล้ว:
- ✅ PHP 8.1+ (ตรวจสอบแล้ว: PHP 8.1.10)
- ✅ Composer (ตรวจสอบแล้ว: Composer 2.8.10)
- ✅ Node.js & npm (ตรวจสอบแล้ว: Node.js v22.14.0)
- ✅ MySQL/MariaDB (ผ่าน Laragon)
- ✅ Git

---

## 🔧 ขั้นตอนการตั้งค่า

### 1. ติดตั้ง Dependencies

#### PHP Dependencies (Composer):
```bash
cd d:\laragon\www\nitesa2
composer install
```

#### JavaScript Dependencies (npm):
```bash
npm install
```

---

### 2. ตั้งค่า Environment

#### ตรวจสอบไฟล์ `.env`:
```bash
# ตรวจสอบว่ามี .env หรือไม่
# ถ้าไม่มี ให้คัดลอกจาก .env.example
copy .env.example .env
```

#### ตั้งค่า Database ใน `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nitesa
DB_USERNAME=root
DB_PASSWORD=
```

#### สร้าง Application Key (ถ้ายังไม่มี):
```bash
php artisan key:generate
```

---

### 3. ตั้งค่า Database

#### สร้าง Database:
```sql
-- เปิด MySQL ใน Laragon
CREATE DATABASE nitesa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Run Migrations:
```bash
php artisan migrate
```

#### (Optional) Run Seeders:
```bash
php artisan db:seed
```

---

### 4. ตั้งค่า Storage Link

```bash
php artisan storage:link
```

---

### 5. Build Frontend Assets

#### Development Mode (Hot Reload):
```bash
npm run dev
```

#### Production Mode:
```bash
npm run build
```

---

### 6. เริ่มต้น Development Server

#### วิธีที่ 1: ใช้ Laravel Artisan Serve
```bash
php artisan serve
```
ระบบจะรันที่: `http://localhost:8000`

#### วิธีที่ 2: ใช้ Laragon Built-in Server
- เปิด Laragon
- คลิก "Start All"
- เข้าถึงผ่าน: `http://nitesa2.test` (หรือตามที่ตั้งค่าใน Laragon)

---

## 🎯 Quick Start Script

สร้างไฟล์ `start-local.bat` สำหรับ Windows:

```batch
@echo off
echo Starting NITESA Local Development...

cd /d d:\laragon\www\nitesa2

echo [1/5] Installing PHP dependencies...
call composer install --no-interaction

echo [2/5] Installing Node dependencies...
call npm install

echo [3/5] Generating application key...
php artisan key:generate --force

echo [4/5] Running migrations...
php artisan migrate --force

echo [5/5] Creating storage link...
php artisan storage:link

echo.
echo ✅ Setup complete!
echo.
echo Starting development server...
echo Open: http://localhost:8000
echo.
php artisan serve
```

---

## 📝 คำสั่งที่ใช้บ่อย

### Development:
```bash
# Start development server
php artisan serve

# Start Vite dev server (Hot Reload)
npm run dev

# Watch for changes
npm run dev -- --watch
```

### Database:
```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (drop all tables)
php artisan migrate:fresh

# Seed database
php artisan db:seed
```

### Cache:
```bash
# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Queue (ถ้าใช้):
```bash
# Start queue worker
php artisan queue:work

# Monitor queue
php artisan queue:monitor
```

---

## 🔍 Troubleshooting

### ปัญหา: Composer install ล้มเหลว

**แก้ไข:**
```bash
# Clear composer cache
composer clear-cache

# Update composer
composer self-update

# Install again
composer install --no-interaction
```

### ปัญหา: npm install ล้มเหลว

**แก้ไข:**
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and package-lock.json
rmdir /s /q node_modules
del package-lock.json

# Install again
npm install
```

### ปัญหา: Database Connection Error

**ตรวจสอบ:**
1. MySQL service ทำงานใน Laragon
2. Database name ใน `.env` ถูกต้อง
3. Username/Password ถูกต้อง
4. Port ถูกต้อง (default: 3306)

**แก้ไข:**
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### ปัญหา: Storage Link ไม่ทำงาน

**แก้ไข:**
```bash
# ลบ link เก่า (ถ้ามี)
rmdir public\storage

# สร้างใหม่
php artisan storage:link

# ตรวจสอบ
dir public\storage
```

### ปัญหา: Vite ไม่ทำงาน

**แก้ไข:**
```bash
# Clear Vite cache
rmdir /s /q node_modules\.vite

# Rebuild
npm run build
npm run dev
```

### ปัญหา: Permission Denied

**แก้ไข (Windows):**
```bash
# ตั้งค่า permissions สำหรับ storage
icacls storage /grant Users:F /T
icacls bootstrap\cache /grant Users:F /T
```

---

## 🌐 URLs

### Local Development:
- **Main App**: `http://localhost:8000` หรือ `http://nitesa2.test`
- **Mailpit** (Email Testing): `http://localhost:8025`

### Default Routes:
- Login: `http://localhost:8000/login`
- Dashboard: `http://localhost:8000/dashboard`
- Import: `http://localhost:8000/import`

---

## 📦 Default Credentials

ถ้ามี Seeder:
```php
// ตรวจสอบใน database/seeders/DatabaseSeeder.php
// หรือสร้าง user ใหม่:
php artisan tinker
>>> User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('password'), 'role' => 'ADMIN']);
```

---

## ✅ Checklist

ก่อนรันระบบ ตรวจสอบ:

- [ ] `composer install` ทำงานสำเร็จ
- [ ] `npm install` ทำงานสำเร็จ
- [ ] `.env` ตั้งค่าถูกต้อง
- [ ] Database สร้างแล้ว
- [ ] `php artisan migrate` ทำงานสำเร็จ
- [ ] `php artisan storage:link` ทำงานสำเร็จ
- [ ] `php artisan key:generate` ทำงานสำเร็จ
- [ ] MySQL service ทำงานใน Laragon
- [ ] Port 8000 ไม่ถูกใช้งาน (สำหรับ artisan serve)

---

## 🚀 Start Development

### Terminal 1: Laravel Server
```bash
cd d:\laragon\www\nitesa2
php artisan serve
```

### Terminal 2: Vite Dev Server (Hot Reload)
```bash
cd d:\laragon\www\nitesa2
npm run dev
```

### Terminal 3: Queue Worker (ถ้าใช้ Queue)
```bash
cd d:\laragon\www\nitesa2
php artisan queue:work
```

---

## 📚 เอกสารเพิ่มเติม

- [Laravel Documentation](https://laravel.com/docs/10.x)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Vite Documentation](https://vitejs.dev/)

---

**🎉 พร้อมรันระบบใน Local แล้ว!**
