<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use App\Services\TotpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Autenticação da API mobile via tokens Sanctum (Bearer).
 *
 * Aditivo: não interfere com o fluxo de sessão web (Breeze) existente.
 * Cada endpoint emite/revoga personal access tokens — sem cookies nem CSRF.
 */
class AuthController extends Controller
{
    /**
     * Registo de novo cliente. Devolve token + utilizador.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'full_name' => $request->string('name'),
            'email'     => $request->string('email'),
            'password'  => Hash::make($request->string('password')),
        ]);

        // Mantém o fluxo de verificação de email existente (envia notificação).
        event(new Registered($user));

        $token = $user->createToken($request->deviceName())->plainTextToken;

        AuditLogger::auth('register', 'Registo via app mobile', [
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'metadata'   => ['device' => $request->deviceName(), 'channel' => 'mobile'],
        ]);

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ], 201);
    }

    /**
     * Login. Valida credenciais (com throttle) e emite token novo.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Lança ValidationException (422) em credenciais inválidas ou throttle.
        $request->authenticate();

        /** @var User $user */
        $user = $request->user();

        // Gate de 2FA (TOTP) — só para quem tem a autenticação em 2 passos activa.
        if ($user->hasTwoFactorEnabled()) {
            $code = (string) $request->input('two_factor_code', '');

            if ($code === '') {
                return response()->json([
                    'message'             => 'Introduz o código de autenticação em 2 passos.',
                    'two_factor_required' => true,
                ], 422);
            }

            if (! app(TotpService::class)->verify($user->two_factor_secret, $code)) {
                throw ValidationException::withMessages([
                    'two_factor_code' => 'Código de 2 passos inválido.',
                ]);
            }
        }

        // Auditoria de novo IP: notifica + email se o acesso vier de um IP novo.
        $ip         = $request->ip();
        $device     = (string) $request->input('device_name', 'dispositivo móvel');
        $previousIp = $user->last_login_ip;

        if ($previousIp && $previousIp !== $ip) {
            $when = now()->format('d/m/Y H:i');

            NotificationService::notify($user, 'security',
                'Novo acesso à tua conta',
                "Detetámos um acesso a partir de um novo endereço (IP {$ip}) em {$when}. Se não foste tu, muda a tua palavra-passe.",
                ['ip' => $ip, 'device' => $device, 'at' => now()->toIso8601String()],
                true, // também envia push
            );

            try {
                Mail::raw(
                    "Olá,\n\nDetetámos um novo acesso à tua conta KwanzaSafe:\n".
                    "  • IP: {$ip}\n  • Dispositivo: {$device}\n  • Data: {$when}\n\n".
                    "Se foste tu, ignora este email. Se não reconheces este acesso, muda a tua palavra-passe imediatamente.",
                    fn ($m) => $m->to($user->email)->subject('KwanzaSafe — novo acesso à tua conta')
                );
            } catch (\Throwable) {
                // Email nunca quebra o login.
            }
        }

        $user->forceFill(['last_login_ip' => $ip, 'last_login_at' => now()])->saveQuietly();

        $token = $user->createToken($request->deviceName())->plainTextToken;

        AuditLogger::auth('login', 'Login via app mobile', [
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'metadata'   => ['device' => $request->deviceName(), 'channel' => 'mobile'],
        ]);

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ]);
    }

    /**
     * Logout — revoga apenas o token usado neste pedido.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->user()->currentAccessToken()->delete();

        AuditLogger::auth('logout', 'Logout via app mobile', [
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'metadata'   => ['channel' => 'mobile'],
        ]);

        return response()->json(['message' => 'Sessão terminada.']);
    }

    /**
     * Perfil do utilizador autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }
}
