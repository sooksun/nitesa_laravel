# 🚀 Quick Start: ระบบแจ้งเตือน NITESA

## การเริ่มต้นใช้งานอย่างรวดเร็ว (5 นาที)

### 1. Run Migration

```bash
php artisan migrate
php artisan queue:table
php artisan migrate
```

### 2. ตั้งค่า Mail (เลือก 1 วิธี)

#### วิธีที่ 1: ทดสอบ Local (Mailpit)
```bash
# ใน .env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
```
เปิดดูที่: http://localhost:8025

#### วิธีที่ 2: ใช้ Gmail
```bash
# ใน .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@nitesa.go.th"
MAIL_FROM_NAME="ระบบนิเทศ NITESA"
```

### 3. ตั้งค่า Queue

```bash
# ใน .env
QUEUE_CONNECTION=database

# เริ่ม queue worker
php artisan queue:work
```

### 4. ทดสอบการส่ง Email

```bash
php artisan tinker
```

```php
$user = App\Models\User::first();
$supervision = App\Models\Supervision::first();
$user->notify(new App\Notifications\SupervisionSubmittedNotification($supervision));
exit
```

ตรวจสอบผลใน Mailpit (http://localhost:8025) หรือ inbox ของคุณ

---

## การทำงานของ Notification

### เมื่อส่งการนิเทศเพื่ออนุมัติ
```
ศึกษานิเทศก์ส่งการนิเทศ 
    ↓
📧 Email + 🔔 Database notification 
    ↓
ผู้บริหาร + Admin ทุกคน
```

### เมื่ออนุมัติการนิเทศ
```
ผู้บริหารอนุมัติ
    ↓
📧 Email + 🔔 Database notification
    ↓
ศึกษานิเทศก์ที่สร้างการนิเทศ
```

### เมื่อส่งกลับเพื่อแก้ไข
```
ผู้บริหารส่งกลับ (พร้อมเหตุผล)
    ↓
📧 Email + 🔔 Database notification
    ↓
ศึกษานิเทศก์ที่สร้างการนิเทศ
```

### เมื่อเผยแพร่การนิเทศ
```
Admin เผยแพร่การนิเทศ
    ↓
📧 Email + 🔔 Database notification
    ↓
โรงเรียน + ศึกษานิเทศก์
```

---

## คำสั่งที่ใช้บ่อย

```bash
# ดู queue jobs
php artisan queue:monitor

# ดู failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry all

# Restart queue worker
php artisan queue:restart

# Clear cache
php artisan config:clear
php artisan cache:clear

# ตรวจสอบ database notifications
php artisan tinker
>>> auth()->user()->notifications
>>> auth()->user()->unreadNotifications
```

---

## Production Deployment

### 1. ตั้งค่า Supervisor

สร้างไฟล์ `/etc/supervisor/conf.d/nitesa-worker.conf`:

```ini
[program:nitesa-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/nitesa2/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/nitesa2/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start nitesa-worker:*
```

### 2. ตั้งค่า Cron (สำหรับ Schedule)

```bash
* * * * * cd /var/www/nitesa2 && php artisan schedule:run >> /dev/null 2>&1
```

### 3. ตรวจสอบ Logs

```bash
tail -f storage/logs/laravel.log
tail -f storage/logs/worker.log
```

---

## Troubleshooting

| ปัญหา | วิธีแก้ |
|-------|---------|
| Email ไม่ส่ง | ตรวจสอบ queue worker และ mail config |
| Queue jobs ค้าง | `php artisan queue:restart` |
| Gmail ปฏิเสธ | ใช้ App-Specific Password |
| Notification ไม่มี | ตรวจสอบ users ที่ควรได้รับ notification |

---

## 📖 เอกสารเพิ่มเติม

- [NOTIFICATION_SETUP.md](./NOTIFICATION_SETUP.md) - คู่มือฉบับเต็ม
- [USER_MANUAL.md](./USER_MANUAL.md) - คู่มือผู้ใช้งาน

---

## ✅ Checklist

- [ ] Run migrations
- [ ] ตั้งค่า mail configuration
- [ ] ตั้งค่า queue connection
- [ ] เริ่ม queue worker
- [ ] ทดสอบส่ง notification
- [ ] ตรวจสอบ email ได้รับ
- [ ] ตรวจสอบ database notifications
- [ ] (Production) ตั้งค่า Supervisor
- [ ] (Production) ตั้งค่า Cron

**🎉 เมื่อทำครบทุกข้อแล้ว ระบบแจ้งเตือนพร้อมใช้งาน!**
