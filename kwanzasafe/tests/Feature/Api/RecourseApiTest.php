<?php

use App\Models\Recourse;
use App\Models\Transaction;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

/*
| Recursos (appeal) via API mobile — /transactions/{ref}/recourse.
*/

function ownedTransaction(User $user): Transaction
{
    return Transaction::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
}

it('abre um recurso para a própria transação', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = ownedTransaction($user);

    $this->postJson("/api/v1/transactions/{$tx->reference_id}/recourse", [
        'reason' => 'Não recebi os Kwanzas embora a transação esteja concluída.',
    ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'open')
        ->assertJsonPath('data.can_cancel', true);

    $this->assertDatabaseHas('recourses', ['transaction_id' => $tx->id, 'status' => 'open']);
    // Cria a mensagem no canal de recurso.
    $this->assertDatabaseHas('chat_messages', ['transaction_id' => $tx->id, 'channel' => 'recourse']);
});

it('bloqueia um segundo recurso activo na mesma transação', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = ownedTransaction($user);
    Recourse::create(['transaction_id' => $tx->id, 'opened_by' => $user->id, 'reason' => 'Primeiro recurso aqui.', 'status' => 'open']);

    $this->postJson("/api/v1/transactions/{$tx->reference_id}/recourse", ['reason' => 'Outro recurso qualquer aqui.'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('reason');
});

it('recusa motivo demasiado curto', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = ownedTransaction($user);

    $this->postJson("/api/v1/transactions/{$tx->reference_id}/recourse", ['reason' => 'curto'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('reason');
});

it('cancela um recurso activo', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = ownedTransaction($user);
    Recourse::create(['transaction_id' => $tx->id, 'opened_by' => $user->id, 'reason' => 'Recurso a cancelar aqui.', 'status' => 'open']);

    $this->postJson("/api/v1/transactions/{$tx->reference_id}/recourse/cancel")
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');

    $this->assertDatabaseHas('recourses', ['transaction_id' => $tx->id, 'status' => 'cancelled']);
});

it('mostra o recurso da transação', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $tx = ownedTransaction($user);
    Recourse::create(['transaction_id' => $tx->id, 'opened_by' => $user->id, 'reason' => 'Recurso visível aqui.', 'status' => 'in_review']);

    $this->getJson("/api/v1/transactions/{$tx->reference_id}/recourse")
        ->assertOk()
        ->assertJsonPath('data.status', 'in_review')
        ->assertJsonPath('data.status_label', 'Em análise');
});

it('bloqueia recurso na transação de outro utilizador', function () {
    $outro = User::factory()->create();
    $tx = ownedTransaction($outro);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson("/api/v1/transactions/{$tx->reference_id}/recourse", ['reason' => 'Tentativa indevida de recurso.'])
        ->assertNotFound();
});
