<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "========================================\n";
echo "  Testing Login Credentials\n";
echo "========================================\n\n";

$email = 'admin@nitesa.local';
$password = 'password';

// ตรวจสอบ user
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User not found!\n";
    echo "Creating user...\n";
    $user = User::create([
        'name' => 'ผู้ดูแลระบบ',
        'email' => $email,
        'password' => Hash::make($password),
        'role' => \App\Enums\Role::ADMIN,
        'isActive' => true,
    ]);
    echo "✅ User created!\n\n";
}

echo "User Info:\n";
echo "  Email: {$user->email}\n";
echo "  Name: {$user->name}\n";
echo "  Role: {$user->role->value}\n";
echo "  Is Active: " . ($user->isActive ? 'Yes' : 'No') . "\n";
echo "\n";

// ทดสอบ password verification
echo "Testing Password Verification:\n";
$passwordCheck = Hash::check($password, $user->password);
echo "  Password match: " . ($passwordCheck ? '✅ Yes' : '❌ No') . "\n";
echo "\n";

// ทดสอบ Auth::attempt
echo "Testing Auth::attempt:\n";
$credentials = [
    'email' => $email,
    'password' => $password,
];
$attempt = Auth::attempt($credentials);
echo "  Auth::attempt: " . ($attempt ? '✅ Success' : '❌ Failed') . "\n";
echo "\n";

if ($attempt) {
    echo "✅ Login should work!\n";
    echo "\n";
    echo "Try logging in with:\n";
    echo "  Email: {$email}\n";
    echo "  Password: {$password}\n";
} else {
    echo "❌ Login will fail. Checking issues...\n";
    echo "\n";
    
    // ตรวจสอบ password hash
    echo "Password Hash Info:\n";
    echo "  Stored hash: " . substr($user->password, 0, 20) . "...\n";
    echo "  Hash length: " . strlen($user->password) . "\n";
    echo "  Is bcrypt: " . (str_starts_with($user->password, '$2y$') ? 'Yes' : 'No') . "\n";
    echo "\n";
    
    // ลองรีเซ็ตรหัสผ่านใหม่
    echo "Resetting password...\n";
    $user->password = Hash::make($password);
    $user->isActive = true;
    $user->save();
    
    echo "✅ Password reset!\n";
    echo "Try logging in again.\n";
}

echo "\n";
