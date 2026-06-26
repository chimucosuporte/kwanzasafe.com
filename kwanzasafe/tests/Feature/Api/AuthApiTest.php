<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

/*
| FASE 0 — Autenticação da API mobile (tokens Sanctum).
| Cobre register / login / me / logout e o gating por token.
*/

it('regista um novo cliente e devolve token + utilizador', function () {
    $response = $this->postJson('/api/v1/register', [
        'name'                  => 'João Cliente',
        'email'                 => 'joao@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
        'device_name'           => 'iPhone de teste',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['token', 'user' => ['id', 'email', 'role', 'is_fully_verified']])
        ->assertJsonPath('user.email', 'joao@example.com')
        ->assertJsonPath('user.role', 'client');

    $this->assertDatabaseHas('users', ['email' => 'joao@example.com', 'full_name' => 'João Cliente']);
    expect($response->json('token'))->not->toBeEmpty();
});

it('rejeita registo com email duplicado', function () {
    User::factory()->create(['email' => 'dup@example.com']);

    $this->postJson('/api/v1/register', [
        'name'                  => 'Outro',
        'email'                 => 'dup@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

it('autentica com credenciais válidas e devolve token', function () {
    $user = User::factory()->create(['password' => bcrypt('segredo123')]);

    $response = $this->postJson('/api/v1/login', [
        'email'    => $user->email,
        'password' => 'segredo123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'email']])
        ->assertJsonPath('user.id', $user->id);
});

it('rejeita login com password errada (422)', function () {
    $user = User::factory()->create(['password' => bcrypt('segredo123')]);

    $this->postJson('/api/v1/login', [
        'email'    => $user->email,
        'password' => 'errada',
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

it('devolve o perfil em /me com token válido', function () {
    $user  = User::factory()->create();
    $token = $user->createToken('mobile')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.email', $user->email);
});

it('bloqueia /me sem token (401)', function () {
    $this->getJson('/api/v1/me')->assertUnauthorized();
});

it('rejeita um token revogado com 401', function () {
    $user      = User::factory()->create();
    $plainText = $user->createToken('mobile')->plainTextToken;

    // Revoga o token directamente (simula o efeito do logout, sem pedido autenticado
    // prévio no mesmo teste — evita o cache de guard do test harness).
    [$id] = explode('|', $plainText, 2);
    PersonalAccessToken::find($id)->delete();

    $this->withHeader('Authorization', "Bearer {$plainText}")
        ->getJson('/api/v1/me')
        ->assertUnauthorized();
});

it('revoga o token no logout', function () {
    $user      = User::factory()->create();
    $plainText = $user->createToken('mobile')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$plainText}")
        ->postJson('/api/v1/logout')
        ->assertOk();

    // Logout revoga o token: deixa de existir na BD (verificação HTTP→401 feita via curl).
    [$id] = explode('|', $plainText, 2);
    expect(PersonalAccessToken::find($id))->toBeNull();
});
