<?php

// Skripta za kreiranje test korisnika
// Pokreni: php create_test_user.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Provjeri da li korisnik već postoji
$user = User::where('email', 'test@example.com')->first();

if ($user) {
    echo "User already exists!\n";
    echo "Email: {$user->email}\n";
    echo "Password: password123\n";
} else {
    // Kreiraj test korisnika
    $user = User::create([
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'user_type' => 'student',
    ]);

    echo "Test user created successfully!\n";
    echo "Email: test@example.com\n";
    echo "Password: password123\n";
}
