<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('email', 'admin@nitesa.local')->first();

if ($admin) {
    $admin->password = Hash::make('password');
    $admin->isActive = true;
    $admin->save();
    
    echo "✅ รีเซ็ตรหัสผ่านสำเร็จ!\n";
    echo "Email: admin@nitesa.local\n";
    echo "Password: password\n";
    echo "\n";
    echo "ลอง login อีกครั้งด้วยข้อมูลข้างต้น\n";
} else {
    echo "❌ ไม่พบ admin user\n";
    echo "กำลังสร้าง admin user...\n";
    
    $admin = User::create([
        'name' => 'ผู้ดูแลระบบ',
        'email' => 'admin@nitesa.local',
        'password' => Hash::make('password'),
        'role' => \App\Enums\Role::ADMIN,
        'isActive' => true,
    ]);
    
    echo "✅ สร้าง admin user สำเร็จ!\n";
    echo "Email: admin@nitesa.local\n";
    echo "Password: password\n";
}
