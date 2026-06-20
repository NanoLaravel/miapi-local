<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Verificar si el usuario ya existe
    $existing = User::where('email', 'admin@nortedesantander.com')->first();
    
    if ($existing) {
        echo "✅ Usuario ya existe en la BD\n";
        echo "Email: " . $existing->email . "\n";
        echo "Nombre: " . $existing->name . "\n";
        echo "ID: " . $existing->id . "\n";
    } else {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@nortedesantander.com',
            'password' => Hash::make('Admin@12345'),
            'email_verified_at' => now(),
        ]);
        
        echo "✅ Usuario admin creado exitosamente\n";
        echo "Email: " . $user->email . "\n";
        echo "Contraseña: Admin@12345\n";
        echo "ID: " . $user->id . "\n";
    }
    
    // Listar todos los usuarios
    echo "\n📋 Todos los usuarios en la BD:\n";
    $users = User::all(['id', 'name', 'email']);
    foreach ($users as $u) {
        echo "  - " . $u->email . " (" . $u->name . ")\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
