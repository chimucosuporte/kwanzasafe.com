<?php

use App\Models\ChatMessage;
use App\Models\Recourse;
use App\Models\Transaction;
use App\Models\User;

// ---------------------------------------------------------------------------
// PRIVACIDADE DE CANAIS (crítico)
// ---------------------------------------------------------------------------

it('never exposes internal notes to the client via poll', function () {
    $client = User::factory()->create();
    $agent  = User::factory()->support()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $client->id]);

    ChatMessage::create(['transaction_id' => $tx->id, 'sender_id' => $agent->id, 'message_text' => 'NOTA SECRETA INTERNA', 'message_type' => 'text', 'channel' => 'internal']);
    ChatMessage::create(['transaction_id' => $tx->id, 'sender_id' => $agent->id, 'message_text' => 'Ola cliente', 'message_type' => 'text', 'channel' => 'client']);

    $resp = $this->actingAs($client)->getJson(route('chat.poll', $tx->reference_id) . '?after=0');
    $resp->assertOk();

    $texts = collect($resp->json('messages'))->pluck('text');
    expect($texts)->toContain('Ola cliente');
    expect($texts)->not->toContain('NOTA SECRETA INTERNA');
});

it('never renders internal notes on the client ticket page', function () {
    $client = User::factory()->create();
    $agent  = User::factory()->support()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $client->id]);

    ChatMessage::create(['transaction_id' => $tx->id, 'sender_id' => $agent->id, 'message_text' => 'NOTA SECRETA INTERNA', 'message_type' => 'text', 'channel' => 'internal']);

    $this->actingAs($client)
        ->get(route('transaction.show', $tx->reference_id))
        ->assertOk()
        ->assertDontSee('NOTA SECRETA INTERNA');
});

it('hides recourse messages from the support agent poll but shows internal notes', function () {
    $client = User::factory()->create();
    $agent  = User::factory()->support()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $client->id]);

    ChatMessage::create(['transaction_id' => $tx->id, 'sender_id' => $client->id, 'message_text' => 'RECURSO SECRETO', 'message_type' => 'text', 'channel' => 'recourse']);
    ChatMessage::create(['transaction_id' => $tx->id, 'sender_id' => $agent->id, 'message_text' => 'nota da equipa', 'message_type' => 'text', 'channel' => 'internal']);

    $resp = $this->actingAs($agent)->getJson(route('admin.transaction.poll', $tx->id) . '?after=0');
    $resp->assertOk();

    $texts = collect($resp->json('messages'))->pluck('text');
    expect($texts)->toContain('nota da equipa');
    expect($texts)->not->toContain('RECURSO SECRETO');
});

it('shows every channel to the super admin poll', function () {
    $client = User::factory()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $client->id]);

    ChatMessage::create(['transaction_id' => $tx->id, 'sender_id' => $client->id, 'message_text' => 'msg recurso', 'message_type' => 'text', 'channel' => 'recourse']);
    ChatMessage::create(['transaction_id' => $tx->id, 'sender_id' => $client->id, 'message_text' => 'msg interna', 'message_type' => 'text', 'channel' => 'internal']);

    $resp = $this->actingAs(User::factory()->superAdmin()->create())
        ->getJson(route('admin.transaction.poll', $tx->id) . '?after=0');

    $texts = collect($resp->json('messages'))->pluck('text');
    expect($texts)->toContain('msg recurso');
    expect($texts)->toContain('msg interna');
});

// ---------------------------------------------------------------------------
// NOTAS INTERNAS
// ---------------------------------------------------------------------------

it('lets a support agent post an internal note', function () {
    $agent = User::factory()->support()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($agent)
        ->post(route('admin.chat.send', $tx->id), ['message_text' => 'nota privada', 'channel' => 'internal'])
        ->assertRedirect();

    $this->assertDatabaseHas('chat_messages', [
        'transaction_id' => $tx->id,
        'channel'        => 'internal',
        'message_text'   => 'nota privada',
    ]);
});

// ---------------------------------------------------------------------------
// RECURSO — abertura
// ---------------------------------------------------------------------------

it('lets a client open a recourse on their own transaction', function () {
    $client = User::factory()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $client->id]);

    $this->actingAs($client)
        ->post(route('recourse.open', $tx->reference_id), ['reason' => 'Fui prejudicado na taxa'])
        ->assertRedirect();

    $this->assertDatabaseHas('recourses', [
        'transaction_id' => $tx->id,
        'opened_by'      => $client->id,
        'status'         => 'open',
    ]);
    $this->assertDatabaseHas('chat_messages', [
        'transaction_id' => $tx->id,
        'channel'        => 'recourse',
        'message_text'   => 'Fui prejudicado na taxa',
    ]);
});

it('prevents a second active recourse on the same transaction', function () {
    $client = User::factory()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $client->id]);
    Recourse::create(['transaction_id' => $tx->id, 'opened_by' => $client->id, 'reason' => 'primeiro', 'status' => 'open']);

    $this->actingAs($client)
        ->from(route('transaction.show', $tx->reference_id))
        ->post(route('recourse.open', $tx->reference_id), ['reason' => 'segundo']);

    expect(Recourse::where('transaction_id', $tx->id)->count())->toBe(1);
});

it('blocks a client from opening a recourse on another users transaction', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->post(route('recourse.open', $tx->reference_id), ['reason' => 'intruso'])
        ->assertNotFound();
});

// ---------------------------------------------------------------------------
// RECURSO — arbitragem (super-admin)
// ---------------------------------------------------------------------------

it('lets a super admin resolve a recourse', function () {
    $client = User::factory()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $client->id]);
    $recourse = Recourse::create(['transaction_id' => $tx->id, 'opened_by' => $client->id, 'reason' => 'erro', 'status' => 'open']);

    $this->actingAs(User::factory()->superAdmin()->create())
        ->post(route('admin.recourses.resolve', $recourse->id), ['resolution' => 'Reembolso efectuado'])
        ->assertRedirect();

    $fresh = $recourse->fresh();
    expect($fresh->status)->toBe('resolved');
    expect($fresh->resolved_at)->not->toBeNull();

    // Mensagem de sistema no canal recourse com a decisão
    $this->assertDatabaseHas('chat_messages', [
        'transaction_id' => $tx->id,
        'channel'        => 'recourse',
        'sender_id'      => null,
    ]);
});

it('blocks a support agent from the recourses queue', function () {
    $this->actingAs(User::factory()->support()->create())
        ->get(route('admin.recourses.index'))
        ->assertRedirect(route('admin.dashboard'));
});

it('blocks a support agent from resolving a recourse', function () {
    $client = User::factory()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $client->id]);
    $recourse = Recourse::create(['transaction_id' => $tx->id, 'opened_by' => $client->id, 'reason' => 'erro', 'status' => 'open']);

    $this->actingAs(User::factory()->support()->create())
        ->post(route('admin.recourses.resolve', $recourse->id), ['resolution' => 'x'])
        ->assertRedirect(route('admin.dashboard'));

    expect($recourse->fresh()->status)->toBe('open');
});
