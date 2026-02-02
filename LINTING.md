# 🔍 Linting Guide

คู่มือการรันและตั้งค่า Linting สำหรับโปรเจค NITESA

---

## 📦 Tools ที่ใช้

| Tool | ใช้สำหรับ | Config |
|------|-----------|--------|
| **PHP CS Fixer** | Code style (PSR-12, PHP 8.1) | `.php-cs-fixer.dist.php` |
| **PHPStan (Larastan)** | Static analysis | `phpstan.neon` |
| **Laravel Pint** | Code style (ทางเลือก) | - |

---

## ⚡ Quick Commands

```bash
# จัดรูปแบบ / แก้ไข code style อัตโนมัติ (PHP CS Fixer)
composer format
composer lint:fix

# ตรวจสอบ code style โดยไม่แก้ (dry-run)
composer format:check
composer lint:php:check

# รัน static analysis (PHPStan)
composer lint:stan

# รันทั้งหมด (lint:fix + lint:stan)
composer lint
```

---

## 📋 PHP CS Fixer

### รัน
```bash
# แก้ไขไฟล์อัตโนมัติ
vendor\bin\php-cs-fixer fix

# ตรวจสอบเท่านั้น (แสดง diff)
vendor\bin\php-cs-fixer fix --dry-run --diff

# แก้เฉพาะ path
vendor\bin\php-cs-fixer fix app/
```

### Rules ที่ใช้ (สรุป)
- `@PER-CS` – PSR-12 style
- `@PHP81Migration` – โค้ดเหมาะกับ PHP 8.1
- `array_syntax` – short array `[]`
- `ordered_imports` – เรียง use ตาม alpha
- `no_unused_imports` – ลบ use ที่ไม่ใช้
- `trailing_comma_in_multiline` – เครื่องหมาย comma ท้าย multiline
- `blank_line_before_statement` – บรรทัดว่างก่อน return, throw, try

Config เต็ม: `.php-cs-fixer.dist.php`

---

## 📋 PHPStan (Larastan)

### รัน
```bash
vendor\bin\phpstan analyse --memory-limit=512M

# เฉพาะ path
vendor\bin\phpstan analyse app/ --memory-limit=512M

# ระดับความเข้มงวด (0–9)
vendor\bin\phpstan analyse --level=5
```

### Config
- ไฟล์: `phpstan.neon`
- ใช้ Larastan (PHPStan สำหรับ Laravel)
- Level: 5
- วิเคราะห์: `app/`
- ยกเว้น: `app/Http/Middleware/TrustProxies.php`

---

## 📋 Laravel Pint (ทางเลือก)

```bash
# ตรวจสอบ
./vendor/bin/pint --test

# แก้ไข
./vendor/bin/pint
```

Pint ใช้กฎที่เหมาะกับ Laravel โดยไม่ต้องมี config แยก (หรือสร้าง `pint.json` ได้)

---

## 🔧 Composer Scripts

ใน `composer.json`:

| Script | คำอธิบาย |
|--------|----------|
| `composer lint` | รัน lint:php แล้วตามด้วย lint:stan |
| `composer lint:fix` | PHP CS Fixer แก้ไขไฟล์ |
| `composer lint:php` | PHP CS Fixer fix |
| `composer lint:php:check` | PHP CS Fixer dry-run |
| `composer lint:stan` | PHPStan analyse |

---

## ✅ แนะนำก่อน Commit

1. รัน `composer lint:fix` ให้ผ่าน
2. (ถ้ามีเวลา) รัน `composer lint:stan` ให้ไม่มี error
3. Commit เมื่อทั้งสองผ่านตามที่ทีมกำหนด

---

## 🆘 ปัญหาที่พบบ่อย

### PHP CS Fixer: Cache
```bash
# ลบ cache
del .php-cs-fixer.cache   # Windows
rm .php-cs-fixer.cache    # Linux/Mac
```

### PHPStan: Memory
```bash
# เพิ่ม memory
vendor\bin\phpstan analyse --memory-limit=1G
```

### PHPStan: ช้า
- รันครั้งแรกจะช้า (ยังไม่มี cache)
- ครั้งถัดไปจะใช้ cache เร็วขึ้น
- ลด path: `vendor\bin\phpstan analyse app/Http/`

---

## 📁 ไฟล์ที่เกี่ยวข้อง

- `.php-cs-fixer.dist.php` – config PHP CS Fixer
- `phpstan.neon` – config PHPStan/Larastan
- `.php-cs-fixer.cache` – cache ของ PHP CS Fixer (ไม่ commit)
- `.gitignore` – ใส่ `.php-cs-fixer.cache` ถ้ายังไม่มี

---

**อัปเดตล่าสุด:** ตามการตั้งค่าในโปรเจคปัจจุบัน
