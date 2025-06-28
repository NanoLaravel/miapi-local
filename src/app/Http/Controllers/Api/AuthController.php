<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Login tradicional
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user]);
    }

    // Login con Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::firstOrCreate([
            'email' => $googleUser->getEmail()
        ], [
            'name' => $googleUser->getName() ?? $googleUser->getNickname(),
            'password' => Hash::make(Str::random(16)),
        ]);
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user]);
    }

    // Login con Facebook
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }
    public function handleFacebookCallback()
    {
        $fbUser = Socialite::driver('facebook')->stateless()->user();
        $user = User::firstOrCreate([
            'email' => $fbUser->getEmail()
        ], [
            'name' => $fbUser->getName() ?? $fbUser->getNickname(),
            'password' => Hash::make(Str::random(16)),
        ]);
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user]);
    }

    // Registro de usuario
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $role = 'user';
        if ($request->user() && $request->user()->role === 'admin' && $request->filled('role')) {
            $role = $request->role;
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'role' => $role, // Se asignará con Spatie
        ]);
        if (!$user) {
            Log::error('No se pudo crear el usuario', ['request' => $request->all()]);
            return response()->json(['message' => 'No se pudo crear el usuario'], 500);
        }
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    // Registro público (solo rol user)
    public function registerPublic(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole('user');
        if (!$user) {
            Log::error('No se pudo crear el usuario', ['request' => $request->all()]);
            return response()->json(['message' => 'No se pudo crear el usuario'], 500);
        }
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    // Registro avanzado (solo admin puede asignar rol)
    public function registerWithRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,editor,user,guest',
        ]);
        // Aquí luego se usará Spatie para asignar roles
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole($request->role);
        if (!$user) {
            Log::error('No se pudo crear el usuario (admin)', ['request' => $request->all()]);
            return response()->json(['message' => 'No se pudo crear el usuario'], 500);
        }
        // $user->assignRole($request->role); // Se agregará tras instalar Spatie
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
