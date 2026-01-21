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

## License

MIT
