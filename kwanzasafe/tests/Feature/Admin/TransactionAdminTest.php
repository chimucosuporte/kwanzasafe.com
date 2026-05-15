<?php

use App\Models\Transaction;
use App\Models\User;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function adminUser(): User
{
    return User::factory()->admin()->create();
}

function clientUser(): User
{
    return User::factory()->create();
}

// ---------------------------------------------------------------------------
// Acesso ao painel
// ---------------------------------------------------------------------------

it('blocks non-admin from admin routes', function () {
    $user = clientUser();
    $this->actingAs($user)
        ->get(route('admin.transactions.index'))
        ->assertRedirect(); // IsAdmin middleware redirects to dashboard (not 403)
});

it('admin can access transactions index', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->get(route('admin.transactions.index'))
        ->assertOk();
});

// ---------------------------------------------------------------------------
// requestPayment — pending → awaiting_payment
// ---------------------------------------------------------------------------

it('admin can request payment from pending transaction', function () {
    $admin       = adminUser();
    $transaction = Transaction::factory()->pending()->create();

    $this->actingAs($admin)
        ->post(route('admin.transaction.request_payment', $transaction->id))
        ->assertRedirect(route('admin.transaction.show', $transaction->id));

    expect($transaction->fresh()->status)->toBe('awaiting_payment');
});

it('admin can request payment from negotiating transaction', function () {
    $admin       = adminUser();
    $transaction = Transaction::factory()->negotiating()->create();

    $this->actingAs($admin)
        ->post(route('admin.transaction.request_payment', $transaction->id))
        ->assertRedirect();

    expect($transaction->fresh()->status)->toBe('awaiting_payment');
});

it('admin cannot request payment when already completed', function () {
    $admin       = adminUser();
    $transaction = Transaction::factory()->completed()->create();

    $this->actingAs($admin)
        ->post(route('admin.transaction.request_payment', $transaction->id))
        ->assertRedirect();

    expect($transaction->fresh()->status)->toBe('completed');
});

// ---------------------------------------------------------------------------
// markPaymentReceived — awaiting_payment → payment_received
// ---------------------------------------------------------------------------

it('admin can confirm payment received', function () {
    $admin       = adminUser();
    $transaction = Transaction::factory()->awaitingPayment()->create();

    $this->actingAs($admin)
        ->post(route('admin.transaction.payment_received', $transaction->id))
        ->assertRedirect();

    expect($transaction->fresh()->status)->toBe('payment_received');
});

it('admin cannot confirm payment from pending state', function () {
    $admin       = adminUser();
    $transaction = Transaction::factory()->pending()->create();

    $this->actingAs($admin)
        ->post(route('admin.transaction.payment_received', $transaction->id))
        ->assertRedirect();

    expect($transaction->fresh()->status)->toBe('pending');
});

// ---------------------------------------------------------------------------
// markAoaSent — payment_received → aoa_sent
// ---------------------------------------------------------------------------

it('admin can mark AOA as sent', function () {
    $admin       = adminUser();
    $transaction = Transaction::factory()->paymentReceived()->create();

    $this->actingAs($admin)
        ->post(route('admin.transaction.aoa_sent', $transaction->id))
        ->assertRedirect();

    expect($transaction->fresh()->status)->toBe('aoa_sent');
    expect($transaction->fresh()->aoa_sent_at)->not->toBeNull();
});

it('admin cannot mark AOA sent from awaiting_payment', function () {
    $admin       = adminUser();
    $transaction = Transaction::factory()->awaitingPayment()->create();

    $this->actingAs($admin)
        ->post(route('admin.transaction.aoa_sent', $transaction->id))
        ->assertRedirect();

    expect($transaction->fresh()->status)->toBe('awaiting_payment');
});

// ---------------------------------------------------------------------------
// approve (override)
// ---------------------------------------------------------------------------

it('admin approve override completes any active transaction', function () {
    $admin       = adminUser();
    $transaction = Transaction::factory()->awaitingPayment()->create();

    $this->actingAs($admin)
        ->post(route('admin.transaction.approve', $transaction->id))
        ->assertRedirect();

    expect($transaction->fresh()->status)->toBe('completed');
});

it('approve is idempotent on already-completed transaction', function () {
    $admin       = adminUser();
    $transaction = Transaction::factory()->completed()->create();

    $response = $this->actingAs($admin)
        ->post(route('admin.transaction.approve', $transaction->id));

    $response->assertRedirect();
    $response->assertSessionHas('error');
    expect($transaction->fresh()->status)->toBe('completed');
});

// ---------------------------------------------------------------------------
// Fluxo completo end-to-end
// ---------------------------------------------------------------------------

it('completes the full transaction flow', function () {
    $admin       = adminUser();
    $client      = clientUser();
    $transaction = Transaction::factory()->pending()->create(['user_id' => $client->id]);

    // Passo 1: solicitar pagamento
    $this->actingAs($admin)->post(route('admin.transaction.request_payment', $transaction->id));
    expect($transaction->fresh()->status)->toBe('awaiting_payment');

    // Passo 2: confirmar pagamento
    $this->actingAs($admin)->post(route('admin.transaction.payment_received', $transaction->id));
    expect($transaction->fresh()->status)->toBe('payment_received');

    // Passo 3: enviar AOA
    $this->actingAs($admin)->post(route('admin.transaction.aoa_sent', $transaction->id));
    expect($transaction->fresh()->status)->toBe('aoa_sent');

    // Passo 4: cliente confirma
    $this->actingAs($client)->post(route('transaction.confirm', $transaction->reference_id));
    expect($transaction->fresh()->status)->toBe('completed');
});
