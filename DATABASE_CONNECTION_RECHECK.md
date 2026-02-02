# 🗄️ รายงานการตรวจสอบการเชื่อมต่อฐานข้อมูล (Database Connection Recheck)

**วันที่ตรวจสอบ:** _______________  
**ระบบ:** NITESA - ระบบนิเทศ ติดตาม และประเมินผลการศึกษา

---

## 📋 สรุปผลการตรวจสอบ

| หัวข้อ | สถานะ | หมายเหตุ |
|--------|--------|----------|
| Default connection | ✅ | ใช้ `env('DB_CONNECTION', 'mysql')` |
| MySQL config | ✅ | host, port, database, username, password จาก .env |
| Charset / Collation | ✅ | utf8mb4, utf8mb4_unicode_ci |
| Models | ✅ | ไม่ระบุ $connection = ใช้ default |
| Migrations | ✅ | ใช้ Schema:: ไม่ระบุ connection |
| DB::transaction / DB::table | ✅ | ใช้ default connection |
| Connection switching | ✅ | ไม่มีการสลับ connection ใน app |

---

## 1. การตั้งค่า (config/database.php)

- **default:** `env('DB_CONNECTION', 'mysql')` → ใช้ MySQL เป็นค่าเริ่มต้น
- **mysql:** อ่านจาก `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_SOCKET`
- **charset:** `utf8mb4`  
- **collation:** `utf8mb4_unicode_ci`  
- **strict:** `true`  
- **options:** รองรับ SSL (MYSQL_ATTR_SSL_CA) และ MYSQL_ATTR_CONNECT_TIMEOUT (default 10 วินาที ถ้ามี env `DB_CONNECT_TIMEOUT`)

---

## 2. ตัวแปร Environment (.env)

ต้องมีค่าต่อไปนี้ (ตัวอย่าง):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nitesa
DB_USERNAME=root
DB_PASSWORD=your_password
```

- **DB_SOCKET:** ถ้าใช้ socket แทน host ให้กำหนด (เช่น Laragon)
- **DB_CONNECT_TIMEOUT:** (ถ้าต้องการ) กำหนด connection timeout เป็นวินาที (default 10)
- **DATABASE_URL:** ถ้ากำหนด Laravel จะใช้แทน host/port/database/user/password

---

## 3. การใช้งานในโค้ด

- **Models:** ทุก model ไม่ได้กำหนด `protected $connection` → ใช้ default
- **Migrations:** ใช้ `Schema::` โดยไม่ระบุ connection → ใช้ default
- **DB::transaction / DB::table:** ใช้ default connection
- **ไม่มี** `DB::connection('other')` หรือ `->connection('other')` ใน app → ใช้ connection เดียว

---

## 4. วิธีทดสอบการเชื่อมต่อ

### 4.1 ผ่าน Artisan Tinker

```bash
php artisan tinker
```

ใน tinker:

```php
DB::connection()->getPdo();
// ได้ PDO instance แปลว่าเชื่อมต่อได้

DB::connection()->getDatabaseName();
// ได้ชื่อ database

DB::select('SELECT 1');
// ได้ผลลัพธ์ [{"1": 1}]
```

### 4.2 ผ่าน Artisan Command

```bash
php artisan db:show
```

แสดงข้อมูล connection ปัจจุบันและตาราง

### 4.3 ผ่าน Script (ในโปรเจค)

สร้างไฟล์ `check-db.php` ที่ root โปรเจค:

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $pdo = DB::connection()->getPdo();
    $name = DB::connection()->getDatabaseName();
    echo "OK: Connected to database '{$name}'\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
```

รัน: `php check-db.php`

---

## 5. ปัญหาที่พบบ่อยและแนวทางแก้

| อาการ | สาเหตุที่เป็นไปได้ | แนวทาง |
|--------|----------------------|--------|
| SQLSTATE[HY000] [2002] No connection | MySQL ไม่รัน / host หรือ port ผิด | ตรวจสอบ MySQL service, DB_HOST, DB_PORT |
| Access denied for user | username/password ผิด หรือไม่มีสิทธิ์ | ตรวจ DB_USERNAME, DB_PASSWORD และสิทธิ์ใน MySQL |
| Unknown database 'xxx' | database ยังไม่ได้สร้าง | สร้าง database ใน MySQL แล้วตั้ง DB_DATABASE |
| Connection timeout | เครือข่าย/ firewall หรือ MySQL ช้า | ตรวจ DB_HOST, เพิ่ม DB_CONNECT_TIMEOUT ถ้าจำเป็น |
| .env ไม่โหลด | แก้ .env แต่ยังใช้ config เก่า | รัน `php artisan config:clear` แล้วทดสอบใหม่ |

---

## 6. Production

- ใช้ค่าจาก environment จริง (ไม่ hardcode ใน config)
- ควรใช้รหัสผ่านที่แข็งแรงและไม่ commit .env
- ถ้า MySQL อยู่คนละเครื่องกับ app ตรวจ firewall และ MySQL bind-address
- ถ้าใช้ SSL ต่อ MySQL ตั้ง MYSQL_ATTR_SSL_CA ใน .env แล้วใช้ใน config (มีอยู่แล้ว)

---

## 7. สรุป

- การเชื่อมต่อใช้ default connection เดียว (mysql) และอ่านจาก .env ครบ
- โค้ดใน app ไม่สลับ connection และใช้ค่า config มาตรฐานของ Laravel
- เพิ่ม ATTR_TIMEOUT ใน options ของ MySQL แล้ว
- ตรวจการเชื่อมต่อได้ด้วย tinker, `php artisan db:show` หรือ script ด้านบน
