<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class AuthController extends Controller
{
    // --- SOCIALITE GOOGLE ---
    /**
     * Redirige al usuario a Google para autenticación social.
     *
     * @group Autenticación Social
     * @subgroup Google
     * @unauthenticated
     * @response 302 Redirección a Google OAuth.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    /**
     * Callback de Google para autenticación social.
     *
     * @group Autenticación Social
     * @subgroup Google
     * @unauthenticated
     * @response 200 {"user": {"id": 1, "name": "Google User", "email": "user@gmail.com"}, "token": "1|abc..."}
     * @response 401 {"error": "No autorizado"}
     */
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                'password' => bcrypt(uniqid()),
            ]
        );
        // Opcional: asignar rol por defecto
        if (!$user->hasRole('user')) {
            $user->assignRole('user');
        }
        $token = $user->createToken('google')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    // --- SOCIALITE FACEBOOK ---
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function handleFacebookCallback()
    /**
     * Callback de Facebook para autenticación social.
     *
     * @group Autenticación Social
     * @subgroup Facebook
     * @unauthenticated
     * @response 200 {"user": {"id": 1, "name": "Facebook User", "email": "user@fb.com"}, "token": "1|abc..."}
     * @response 401 {"error": "No autorizado"}
     */
    {
        $fbUser = Socialite::driver('facebook')->stateless()->user();
        $user = User::firstOrCreate(
            ['email' => $fbUser->getEmail()],
            [
                'name' => $fbUser->getName() ?? $fbUser->getNickname() ?? 'Facebook User',
                'password' => bcrypt(uniqid()),
            ]
        );
        if (!$user->hasRole('user')) {
            $user->assignRole('user');
        }
        $token = $user->createToken('facebook')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
    // Login básico con Sanctum
    /**
     * Iniciar sesión con email y contraseña.
     *
     * @group Autenticación
     * @bodyParam email string required El email del usuario. Example: user@example.com
     * @bodyParam password string required La contraseña. Example: secret
     * @response 200 {"user": {"id": 1, "name": "Juan", "email": "user@example.com"}, "token": "1|abc..."}
     * @response 401 {"error": "Credenciales inválidas"}
     * @unauthenticated
     */
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
    /**
     * Registro público de usuario (rol: user).
     *
     * @group Autenticación
     * @bodyParam name string required Nombre del usuario. Example: Juan
     * @bodyParam email string required Email único. Example: user@example.com
     * @bodyParam password string required Contraseña. Example: secret
     * @bodyParam password_confirmation string required Confirmación de contraseña. Example: secret
     * @response 201 {"user": {"id": 2, "name": "Juan", "email": "user@example.com"}, "token": "1|abc..."}
     * @unauthenticated
     */
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
    /**
     * Registro avanzado (solo admin puede asignar rol).
     *
     * @group Autenticación
     * @authenticated
     * @bodyParam name string required Nombre del usuario. Example: Admin
     * @bodyParam email string required Email único. Example: admin@example.com
     * @bodyParam password string required Contraseña. Example: secret
     * @bodyParam password_confirmation string required Confirmación de contraseña. Example: secret
     * @bodyParam role string required Rol a asignar (admin, editor, user). Example: editor
     * @response 201 {"user": {"id": 3, "name": "Admin", "email": "admin@example.com"}, "token": "1|abc...", "role": "editor"}
     * @response 403 {"error": "No autorizado"}
     */
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
    /**
     * Cerrar sesión (logout).
     *
     * @group Autenticación
     * @authenticated
     * @response 200 {"message": "Sesión cerrada correctamente"}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }


}
