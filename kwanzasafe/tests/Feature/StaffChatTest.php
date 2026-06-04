<?php

use App\Models\StaffMessage;
use App\Models\User;

// ---------------------------------------------------------------------------
// Acesso
// ---------------------------------------------------------------------------

it('blocks a client from the staff channel', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.staff_chat.index'))
        ->assertRedirect(route('dashboard'));
});

it('lets a support agent open the staff channel', function () {
    $this->actingAs(User::factory()->support()->create())
        ->get(route('admin.staff_chat.index'))
        ->assertOk();
});

// ---------------------------------------------------------------------------
// Permissões de envio
// ---------------------------------------------------------------------------

it('lets a support agent message a super admin', function () {
    $agent = User::factory()->support()->create();
    $boss  = User::factory()->superAdmin()->create();

    $this->actingAs($agent)
        ->post(route('admin.staff_chat.send', $boss->id), ['body' => 'Relatório de falha no fluxo X'])
        ->assertRedirect();

    $this->assertDatabaseHas('staff_messages', [
        'sender_id'    => $agent->id,
        'recipient_id' => $boss->id,
        'body'         => 'Relatório de falha no fluxo X',
    ]);
});

it('lets a super admin message a support agent', function () {
    $boss  = User::factory()->superAdmin()->create();
    $agent = User::factory()->support()->create();

    $this->actingAs($boss)
        ->post(route('admin.staff_chat.send', $agent->id), ['body' => 'Bom trabalho hoje'])
        ->assertRedirect();

    $this->assertDatabaseHas('staff_messages', [
        'sender_id'    => $boss->id,
        'recipient_id' => $agent->id,
    ]);
});

it('blocks a support agent from messaging another support agent', function () {
    $a = User::factory()->support()->create();
    $b = User::factory()->support()->create();

    $this->actingAs($a)
        ->post(route('admin.staff_chat.send', $b->id), ['body' => 'ola colega'])
        ->assertRedirect();

    $this->assertDatabaseMissing('staff_messages', [
        'sender_id'    => $a->id,
        'recipient_id' => $b->id,
    ]);
});

it('blocks messaging yourself', function () {
    $boss = User::factory()->superAdmin()->create();

    $this->actingAs($boss)
        ->post(route('admin.staff_chat.send', $boss->id), ['body' => 'nota pessoal'])
        ->assertRedirect();

    $this->assertDatabaseMissing('staff_messages', ['sender_id' => $boss->id, 'recipient_id' => $boss->id]);
});

// ---------------------------------------------------------------------------
// Conversa e privacidade
// ---------------------------------------------------------------------------

it('shows the conversation between the two participants only', function () {
    $a = User::factory()->support()->create(['full_name' => 'Agente A']);
    $boss = User::factory()->superAdmin()->create();

    StaffMessage::create(['sender_id' => $a->id, 'recipient_id' => $boss->id, 'body' => 'MENSAGEM PRIVADA A']);

    // O super-admin vê a conversa com A
    $this->actingAs($boss)
        ->get(route('admin.staff_chat.index', ['with' => $a->id]))
        ->assertOk()
        ->assertSee('MENSAGEM PRIVADA A');

    // Outro agente de suporte não vê esta conversa
    $other = User::factory()->support()->create();
    $this->actingAs($other)
        ->get(route('admin.staff_chat.index', ['with' => $boss->id]))
        ->assertOk()
        ->assertDontSee('MENSAGEM PRIVADA A');
});

it('marks incoming messages as read when polling', function () {
    $boss = User::factory()->superAdmin()->create();
    $agent = User::factory()->support()->create();

    $msg = StaffMessage::create(['sender_id' => $agent->id, 'recipient_id' => $boss->id, 'body' => 'por ler', 'is_read' => false]);

    $this->actingAs($boss)
        ->getJson(route('admin.staff_chat.poll', $agent->id) . '?after=0')
        ->assertOk();

    expect($msg->fresh()->is_read)->toBeTrue();
});
