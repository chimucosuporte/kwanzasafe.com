<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

/**
 * Recuperação de palavra-passe por OTP (email) via API mobile.
 *
 * Fluxo: forgot (envia código) → reset (código + nova palavra-passe).
 * Não usa link de reset (impróprio para app nativa); usa código de 6 dígitos.
 */
class PasswordResetController extends Controller
{
    public function __construct(private OtpService $otp)
    {
    }

    /**
     * Envia um código de recuperação para o email indicado.
     * Resposta genérica — nunca revela se o email existe.
     */
    public function forgot(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email']);

        $user = User::where('email', $data['email'])->first();
        if ($user) {
            $this->otp->sendPasswordResetOtp($user, $request->ip());

            AuditLogger::auth('password_reset_requested', 'Pedido de recuperação de palavra-passe (mobile)', [
                'user_id'    => $user->id,
                'user_email' => $user->email,
                'metadata'   => ['channel' => 'mobile'],
            ]);
        }

        return response()->json([
            'message' => 'Se existir uma conta com esse email, enviámos um código de 6 dígitos.',
        ]);
    }

    /**
     * Valida o código e define a nova palavra-passe.
     */
    public function reset(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'code'     => 'required|string|size:6',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            throw ValidationException::withMessages(['email' => 'Não foi possível redefinir a palavra-passe.']);
        }

        $result = $this->otp->verifyPasswordResetOtp($user, $data['code']);
        if (! $result['success']) {
            throw ValidationException::withMessages(['code' => $result['message']]);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        // Segurança: revoga todos os tokens existentes após redefinir.
        $user->tokens()->delete();

        AuditLogger::auth('password_reset', 'Palavra-passe redefinida (mobile)', [
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'metadata'   => ['channel' => 'mobile'],
        ]);

        return response()->json([
            'message' => 'Palavra-passe redefinida com sucesso. Já podes entrar.',
        ]);
    }
}
