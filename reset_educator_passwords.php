<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

// Get the educator role
$educatorRole = Role::where('name', 'educator')->first();

if (!$educatorRole) {
    echo "Educator role not found. Creating it...\n";
    $educatorRole = Role::create(['name' => 'educator']);
}

// Reset educator passwords
$educators = [
    [
        'email' => 'educator1@example.com',
        'name' => 'Charwel Glera',
        'password' => 'password123',
        'sex' => 'male',
        'gender' => 'male'
    ],
    [
        'email' => 'educator2@example.com',
        'name' => 'Jane Tumulak',
        'password' => 'password123',
        'sex' => 'female',
        'gender' => 'female'
    ]
];

foreach ($educators as $educatorData) {
    $educator = User::where('email', $educatorData['email'])->first();
    
    if ($educator) {
        echo "Updating educator: {$educatorData['email']}\n";
        $educator->password = Hash::make($educatorData['password']);
        $educator->sex = $educatorData['sex'];
        $educator->gender = $educatorData['gender'];
        $educator->save();
        
        // Make sure the educator role is attached
        if (!$educator->roles()->where('name', 'educator')->exists()) {
            echo "Attaching educator role to: {$educatorData['email']}\n";
            $educator->roles()->attach($educatorRole);
        }
    } else {
        echo "Creating new educator: {$educatorData['email']}\n";
        $educator = User::create([
            'email' => $educatorData['email'],
            'name' => $educatorData['name'],
            'password' => Hash::make($educatorData['password']),
            'sex' => $educatorData['sex'],
            'gender' => $educatorData['gender']
        ]);
        
        $educator->roles()->attach($educatorRole);
    }
}

echo "Educator passwords have been reset successfully.\n";
echo "You can now login with:\n";
echo "Email: educator1@example.com\n";
echo "Password: password123\n";
echo "OR\n";
echo "Email: educator2@example.com\n";
echo "Password: password123\n";
