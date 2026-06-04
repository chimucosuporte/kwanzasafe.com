<?php

use App\Models\PaymentAccount;
use App\Models\Transaction;
use App\Models\User;

// ---------------------------------------------------------------------------
// Acesso (super-admin)
// ---------------------------------------------------------------------------

it('lets a super admin manage payment accounts', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('admin.payment_accounts.index'))
        ->assertOk();
});

it('blocks a support agent from payment accounts', function () {
    $this->actingAs(User::factory()->support()->create())
        ->get(route('admin.payment_accounts.index'))
        ->assertRedirect(route('admin.dashboard'));
});

// ---------------------------------------------------------------------------
// CRUD
// ---------------------------------------------------------------------------

it('creates a payment account', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->post(route('admin.payment_accounts.store'), [
            'currency'   => 'eur',
            'holder'     => 'KwanzaSafe Lda',
            'identifier' => 'PT50 0002 0123 4567 8901 2',
            'is_active'  => '1',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('payment_accounts', [
        'currency'   => 'EUR', // normalizado para maiúsculas
        'identifier' => 'PT50 0002 0123 4567 8901 2',
    ]);
});

it('rejects a duplicate currency', function () {
    PaymentAccount::create(['currency' => 'EUR', 'holder' => 'X', 'identifier' => 'PT50 1']);

    $this->actingAs(User::factory()->superAdmin()->create())
        ->post(route('admin.payment_accounts.store'), [
            'currency'   => 'EUR',
            'holder'     => 'Y',
            'identifier' => 'PT50 2',
        ])
        ->assertSessionHasErrors('currency');
});

it('updates and deletes a payment account', function () {
    $acc = PaymentAccount::create(['currency' => 'BRL', 'holder' => 'X', 'identifier' => 'pix-antigo']);
    $boss = User::factory()->superAdmin()->create();

    $this->actingAs($boss)
        ->put(route('admin.payment_accounts.update', $acc->id), [
            'currency'   => 'BRL',
            'holder'     => 'KwanzaSafe',
            'identifier' => 'pix-novo@kwanzasafe.com',
            'is_active'  => '1',
        ])
        ->assertRedirect();
    expect($acc->fresh()->identifier)->toBe('pix-novo@kwanzasafe.com');

    $this->actingAs($boss)
        ->delete(route('admin.payment_accounts.destroy', $acc->id))
        ->assertRedirect();
    $this->assertDatabaseMissing('payment_accounts', ['id' => $acc->id]);
});

// ---------------------------------------------------------------------------
// Exibição na Sala de Transação
// ---------------------------------------------------------------------------

it('shows the configured payment account for the transaction currency', function () {
    $client = User::factory()->create();
    PaymentAccount::create(['currency' => 'EUR', 'holder' => 'KwanzaSafe Lda', 'identifier' => 'PT50 REAL 0001', 'is_active' => true]);
    $tx = Transaction::factory()->pending()->create(['user_id' => $client->id, 'currency_from' => 'EUR']);

    $this->actingAs($client)
        ->get(route('transaction.show', $tx->reference_id))
        ->assertOk()
        ->assertSee('PT50 REAL 0001');
});

it('shows a chat fallback when no payment account is configured', function () {
    $client = User::factory()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $client->id, 'currency_from' => 'EUR']);

    $this->actingAs($client)
        ->get(route('transaction.show', $tx->reference_id))
        ->assertOk()
        ->assertSee('indicar-te os dados de pagamento')
        ->assertDontSee('PT50 0000 0000');
});
