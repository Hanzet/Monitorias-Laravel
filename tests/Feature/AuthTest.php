<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test de registro de usuario exitoso
     */
    public function test_user_can_register(): void
    {
        $userData = [
            'name' => 'Juan Pérez',
            'email' => 'juan@ejemplo.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'created_at'
                        ],
                        'token',
                        'token_type'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente'
                ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Juan Pérez',
            'email' => 'juan@ejemplo.com'
        ]);
    }

    /**
     * Test de registro con datos inválidos
     */
    public function test_user_cannot_register_with_invalid_data(): void
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors'
                ])
                ->assertJson([
                    'success' => false
                ]);
    }

    /**
     * Test de login exitoso
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@ejemplo.com',
            'password' => bcrypt('password123')
        ]);

        $loginData = [
            'email' => 'test@ejemplo.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'created_at'
                        ],
                        'token',
                        'token_type'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Inicio de sesión exitoso'
                ]);
    }

    /**
     * Test de login con credenciales inválidas
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@ejemplo.com',
            'password' => bcrypt('password123')
        ]);

        $loginData = [
            'email' => 'test@ejemplo.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ]);
    }

    /**
     * Test de obtener información del usuario autenticado
     */
    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'email_verified_at',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true
                ]);
    }

    /**
     * Test de logout exitoso
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Sesión cerrada exitosamente'
                ]);

        // Verificar que el token fue revocado
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token)
        ]);
    }

    /**
     * Test de logout de todas las sesiones
     */
    public function test_user_can_logout_all_sessions(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('test-token-1')->plainTextToken;
        $token2 = $user->createToken('test-token-2')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
            'Accept' => 'application/json'
        ])->postJson('/api/logout-all');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Todas las sesiones han sido cerradas exitosamente'
                ]);

        // Verificar que todos los tokens fueron revocados
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token1)
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token2)
        ]);
    }

    /**
     * Test de refresh token
     */
    public function test_user_can_refresh_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/refresh');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'token',
                        'token_type'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Token refrescado exitosamente'
                ]);

        // Verificar que el token anterior fue revocado
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token)
        ]);
    }

    /**
     * Test de acceso a ruta protegida sin token
     */
    public function test_user_cannot_access_protected_route_without_token(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    /**
     * Test de acceso a ruta protegida con token inválido
     */
    public function test_user_cannot_access_protected_route_with_invalid_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json'
        ])->getJson('/api/me');

        $response->assertStatus(401);
    }
} 