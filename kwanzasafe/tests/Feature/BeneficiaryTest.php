<?php

use App\Models\User;

// ---------------------------------------------------------------------------
// Criar beneficiário (IBAN)
// ---------------------------------------------------------------------------

it('client can store a beneficiary', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('beneficiary.store'), [
            'bank_name'   => 'Millennium BCP',
            'iban'        => 'PT50000201231234567890154',
            'holder_name' => $user->full_name,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('beneficiaries', [
        'user_id'   => $user->id,
        'bank_name' => 'Millennium BCP',
    ]);
});

it('rejects duplicate IBAN for the same user', function () {
    $user = User::factory()->create();
    $iban = 'PT50000201231234567890154';

    $this->actingAs($user)->post(route('beneficiary.store'), [
        'bank_name'   => 'BPI',
        'iban'        => $iban,
        'holder_name' => $user->full_name,
    ]);

    $response = $this->actingAs($user)->post(route('beneficiary.store'), [
        'bank_name'   => 'CGD',
        'iban'        => $iban,
        'holder_name' => $user->full_name,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseCount('beneficiaries', 1);
});

it('requires bank_name, iban and holder_name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('beneficiary.store'), [])
        ->assertSessionHasErrors(['bank_name', 'iban', 'holder_name']);
});

it('rejects IBAN that is too short', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('beneficiary.store'), [
            'bank_name'   => 'BPI',
            'iban'        => 'PT123',
            'holder_name' => $user->full_name,
        ])
        ->assertSessionHasErrors('iban');
});

// ---------------------------------------------------------------------------
// Apagar beneficiário
// ---------------------------------------------------------------------------

it('client can delete their own beneficiary', function () {
    $user        = User::factory()->create();
    $beneficiary = $user->beneficiaries()->create([
        'bank_name'   => 'BPI',
        'iban'        => 'PT50000201231234567890154',
        'holder_name' => $user->full_name,
    ]);

    $this->actingAs($user)
        ->delete(route('beneficiary.destroy', $beneficiary->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('beneficiaries', ['id' => $beneficiary->id]);
});

it('blocks client from deleting another user\'s beneficiary', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $beneficiary = $owner->beneficiaries()->create([
        'bank_name'   => 'BPI',
        'iban'        => 'PT50000201231234567890154',
        'holder_name' => $owner->full_name,
    ]);

    $this->actingAs($other)
        ->delete(route('beneficiary.destroy', $beneficiary->id))
        ->assertStatus(404);

    $this->assertDatabaseHas('beneficiaries', ['id' => $beneficiary->id]);
});
