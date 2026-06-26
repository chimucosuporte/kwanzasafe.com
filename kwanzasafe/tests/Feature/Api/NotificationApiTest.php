<?php

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

/*
| Auditoria de novo IP + feed de notificações — /api/v1/notifications.
*/

beforeEach(fn () => test()->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class));

it('cria notificação de segurança ao entrar de um novo IP', function () {
    Mail::fake();
    User::factory()->create(['email' => 'ip@example.com', 'password' => Hash::make('Kwanza@2026'), 'last_login_ip' => '9.9.9.9']);

    $this->postJson('/api/v1/login', ['email' => 'ip@example.com', 'password' => 'Kwanza@2026'])->assertOk();

    $this->assertDatabaseHas('user_notifications', ['type' => 'security', 'title' => 'Novo acesso à tua conta']);
});

it('não notifica quando o IP é o mesmo', function () {
    User::factory()->create(['email' => 'mesmo@example.com', 'password' => Hash::make('Kwanza@2026'), 'last_login_ip' => '127.0.0.1']);

    $this->postJson('/api/v1/login', ['email' => 'mesmo@example.com', 'password' => 'Kwanza@2026'])->assertOk();

    expect(UserNotification::where('type', 'security')->count())->toBe(0);
});

it('não notifica no primeiro login mas guarda o IP', function () {
    $u = User::factory()->create(['email' => 'primeiro@example.com', 'password' => Hash::make('Kwanza@2026'), 'last_login_ip' => null]);

    $this->postJson('/api/v1/login', ['email' => 'primeiro@example.com', 'password' => 'Kwanza@2026'])->assertOk();

    expect(UserNotification::where('type', 'security')->count())->toBe(0);
    expect($u->fresh()->last_login_ip)->not->toBeNull();
});

it('lista o feed com contagem de não lidas', function () {
    Sanctum::actingAs($user = User::factory()->create());
    UserNotification::create(['user_id' => $user->id, 'type' => 'transaction', 'title' => 'Transação KZ1', 'body' => 'Criada.', 'is_read' => false]);
    UserNotification::create(['user_id' => $user->id, 'type' => 'security', 'title' => 'Novo acesso', 'body' => 'IP novo.', 'is_read' => true]);

    $this->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('unread', 1);
});

it('marca todas como lidas', function () {
    Sanctum::actingAs($user = User::factory()->create());
    UserNotification::create(['user_id' => $user->id, 'type' => 'info', 'title' => 'A', 'body' => 'a', 'is_read' => false]);
    UserNotification::create(['user_id' => $user->id, 'type' => 'info', 'title' => 'B', 'body' => 'b', 'is_read' => false]);

    $this->postJson('/api/v1/notifications/read-all')->assertOk();

    expect(UserNotification::where('user_id', $user->id)->where('is_read', false)->count())->toBe(0);
});
