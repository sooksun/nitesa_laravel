# คู่มือการตั้งค่าระบบแจ้งเตือน (Notification System)

## 📧 ภาพรวมระบบแจ้งเตือน

ระบบนิเทศ NITESA มีระบบแจ้งเตือนอัตโนมัติผ่าน Email และ Database (in-app notification) เมื่อมีเหตุการณ์สำคัญเกิดขึ้น:

### เหตุการณ์ที่มีการแจ้งเตือน

1. **การส่งการนิเทศเพื่ออนุมัติ** (`SupervisionSubmittedNotification`)
   - ส่งถึง: ผู้บริหาร (EXECUTIVE) และผู้ดูแลระบบ (ADMIN)
   - เมื่อ: ศึกษานิเทศก์ส่งการนิเทศเพื่อขออนุมัติ

2. **การอนุมัติการนิเทศ** (`SupervisionApprovedNotification`)
   - ส่งถึง: ศึกษานิเทศก์ที่สร้างการนิเทศ
   - เมื่อ: ผู้บริหารอนุมัติการนิเทศ

3. **การส่งกลับเพื่อแก้ไข** (`SupervisionRejectedNotification`)
   - ส่งถึง: ศึกษานิเทศก์ที่สร้างการนิเทศ
   - เมื่อ: ผู้บริหารส่งกลับเพื่อปรับปรุง

4. **การเผยแพร่การนิเทศ** (`SupervisionPublishedNotification`)
   - ส่งถึง: โรงเรียนที่ถูกนิเทศ และศึกษานิเทศก์
   - เมื่อ: ผู้ดูแลระบบเผยแพร่การนิเทศ

---

## 🚀 ขั้นตอนการติดตั้ง

### 1. สร้าง Notifications Table

```bash
php artisan migrate
```

### 2. สร้าง Jobs Table (สำหรับ Queue)

```bash
php artisan queue:table
php artisan migrate
```

### 3. ตั้งค่า Mail Configuration

#### ตัวเลือก A: ใช้ Gmail (แนะนำสำหรับการทดสอบและ Production ขนาดเล็ก)

1. สร้าง App-Specific Password:
   - ไปที่ https://myaccount.google.com/apppasswords
   - เลือก "Mail" และ "Other (Custom name)"
   - คัดลอก password ที่ได้

2. แก้ไขไฟล์ `.env`:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-specific-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="noreply@nitesa.go.th"
   MAIL_FROM_NAME="ระบบนิเทศ NITESA"
   ```

#### ตัวเลือก B: ใช้ SendGrid (แนะนำสำหรับ Production)

1. สมัคร SendGrid: https://sendgrid.com/
2. สร้าง API Key
3. แก้ไขไฟล์ `.env`:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.sendgrid.net
   MAIL_PORT=587
   MAIL_USERNAME=apikey
   MAIL_PASSWORD=your-sendgrid-api-key
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="noreply@nitesa.go.th"
   MAIL_FROM_NAME="ระบบนิเทศ NITESA"
   ```

#### ตัวเลือก C: ใช้ Mailpit (สำหรับการทดสอบ Local)

Mailpit มาพร้อมกับ Laravel Herd/Valet/Homestead:
```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

เปิด Mailpit UI: http://localhost:8025

### 4. ตั้งค่า Queue Connection

แก้ไขไฟล์ `.env`:
```env
QUEUE_CONNECTION=database
```

### 5. เริ่มต้น Queue Worker

#### Development:
```bash
php artisan queue:work
```

#### Production (Supervisor):
สร้างไฟล์ `/etc/supervisor/conf.d/nitesa-worker.conf`:
```ini
[program:nitesa-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/nitesa2/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/nitesa2/storage/logs/worker.log
stopwaitsecs=3600
```

จากนั้น:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start nitesa-worker:*
```

---

## 📱 การเพิ่ม SMS Notification (Optional)

### ตัวเลือก A: Vonage (Nexmo)

1. สมัคร: https://www.vonage.com/
2. ติดตั้ง package:
   ```bash
   composer require laravel/vonage-notification-channel
   ```
3. แก้ไข `.env`:
   ```env
   VONAGE_KEY=your-api-key
   VONAGE_SECRET=your-api-secret
   VONAGE_SMS_FROM=NITESA
   ```
4. เพิ่ม `phone` field ในตาราง `user`
5. Uncomment SMS channel ใน notification classes

### ตัวเลือก B: Twilio

1. สมัคร: https://www.twilio.com/
2. ติดตั้ง package:
   ```bash
   composer require twilio/sdk
   ```
3. แก้ไข `.env`:
   ```env
   TWILIO_SID=your-account-sid
   TWILIO_TOKEN=your-auth-token
   TWILIO_FROM=+1234567890
   ```

---

## 🧪 การทดสอบ Notification

### ทดสอบส่ง Email:

```bash
php artisan tinker
```

```php
$user = App\Models\User::first();
$supervision = App\Models\Supervision::first();

// ทดสอบ SupervisionSubmittedNotification
$user->notify(new App\Notifications\SupervisionSubmittedNotification($supervision));

// ทดสอบ SupervisionApprovedNotification
$user->notify(new App\Notifications\SupervisionApprovedNotification($supervision, 'ผู้ทดสอบ'));

// ทดสอบ SupervisionRejectedNotification
$user->notify(new App\Notifications\SupervisionRejectedNotification($supervision, 'ผู้ทดสอบ', 'เหตุผลทดสอบ'));

// ทดสอบ SupervisionPublishedNotification
$user->notify(new App\Notifications\SupervisionPublishedNotification($supervision));
```

### ตรวจสอบ Queue Jobs:

```bash
# ดู jobs ใน queue
php artisan queue:monitor

# ดู failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry {job-id}

# Retry all failed jobs
php artisan queue:retry all
```

---

## 🔧 Troubleshooting

### ปัญหา: Email ไม่ถูกส่ง

1. ตรวจสอบ Queue Worker ทำงานหรือไม่:
   ```bash
   ps aux | grep queue:work
   ```

2. ตรวจสอบ logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. ตรวจสอบ mail configuration:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### ปัญหา: Queue Jobs ไม่ทำงาน

1. ตรวจสอบ `QUEUE_CONNECTION` ใน `.env`
2. Run migration สำหรับ jobs table
3. Restart queue worker:
   ```bash
   php artisan queue:restart
   ```

### ปัญหา: Gmail ปฏิเสธการเชื่อมต่อ

1. เปิด "Less secure app access" หรือใช้ App-Specific Password
2. ตรวจสอบ 2FA settings
3. ลอง port 465 (SSL) แทน 587 (TLS)

---

## 📊 Database Notifications (In-App)

Notifications จะถูกบันทึกในตาราง `notifications` และสามารถแสดงผลใน UI ได้:

### ดึงข้อมูล Notifications:

```php
// ดึง unread notifications
$notifications = auth()->user()->unreadNotifications;

// ดึง all notifications
$notifications = auth()->user()->notifications;

// Mark as read
$notification->markAsRead();

// Mark all as read
auth()->user()->unreadNotifications->markAsRead();
```

### ตัวอย่าง Blade Template:

```blade
@if(auth()->user()->unreadNotifications->count() > 0)
    <div class="notifications">
        <span class="badge">{{ auth()->user()->unreadNotifications->count() }}</span>
        
        @foreach(auth()->user()->unreadNotifications as $notification)
            <div class="notification-item">
                <p>{{ $notification->data['message'] }}</p>
                <a href="{{ $notification->data['url'] }}">ดูรายละเอียด</a>
            </div>
        @endforeach
    </div>
@endif
```

---

## 🎯 Best Practices

1. **ใช้ Queue เสมอ** - Notifications ควรทำงานใน background เพื่อไม่ให้ user รอ
2. **Graceful Degradation** - ถ้า email ส่งไม่ได้ ระบบควรทำงานต่อได้
3. **Rate Limiting** - จำกัดจำนวน emails ที่ส่งต่อชั่วโมง
4. **Monitoring** - ติดตาม failed jobs และ email delivery rate
5. **Testing** - ทดสอบทุก notification ก่อน deploy
6. **Unsubscribe Option** - ให้ users เลือกได้ว่าต้องการรับ notification ประเภทไหน

---

## 📚 เอกสารเพิ่มเติม

- [Laravel Notifications](https://laravel.com/docs/10.x/notifications)
- [Laravel Queues](https://laravel.com/docs/10.x/queues)
- [Laravel Mail](https://laravel.com/docs/10.x/mail)
- [Supervisor Documentation](http://supervisord.org/)

---

## 🆘 การติดต่อสนับสนุน

หากมีปัญหาหรือข้อสงสัย กรุณาติดต่อ:
- Email: support@nitesa.go.th
- GitHub Issues: [repository-url]
