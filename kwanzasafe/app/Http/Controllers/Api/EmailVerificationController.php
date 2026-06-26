<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuditLogger;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Verificação de email por OTP via API mobile.
 * Reutiliza OtpService (mesma lógica do OtpController web).
 */
class EmailVerificationController extends Controller
{
    public function __construct(private OtpService $otp)
    {
    }

    /** Envia um código de 6 dígitos para o email do utilizador autenticado. */
    public function send(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'message'          => 'O teu email já está verificado.',
                'already_verified' => true,
            ]);
        }

        $result = $this->otp->sendEmailOtp($user, $request->ip());

        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 429);
        }

        return response()->json(['message' => $result['message']]);
    }

    /** Valida o código submetido e marca o email como verificado. */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => 'required|string|size:6']);
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'O teu email já está verificado.',
                'user'    => new UserResource($user),
            ]);
        }

        $result = $this->otp->verifyEmailOtp($user, $data['code']);

        if (! $result['success']) {
            throw ValidationException::withMessages(['code' => $result['message']]);
        }

        AuditLogger::auth('email_verified', 'Email verificado via app mobile', [
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'metadata'   => ['channel' => 'mobile'],
        ]);

        return response()->json([
            'message' => $result['message'],
            'user'    => new UserResource($user->fresh()),
        ]);
    }
}
