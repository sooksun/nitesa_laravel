# 🚀 Quick Start - รันระบบ NITESA ใน Local

## ✅ สถานะระบบ

- ✅ PHP 8.1.10
- ✅ Composer 2.8.10
- ✅ Node.js v22.14.0
- ✅ Laravel Framework 10.50.0
- ✅ Composer dependencies ติดตั้งแล้ว
- ✅ Node dependencies ติดตั้งแล้ว

---

## 🎯 วิธีรันระบบ (3 ขั้นตอน)

### วิธีที่ 1: ใช้ Batch Scripts (แนะนำสำหรับ Windows)

#### Terminal 1: เริ่ม Laravel Server
```bash
# Double-click หรือรัน:
start-local.bat
```

ระบบจะ:
- ตรวจสอบ dependencies
- สร้าง storage link (ถ้ายังไม่มี)
- Run migrations (ถ้ายังไม่รัน)
- เริ่ม Laravel server ที่ `http://localhost:8000`

#### Terminal 2: เริ่ม Vite Dev Server (Hot Reload)
```bash
# Double-click หรือรัน:
start-vite.bat
```

---

### วิธีที่ 2: รันด้วยคำสั่งเอง

#### Terminal 1: Laravel Server
```bash
cd d:\laragon\www\nitesa2
php artisan serve
```

#### Terminal 2: Vite Dev Server
```bash
cd d:\laragon\www\nitesa2
npm run dev
```

---

## 🔧 การตั้งค่าเบื้องต้น (ครั้งแรก)

### 1. ตรวจสอบ Database

เปิด Laragon → Start All → ตรวจสอบ MySQL ทำงาน

สร้าง Database (ถ้ายังไม่มี):
```sql
CREATE DATABASE nitesa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. ตรวจสอบ .env

ตรวจสอบใน `.env`:
```env
DB_DATABASE=nitesa
DB_USERNAME=root
DB_PASSWORD=
APP_URL=http://localhost:8000
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. สร้าง Storage Link

```bash
php artisan storage:link
```

---

## 🌐 URLs

เมื่อรันระบบแล้ว:

- **Main App**: http://localhost:8000
- **Login**: http://localhost:8000/login
- **Dashboard**: http://localhost:8000/dashboard
- **Mailpit** (Email Testing): http://localhost:8025

---

## 📝 คำสั่งที่ใช้บ่อย

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate

# Build assets (Production)
npm run build

# Start queue worker (ถ้าใช้ Queue)
php artisan queue:work
```

---

## ⚠️ Troubleshooting

### ปัญหา: Port 8000 ถูกใช้งาน

**แก้ไข:**
```bash
# ใช้ port อื่น
php artisan serve --port=8001
```

### ปัญหา: Database Connection Error

**ตรวจสอบ:**
1. Laragon → Start All
2. ตรวจสอบ `.env` DB settings
3. สร้าง database `nitesa`

### ปัญหา: Vite ไม่ทำงาน

**แก้ไข:**
```bash
npm run build
npm run dev
```

---

## ✅ Checklist

ก่อนรัน ตรวจสอบ:

- [x] PHP ติดตั้งแล้ว
- [x] Composer dependencies ติดตั้งแล้ว
- [x] Node dependencies ติดตั้งแล้ว
- [ ] Database สร้างแล้ว
- [ ] `.env` ตั้งค่าถูกต้อง
- [ ] Migrations รันแล้ว
- [ ] Storage link สร้างแล้ว

---

**🎉 พร้อมรัน! Double-click `start-local.bat` เพื่อเริ่มต้น**
