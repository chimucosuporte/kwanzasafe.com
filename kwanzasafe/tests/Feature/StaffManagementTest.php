<?php

use App\Models\Transaction;
use App\Models\User;

// ---------------------------------------------------------------------------
// Acesso
// ---------------------------------------------------------------------------

it('lets a super admin view the staff page', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('admin.staff.index'))
        ->assertOk();
});

it('blocks a support agent from the staff page', function () {
    $this->actingAs(User::factory()->support()->create())
        ->get(route('admin.staff.index'))
        ->assertRedirect(route('admin.dashboard'));
});

it('blocks a client from the staff page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.staff.index'))
        ->assertRedirect(route('dashboard'));
});

// ---------------------------------------------------------------------------
// Criar conta de suporte
// ---------------------------------------------------------------------------

it('lets a super admin create a support account', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->post(route('admin.staff.store'), [
            'full_name'             => 'Maria Suporte',
            'email'                 => 'maria@kwanzasafe.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect();

    $created = User::where('email', 'maria@kwanzasafe.com')->first();

    expect($created)->not->toBeNull();
    expect($created->isSupport())->toBeTrue();
    expect($created->is_admin)->toBeTrue();
    expect($created->is_super_admin)->toBeFalse();
    expect($created->is_active)->toBeTrue();
    expect($created->email_verified_at)->not->toBeNull();
});

it('rejects a duplicate email when creating staff', function () {
    User::factory()->create(['email' => 'taken@kwanzasafe.com']);

    $this->actingAs(User::factory()->superAdmin()->create())
        ->post(route('admin.staff.store'), [
            'full_name'             => 'Outro',
            'email'                 => 'taken@kwanzasafe.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertSessionHasErrors('email');
});

it('rejects mismatched password confirmation', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->post(route('admin.staff.store'), [
            'full_name'             => 'Joao',
            'email'                 => 'joao@kwanzasafe.com',
            'password'              => 'password123',
            'password_confirmation' => 'different',
        ])
        ->assertSessionHasErrors('password');
});

// ---------------------------------------------------------------------------
// Activar/desactivar
// ---------------------------------------------------------------------------

it('toggles a support account active state', function () {
    $agent = User::factory()->support()->create(['is_active' => true]);

    $this->actingAs(User::factory()->superAdmin()->create())
        ->post(route('admin.staff.toggle_active', $agent->id))
        ->assertRedirect();

    expect($agent->fresh()->is_active)->toBeFalse();
});

// ---------------------------------------------------------------------------
// Eliminar (soft delete) + libertar tíquetes
// ---------------------------------------------------------------------------

it('soft deletes a support account and releases open tickets', function () {
    $agent = User::factory()->support()->create();
    $client = User::factory()->create();
    $ticket = Transaction::factory()->pending()->create([
        'user_id'        => $client->id,
        'assigned_admin' => $agent->id,
    ]);

    $this->actingAs(User::factory()->superAdmin()->create())
        ->delete(route('admin.staff.destroy', $agent->id))
        ->assertRedirect();

    $this->assertSoftDeleted('users', ['id' => $agent->id]);
    expect($ticket->fresh()->assigned_admin)->toBeNull();
});

it('does not let a super admin be deleted through staff management', function () {
    $target = User::factory()->superAdmin()->create();

    $this->actingAs(User::factory()->superAdmin()->create())
        ->delete(route('admin.staff.destroy', $target->id))
        ->assertNotFound();

    $this->assertNotSoftDeleted('users', ['id' => $target->id]);
});
