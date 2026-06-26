<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuditLogger;
use App\Services\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Autenticação em 2 passos (TOTP / Google Authenticator) via API mobile.
 *
 * Fluxo: enable (gera segredo) → confirm (valida 1.º código) → activa.
 * Só quando confirmado é que o login passa a exigir o código.
 */
class TwoFactorController extends Controller
{
    public function __construct(private TotpService $totp)
    {
    }

    /** Estado actual do 2FA. */
    public function status(Request $request): JsonResponse
    {
        return response()->json(['enabled' => $request->user()->hasTwoFactorEnabled()]);
    }

    /** Gera um segredo novo (ainda por confirmar) e devolve a chave + otpauth URI. */
    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'two_factor' => 'A autenticação em 2 passos já está activa.',
            ]);
        }

        $secret = $this->totp->generateSecret();
        $user->two_factor_secret = $secret;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return response()->json([
            'secret'      => $secret,
            'otpauth_uri' => $this->totp->otpauthUri($secret, $user->email, 'KwanzaSafe'),
        ]);
    }

    /** Confirma o primeiro código e activa o 2FA. */
    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();

        if (! $user->two_factor_secret) {
            throw ValidationException::withMessages([
                'two_factor' => 'Inicia primeiro a configuração do 2FA.',
            ]);
        }

        if (! $this->totp->verify($user->two_factor_secret, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'Código inválido. Verifica o Google Authenticator e tenta de novo.',
            ]);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        AuditLogger::auth('2fa_enabled', '2FA activada (mobile)', [
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'metadata'   => ['channel' => 'mobile'],
        ]);

        return response()->json([
            'message' => 'Autenticação em 2 passos activada.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    /** Desactiva o 2FA (exige a palavra-passe). */
    public function disable(Request $request): JsonResponse
    {
        $data = $request->validate(['password' => ['required', 'string']]);

        $user = $request->user();

        if (! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'A palavra-passe está incorreta.',
            ]);
        }

        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        AuditLogger::auth('2fa_disabled', '2FA desactivada (mobile)', [
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'metadata'   => ['channel' => 'mobile'],
        ]);

        return response()->json([
            'message' => 'Autenticação em 2 passos desactivada.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }
}
