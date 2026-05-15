<?php

use App\Models\Transaction;
use App\Models\User;

// ---------------------------------------------------------------------------
// Cliente envia mensagem no chat da transação
// ---------------------------------------------------------------------------

it('client can send a text message on their transaction', function () {
    $user        = User::factory()->create();
    $transaction = Transaction::factory()->pending()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('chat.send', $transaction->reference_id), [
            'message_text' => 'Já efectuei o pagamento.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('chat_messages', [
        'transaction_id' => $transaction->id,
        'sender_id'      => $user->id,
        'message_text'   => 'Já efectuei o pagamento.',
        'message_type'   => 'text',
    ]);
});

it('blocks empty message without attachment', function () {
    $user        = User::factory()->create();
    $transaction = Transaction::factory()->pending()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('chat.send', $transaction->reference_id), [
            'message_text' => '',
        ])
        ->assertSessionHasErrors();
});

it('blocks client from sending message on another user\'s transaction', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $transaction = Transaction::factory()->pending()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->post(route('chat.send', $transaction->reference_id), [
            'message_text' => 'intruso',
        ])
        ->assertStatus(404);
});

// ---------------------------------------------------------------------------
// Marcar mensagens como lidas
// ---------------------------------------------------------------------------

it('marks admin messages as read when client calls markAsRead', function () {
    $admin       = User::factory()->admin()->create();
    $client      = User::factory()->create();
    $transaction = Transaction::factory()->pending()->create(['user_id' => $client->id]);

    $transaction->chatMessages()->create([
        'sender_id'    => $admin->id,
        'message_text' => 'Olá cliente!',
        'message_type' => 'text',
        'is_read'      => false,
    ]);

    $this->actingAs($client)
        ->post(route('chat.read', $transaction->reference_id));

    $this->assertDatabaseHas('chat_messages', [
        'transaction_id' => $transaction->id,
        'sender_id'      => $admin->id,
        'is_read'        => true,
    ]);
});
