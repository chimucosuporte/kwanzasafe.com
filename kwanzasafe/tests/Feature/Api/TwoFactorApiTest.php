<?php

use App\Models\User;
use App\Services\TotpService;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

/*
| Autenticação em 2 passos (TOTP) — /api/v1/2fa/* + gate no login.
| (O throttle é desligado aqui: partilha contador por rota/IP entre testes.)
*/
beforeEach(fn () => test()->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class));

it('o TotpService verifica o seu próprio código e rejeita um errado', function () {
    $totp = new TotpService();
    $secret = $totp->generateSecret();

    expect($totp->verify($secret, $totp->currentCode($secret)))->toBeTrue();
    expect($totp->verify($secret, '000000'))->toBeFalse();
});

it('activa o 2FA: enable → confirm', function () {
    Sanctum::actingAs($user = User::factory()->create());

    $secret = $this->postJson('/api/v1/2fa/enable')
        ->assertOk()
        ->assertJsonStructure(['secret', 'otpauth_uri'])
        ->json('secret');

    // Ainda não confirmado.
    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();

    $code = (new TotpService())->currentCode($secret);

    $this->postJson('/api/v1/2fa/confirm', ['code' => $code])
        ->assertOk()
        ->assertJsonPath('user.two_factor_enabled', true);

    expect($user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

it('recusa confirmação com código inválido', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/2fa/enable')->assertOk();

    $this->postJson('/api/v1/2fa/confirm', ['code' => '000000'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('code');
});

it('o login exige código quando o 2FA está activo', function () {
    $totp = new TotpService();
    $secret = $totp->generateSecret();
    $user = User::factory()->create([
        'email' => 'duasvias@example.com',
        'password' => Hash::make('Kwanza@2026'),
        'two_factor_secret' => $secret,
        'two_factor_confirmed_at' => now(),
    ]);

    // Sem código → 422 com two_factor_required.
    $this->postJson('/api/v1/login', [
        'email' => 'duasvias@example.com', 'password' => 'Kwanza@2026',
    ])
        ->assertStatus(422)
        ->assertJsonPath('two_factor_required', true);

    // Com código válido → token.
    $this->postJson('/api/v1/login', [
        'email' => 'duasvias@example.com', 'password' => 'Kwanza@2026',
        'two_factor_code' => $totp->currentCode($secret),
    ])
        ->assertOk()
        ->assertJsonStructure(['token', 'user']);
});

it('o login sem 2FA continua a funcionar normalmente', function () {
    User::factory()->create([
        'email' => 'simples@example.com',
        'password' => Hash::make('Kwanza@2026'),
    ]);

    $this->postJson('/api/v1/login', [
        'email' => 'simples@example.com', 'password' => 'Kwanza@2026',
    ])
        ->assertOk()
        ->assertJsonStructure(['token', 'user']);
});

it('desactiva o 2FA com a palavra-passe', function () {
    $secret = (new TotpService())->generateSecret();
    $user = User::factory()->create([
        'password' => Hash::make('Kwanza@2026'),
        'two_factor_secret' => $secret,
        'two_factor_confirmed_at' => now(),
    ]);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/2fa/disable', ['password' => 'Kwanza@2026'])
        ->assertOk()
        ->assertJsonPath('user.two_factor_enabled', false);

    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});
