<?php

use App\Models\ChatMessage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('requires authentication to access a private file', function () {
    $this->get('/file/kyc/documents/x.pdf')
        ->assertRedirect(route('login'));
});

it('serves a private attachment to the transaction owner', function () {
    Storage::fake('local');
    $client = User::factory()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $client->id]);
    Storage::disk('local')->put('chat_attachments/doc.pdf', 'conteudo');
    ChatMessage::create([
        'transaction_id' => $tx->id,
        'sender_id'      => $client->id,
        'message_text'   => 'anexo',
        'message_type'   => 'document',
        'file_path'      => 'chat_attachments/doc.pdf',
        'channel'        => 'client',
    ]);

    $this->actingAs($client)
        ->get('/file/chat_attachments/doc.pdf')
        ->assertOk();
});

it('blocks another client from a private attachment', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $tx = Transaction::factory()->pending()->create(['user_id' => $owner->id]);
    Storage::disk('local')->put('chat_attachments/doc.pdf', 'conteudo');
    ChatMessage::create([
        'transaction_id' => $tx->id,
        'sender_id'      => $owner->id,
        'message_text'   => 'anexo',
        'message_type'   => 'document',
        'file_path'      => 'chat_attachments/doc.pdf',
        'channel'        => 'client',
    ]);

    $this->actingAs($other)
        ->get('/file/chat_attachments/doc.pdf')
        ->assertForbidden();
});

it('blocks a client from another users KYC document', function () {
    Storage::fake('local');
    $owner = User::factory()->create(['identity_document_path' => 'kyc/documents/bi.pdf']);
    $other = User::factory()->create();
    Storage::disk('local')->put('kyc/documents/bi.pdf', 'documento');

    $this->actingAs($other)
        ->get('/file/kyc/documents/bi.pdf')
        ->assertForbidden();
});

it('lets staff access any private file', function () {
    Storage::fake('local');
    Storage::disk('local')->put('kyc/documents/bi.pdf', 'documento');

    $this->actingAs(User::factory()->support()->create())
        ->get('/file/kyc/documents/bi.pdf')
        ->assertOk();
});
