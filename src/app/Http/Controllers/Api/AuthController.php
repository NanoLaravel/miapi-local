<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class AuthController extends Controller
{
    // --- HELPER DE AUTENTICACIÓN SOCIAL ---
    /**
     * Registra o inicia sesión a un usuario obtenido desde un proveedor social (Google o Facebook).
     */
    protected function socialLoginOrRegister($socialUser, string $provider)
    {
        // 1. Buscar por ID único del proveedor social
        $user = User::where($provider . '_id', $socialUser->getId())->first();

        if (!$user) {
            $email = $socialUser->getEmail();
            
            // 2. Si no tiene ID registrado, intentar buscar por email para vincular
            if ($email) {
                $user = User::where('email', $email)->first();
            }

            if ($user) {
                // Vincular cuenta existente
                $user->update([
                    $provider . '_id' => $socialUser->getId(),
                    'avatar_url' => $user->avatar_url ?? $socialUser->getAvatar(),
                ]);
            } else {
                // 3. Crear nuevo usuario si no existe
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? ucfirst($provider) . ' User',
                    'email' => $email ?? ($socialUser->getId() . '@' . $provider . '.com'),
                    'password' => bcrypt(uniqid()), // Contraseña aleatoria segura
                    $provider . '_id' => $socialUser->getId(),
                    'avatar_url' => $socialUser->getAvatar(),
                ]);
            }
        } else {
            // Actualizar avatar si el usuario no tiene uno y el social provee uno
            if (empty($user->avatar_url) && $socialUser->getAvatar()) {
                $user->update(['avatar_url' => $socialUser->getAvatar()]);
            }
        }

        // Asignar rol por defecto si no tiene ninguno
        if (!$user->hasRole('user')) {
            $user->assignRole('user');
        }

        // Crear token Sanctum para consumo API
        $token = $user->createToken($provider . '-auth')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

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

    /**
     * Callback de Google para autenticación social.
     *
     * @group Autenticación Social
     * @subgroup Google
     * @unauthenticated
     * @response 200 {"user": {"id": 1, "name": "Google User", "email": "user@gmail.com"}, "token": "1|abc..."}
     * @response 401 {"error": "No autorizado"}
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            return $this->socialLoginOrRegister($googleUser, 'google');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo autenticar con Google: ' . $e->getMessage()
            ], 401);
        }
    }

    /**
     * Iniciar sesión enviando un token de acceso de Google (para dispositivos móviles).
     *
     * @group Autenticación Social
     * @subgroup Google
     * @unauthenticated
     * @bodyParam access_token string required El token de acceso obtenido de Google SDK en la app móvil.
     * @response 200 {"user": {"id": 1, "name": "Google User", "email": "user@gmail.com"}, "token": "1|abc..."}
     * @response 401 {"error": "Token de Google inválido"}
     */
    public function loginWithGoogleToken(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($request->access_token);
                
            return $this->socialLoginOrRegister($googleUser, 'google');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Token de Google inválido: ' . $e->getMessage()
            ], 401);
        }
    }

    // --- SOCIALITE FACEBOOK ---
    /**
     * Redirige al usuario a Facebook para autenticación social.
     *
     * @group Autenticación Social
     * @subgroup Facebook
     * @unauthenticated
     * @response 302 Redirección a Facebook OAuth.
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    /**
     * Callback de Facebook para autenticación social.
     *
     * @group Autenticación Social
     * @subgroup Facebook
     * @unauthenticated
     * @response 200 {"user": {"id": 1, "name": "Facebook User", "email": "user@fb.com"}, "token": "1|abc..."}
     * @response 401 {"error": "No autorizado"}
     */
    public function handleFacebookCallback()
    {
        try {
            $fbUser = Socialite::driver('facebook')->stateless()->user();
            return $this->socialLoginOrRegister($fbUser, 'facebook');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo autenticar con Facebook: ' . $e->getMessage()
            ], 401);
        }
    }

    /**
     * Iniciar sesión enviando un token de acceso de Facebook (para dispositivos móviles).
     *
     * @group Autenticación Social
     * @subgroup Facebook
     * @unauthenticated
     * @bodyParam access_token string required El token de acceso obtenido de Facebook SDK en la app móvil.
     * @response 200 {"user": {"id": 1, "name": "Facebook User", "email": "user@fb.com"}, "token": "1|abc..."}
     * @response 401 {"error": "Token de Facebook inválido"}
     */
    public function loginWithFacebookToken(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            $fbUser = Socialite::driver('facebook')
                ->stateless()
                ->userFromToken($request->access_token);
                
            return $this->socialLoginOrRegister($fbUser, 'facebook');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Token de Facebook inválido: ' . $e->getMessage()
            ], 401);
        }
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
