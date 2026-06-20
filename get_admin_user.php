<?php
echo "=== USUARIOS EN BASE DE DATOS ===\n";
$users = App\Models\User::all(['id', 'name', 'email'])->toArray();
echo "Total usuarios: " . count($users) . "\n\n";
foreach ($users as $user) {
    echo "ID: " . $user['id'] . " | Email: " . $user['email'] . " | Nombre: " . $user['name'] . "\n";
}
echo "\n=== BUSCANDO ADMIN ===\n";
$admin = App\Models\User::whereEmail('admin@example.com')->first();
if ($admin) {
    echo "✅ Admin encontrado: " . $admin->email . "\n";
    echo "Nombre: " . $admin->name . "\n";
    echo "ID: " . $admin->id . "\n";
} else {
    echo "❌ No hay admin con email admin@example.com\n";
    echo "Probando primer usuario...\n";
    $first = App\Models\User::first();
    if ($first) {
        echo "Primer usuario: " . $first->email . "\n";
    }
}
