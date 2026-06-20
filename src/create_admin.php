<?php
$_SERVER['LARAVEL_ENV'] = 'production';
require __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Verificar si el usuario ya existe
    $existing = User::where('email', 'admin@nortedesantander.com')->first();
    
    if ($existing) {
        echo "✅ Usuario ya existe\n";
        echo "Email: " . $existing->email . "\n";
        echo "ID: " . $existing->id . "\n";
    } else {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@nortedesantander.com',
            'password' => Hash::make('Admin@12345'),
            'email_verified_at' => now(),
        ]);
        
        echo "✅ Usuario creado exitosamente\n";
        echo "Email: " . $user->email . "\n";
        echo "Contraseña: Admin@12345\n";
        echo "ID: " . $user->id . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
