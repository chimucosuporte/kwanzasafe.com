<?php

use App\Models\ExchangeRate;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TicketAssignment;

// ---------------------------------------------------------------------------
// Serviço de atribuição (round-robin "menos ocupado")
// ---------------------------------------------------------------------------

it('assigns a ticket to the only active support agent', function () {
    $agent = User::factory()->support()->create();
    $tx = Transaction::factory()->pending()->create([
        'user_id'        => User::factory()->create()->id,
        'assigned_admin' => null,
    ]);

    $picked = TicketAssignment::assign($tx);

    expect($picked->id)->toBe($agent->id);
    expect($tx->fresh()->assigned_admin)->toBe($agent->id);
});

it('picks the least busy support agent', function () {
    $busy = User::factory()->support()->create();
    $free = User::factory()->support()->create();

    // Dá um tíquete aberto ao "busy"
    Transaction::factory()->pending()->create([
        'user_id'        => User::factory()->create()->id,
        'assigned_admin' => $busy->id,
    ]);

    $tx = Transaction::factory()->pending()->create([
        'user_id'        => User::factory()->create()->id,
        'assigned_admin' => null,
    ]);

    expect(TicketAssignment::assign($tx)->id)->toBe($free->id);
});

it('ignores super admins, inactive agents and clients when assigning', function () {
    User::factory()->superAdmin()->create();
    User::factory()->support()->inactive()->create();
    User::factory()->create(); // cliente

    $tx = Transaction::factory()->pending()->create([
        'user_id'        => User::factory()->create()->id,
        'assigned_admin' => null,
    ]);

    expect(TicketAssignment::assign($tx))->toBeNull();
    expect($tx->fresh()->assigned_admin)->toBeNull();
});

// ---------------------------------------------------------------------------
// Auto-atribuição na criação
// ---------------------------------------------------------------------------

it('auto-assigns a newly created transaction to a support agent', function () {
    $rate  = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    $agent = User::factory()->support()->create();
    $client = User::factory()->create();

    $dest = \App\Models\Beneficiary::create([
        'user_id' => $client->id, 'bank_name' => 'BAI',
        'iban' => 'AO06004400006729503010102', 'holder_name' => $client->full_name ?: 'Titular',
    ]);

    $this->actingAs($client)
        ->post(route('transaction.store'), [
            'moeda' => $rate->id, 'valor_enviar' => 100,
            'destino_tipo' => 'bank', 'destino_id' => $dest->id,
        ])
        ->assertRedirect();

    $tx = Transaction::where('user_id', $client->id)->firstOrFail();
    expect($tx->assigned_admin)->toBe($agent->id);
});

// ---------------------------------------------------------------------------
// Reatribuição (super-admin)
// ---------------------------------------------------------------------------

it('lets a super admin reassign a ticket to another agent', function () {
    $a = User::factory()->support()->create();
    $b = User::factory()->support()->create();
    $tx = Transaction::factory()->pending()->create([
        'user_id'        => User::factory()->create()->id,
        'assigned_admin' => $a->id,
    ]);

    $this->actingAs(User::factory()->superAdmin()->create())
        ->post(route('admin.transaction.reassign', $tx->id), ['agent_id' => $b->id])
        ->assertRedirect();

    expect($tx->fresh()->assigned_admin)->toBe($b->id);
});

it('blocks a support agent from reassigning tickets', function () {
    $b = User::factory()->support()->create();
    $tx = Transaction::factory()->pending()->create([
        'user_id'        => User::factory()->create()->id,
        'assigned_admin' => null,
    ]);

    $this->actingAs(User::factory()->support()->create())
        ->post(route('admin.transaction.reassign', $tx->id), ['agent_id' => $b->id])
        ->assertRedirect(route('admin.dashboard'));

    expect($tx->fresh()->assigned_admin)->toBeNull();
});

// ---------------------------------------------------------------------------
// Filtro "Meus tíquetes"
// ---------------------------------------------------------------------------

it('filters the admin ticket list to the agents own tickets', function () {
    $agent = User::factory()->support()->create();
    $mine = Transaction::factory()->pending()->create([
        'user_id'        => User::factory()->create()->id,
        'assigned_admin' => $agent->id,
    ]);
    $other = Transaction::factory()->pending()->create([
        'user_id'        => User::factory()->create()->id,
        'assigned_admin' => null,
    ]);

    $this->actingAs($agent)
        ->get(route('admin.transactions.index', ['assigned' => 'mine', 'status' => 'all']))
        ->assertOk()
        ->assertSee($mine->reference_id)
        ->assertDontSee($other->reference_id);
});
