<?php

use App\Models\User;

// ---------------------------------------------------------------------------
// Acesso ao perfil
// ---------------------------------------------------------------------------

it('admin can view user profile', function () {
    $admin  = adminUser();
    $client = clientUser();

    $this->actingAs($admin)
        ->get(route('admin.users.show', $client->id))
        ->assertOk()
        ->assertSee($client->email);
});

it('non-admin cannot view user profile', function () {
    $viewer = clientUser();
    $target = clientUser();

    $this->actingAs($viewer)
        ->get(route('admin.users.show', $target->id))
        ->assertRedirect();
});

it('returns 404 for non-existent user', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->get(route('admin.users.show', 99999))
        ->assertNotFound();
});

// ---------------------------------------------------------------------------
// toggleAdmin — promover / revogar admin
// ---------------------------------------------------------------------------

it('admin can promote a client to admin', function () {
    $admin  = adminUser();
    $client = clientUser();

    $this->actingAs($admin)
        ->post(route('admin.users.toggle_admin', $client->id))
        ->assertRedirect();

    expect($client->fresh()->is_admin)->toBeTrue();
});

it('admin can demote another admin to client', function () {
    $admin  = adminUser();
    $target = adminUser();

    $this->actingAs($admin)
        ->post(route('admin.users.toggle_admin', $target->id))
        ->assertRedirect();

    expect($target->fresh()->is_admin)->toBeFalse();
});

it('admin cannot change their own role', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('admin.users.toggle_admin', $admin->id))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($admin->fresh()->is_admin)->toBeTrue();
});

it('non-admin cannot toggle admin status', function () {
    $client = clientUser();
    $target = clientUser();

    $this->actingAs($client)
        ->post(route('admin.users.toggle_admin', $target->id))
        ->assertRedirect();

    expect($target->fresh()->is_admin)->toBeFalse();
});

// ---------------------------------------------------------------------------
// receipt — comprovativo pelo cliente
// ---------------------------------------------------------------------------

it('client can view receipt for their own completed transaction', function () {
    $client      = clientUser();
    $transaction = \App\Models\Transaction::factory()->completed()->create(['user_id' => $client->id]);

    $this->actingAs($client)
        ->get(route('transaction.receipt', $transaction->reference_id))
        ->assertOk()
        ->assertSee($transaction->reference_id);
});

it('client cannot view receipt for pending transaction', function () {
    $client      = clientUser();
    $transaction = \App\Models\Transaction::factory()->pending()->create(['user_id' => $client->id]);

    $this->actingAs($client)
        ->get(route('transaction.receipt', $transaction->reference_id))
        ->assertRedirect();
});

it('client cannot view receipt of another client transaction', function () {
    $client      = clientUser();
    $other       = clientUser();
    $transaction = \App\Models\Transaction::factory()->completed()->create(['user_id' => $other->id]);

    $this->actingAs($client)
        ->get(route('transaction.receipt', $transaction->reference_id))
        ->assertNotFound();
});
