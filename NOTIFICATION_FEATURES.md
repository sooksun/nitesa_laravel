# ✨ Notification Features - ระบบนิเทศ NITESA

## 📋 สรุปฟีเจอร์ที่เพิ่มเข้ามา

### 1. Notification Classes (4 ชนิด)

#### 📤 SupervisionSubmittedNotification
- **เมื่อไหร่**: ศึกษานิเทศก์ส่งการนิเทศเพื่ออนุมัติ
- **ส่งถึง**: ผู้บริหาร (EXECUTIVE) + Admin
- **ช่องทาง**: Email + Database
- **เนื้อหา**: ข้อมูลโรงเรียน, ประเภทการนิเทศ, ผู้นิเทศ, ลิงก์เพื่อพิจารณา

#### ✅ SupervisionApprovedNotification
- **เมื่อไหร่**: ผู้บริหารอนุมัติการนิเทศ
- **ส่งถึง**: ศึกษานิเทศก์ที่สร้างการนิเทศ
- **ช่องทาง**: Email + Database
- **เนื้อหา**: ข้อมูลการอนุมัติ, ผู้อนุมัติ, ลิงก์เพื่อดูรายละเอียด

#### 🔄 SupervisionRejectedNotification
- **เมื่อไหร่**: ผู้บริหารส่งกลับเพื่อแก้ไข
- **ส่งถึง**: ศึกษานิเทศก์ที่สร้างการนิเทศ
- **ช่องทาง**: Email + Database
- **เนื้อหา**: เหตุผลที่ส่งกลับ, ลิงก์เพื่อแก้ไข

#### 🌐 SupervisionPublishedNotification
- **เมื่อไหร่**: Admin เผยแพร่การนิเทศ
- **ส่งถึง**: โรงเรียน (SCHOOL role) + ศึกษานิเทศก์
- **ช่องทาง**: Email + Database
- **เนื้อหา**: ผลการนิเทศ, ลิงก์เพื่อดู, ลิงก์เพื่อยืนยันรับทราบ

---

## 🗂️ ไฟล์ที่สร้าง/แก้ไข

### ไฟล์ใหม่ที่สร้าง:
```
app/Notifications/
├── SupervisionSubmittedNotification.php    ✨ NEW
├── SupervisionApprovedNotification.php     ✨ NEW
├── SupervisionRejectedNotification.php     ✨ NEW
└── SupervisionPublishedNotification.php    ✨ NEW

database/migrations/
└── 2026_01_26_XXXXXX_create_notifications_table.php  ✨ NEW

Documentation/
├── NOTIFICATION_SETUP.md          ✨ NEW (คู่มือฉบับเต็ม)
├── NOTIFICATION_QUICKSTART.md     ✨ NEW (Quick Start)
└── NOTIFICATION_FEATURES.md       ✨ NEW (เอกสารนี้)
```

### ไฟล์ที่แก้ไข:
```
app/Livewire/Supervision/SupervisionShow.php     ✏️ UPDATED
├── เพิ่ม notification ใน submit()
├── เพิ่ม notification ใน approve()
├── เพิ่ม notification ใน reject()
├── เพิ่ม notification ใน publish()
├── เพิ่ม notifyExecutivesAndAdmins() method
└── เพิ่ม notifySchool() method

app/Http/Controllers/Api/SupervisionController.php  ✏️ UPDATED
├── เพิ่ม notification ใน submit()
├── เพิ่ม notification ใน approve()
├── เพิ่ม notification ใน reject() + reason parameter
├── เพิ่ม notification ใน publish()
├── เพิ่ม notifyExecutivesAndAdmins() method
└── เพิ่ม notifySchool() method

.env.example                                  ✏️ UPDATED
├── เพิ่ม QUEUE_CONNECTION=database
├── เพิ่ม Mail configuration ทั้ง Gmail, SendGrid, Mailpit
└── เพิ่ม SMS configuration (Vonage, Twilio)
```

---

## 🔧 Technical Details

### Notification Channels
```php
['mail', 'database']  // Default

// Optional: SMS (ต้องติดตั้งและตั้งค่าเพิ่ม)
// ['mail', 'database', 'nexmo']
```

### Queue Implementation
- ใช้ `implements ShouldQueue` - Notifications ทำงานใน background
- ไม่บล็อก user action
- สามารถ retry ได้ถ้าส่งไม่สำเร็จ

### Database Notifications Structure
```json
{
  "type": "supervision_submitted",
  "supervision_id": "01HQXXX...",
  "school_name": "โรงเรียนตัวอย่าง",
  "supervision_type": "นิเทศติดตาม",
  "supervision_date": "2026-01-26",
  "supervisor_name": "ชื่อศึกษานิเทศก์",
  "url": "https://nitesa.go.th/supervisions/01HQXXX...",
  "message": "มีการส่งการนิเทศเพื่อขออนุมัติ: โรงเรียนตัวอย่าง"
}
```

---

## 🎯 Workflow ที่ครบถ้วน

```
1. ศึกษานิเทศก์สร้างการนิเทศ (DRAFT)
   └─ ไม่มี notification

2. ศึกษานิเทศก์ส่งเพื่ออนุมัติ (SUBMITTED)
   └─ 📧 ส่ง SupervisionSubmittedNotification
       ↓
   ผู้บริหาร + Admin ทุกคน

3a. ผู้บริหารอนุมัติ (APPROVED)
    └─ 📧 ส่ง SupervisionApprovedNotification
        ↓
    ศึกษานิเทศก์ที่สร้าง

3b. ผู้บริหารส่งกลับ (NEEDS_IMPROVEMENT)
    └─ 📧 ส่ง SupervisionRejectedNotification
        ↓
    ศึกษานิเทศก์ที่สร้าง
    
    กลับไป step 1 เพื่อแก้ไขและส่งใหม่

4. Admin เผยแพร่ (PUBLISHED)
   └─ 📧 ส่ง SupervisionPublishedNotification
       ↓
   โรงเรียน + ศึกษานิเทศก์
```

---

## 📊 ตัวอย่าง Email Template

### SupervisionSubmittedNotification
```
Subject: มีการส่งการนิเทศเพื่อขออนุมัติ - โรงเรียนตัวอย่าง

สวัสดีครับ/ค่ะ คุณสมชาย

มีการส่งการนิเทศใหม่เพื่อขออนุมัติจากคุณ

โรงเรียน: โรงเรียนตัวอย่าง
ประเภทการนิเทศ: นิเทศติดตาม
วันที่นิเทศ: 26/01/2026
ผู้นิเทศ: คุณสมศรี

[ดูรายละเอียดและพิจารณา] <-- Button

กรุณาพิจารณาอนุมัติหรือส่งกลับเพื่อแก้ไข

ขอบคุณครับ/ค่ะ
ระบบนิเทศ NITESA
```

---

## 🚀 การใช้งานใน Code

### ส่ง Notification แบบ Simple:
```php
$user->notify(new SupervisionSubmittedNotification($supervision));
```

### ส่ง Notification หลายคน:
```php
$users = User::where('role', 'EXECUTIVE')->get();
Notification::send($users, new SupervisionSubmittedNotification($supervision));
```

### ส่งแบบกำหนดเวลา (Delayed):
```php
$user->notify(
    (new SupervisionSubmittedNotification($supervision))
        ->delay(now()->addMinutes(10))
);
```

### ดึง Notifications ของ User:
```php
// Unread
$unread = auth()->user()->unreadNotifications;

// All
$all = auth()->user()->notifications;

// Mark as read
auth()->user()->unreadNotifications->markAsRead();

// ลบ notification
auth()->user()->notifications()->where('id', $notificationId)->delete();
```

---

## 🎨 UI Components ที่แนะนำ (TODO)

### 1. Notification Bell (ใน Navigation)
```blade
<div class="notification-bell">
    @if(auth()->user()->unreadNotifications->count() > 0)
        <span class="badge">{{ auth()->user()->unreadNotifications->count() }}</span>
    @endif
    <svg>...</svg> <!-- Bell icon -->
</div>
```

### 2. Notification Dropdown
```blade
<div class="notification-dropdown">
    @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
        <div class="notification-item">
            <p>{{ $notification->data['message'] }}</p>
            <a href="{{ $notification->data['url'] }}">ดูรายละเอียด</a>
        </div>
    @empty
        <p>ไม่มีการแจ้งเตือนใหม่</p>
    @endforelse
</div>
```

### 3. Notification Center Page
หน้าสำหรับแสดง notifications ทั้งหมด พร้อมการกรองและค้นหา

---

## 📈 Metrics & Monitoring

### ข้อมูลที่ควร Monitor:
- จำนวน notifications ที่ส่งต่อวัน
- Email delivery rate
- Failed jobs count
- Average notification delivery time
- User engagement rate (open/click)

### ตัวอย่าง Query:
```php
// Notifications ที่ส่งวันนี้
Notification::whereDate('created_at', today())->count();

// Failed jobs
DB::table('failed_jobs')->count();

// Unread notifications ทั้งระบบ
DB::table('notifications')->whereNull('read_at')->count();
```

---

## 🔐 Security & Privacy

- ✅ Notifications ส่งเฉพาะคนที่เกี่ยวข้อง (Role-based)
- ✅ URLs มี authentication guard
- ✅ ข้อมูลใน email ไม่มีข้อมูลอ่อนไหว (sensitive data)
- ✅ Database notifications เห็นได้เฉพาะ owner
- ⚠️ ควรเพิ่ม rate limiting สำหรับการส่ง email
- ⚠️ ควรมี unsubscribe option

---

## 🎓 Best Practices

1. **Always use Queue** - ไม่ทำให้ user รอ
2. **Test notifications** - ทดสอบทุก scenario ก่อน deploy
3. **Monitor failed jobs** - ติดตามและแก้ไขทันที
4. **Meaningful messages** - ข้อความชัดเจน มี context
5. **Actionable** - ให้ link หรือ action ที่ user ทำได้
6. **Not too many** - ไม่ส่งบ่อยเกินไป (notification fatigue)

---

## 📞 Support

หากมีคำถามหรือพบปัญหา:
- 📧 Email: support@nitesa.go.th
- 📖 Docs: `/NOTIFICATION_SETUP.md`
- 🚀 Quick Start: `/NOTIFICATION_QUICKSTART.md`

---

**🎉 ระบบแจ้งเตือนพร้อมใช้งานแล้ว!**
