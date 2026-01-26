# 📋 คู่มือระบบบันทึกกิจกรรม (Activity Log)

## 📖 ภาพรวม

ระบบ Activity Log บันทึกทุกการกระทำที่สำคัญในระบบ NITESA โดยอัตโนมัติ ช่วยให้ผู้ดูแลระบบสามารถติดตามและตรวจสอบการใช้งานได้อย่างละเอียด

---

## 🎯 ฟีเจอร์หลัก

### 1. การบันทึกอัตโนมัติ
ระบบบันทึกกิจกรรมต่อไปนี้อัตโนมัติ:
- ✅ การสร้างข้อมูล (created)
- ✏️ การแก้ไขข้อมูล (updated)  
- ❌ การลบข้อมูล (deleted)
- 📤 การส่งการนิเทศ
- ✔️ การอนุมัติการนิเทศ
- 🔄 การส่งกลับเพื่อแก้ไข
- 🌐 การเผยแพร่การนิเทศ
- 📝 การยืนยันรับทราบ

### 2. ระบบกรองและค้นหา
- 🔍 ค้นหาจากคำอธิบายหรือเหตุการณ์
- 📅 กรองตามช่วงวันที่
- 👤 กรองตามผู้กระทำ
- 📊 กรองตามประเภทเหตุการณ์
- 📦 กรองตามประเภทข้อมูล

### 3. สถิติและรายงาน
- 📈 จำนวนกิจกรรมทั้งหมด
- 📅 กิจกรรมวันนี้
- 👥 จำนวนผู้ใช้งานที่มีกิจกรรม
- 🎭 ประเภทกิจกรรมทั้งหมด

### 4. การส่งออกข้อมูล
- 💾 Export เป็น CSV
- 🎯 Export ตามเงื่อนไขที่กรอง
- 📊 จำกัดไม่เกิน 10,000 รายการต่อครั้ง

---

## 👥 สิทธิ์การเข้าถึง

### Admin (ผู้ดูแลระบบ)
- ✅ เข้าถึงได้ทั้งหมด
- ✅ ดู Activity Log ทั้งหมด
- ✅ กรอง/ค้นหาได้ทุกประเภท
- ✅ Export ข้อมูล

### Executive (ผู้บริหาร)
- ✅ เข้าถึงได้ทั้งหมด
- ✅ ดู Activity Log ทั้งหมด
- ✅ กรอง/ค้นหาได้ทุกประเภท
- ✅ Export ข้อมูล

### Supervisor (ศึกษานิเทศก์) & School (โรงเรียน)
- ❌ ไม่สามารถเข้าถึงได้

---

## 🖥️ การใช้งาน Web UI

### เข้าถึงหน้า Activity Log

1. เข้าสู่ระบบด้วยบัญชี Admin หรือ Executive
2. คลิกที่เมนู **"บันทึกกิจกรรม"** ในแถบด้านซ้าย
3. หน้า Activity Log จะแสดงข้อมูลทั้งหมด

### การใช้ตัวกรอง

#### 1. ค้นหาด้วยคำ
```
พิมพ์คำที่ต้องการค้นหาในช่อง "ค้นหา"
ระบบจะค้นหาใน:
- คำอธิบายกิจกรรม
- ชื่อเหตุการณ์
```

#### 2. กรองตามเหตุการณ์
```
เลือกประเภทเหตุการณ์จาก dropdown:
- created (สร้างใหม่)
- updated (แก้ไข)
- deleted (ลบ)
- หรือเหตุการณ์อื่นๆ ที่กำหนดเอง
```

#### 3. กรองตามประเภทข้อมูล
```
เลือกประเภทข้อมูลที่ต้องการดู:
- Supervision (การนิเทศ)
- School (โรงเรียน)
- User (ผู้ใช้งาน)
- Policy (นโยบาย)
- ฯลฯ
```

#### 4. กรองตามผู้กระทำ
```
เลือกชื่อผู้ใช้งานที่ต้องการดูกิจกรรม
```

#### 5. กรองตามช่วงวันที่
```
เลือก "ตั้งแต่วันที่" และ "ถึงวันที่"
ระบบจะแสดงเฉพาะกิจกรรมในช่วงนั้น
```

### ล้างตัวกรอง
คลิกปุ่ม **"ล้างตัวกรองทั้งหมด"** เพื่อรีเซ็ตการค้นหา

### ดูรายละเอียด
คลิกที่ไอคอน 👁️ ในคอลัมน์ "รายละเอียด" เพื่อดู:
- ID ของกิจกรรม
- Event name
- Description
- Subject และ Causer information
- Properties (ข้อมูลเพิ่มเติม)
- Timestamp

---

## 🔌 การใช้งาน API

### Base URL
```
/api/activity-log
```

### Authentication
ต้องใช้ Sanctum Token และมี role เป็น `ADMIN` หรือ `EXECUTIVE`

### Endpoints

#### 1. Get All Activity Logs
```http
GET /api/activity-log

Query Parameters:
- search (string): ค้นหาในคำอธิบายหรือเหตุการณ์
- event (string): กรองตามเหตุการณ์
- subject_type (string): กรองตามประเภทข้อมูล
- causer_id (string): กรองตามผู้กระทำ
- date_from (date): ตั้งแต่วันที่ (YYYY-MM-DD)
- date_to (date): ถึงวันที่ (YYYY-MM-DD)
- per_page (integer): จำนวนรายการต่อหน้า (default: 20)
- page (integer): หมายเลขหน้า

Response:
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "log_name": "default",
      "description": "อนุมัติการนิเทศ",
      "subject_type": "App\\Models\\Supervision",
      "subject_id": "01HQXXX...",
      "causer_type": "App\\Models\\User",
      "causer_id": "01HQXXX...",
      "properties": {},
      "event": "updated",
      "created_at": "2026-01-26T10:30:00.000000Z",
      "causer": {
        "id": "01HQXXX...",
        "name": "สมชาย ใจดี",
        "email": "somchai@example.com"
      },
      "subject": {
        ...
      }
    }
  ],
  "first_page_url": "...",
  "from": 1,
  "last_page": 5,
  "per_page": 20,
  "to": 20,
  "total": 100
}
```

#### 2. Get Activity Statistics
```http
GET /api/activity-log/stats

Response:
{
  "total": 1234,
  "today": 56,
  "this_week": 234,
  "this_month": 567,
  "unique_causers": 12,
  "events": ["created", "updated", "deleted", ...],
  "subject_types": [
    {
      "value": "App\\Models\\Supervision",
      "label": "Supervision"
    },
    ...
  ]
}
```

#### 3. Get Recent Activities (Last 24 hours)
```http
GET /api/activity-log/recent

Response:
[
  {
    "id": 123,
    "description": "สร้างการนิเทศใหม่",
    ...
  }
]
```

#### 4. Get Activities by Causer
```http
GET /api/activity-log/causer/{causerId}

Query Parameters:
- per_page (integer): จำนวนรายการต่อหน้า

Response: (Paginated activities)
```

#### 5. Get Activities by Subject
```http
GET /api/activity-log/subject/{subjectType}/{subjectId}

Example:
GET /api/activity-log/subject/App\Models\Supervision/01HQXXX...

Query Parameters:
- per_page (integer): จำนวนรายการต่อหน้า

Response: (Paginated activities)
```

#### 6. Get Single Activity
```http
GET /api/activity-log/{activityId}

Response:
{
  "id": 123,
  "description": "อนุมัติการนิเทศ",
  "event": "updated",
  "causer": {...},
  "subject": {...},
  ...
}
```

#### 7. Export to CSV
```http
GET /api/activity-log/export

Query Parameters: (เหมือน GET /api/activity-log)
- search
- event
- subject_type
- causer_id
- date_from
- date_to

Response: CSV file
```

### ตัวอย่างการใช้งาน API

#### cURL Example
```bash
# Get all activities with filter
curl -X GET \
  'https://api.nitesa.go.th/api/activity-log?event=created&per_page=10' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Accept: application/json'

# Get statistics
curl -X GET \
  'https://api.nitesa.go.th/api/activity-log/stats' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Accept: application/json'

# Export to CSV
curl -X GET \
  'https://api.nitesa.go.th/api/activity-log/export?date_from=2026-01-01&date_to=2026-01-31' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -O activity-log.csv
```

#### JavaScript Example
```javascript
// Fetch activities with filters
const fetchActivities = async () => {
  const response = await fetch('/api/activity-log?search=นิเทศ&per_page=20', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const data = await response.json();
  console.log(data);
};

// Get statistics
const fetchStats = async () => {
  const response = await fetch('/api/activity-log/stats', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const stats = await response.json();
  console.log('Total activities:', stats.total);
  console.log('Today:', stats.today);
};
```

---

## 💻 การบันทึก Activity Log ในโค้ด

### ใช้ Helper Function
```php
// บันทึกกิจกรรมอัตโนมัติ (ถูกเรียกใน Controllers แล้ว)
activity()
    ->causedBy(auth()->user())
    ->performedOn($supervision)
    ->log('อนุมัติการนิเทศ');

// บันทึกพร้อม properties
activity()
    ->causedBy(auth()->user())
    ->performedOn($supervision)
    ->withProperties([
        'old_status' => 'SUBMITTED',
        'new_status' => 'APPROVED',
        'reason' => 'ผ่านเกณฑ์ทุกข้อ'
    ])
    ->log('เปลี่ยนสถานะการนิเทศ');
```

### Log Events ที่บันทึกอัตโนมัติ

ใน Model ที่ใช้ `LogsActivity` trait:
```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Supervision extends Model
{
    use LogsActivity;
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'summary', 'suggestions'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

---

## 📊 โครงสร้างข้อมูล

### Database Schema
```sql
CREATE TABLE activity_log (
    id BIGINT PRIMARY KEY,
    log_name VARCHAR(255),
    description TEXT,
    subject_type VARCHAR(255),
    subject_id VARCHAR(26),
    causer_type VARCHAR(255),
    causer_id VARCHAR(26),
    properties JSON,
    event VARCHAR(255),
    batch_uuid VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Properties Structure
```json
{
  "attributes": {
    "status": "APPROVED",
    "updated_at": "2026-01-26 10:30:00"
  },
  "old": {
    "status": "SUBMITTED",
    "updated_at": "2026-01-25 15:20:00"
  }
}
```

---

## 🔧 Maintenance

### ล้างข้อมูล Activity Log เก่า

#### ด้วย Artisan Command
```bash
# ลบ activities เก่ากว่า 90 วัน
php artisan activitylog:clean --days=90

# ลบทั้งหมด
php artisan activitylog:clean
```

#### ด้วย Code
```php
use Spatie\Activitylog\Models\Activity;

// ลบกิจกรรมเก่ากว่า 3 เดือน
Activity::where('created_at', '<', now()->subMonths(3))->delete();

// ลบกิจกรรมของ user ที่ถูกลบแล้ว
Activity::whereNull('causer_id')->delete();
```

### ตั้งค่า Automatic Cleanup (Recommended)

เพิ่มใน `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // ลบ activity log เก่ากว่า 90 วันทุกวันเวลา 02:00
    $schedule->command('activitylog:clean --days=90')
             ->daily()
             ->at('02:00');
}
```

---

## 🎯 Best Practices

### 1. การตั้งชื่อ Description
```php
// ❌ ไม่ดี - ไม่ชัดเจน
activity()->log('updated');

// ✅ ดี - ชัดเจน เข้าใจง่าย
activity()->log('อนุมัติการนิเทศโรงเรียนบ้านดอน');
```

### 2. ใช้ Properties เพื่อเก็บข้อมูลเพิ่มเติม
```php
activity()
    ->withProperties([
        'school_id' => $school->id,
        'school_name' => $school->name,
        'supervisor_id' => auth()->id(),
        'changes' => $changes
    ])
    ->log('แก้ไขข้อมูลโรงเรียน');
```

### 3. Batch Activities (กิจกรรมที่เกี่ยวข้องกัน)
```php
activity()->enableBatchLogging();

// Multiple activities จะได้ batch_uuid เดียวกัน
activity()->log('เริ่มต้นนำเข้าข้อมูล');
// ... import 100 records ...
activity()->log('นำเข้าข้อมูลเสร็จสิ้น 100 รายการ');

activity()->disableBatchLogging();
```

### 4. Don't Log Sensitive Data
```php
// ❌ อย่าบันทึกข้อมูลอ่อนไหว
activity()
    ->withProperties(['password' => $password]) // NO!
    ->log('เปลี่ยนรหัสผ่าน');

// ✅ บันทึกแค่ว่ามีการเปลี่ยน
activity()->log('เปลี่ยนรหัสผ่าน'); // OK
```

---

## ❓ FAQ

**Q: Activity Log จะมีผลต่อ Performance หรือไม่?**  
A: มีผลเล็กน้อย แต่สามารถใช้ Queue สำหรับ logging ได้:
```php
activity()
    ->useLog('async')
    ->queue() // Log แบบ async
    ->log('กิจกรรม');
```

**Q: สามารถแก้ไขหรือลบ Activity Log ได้หรือไม่?**  
A: ไม่แนะนำ เพื่อรักษาความถูกต้องของ audit trail แต่ Admin สามารถลบได้ถ้าจำเป็น

**Q: Activity Log เก็บไว้นานแค่ไหน?**  
A: แนะนำเก็บ 90-180 วัน แล้วใช้ automatic cleanup หรือ archive เป็น CSV

**Q: สามารถแสดง Activity Log ให้ผู้ใช้ทั่วไปดูได้หรือไม่?**  
A: ได้ แต่ควร filter เฉพาะกิจกรรมที่เกี่ยวข้องกับผู้ใช้นั้นๆ เท่านั้น

---

## 📚 เอกสารเพิ่มเติม

- [Spatie Activity Log Documentation](https://spatie.be/docs/laravel-activitylog/)
- [Laravel Eloquent Events](https://laravel.com/docs/10.x/eloquent#events)
- [Audit Trail Best Practices](https://en.wikipedia.org/wiki/Audit_trail)

---

## 🆘 การแก้ปัญหา

### ปัญหา: Activity Log ไม่ถูกบันทึก
1. ตรวจสอบว่า Migration ทำงานแล้ว: `php artisan migrate:status`
2. ตรวจสอบว่า Model ใช้ `LogsActivity` trait
3. ดู logs: `tail -f storage/logs/laravel.log`

### ปัญหา: หน้า Activity Log ช้า
1. เพิ่ม index ในตาราง `activity_log`:
```sql
CREATE INDEX idx_created_at ON activity_log(created_at);
CREATE INDEX idx_causer_id ON activity_log(causer_id);
CREATE INDEX idx_subject_type_id ON activity_log(subject_type, subject_id);
```
2. ใช้ Pagination และ Limit results
3. Archive logs เก่า

### ปัญหา: CSV Export ใหญ่เกินไป
1. จำกัดช่วงวันที่ที่ Export
2. ใช้ filters เพื่อลดจำนวนข้อมูล
3. Export เป็นงวดๆ แทนครั้งเดียวทั้งหมด

---

**🎉 พร้อมใช้งาน Activity Log แล้ว!**

สำหรับคำถามหรือปัญหา กรุณาติดต่อ: support@nitesa.go.th
