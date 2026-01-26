<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;

// ตรวจสอบว่ามี admin user หรือไม่
$admin = User::where('email', 'admin@nitesa.local')->first();

if ($admin) {
    echo "✅ Admin user พบแล้ว!\n";
    echo "Email: {$admin->email}\n";
    echo "Name: {$admin->name}\n";
    echo "Role: {$admin->role->value}\n";
    echo "Is Active: " . ($admin->isActive ? 'Yes' : 'No') . "\n";
    echo "\n";
    echo "⚠️  ถ้าลืมรหัสผ่าน ให้รันคำสั่งนี้เพื่อรีเซ็ตรหัสผ่าน:\n";
    echo "php artisan tinker\n";
    echo ">>> \$user = App\Models\User::where('email', 'admin@nitesa.local')->first();\n";
    echo ">>> \$user->password = Hash::make('password');\n";
    echo ">>> \$user->save();\n";
} else {
    echo "❌ ไม่พบ admin user\n";
    echo "กำลังสร้าง admin user...\n";
    
    $admin = User::create([
        'name' => 'ผู้ดูแลระบบ',
        'email' => 'admin@nitesa.local',
        'password' => Hash::make('password'),
        'role' => Role::ADMIN,
        'isActive' => true,
    ]);
    
    echo "✅ สร้าง admin user สำเร็จ!\n";
    echo "Email: {$admin->email}\n";
    echo "Password: password\n";
}

echo "\n";
echo "📝 ข้อมูล Login:\n";
echo "Email: admin@nitesa.local\n";
echo "Password: password\n";
echo "\n";
