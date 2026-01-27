<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'test@example.com')->first();

if ($user) {
    $user->password = Hash::make('password123');
    $user->save();
    echo "Password reset successfully!\n";
    echo "Email: test@example.com\n";
    echo "Password: password123\n";
    
    // Provjeri da li radi
    if (Hash::check('password123', $user->password)) {
        echo "Password verification: OK\n";
    } else {
        echo "Password verification: FAILED\n";
    }
} else {
    echo "User not found! Creating new user...\n";
    $user = User::create([
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'user_type' => 'student',
    ]);
    echo "User created successfully!\n";
    echo "Email: test@example.com\n";
    echo "Password: password123\n";
}
