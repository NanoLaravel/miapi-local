<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use App\Models\User;
use Exception;
use Spatie\Permission\Models\Role;

class ApiSocialAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crear roles necesarios para los tests (Spatie Permission).
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Limpiar cache de permisos y crear rol 'user' requerido por socialLoginOrRegister
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    }

    /**
     * Crea un objeto SocialiteUser para simular la respuesta del proveedor social.
     */
    private function makeSocialiteUser(string $id, string $email, string $name, string $avatar): SocialiteUser
    {
        $user           = new SocialiteUser();
        $user->id       = $id;
        $user->email    = $email;
        $user->name     = $name;
        $user->avatar   = $avatar;
        $user->nickname = null;
        $user->token    = 'fake-token';

        return $user;
    }

    /**
     * Crea un mock de provider que responde stateless()->userFromToken().
     */
    private function makeProviderMock($returnValue): object
    {
        $provider = Mockery::mock(\stdClass::class);
        $provider->shouldReceive('stateless')->andReturnSelf();

        if ($returnValue instanceof Exception) {
            $provider->shouldReceive('userFromToken')->andThrow($returnValue);
        } else {
            $provider->shouldReceive('userFromToken')->andReturn($returnValue);
        }

        return $provider;
    }

    /**
     * Test: login con token de Google crea usuario y devuelve token Sanctum.
     */
    public function test_google_token_login_creates_user_and_returns_token()
    {
        $socialUser = $this->makeSocialiteUser(
            'google-123',
            'john.doe@example.com',
            'John Doe',
            'https://example.com/avatar.jpg'
        );

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($this->makeProviderMock($socialUser));

        $response = $this->postJson('/api/auth/google/token', [
            'access_token' => 'dummy-google-token',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'token']);
        $this->assertDatabaseHas('users', [
            'email'     => 'john.doe@example.com',
            'google_id' => 'google-123',
        ]);
    }

    /**
     * Test: login con token de Google vincula cuenta existente por email.
     */
    public function test_google_token_login_links_existing_user()
    {
        User::factory()->create([
            'email'     => 'john.doe@example.com',
            'google_id' => null,
        ]);

        $socialUser = $this->makeSocialiteUser(
            'google-456',
            'john.doe@example.com',
            'John Doe',
            'https://example.com/avatar.jpg'
        );

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($this->makeProviderMock($socialUser));

        $response = $this->postJson('/api/auth/google/token', [
            'access_token' => 'dummy-google-token',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'email'     => 'john.doe@example.com',
            'google_id' => 'google-456',
        ]);
        $this->assertDatabaseCount('users', 1);
    }

    /**
     * Test: token de Facebook inválido devuelve 401.
     */
    public function test_facebook_token_login_invalid_token_returns_401()
    {
        Socialite::shouldReceive('driver')
            ->with('facebook')
            ->once()
            ->andReturn($this->makeProviderMock(new Exception('Invalid token')));

        $response = $this->postJson('/api/auth/facebook/token', [
            'access_token' => 'invalid-token',
        ]);

        $response->assertStatus(401);
        $response->assertJsonFragment(['error' => 'Token de Facebook inválido: Invalid token']);
    }

    /**
     * Test: solicitud sin access_token devuelve 422 (validación).
     */
    public function test_google_token_login_without_token_returns_422()
    {
        $response = $this->postJson('/api/auth/google/token', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['access_token']);
    }
}
?>
