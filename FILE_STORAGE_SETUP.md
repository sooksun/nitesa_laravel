# 📁 คู่มือการตั้งค่า File Storage

## 📖 ภาพรวม

ระบบ NITESA ใช้ Laravel Filesystem เพื่อจัดการไฟล์แนบ (attachments) ในระบบนิเทศ เอกสารนี้จะอธิบายวิธีการตั้งค่าและใช้งาน File Storage ให้ถูกต้อง

---

## ⚙️ การตั้งค่า Filesystem Disk

### 1. ตั้งค่าใน `.env`

```env
# ตัวเลือก: 'local', 'public', 's3'
FILESYSTEM_DISK=public
```

#### ตัวเลือก Filesystem Disk:

| Disk | คำอธิบาย | ใช้เมื่อไหร่ |
|------|----------|-------------|
| **`local`** | เก็บไฟล์ใน `storage/app` | Development, ไฟล์ส่วนตัว |
| **`public`** | เก็บไฟล์ใน `storage/app/public` | Production (แนะนำ) |
| **`s3`** | เก็บไฟล์ใน AWS S3 | Production ขนาดใหญ่, CDN |

---

## 🚀 ขั้นตอนการติดตั้ง

### ตัวเลือก A: ใช้ Public Disk (แนะนำ)

#### 1. ตั้งค่า `.env`
```env
FILESYSTEM_DISK=public
```

#### 2. สร้าง Symbolic Link
```bash
php artisan storage:link
```

คำสั่งนี้จะสร้าง symbolic link จาก `public/storage` → `storage/app/public`

#### 3. ตรวจสอบว่า Link ถูกสร้างแล้ว
```bash
# Windows (PowerShell)
Test-Path public\storage

# Linux/Mac
ls -la public/storage
```

ควรเห็น:
```
public/storage -> /path/to/storage/app/public
```

#### 4. ตั้งค่า Permissions (Linux/Mac)
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

---

### ตัวเลือก B: ใช้ Local Disk

#### 1. ตั้งค่า `.env`
```env
FILESYSTEM_DISK=local
```

#### 2. ไฟล์จะถูกเก็บใน `storage/app/attachments/`

⚠️ **หมายเหตุ**: ต้องใช้ Controller route เพื่อดาวน์โหลดไฟล์ (ไม่สามารถเข้าถึงโดยตรงผ่าน URL)

---

### ตัวเลือก C: ใช้ AWS S3 (Production)

#### 1. ตั้งค่า AWS Credentials ใน `.env`
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=nitesa-attachments
AWS_USE_PATH_STYLE_ENDPOINT=false
```

#### 2. ติดตั้ง AWS SDK
```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

#### 3. สร้าง S3 Bucket
- ไปที่ AWS Console → S3
- สร้าง bucket ใหม่
- ตั้งค่า CORS และ Permissions

---

## 🔍 การตรวจสอบการตั้งค่า

### 1. ตรวจสอบ Disk Configuration
```bash
php artisan tinker
```

```php
// ตรวจสอบ disk ที่ใช้
config('filesystems.default');

// ตรวจสอบ path
Storage::disk('public')->path('test.txt');
```

### 2. ทดสอบการเขียนไฟล์
```php
// ใน tinker
Storage::disk('public')->put('test.txt', 'Hello World');
Storage::disk('public')->exists('test.txt'); // ควรได้ true
```

### 3. ทดสอบการอ่านไฟล์
```php
$url = Storage::disk('public')->url('test.txt');
echo $url; // ควรได้ URL ที่ถูกต้อง
```

---

## 🛠️ การใช้งานในโค้ด

### ใน Model (Attachment)

```php
use App\Models\Attachment;

$attachment = Attachment::find($id);

// ตรวจสอบว่าไฟล์มีอยู่จริง
if ($attachment->fileExists()) {
    // ไฟล์มีอยู่
}

// ดึง URL สำหรับแสดงผล
$url = $attachment->getUrl(); // อาจเป็น null ถ้าไฟล์ไม่มี

// ดึง URL แบบปลอดภัย (มี fallback)
$safeUrl = $attachment->getSafeUrl(); // จะมี placeholder ถ้าไฟล์ไม่มี
```

### ใน Blade Template

```blade
@if($attachment->fileExists())
    <img src="{{ $attachment->getUrl() }}" alt="{{ $attachment->filename }}">
@else
    <p>ไฟล์ไม่พบ</p>
@endif
```

### Download File

```blade
<a href="{{ route('attachments.download', $attachment) }}">
    ดาวน์โหลด {{ $attachment->filename }}
</a>
```

---

## 🔧 Troubleshooting

### ปัญหา: ไฟล์ไม่แสดง (404 Not Found)

#### สาเหตุ 1: Symbolic Link ไม่ถูกสร้าง
**แก้ไข:**
```bash
php artisan storage:link
```

#### สาเหตุ 2: Permissions ไม่ถูกต้อง
**แก้ไข (Linux/Mac):**
```bash
chmod -R 775 storage
chmod -R 775 public/storage
```

#### สาเหตุ 3: FILESYSTEM_DISK ไม่ตรงกับที่ตั้งค่า
**แก้ไข:**
```bash
# ตรวจสอบ .env
cat .env | grep FILESYSTEM_DISK

# Clear config cache
php artisan config:clear
php artisan cache:clear
```

### ปัญหา: Storage::url() ไม่ทำงาน

#### สำหรับ Local Disk:
ต้องใช้ route แทน:
```php
// ❌ ไม่ทำงาน
Storage::url($fileUrl);

// ✅ ใช้ route
route('attachments.download', $attachment);
```

#### สำหรับ Public Disk:
ต้องมี symbolic link:
```bash
php artisan storage:link
```

### ปัญหา: ไฟล์ถูกอัพโหลดแต่ไม่แสดง

1. **ตรวจสอบ path ใน database:**
```sql
SELECT fileUrl FROM attachment WHERE id = 'xxx';
```

2. **ตรวจสอบว่าไฟล์มีอยู่จริง:**
```bash
# สำหรับ public disk
ls -la storage/app/public/attachments/

# สำหรับ local disk
ls -la storage/app/attachments/
```

3. **ตรวจสอบ permissions:**
```bash
ls -la storage/app/public/
```

---

## 📊 โครงสร้าง Directory

### Public Disk
```
storage/
└── app/
    └── public/
        └── attachments/
            ├── file1.jpg
            ├── file2.pdf
            └── ...

public/
└── storage -> ../storage/app/public (symbolic link)
```

### Local Disk
```
storage/
└── app/
    └── attachments/
        ├── file1.jpg
        ├── file2.pdf
        └── ...
```

---

## 🔐 Security Best Practices

### 1. ตรวจสอบไฟล์ก่อนแสดง
```php
// ✅ ดี - ตรวจสอบก่อน
if ($attachment->fileExists()) {
    $url = $attachment->getUrl();
}

// ❌ ไม่ดี - ไม่ตรวจสอบ
$url = Storage::url($attachment->fileUrl);
```

### 2. ใช้ Route สำหรับ Download
```php
// ✅ ดี - ผ่าน Controller (มี authentication)
route('attachments.download', $attachment);

// ❌ ไม่ดี - ตรงไปที่ไฟล์ (อาจ bypass auth)
Storage::url($attachment->fileUrl);
```

### 3. จำกัด File Types
ตรวจสอบใน `SupervisionForm.php`:
```php
'uploads.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
```

### 4. ตั้งค่า File Size Limit
```php
// ใน php.ini
upload_max_filesize = 10M
post_max_size = 10M

// ใน config/filesystems.php
'max_file_size' => 10240, // KB
```

---

## 📝 Migration Checklist

เมื่อ Deploy ไป Production:

- [ ] ตั้งค่า `FILESYSTEM_DISK=public` ใน `.env`
- [ ] Run `php artisan storage:link`
- [ ] ตรวจสอบ symbolic link ถูกสร้าง
- [ ] ตั้งค่า permissions (775 สำหรับ storage)
- [ ] ทดสอบอัพโหลดไฟล์
- [ ] ทดสอบดาวน์โหลดไฟล์
- [ ] ตรวจสอบว่าไฟล์แสดงผลได้
- [ ] Backup ไฟล์เก่าก่อน migrate (ถ้ามี)

---

## 🚀 Production Deployment

### สำหรับ Production (Public Disk)

1. **ตั้งค่า `.env`:**
```env
FILESYSTEM_DISK=public
APP_ENV=production
APP_DEBUG=false
```

2. **สร้าง Symbolic Link:**
```bash
php artisan storage:link
```

3. **ตั้งค่า Permissions:**
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

4. **Clear Cache:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

5. **ทดสอบ:**
- อัพโหลดไฟล์ใหม่
- ตรวจสอบว่าไฟล์แสดงผลได้
- ทดสอบดาวน์โหลด

---

## 📚 เอกสารเพิ่มเติม

- [Laravel Filesystem Documentation](https://laravel.com/docs/10.x/filesystem)
- [Storage Symbolic Links](https://laravel.com/docs/10.x/filesystem#the-public-disk)
- [AWS S3 Configuration](https://laravel.com/docs/10.x/filesystem#amazon-s3-compatible-filesystems)

---

## 🆘 การติดต่อสนับสนุน

หากมีปัญหาหรือข้อสงสัย:
- 📧 Email: support@nitesa.go.th
- 📖 ดู Logs: `storage/logs/laravel.log`

---

**✅ เมื่อตั้งค่าเสร็จแล้ว ระบบ File Storage พร้อมใช้งาน!**
