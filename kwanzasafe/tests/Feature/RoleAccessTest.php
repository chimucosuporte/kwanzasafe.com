<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Helpers de papel
// ---------------------------------------------------------------------------

it('classifies a client correctly', function () {
    $u = User::factory()->create();

    expect($u->isClient())->toBeTrue();
    expect($u->isStaff())->toBeFalse();
    expect($u->isSupport())->toBeFalse();
    expect($u->isSuperAdmin())->toBeFalse();
});

it('classifies a support agent correctly', function () {
    $u = User::factory()->support()->create();

    expect($u->isClient())->toBeFalse();
    expect($u->isStaff())->toBeTrue();
    expect($u->isSupport())->toBeTrue();
    expect($u->isSuperAdmin())->toBeFalse();
    expect($u->role)->toBe('admin');
});

it('classifies a super admin correctly', function () {
    $u = User::factory()->superAdmin()->create();

    expect($u->isStaff())->toBeTrue();
    expect($u->isSupport())->toBeFalse();
    expect($u->isSuperAdmin())->toBeTrue();
    expect($u->role)->toBe('super_admin');
});

// ---------------------------------------------------------------------------
// Consistência: super ⇒ admin, e role espelha os booleanos
// ---------------------------------------------------------------------------

it('forces is_admin true whenever is_super_admin is set', function () {
    // Mesmo sem passar is_admin, o booted() deve forçá-lo.
    $u = User::factory()->create(['is_super_admin' => true, 'is_admin' => false]);

    expect($u->fresh()->is_admin)->toBeTrue();
    expect($u->fresh()->role)->toBe('super_admin');
});

it('mirrors role to client when admin flags are cleared', function () {
    $u = User::factory()->support()->create();
    expect($u->role)->toBe('admin');

    $u->update(['is_admin' => false]);

    expect($u->fresh()->role)->toBe('client');
    expect($u->fresh()->isClient())->toBeTrue();
});

// ---------------------------------------------------------------------------
// Middleware is_super_admin
// ---------------------------------------------------------------------------

it('lets a super admin through is_super_admin middleware', function () {
    Route::get('/__test/super-only', fn () => 'ok')->middleware(['web', 'auth', 'is_super_admin']);

    $this->actingAs(User::factory()->superAdmin()->create())
        ->get('/__test/super-only')
        ->assertOk()
        ->assertSee('ok');
});

it('blocks a support agent from a super-admin-only route', function () {
    Route::get('/__test/super-only', fn () => 'ok')->middleware(['web', 'auth', 'is_super_admin']);

    $this->actingAs(User::factory()->support()->create())
        ->get('/__test/super-only')
        ->assertRedirect(route('admin.dashboard'));
});

it('blocks a client from a super-admin-only route', function () {
    Route::get('/__test/super-only', fn () => 'ok')->middleware(['web', 'auth', 'is_super_admin']);

    $this->actingAs(User::factory()->create())
        ->get('/__test/super-only')
        ->assertRedirect(route('dashboard'));
});
