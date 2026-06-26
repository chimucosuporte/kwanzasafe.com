<?php

use App\Models\PaymentWallet;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

/*
| Carteiras de recepção (Bybit / Binance / RedotPay) — /api/v1/wallets.
| Anti-fraude: holder_name tem de coincidir com o full_name do KYC.
*/

it('lista as carteiras do próprio utilizador', function () {
    $user = User::factory()->create(['full_name' => 'Ana Exemplo']);
    PaymentWallet::create([
        'user_id' => $user->id, 'provider' => 'bybit',
        'identifier' => '123456', 'holder_name' => 'Ana Exemplo',
    ]);
    // Carteira de outro utilizador não deve aparecer.
    PaymentWallet::create([
        'user_id' => User::factory()->create()->id, 'provider' => 'binance',
        'identifier' => 'outro@email.com', 'holder_name' => 'Outro',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/wallets')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.provider', 'bybit');
});

it('adiciona uma carteira com titular igual ao KYC', function () {
    $user = User::factory()->create(['full_name' => 'Ana Exemplo']);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/wallets', [
        'provider' => 'bybit', 'identifier' => '987654321',
        'holder_name' => 'Ana Exemplo', 'network' => 'TRC20', 'is_default' => true,
    ])
        ->assertCreated()
        ->assertJsonPath('data.provider', 'bybit')
        ->assertJsonPath('data.is_default', true);

    $this->assertDatabaseHas('payment_wallets', [
        'user_id' => $user->id, 'provider' => 'bybit', 'is_default' => true,
    ]);
});

it('recusa carteira com titular diferente do KYC (422)', function () {
    $user = User::factory()->create(['full_name' => 'Ana Exemplo']);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/wallets', [
        'provider' => 'binance', 'identifier' => 'fraude@email.com',
        'holder_name' => 'Outra Pessoa',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('holder_name');
});

it('recusa carteira duplicada (422)', function () {
    $user = User::factory()->create(['full_name' => 'Ana Exemplo']);
    PaymentWallet::create([
        'user_id' => $user->id, 'provider' => 'bybit',
        'identifier' => '123456', 'holder_name' => 'Ana Exemplo',
    ]);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/wallets', [
        'provider' => 'bybit', 'identifier' => '123456', 'holder_name' => 'Ana Exemplo',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('identifier');
});

it('define uma carteira como predefinida e limpa as outras', function () {
    $user = User::factory()->create(['full_name' => 'Ana Exemplo']);
    $w1 = PaymentWallet::create(['user_id' => $user->id, 'provider' => 'bybit', 'identifier' => 'a', 'holder_name' => 'Ana Exemplo', 'is_default' => true]);
    $w2 = PaymentWallet::create(['user_id' => $user->id, 'provider' => 'binance', 'identifier' => 'b', 'holder_name' => 'Ana Exemplo', 'is_default' => false]);

    Sanctum::actingAs($user);

    $this->postJson("/api/v1/wallets/{$w2->id}/default")->assertOk();

    expect($w1->fresh()->is_default)->toBeFalse();
    expect($w2->fresh()->is_default)->toBeTrue();
});

it('remove uma carteira', function () {
    $user = User::factory()->create(['full_name' => 'Ana Exemplo']);
    $w = PaymentWallet::create(['user_id' => $user->id, 'provider' => 'bybit', 'identifier' => 'a', 'holder_name' => 'Ana Exemplo']);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/wallets/{$w->id}")->assertOk();
    $this->assertSoftDeleted('payment_wallets', ['id' => $w->id]);
});

it('exige autenticação', function () {
    $this->getJson('/api/v1/wallets')->assertUnauthorized();
});
