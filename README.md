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

## 🚀 Production Deployment

### สำหรับ Server: http://203.172.184.47:9000/

**Quick Setup:**
```bash
# 1. Run production setup script
chmod +x production-setup.sh
./production-setup.sh

# 2. Review and update .env
nano .env

# 3. Test the application
# Visit: http://203.172.184.47:9000/
```

**เอกสาร:**
- 📖 [คู่มือการตั้งค่า Production Server (PRODUCTION_SERVER_SETUP.md)](./PRODUCTION_SERVER_SETUP.md) - สำหรับ server นี้โดยเฉพาะ
- 📖 [คู่มือ Production Deployment แบบเต็ม (PRODUCTION_DEPLOYMENT.md)](./PRODUCTION_DEPLOYMENT.md) - สำหรับ production ทั่วไป

### สำหรับ Production Server อื่นๆ

**Quick Start:**
```bash
# 1. Copy production environment file
cp .env.production.example .env

# 2. Edit .env with production values
nano .env

# 3. Run deployment script
chmod +x deploy.sh
./deploy.sh
```

## License

MIT
