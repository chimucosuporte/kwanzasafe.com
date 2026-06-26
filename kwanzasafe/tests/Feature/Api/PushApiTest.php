<?php

use App\Models\User;
use App\Services\PushService;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

/*
| Notificações push (token Expo) — /api/v1/push/token + PushService.
*/

it('regista o token Expo do utilizador', function () {
    Sanctum::actingAs($user = User::factory()->create());

    $this->postJson('/api/v1/push/token', ['token' => 'ExponentPushToken[abc123]'])
        ->assertOk();

    expect($user->fresh()->expo_push_token)->toBe('ExponentPushToken[abc123]');
});

it('remove o token Expo', function () {
    $user = User::factory()->create(['expo_push_token' => 'ExponentPushToken[abc123]']);
    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/push/token')->assertOk();

    expect($user->fresh()->expo_push_token)->toBeNull();
});

it('o PushService envia para a Expo quando há token válido', function () {
    Http::fake();
    $user = User::factory()->create(['expo_push_token' => 'ExponentPushToken[xyz]']);

    PushService::sendToUser($user, 'Olá', 'Corpo da mensagem', ['ref' => 'KZ1']);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'exp.host')
        && $request['to'] === 'ExponentPushToken[xyz]'
        && $request['title'] === 'Olá');
});

it('o PushService não envia nada sem token', function () {
    Http::fake();
    $user = User::factory()->create(['expo_push_token' => null]);

    PushService::sendToUser($user, 'Olá', 'Corpo');

    Http::assertNothingSent();
});

it('envia notificação de teste quando há token', function () {
    Http::fake();
    Sanctum::actingAs(User::factory()->create(['expo_push_token' => 'ExponentPushToken[teste]']));

    $this->postJson('/api/v1/push/test')->assertOk();

    Http::assertSent(fn ($r) => str_contains($r->url(), 'exp.host') && $r['to'] === 'ExponentPushToken[teste]');
});

it('o teste de push falha sem token registado (422)', function () {
    Sanctum::actingAs(User::factory()->create(['expo_push_token' => null]));

    $this->postJson('/api/v1/push/test')->assertStatus(422);
});
