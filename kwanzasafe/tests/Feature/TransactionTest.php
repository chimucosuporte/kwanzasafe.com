<?php

use App\Models\ExchangeRate;
use App\Models\Transaction;
use App\Models\User;
use Database\Factories\ExchangeRateFactory;

// ---------------------------------------------------------------------------
// Criar transação
// ---------------------------------------------------------------------------

it('creates a transaction for a fully-verified client', function () {
    $rate = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('transaction.store'), [
            'moeda'        => $rate->id,
            'valor_enviar' => 100,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'user_id'       => $user->id,
        'currency_from' => 'EUR',
        'amount_sent'   => 100,
        'status'        => 'pending',
    ]);
});

it('posts an automatic system message when a transaction is created', function () {
    $rate = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('transaction.store'), [
            'moeda'        => $rate->id,
            'valor_enviar' => 100,
        ])
        ->assertRedirect();

    $transaction = Transaction::where('user_id', $user->id)->firstOrFail();

    // A mensagem de boas-vindas é de sistema (sender_id null) na sala de transação
    $this->assertDatabaseHas('chat_messages', [
        'transaction_id' => $transaction->id,
        'sender_id'      => null,
        'message_type'   => 'text',
    ]);
});

it('blocks transaction creation when KYC is incomplete', function () {
    $rate = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    $user = User::factory()->kycIncomplete()->create();

    $response = $this->actingAs($user)
        ->post(route('transaction.store'), [
            'moeda'        => $rate->id,
            'valor_enviar' => 100,
        ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseMissing('transactions', ['user_id' => $user->id]);
});

it('blocks transaction creation when email is unverified', function () {
    $rate = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)
        ->post(route('transaction.store'), [
            'moeda'        => $rate->id,
            'valor_enviar' => 100,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseMissing('transactions', ['user_id' => $user->id]);
});

it('rejects invalid amount (below minimum)', function () {
    $rate = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('transaction.store'), [
            'moeda'        => $rate->id,
            'valor_enviar' => 5, // below min:10
        ]);

    $response->assertSessionHasErrors();
});

// ---------------------------------------------------------------------------
// Ver transação
// ---------------------------------------------------------------------------

it('lets a client view their own transaction', function () {
    $user        = User::factory()->create();
    $transaction = Transaction::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('transaction.show', $transaction->reference_id))
        ->assertOk();
});

it('blocks a client from viewing another user\'s transaction', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $transaction = Transaction::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->get(route('transaction.show', $transaction->reference_id))
        ->assertStatus(404);
});

// ---------------------------------------------------------------------------
// Confirmar recepção dos AOA
// ---------------------------------------------------------------------------

it('lets client confirm receipt when status is aoa_sent', function () {
    $user        = User::factory()->create();
    $transaction = Transaction::factory()->aoaSent()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('transaction.confirm', $transaction->reference_id))
        ->assertRedirect();

    expect($transaction->fresh()->status)->toBe('completed');
});

it('blocks client from confirming receipt in wrong state', function () {
    $user        = User::factory()->create();
    $transaction = Transaction::factory()->pending()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('transaction.confirm', $transaction->reference_id))
        ->assertRedirect();

    expect($transaction->fresh()->status)->toBe('pending');
});
