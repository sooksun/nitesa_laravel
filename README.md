# NITESA - ระบบนิเทศ ติดตาม และประเมินผลการศึกษา

ระบบบันทึกการนิเทศติดตามโรงเรียนสำหรับสำนักงานเขตพื้นที่การศึกษา

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.1+)
- **Frontend:** Blade + Tailwind CSS
- **Reactive UI:** Livewire v3
- **JS Utility:** Alpine.js
- **Database:** MySQL
- **Auth:** Laravel Auth (RBAC)

## ฟีเจอร์หลัก

- 📊 Dashboard ภาพรวมการนิเทศ
- 🏫 จัดการโรงเรียน
- 👥 จัดการผู้ใช้งาน (RBAC)
- 📋 จัดการนโยบาย
- 📝 บันทึกการนิเทศ พร้อม Workflow อนุมัติ
- 📈 รายงานเชิงวิเคราะห์
- 📥 นำเข้าข้อมูลจาก Excel
- 🔐 Activity Log

## การติดตั้ง

### 1. Clone และติดตั้ง Dependencies

```bash
cd nitesa2
composer install
npm install
```

### 2. ตั้งค่า Environment

```bash
cp .env.example .env
php artisan key:generate
```

แก้ไข `.env`:

```
DB_DATABASE=nitesa2
DB_USERNAME=root
DB_PASSWORD=
```

### 3. สร้างฐานข้อมูล

```bash
php artisan migrate --seed
```

### 4. Build Assets

```bash
npm run build
```

### 5. รันเซิร์ฟเวอร์

```bash
php artisan serve
```

เปิด http://localhost:8000

## บัญชีทดสอบ

| บทบาท | อีเมล | รหัสผ่าน |
|-------|-------|----------|
| Admin | admin@nitesa.local | password |
| Supervisor | supervisor@nitesa.local | password |
| Executive | executive@nitesa.local | password |
| School | school@nitesa.local | password |

## Workflow การนิเทศ

```
DRAFT → SUBMITTED → APPROVED → PUBLISHED
                 ↘ NEEDS_IMPROVEMENT ↗
```

1. **DRAFT** - ร่าง (Supervisor สร้าง)
2. **SUBMITTED** - ส่งเพื่ออนุมัติ
3. **APPROVED** - อนุมัติแล้ว (โดย Admin/Executive)
4. **PUBLISHED** - เผยแพร่แล้ว (โรงเรียนดูได้)
5. **NEEDS_IMPROVEMENT** - ต้องปรับปรุง (ส่งกลับ)

## API Endpoints

ดู routes/api.php สำหรับ REST API ทั้งหมด

ใช้ Laravel Sanctum สำหรับ authentication

## โครงสร้างโปรเจค

```
app/
├── Enums/           # Role, SupervisionStatus, IndicatorLevel, PolicyType
├── Http/
│   ├── Controllers/
│   │   ├── Api/     # REST API Controllers
│   │   └── Auth/    # Auth Controllers
│   └── Middleware/  # RoleMiddleware, EnsureSchoolAccess
├── Livewire/        # Livewire Components
│   ├── Dashboard/
│   ├── School/
│   ├── User/
│   ├── Policy/
│   ├── Supervision/
│   ├── Report/
│   ├── Import/
│   ├── Settings/
│   └── Profile/
└── Models/          # Eloquent Models

resources/views/
├── layouts/         # App & Guest Layouts
├── livewire/        # Livewire Views
└── auth/            # Auth Views
```

## 📚 เอกสารเพิ่มเติม

- 📖 [คู่มือการใช้งานระบบ (USER_MANUAL.md)](./USER_MANUAL.md)
- 🚀 [คู่มือ Production Deployment (PRODUCTION_DEPLOYMENT.md)](./PRODUCTION_DEPLOYMENT.md)
- ⚡ [คู่มือการตั้งค่า Local (LOCAL_SETUP.md)](./LOCAL_SETUP.md)
- 🔔 [คู่มือการตั้งค่า Notification (NOTIFICATION_SETUP.md)](./NOTIFICATION_SETUP.md)
- 📁 [คู่มือการตั้งค่า File Storage (FILE_STORAGE_SETUP.md)](./FILE_STORAGE_SETUP.md)
- 📊 [คู่มือ Performance Optimization (PERFORMANCE_OPTIMIZATION.md)](./PERFORMANCE_OPTIMIZATION.md)
- 📈 [รายงาน Code Quality (CODE_QUALITY_IMPROVEMENTS.md)](./CODE_QUALITY_IMPROVEMENTS.md)

## 🚀 Production Deployment (Docker)

### สำหรับ Server: https://nitesa.cnppai.com/

**ข้อกำหนด:**
- Ubuntu 24.04 LTS
- Docker & Docker Compose
- MySQL/MariaDB (Host หรือ Container)

### Quick Install on Server

```bash
# 1. Clone repository
cd /DATA/AppData/www
git clone https://github.com/sooksun/nitesa_laravel.git nitesa
cd nitesa

# 2. สร้างไฟล์ .env
cat > .env << 'EOF'
APP_NAME="ระบบนิเทศการศึกษา"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://nitesa.cnppai.com
ASSET_URL=https://nitesa.cnppai.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=192.168.1.4
DB_PORT=3306
DB_DATABASE=nitesa
DB_USERNAME=tok
DB_PASSWORD=your_password

SESSION_DRIVER=redis
SESSION_LIFETIME=120
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

FILESYSTEM_DISK=public
EOF

# 3. Generate APP_KEY
php artisan key:generate
# หรือ
APP_KEY=$(openssl rand -base64 32)
sed -i "s/APP_KEY=/APP_KEY=base64:$APP_KEY/" .env

# 4. Build และ Start Docker containers
docker compose up -d --build

# 5. หลัง build เสร็จ - ติดตั้ง dependencies และ build assets ใน container
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app npm ci
docker compose exec app npm run build

# 6. แก้ไข permissions
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/html/storage
docker compose exec app chmod -R 775 /var/www/html/bootstrap/cache

# 7. Run migrations
docker compose exec app php artisan migrate --force

# 8. Publish Livewire assets
docker compose exec app php artisan livewire:publish --assets

# 9. Optimize Laravel
docker compose exec app php artisan optimize
docker compose exec app php artisan view:cache

# 10. Restart containers
docker compose restart
```

### อัพเดทโค้ดบน Server

```bash
cd /DATA/AppData/www/nitesa

# Pull latest code
git pull origin main

# Rebuild assets (ถ้ามีการเปลี่ยนแปลง frontend)
docker compose exec app npm ci
docker compose exec app npm run build

# Clear caches
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
docker compose exec app php artisan view:cache

# Restart
docker compose restart
```

### Docker Services

| Service | Port | Description |
|---------|------|-------------|
| nginx | 9000 | Web Server (เชื่อมต่อผ่าน Nginx Proxy Manager) |
| app | - | PHP-FPM 8.3 |
| redis | - | Cache & Session |
| queue | - | Laravel Queue Worker |

### Troubleshooting

```bash
# ดู logs
docker compose logs app --tail 50
docker compose exec app tail -50 storage/logs/laravel.log

# Restart all containers
docker compose restart

# Rebuild containers
docker compose down
docker compose up -d --build

# เข้าไปใน container
docker compose exec app sh
```

**เอกสารเพิ่มเติม:**
- 📖 [คู่มือการตั้งค่า Production Server (PRODUCTION_SERVER_SETUP.md)](./PRODUCTION_SERVER_SETUP.md)
- 📖 [คู่มือ Production Deployment แบบเต็ม (PRODUCTION_DEPLOYMENT.md)](./PRODUCTION_DEPLOYMENT.md)

## License

MIT
