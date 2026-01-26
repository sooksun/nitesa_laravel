# 🔐 ข้อมูล Login สำหรับ Local Development

## 👤 Default Users

หลังจากรัน `php artisan db:seed` หรือใช้ seeder จะมี users ต่อไปนี้:

### 1. Admin (ผู้ดูแลระบบ)
```
Email: admin@nitesa.local
Password: password
Role: ADMIN
```

### 2. Executive (ผู้บริหาร)
```
Email: executive@nitesa.local
Password: password
Role: EXECUTIVE
```

### 3. Supervisor (ศึกษานิเทศก์)
```
Email: supervisor1@nitesa.local
Password: password
Role: SUPERVISOR

Email: supervisor2@nitesa.local
Password: password
Role: SUPERVISOR

Email: supervisor3@nitesa.local
Password: password
Role: SUPERVISOR
```

### 4. School (โรงเรียน)
```
Email: school1@nitesa.local
Password: password
Role: SCHOOL

Email: school2@nitesa.local
Password: password
Role: SCHOOL

... (school3-5)
```

---

## 🔧 การรีเซ็ตรหัสผ่าน

### วิธีที่ 1: ใช้ Script
```bash
php reset-admin-password.php
```

### วิธีที่ 2: ใช้ Artisan Tinker
```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'admin@nitesa.local')->first();
$user->password = Hash::make('password');
$user->isActive = true;
$user->save();
exit
```

### วิธีที่ 3: สร้าง User ใหม่
```bash
php artisan tinker
```

```php
App\Models\User::create([
    'name' => 'ผู้ดูแลระบบ',
    'email' => 'admin@nitesa.local',
    'password' => Hash::make('password'),
    'role' => \App\Enums\Role::ADMIN,
    'isActive' => true,
]);
exit
```

---

## ⚠️ Troubleshooting

### ปัญหา: Login ไม่ได้ "อีเมลหรือรหัสผ่านไม่ถูกต้อง"

**สาเหตุที่เป็นไปได้:**
1. Password ไม่ถูกต้อง
2. User ไม่มีในระบบ
3. User ถูกระงับ (isActive = false)
4. Database connection issue

**แก้ไข:**
1. รีเซ็ตรหัสผ่าน:
   ```bash
   php reset-admin-password.php
   ```

2. ตรวจสอบ user:
   ```bash
   php create-admin-user.php
   ```

3. ตรวจสอบ database connection:
   ```bash
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

4. ตรวจสอบ user status:
   ```bash
   php artisan tinker
   >>> $user = App\Models\User::where('email', 'admin@nitesa.local')->first();
   >>> echo $user->isActive ? 'Active' : 'Inactive';
   ```

---

## 🔒 Security Note

⚠️ **คำเตือน**: รหัสผ่าน `password` ใช้สำหรับ Development เท่านั้น!

สำหรับ Production:
- เปลี่ยนรหัสผ่านให้แข็งแรง
- ใช้ environment variables
- ตั้งค่า password policy
- ใช้ 2FA (ถ้าเป็นไปได้)

---

## 📝 สร้าง Users เพิ่มเติม

### สร้าง Admin ใหม่:
```php
App\Models\User::create([
    'name' => 'ชื่อผู้ใช้',
    'email' => 'email@example.com',
    'password' => Hash::make('รหัสผ่าน'),
    'role' => \App\Enums\Role::ADMIN,
    'isActive' => true,
]);
```

### สร้าง Supervisor ใหม่:
```php
App\Models\User::create([
    'name' => 'ศึกษานิเทศก์',
    'email' => 'supervisor@example.com',
    'password' => Hash::make('password'),
    'role' => \App\Enums\Role::SUPERVISOR,
    'isActive' => true,
]);
```

---

**🔐 ข้อมูล Login พร้อมใช้งาน!**

ลอง login ด้วย:
- Email: `admin@nitesa.local`
- Password: `password`
