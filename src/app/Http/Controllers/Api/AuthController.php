<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Login básico con Sanctum
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }
        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    // Registro público con Sanctum
    public function registerPublic(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        $user->assignRole('user');
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // Registro avanzado solo para admin
    public function registerWithRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,editor,user',
        ]);
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        $user->assignRole($request->role);
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token,
            'role' => $request->role
        ], 201);
    }

    // Logout con Sanctum
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    // Socialite Google
    public function redirectToGoogle()
    {
        // Implementa redirección a Google
        return response()->json(['message' => 'Google redirect (implementa lógica)']);
    }
    public function handleGoogleCallback()
    {
        // Implementa callback de Google
        return response()->json(['message' => 'Google callback (implementa lógica)']);
    }

    // Socialite Facebook
    public function redirectToFacebook()
    {
        // Implementa redirección a Facebook
        return response()->json(['message' => 'Facebook redirect (implementa lógica)']);
    }
    public function handleFacebookCallback()
    {
        // Implementa callback de Facebook
        return response()->json(['message' => 'Facebook callback (implementa lógica)']);
    }
}
