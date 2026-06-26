<?php

use App\Models\Transaction;
use App\Models\User;

/*
| Export CSV de transações (admin).
*/

it('exporta transações filtradas para CSV', function () {
    $admin  = User::factory()->create(['is_admin' => true]);
    $client = User::factory()->create(['full_name' => 'Cliente Teste', 'email' => 'cli@example.com']);
    $tx = Transaction::factory()->create(['user_id' => $client->id, 'status' => 'completed']);

    $response = $this->actingAs($admin)->get('/admin/transactions/export?status=all&assigned=all');

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();
    expect($csv)->toContain('Referência')          // cabeçalho
        ->and($csv)->toContain($tx->reference_id)   // a transação
        ->and($csv)->toContain('cli@example.com');  // o cliente
});

it('bloqueia o export a não-admins', function () {
    $client = User::factory()->create(['is_admin' => false]);

    $this->actingAs($client)->get('/admin/transactions/export')->assertRedirect();
});
