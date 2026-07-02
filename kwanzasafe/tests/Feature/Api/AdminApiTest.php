<?php

use App\Models\ExchangeRate;
use App\Models\Recourse;
use App\Models\Transaction;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function superAdminUser(): User
{
    return User::factory()->create(['is_super_admin' => true]);
}

/*
| API admin (app de administração) — autorização, stats, transições, KYC.
*/

function apiAdminUser(array $attrs = []): User
{
    return User::factory()->create(array_merge(['is_admin' => true], $attrs));
}

// ---------------------------------------------------------------------------
// Autorização
// ---------------------------------------------------------------------------

it('bloqueia clientes (não-admin) nos endpoints admin', function () {
    Sanctum::actingAs(User::factory()->create()); // cliente normal

    $this->getJson('/api/v1/admin/stats')->assertStatus(403);
    $this->getJson('/api/v1/admin/transactions')->assertStatus(403);
    $this->getJson('/api/v1/admin/kyc')->assertStatus(403);
});

it('exige autenticação nos endpoints admin', function () {
    $this->getJson('/api/v1/admin/stats')->assertStatus(401);
});

// ---------------------------------------------------------------------------
// Dashboard / stats
// ---------------------------------------------------------------------------

it('devolve stats ao admin', function () {
    Transaction::factory()->completed()->create();
    Transaction::factory()->pending()->create();
    Sanctum::actingAs(apiAdminUser());

    $this->getJson('/api/v1/admin/stats?period=all')
        ->assertOk()
        ->assertJsonStructure(['stats' => ['tx_pending', 'tx_completed', 'volume_by_currency', 'period'], 'chart']);
});

// ---------------------------------------------------------------------------
// Transações
// ---------------------------------------------------------------------------

it('lista transações para o admin', function () {
    Transaction::factory()->pending()->create();
    Sanctum::actingAs(apiAdminUser());

    $this->getJson('/api/v1/admin/transactions?assigned=all&status=pending')
        ->assertOk()
        ->assertJsonStructure(['data' => [['reference_id', 'status', 'client_email', 'unread_count']], 'meta']);
});

it('faz a transição pending → awaiting_payment (solicitar pagamento)', function () {
    $tx = Transaction::factory()->pending()->create();
    Sanctum::actingAs(apiAdminUser());

    $this->postJson("/api/v1/admin/transactions/{$tx->id}/request-payment")
        ->assertOk()
        ->assertJsonPath('data.status', 'awaiting_payment');

    expect($tx->fresh()->status)->toBe('awaiting_payment');
});

it('recusa transição inválida com 422', function () {
    $tx = Transaction::factory()->completed()->create();
    Sanctum::actingAs(apiAdminUser());

    $this->postJson("/api/v1/admin/transactions/{$tx->id}/request-payment")
        ->assertStatus(422);
});

it('aprova (conclui) uma transação em aoa_sent', function () {
    $tx = Transaction::factory()->aoaSent()->create();
    Sanctum::actingAs(apiAdminUser());

    $this->postJson("/api/v1/admin/transactions/{$tx->id}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'completed');

    expect($tx->fresh()->status)->toBe('completed');
});

it('envia mensagem no chat da transação', function () {
    $tx = Transaction::factory()->awaitingPayment()->create();
    Sanctum::actingAs(apiAdminUser());

    $this->postJson("/api/v1/admin/transactions/{$tx->id}/messages", ['message_text' => 'Olá, aguardamos o comprovativo.'])
        ->assertCreated()
        ->assertJsonPath('data.is_mine', true);

    $this->assertDatabaseHas('chat_messages', ['transaction_id' => $tx->id, 'message_text' => 'Olá, aguardamos o comprovativo.']);
});

// ---------------------------------------------------------------------------
// KYC
// ---------------------------------------------------------------------------

it('aprova o KYC de um utilizador', function () {
    $client = User::factory()->create(['identity_document_path' => 'kyc/documents/doc.jpg', 'identity_verified_at' => null]);
    Sanctum::actingAs(apiAdminUser());

    $this->postJson("/api/v1/admin/kyc/{$client->id}/approve")
        ->assertOk();

    expect($client->fresh()->identity_verified_at)->not->toBeNull();
});

it('rejeita o KYC (limpa documento) e exige motivo', function () {
    $client = User::factory()->create(['identity_document_path' => 'kyc/documents/doc.jpg', 'profile_photo_path' => 'kyc/photos/s.jpg']);
    Sanctum::actingAs(apiAdminUser());

    // sem motivo → 422
    $this->postJson("/api/v1/admin/kyc/{$client->id}/reject", [])->assertStatus(422);

    // com motivo → ok e documento limpo
    $this->postJson("/api/v1/admin/kyc/{$client->id}/reject", ['reason' => 'Documento ilegível, reenvie.'])
        ->assertOk();

    expect($client->fresh()->identity_document_path)->toBeNull();
});

// ---------------------------------------------------------------------------
// Taxas
// ---------------------------------------------------------------------------

it('lista e atualiza taxas', function () {
    $rate = ExchangeRate::factory()->create(['currency_from' => 'EUR', 'rate' => 900, 'is_active' => true]);
    Sanctum::actingAs(apiAdminUser());

    $this->getJson('/api/v1/admin/rates')
        ->assertOk()
        ->assertJsonStructure(['data' => [['currency_from', 'rate', 'is_active']]]);

    $this->putJson("/api/v1/admin/rates/{$rate->id}", ['rate' => 1250.5, 'is_active' => false])
        ->assertOk()
        ->assertJsonPath('data.is_active', false);

    expect((float) $rate->fresh()->rate)->toBe(1250.5);
});

// ---------------------------------------------------------------------------
// Utilizadores
// ---------------------------------------------------------------------------

it('lista utilizadores e faz toggle de admin', function () {
    $client = User::factory()->create(['is_admin' => false]);
    Sanctum::actingAs(apiAdminUser());

    $this->getJson('/api/v1/admin/users')
        ->assertOk()
        ->assertJsonStructure(['data' => [['email', 'is_admin', 'is_verified']], 'meta']);

    $this->postJson("/api/v1/admin/users/{$client->id}/toggle-admin")
        ->assertOk()
        ->assertJsonPath('data.is_admin', true);

    expect($client->fresh()->is_admin)->toBeTrue();
});

it('impede o admin de alterar o seu próprio papel', function () {
    $admin = apiAdminUser();
    Sanctum::actingAs($admin);

    $this->postJson("/api/v1/admin/users/{$admin->id}/toggle-admin")->assertStatus(422);
});

// ---------------------------------------------------------------------------
// Contas de pagamento (super-admin) + Auditoria
// ---------------------------------------------------------------------------

it('super-admin cria e lista contas de pagamento', function () {
    Sanctum::actingAs(User::factory()->create(['is_super_admin' => true]));

    $this->postJson('/api/v1/admin/payment-accounts', [
        'currency' => 'eur', 'holder' => 'KwanzaSafe Lda', 'identifier' => 'PT50000...', 'is_active' => true,
    ])->assertCreated()->assertJsonPath('data.currency', 'EUR');

    $this->getJson('/api/v1/admin/payment-accounts')
        ->assertOk()
        ->assertJsonStructure(['data' => [['currency', 'holder', 'identifier', 'is_active']], 'available_currencies']);
});

it('bloqueia suporte (não super-admin) nas contas de pagamento', function () {
    Sanctum::actingAs(apiAdminUser()); // is_admin mas não super-admin
    $this->getJson('/api/v1/admin/payment-accounts')->assertStatus(403);
    $this->postJson('/api/v1/admin/payment-accounts', ['currency' => 'EUR', 'holder' => 'x', 'identifier' => 'y'])->assertStatus(403);
});

it('admin consulta a auditoria', function () {
    Sanctum::actingAs(apiAdminUser());
    $this->getJson('/api/v1/admin/audit')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta', 'stats' => ['total_24h', 'critical_24h'], 'categories', 'severities']);
});

// ---------------------------------------------------------------------------
// Staff (super-admin)
// ---------------------------------------------------------------------------

it('super-admin cria, lista e desativa funcionários', function () {
    Sanctum::actingAs(superAdminUser());

    $this->postJson('/api/v1/admin/staff', [
        'full_name' => 'Agente Um', 'email' => 'agente1@kwanzasafe.com',
        'password' => 'segredo123', 'password_confirmation' => 'segredo123',
    ])->assertCreated()->assertJsonPath('data.email', 'agente1@kwanzasafe.com');

    $staff = User::where('email', 'agente1@kwanzasafe.com')->first();
    expect($staff->is_admin)->toBeTrue();

    $this->getJson('/api/v1/admin/staff')->assertOk()->assertJsonStructure(['staff', 'super_admins']);

    $this->postJson("/api/v1/admin/staff/{$staff->id}/toggle-active")
        ->assertOk()->assertJsonPath('data.is_active', false);
});

it('bloqueia suporte (não super-admin) na gestão de staff', function () {
    Sanctum::actingAs(apiAdminUser());
    $this->getJson('/api/v1/admin/staff')->assertStatus(403);
});

// ---------------------------------------------------------------------------
// Recursos (super-admin)
// ---------------------------------------------------------------------------

it('super-admin responde e resolve um recurso', function () {
    $tx = Transaction::factory()->awaitingPayment()->create();
    $rec = Recourse::create(['transaction_id' => $tx->id, 'opened_by' => $tx->user_id, 'reason' => 'Não recebi os kwanzas.', 'status' => 'open']);
    Sanctum::actingAs(superAdminUser());

    $this->getJson('/api/v1/admin/recourses')->assertOk()->assertJsonStructure(['active', 'resolved']);

    $this->postJson("/api/v1/admin/recourses/{$rec->id}/reply", ['message_text' => 'Estamos a analisar.'])
        ->assertOk();
    expect($rec->fresh()->status)->toBe('in_review');
    $this->assertDatabaseHas('chat_messages', ['transaction_id' => $tx->id, 'channel' => 'recourse', 'message_text' => 'Estamos a analisar.']);

    $this->postJson("/api/v1/admin/recourses/{$rec->id}/resolve", ['resolution' => 'Kwanzas reenviados.'])
        ->assertOk()->assertJsonPath('data.status', 'resolved');
});
