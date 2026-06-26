<?php

use App\Models\ChatMessage;
use App\Models\ExchangeRate;
use App\Models\PaymentAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

/*
| FASE 1 — API de domínio mobile: taxas, transações, chat, comprovativo, ficheiros.
*/

// ---------------------------------------------------------------------------
// Taxas / calculadora
// ---------------------------------------------------------------------------

it('lista apenas as taxas activas', function () {
    ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    ExchangeRate::factory()->create(['currency_from' => 'BRL', 'rate' => 150, 'is_active' => false]);

    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/rates')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.currency_from', 'EUR')
        ->assertJsonPath('data.0.rate', '900.0000');
});

// ---------------------------------------------------------------------------
// Criar transação
// ---------------------------------------------------------------------------

it('cria uma transação para um cliente verificado', function () {
    $rate = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    Sanctum::actingAs($user = User::factory()->create());
    $ben = App\Models\Beneficiary::create([
        'user_id' => $user->id, 'bank_name' => 'BAI', 'iban' => 'AO06000000000000000000001', 'holder_name' => $user->full_name,
    ]);

    $this->postJson('/api/v1/transactions', [
        'moeda' => $rate->id, 'valor_enviar' => 100, 'destino_tipo' => 'bank', 'destino_id' => $ben->id,
    ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.currency_from', 'EUR')
        ->assertJsonPath('data.amount_received', '90000.00')
        ->assertJsonPath('data.destination.type', 'bank')
        ->assertJsonPath('data.destination.identifier', 'AO06000000000000000000001');

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id, 'currency_from' => 'EUR', 'status' => 'pending', 'destination_type' => 'bank',
    ]);
});

it('exige um destino de recepção para criar transação', function () {
    $rate = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/transactions', ['moeda' => $rate->id, 'valor_enviar' => 100])
        ->assertStatus(422)
        ->assertJsonValidationErrors('destino_tipo');
});

it('rejeita destino que não pertence ao utilizador', function () {
    $rate = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    $outro = App\Models\Beneficiary::create([
        'user_id' => User::factory()->create()->id, 'bank_name' => 'BFA', 'iban' => 'AO06000000000000000000999', 'holder_name' => 'Outro',
    ]);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/transactions', [
        'moeda' => $rate->id, 'valor_enviar' => 100, 'destino_tipo' => 'bank', 'destino_id' => $outro->id,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('destino_id');
});

it('bloqueia criação quando o KYC está incompleto (403)', function () {
    $rate = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    Sanctum::actingAs(User::factory()->kycIncomplete()->create());

    $this->postJson('/api/v1/transactions', ['moeda' => $rate->id, 'valor_enviar' => 100])
        ->assertStatus(403)
        ->assertJsonPath('code', 'kyc_incomplete');
});

it('bloqueia criação quando o email não está verificado (403)', function () {
    $rate = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    Sanctum::actingAs(User::factory()->unverified()->create());

    $this->postJson('/api/v1/transactions', ['moeda' => $rate->id, 'valor_enviar' => 100])
        ->assertStatus(403)
        ->assertJsonPath('code', 'email_unverified');
});

it('rejeita valor abaixo do mínimo (422)', function () {
    $rate = ExchangeRate::factory()->create(['is_active' => true]);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/transactions', ['moeda' => $rate->id, 'valor_enviar' => 5])
        ->assertStatus(422)->assertJsonValidationErrors('valor_enviar');
});

// ---------------------------------------------------------------------------
// Listar / ver
// ---------------------------------------------------------------------------

it('lista só as transações do próprio utilizador', function () {
    Sanctum::actingAs($user = User::factory()->create());
    Transaction::factory()->count(2)->create(['user_id' => $user->id]);
    Transaction::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->getJson('/api/v1/transactions')->assertOk()->assertJsonCount(2, 'data');
});

it('inclui a conta de recepção activa no detalhe', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = Transaction::factory()->create(['user_id' => $user->id, 'currency_from' => 'EUR']);
    PaymentAccount::create([
        'currency' => 'EUR', 'holder' => 'KwanzaSafe Lda', 'identifier' => 'PT50 1234',
        'network' => 'SEPA', 'instructions' => 'Referência obrigatória', 'is_active' => true,
    ]);

    $this->getJson("/api/v1/transactions/{$tx->reference_id}")
        ->assertOk()
        ->assertJsonPath('data.payment_account.identifier', 'PT50 1234')
        ->assertJsonPath('data.payment_account.holder', 'KwanzaSafe Lda');
});

it('devolve 404 ao ver a transação de outro utilizador', function () {
    $tx = Transaction::factory()->create(['user_id' => User::factory()->create()->id]);
    Sanctum::actingAs(User::factory()->create());

    $this->getJson("/api/v1/transactions/{$tx->reference_id}")->assertNotFound();
});

// ---------------------------------------------------------------------------
// Chat
// ---------------------------------------------------------------------------

it('lista mensagens do cliente mas nunca notas internas', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = Transaction::factory()->create(['user_id' => $user->id]);
    ChatMessage::create(['transaction_id' => $tx->id, 'sender_id' => $user->id, 'message_text' => 'Olá', 'message_type' => 'text', 'channel' => 'client']);
    ChatMessage::create(['transaction_id' => $tx->id, 'sender_id' => null, 'message_text' => 'NOTA SECRETA', 'message_type' => 'text', 'channel' => 'internal']);

    $res = $this->getJson("/api/v1/transactions/{$tx->reference_id}/messages")->assertOk()->assertJsonCount(1, 'data');
    expect($res->json('data.0.text'))->toBe('Olá');
});

it('envia uma mensagem de texto', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = Transaction::factory()->create(['user_id' => $user->id]);

    $this->postJson("/api/v1/transactions/{$tx->reference_id}/messages", ['message_text' => 'Bom dia'])
        ->assertCreated()
        ->assertJsonPath('data.text', 'Bom dia')
        ->assertJsonPath('data.is_mine', true);

    $this->assertDatabaseHas('chat_messages', ['transaction_id' => $tx->id, 'message_text' => 'Bom dia', 'channel' => 'client']);
});

it('rejeita mensagem vazia (422)', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = Transaction::factory()->create(['user_id' => $user->id]);

    $this->postJson("/api/v1/transactions/{$tx->reference_id}/messages", [])
        ->assertStatus(422)->assertJsonValidationErrors('message_text');
});

// ---------------------------------------------------------------------------
// Comprovativo / fluxo de estado
// ---------------------------------------------------------------------------

it('aceita o comprovativo e passa a awaiting_payment', function () {
    Storage::fake('local');
    Sanctum::actingAs($user = User::factory()->create());
    $tx = Transaction::factory()->pending()->create(['user_id' => $user->id]);

    $this->postJson("/api/v1/transactions/{$tx->reference_id}/receipt", [
        'comprovativo' => UploadedFile::fake()->image('comp.jpg'),
    ])->assertOk()->assertJsonPath('data.status', 'awaiting_payment');

    expect($tx->fresh()->status)->toBe('awaiting_payment');
});

it('deixa o cliente confirmar a recepção quando o estado é aoa_sent', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = Transaction::factory()->aoaSent()->create(['user_id' => $user->id]);

    $this->postJson("/api/v1/transactions/{$tx->reference_id}/confirm")
        ->assertOk()->assertJsonPath('data.status', 'completed');

    expect($tx->fresh()->status)->toBe('completed');
});

it('bloqueia confirmação no estado errado (422)', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = Transaction::factory()->pending()->create(['user_id' => $user->id]);

    $this->postJson("/api/v1/transactions/{$tx->reference_id}/confirm")->assertStatus(422);
    expect($tx->fresh()->status)->toBe('pending');
});

it('cancela uma transação pendente', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = Transaction::factory()->pending()->create(['user_id' => $user->id]);

    $this->postJson("/api/v1/transactions/{$tx->reference_id}/cancel")
        ->assertOk()->assertJsonPath('data.status', 'cancelled');
});

// ---------------------------------------------------------------------------
// Ficheiros privados (token-auth)
// ---------------------------------------------------------------------------

it('serve um anexo ao dono e bloqueia terceiros', function () {
    Storage::fake('local');
    Storage::disk('local')->put('chat_attachments/x.jpg', 'binary');

    $owner = User::factory()->create();
    $tx    = Transaction::factory()->create(['user_id' => $owner->id]);
    ChatMessage::create(['transaction_id' => $tx->id, 'sender_id' => $owner->id, 'message_text' => '📷', 'message_type' => 'image', 'channel' => 'client', 'file_path' => 'chat_attachments/x.jpg']);

    Sanctum::actingAs($owner);
    $this->get('/api/v1/file/chat_attachments/x.jpg')->assertOk();

    Sanctum::actingAs(User::factory()->create());
    $this->get('/api/v1/file/chat_attachments/x.jpg')->assertForbidden();
});
