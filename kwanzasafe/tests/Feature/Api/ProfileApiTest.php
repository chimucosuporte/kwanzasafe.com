<?php

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

/** Cria um par de OTP de alteração de email com códigos conhecidos. */
function makeEmailChangeOtps(User $user, string $newEmail, string $codeCurrent, string $codeNew): void
{
    OtpCode::create(['user_id' => $user->id, 'type' => 'email_change_current', 'destination' => $user->email, 'code_hash' => Hash::make($codeCurrent), 'attempts' => 0, 'expires_at' => now()->addMinutes(15)]);
    OtpCode::create(['user_id' => $user->id, 'type' => 'email_change_new', 'destination' => $newEmail, 'code_hash' => Hash::make($codeNew), 'attempts' => 0, 'expires_at' => now()->addMinutes(15)]);
}

/*
| Perfil via API — foco no upload de avatar (/api/v1/profile/photo).
*/

it('faz upload do avatar e devolve avatar_url', function () {
    Storage::fake('local');
    Sanctum::actingAs($user = User::factory()->create());

    $this->postJson('/api/v1/profile/photo', [
        'photo' => UploadedFile::fake()->image('avatar.jpg'),
    ])
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonStructure(['message', 'user' => ['avatar_url']]);

    $user->refresh();
    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('local')->assertExists($user->avatar_path);
});

it('rejeita ficheiro que não é imagem', function () {
    Storage::fake('local');
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/profile/photo', [
        'photo' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('photo');
});

it('exige autenticação para o avatar', function () {
    $this->postJson('/api/v1/profile/photo', [])->assertUnauthorized();
});

it('pede a alteração de email e cria os dois códigos', function () {
    Mail::fake();
    Sanctum::actingAs($user = User::factory()->create(['email' => 'antigo@example.com']));

    $this->postJson('/api/v1/profile/email/request', ['email' => 'novo@example.com'])->assertOk();

    $this->assertDatabaseHas('otp_codes', ['user_id' => $user->id, 'type' => 'email_change_current', 'destination' => 'antigo@example.com']);
    $this->assertDatabaseHas('otp_codes', ['user_id' => $user->id, 'type' => 'email_change_new', 'destination' => 'novo@example.com']);
});

it('confirma a alteração de email com os dois códigos certos', function () {
    $user = User::factory()->create(['email' => 'antigo@example.com']);
    makeEmailChangeOtps($user, 'novo@example.com', '111111', '222222');
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/profile/email/confirm', [
        'email' => 'novo@example.com', 'code_current' => '111111', 'code_new' => '222222',
    ])
        ->assertOk()
        ->assertJsonPath('user.email', 'novo@example.com')
        ->assertJsonPath('user.email_verified', true);

    expect($user->fresh()->email)->toBe('novo@example.com');
});

it('rejeita a alteração de email com o código novo errado', function () {
    $user = User::factory()->create(['email' => 'antigo@example.com']);
    makeEmailChangeOtps($user, 'novo@example.com', '111111', '222222');
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/profile/email/confirm', [
        'email' => 'novo@example.com', 'code_current' => '111111', 'code_new' => '000000',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('code_new');

    expect($user->fresh()->email)->toBe('antigo@example.com');
});

it('recusa email já usado por outro utilizador', function () {
    User::factory()->create(['email' => 'ocupado@example.com']);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/profile/email/request', ['email' => 'ocupado@example.com'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});
