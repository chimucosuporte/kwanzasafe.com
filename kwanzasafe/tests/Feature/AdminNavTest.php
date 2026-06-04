<?php

use App\Models\User;

it('shows super-admin-only navigation to a super admin', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Funcionários')
        ->assertSee('Recursos')
        ->assertSee('Canal de Staff');
});

it('hides super-admin-only navigation from a support agent', function () {
    $this->actingAs(User::factory()->support()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Canal de Staff')   // staff partilhado
        ->assertDontSee('Funcionários') // exclusivo super-admin
        ->assertDontSee('Recursos');
});
