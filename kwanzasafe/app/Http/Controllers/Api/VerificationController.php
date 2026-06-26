<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\KycBot;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * KYC (4 passos) via API mobile — espelha App\Http\Controllers\VerificationController.
 * Cada passo reanalisa com o KycBot e devolve o utilizador + resultado.
 */
class VerificationController extends Controller
{
    /** Passo 1: dados pessoais. */
    public function personalData(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name'    => 'required|string|max:255',
            'birth_date'   => 'required|date|before:-18 years',
            'gender'       => 'required|in:M,F',
            'bi_number'    => 'required|string|max:30|unique:users,bi_number,'.$request->user()->id,
            'bi_expiry'    => 'required|date|after:today',
            'province'     => 'required|string|max:100',
            'municipality' => 'required|string|max:100',
            'address'      => 'required|string|max:255',
        ]);

        $user = $request->user();
        $user->fill($data);
        $user->data_verified = true;
        $user->save();

        $result = (new KycBot())->analyzeAndApply($user);

        return $this->kycResponse($user, $result, 'Dados pessoais guardados.');
    }

    /** Passo 2: envia OTP (por email) para confirmar o número de telefone. */
    public function sendPhoneOtp(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string|min:7|max:30']);

        $user   = $request->user();
        $result = (new OtpService())->sendPhoneOtp($user, $request->phone, $request->ip());

        if (! $result['success']) {
            throw ValidationException::withMessages(['phone' => $result['message']]);
        }

        $user->phone_number      = $request->phone;
        $user->phone_verified_at = null;
        $user->save();

        return response()->json(['message' => $result['message']]);
    }

    /** Passo 2b: confirma o OTP do telefone. */
    public function verifyPhone(Request $request): JsonResponse
    {
        $request->validate(['phone_otp' => 'required|string|size:6']);

        $user   = $request->user();
        $result = (new OtpService())->verifyPhoneOtp($user, $request->phone_otp);

        if (! $result['success']) {
            throw ValidationException::withMessages(['phone_otp' => $result['message']]);
        }

        $bot = (new KycBot())->analyzeAndApply($user);

        return $this->kycResponse($user, $bot, 'Número de telefone verificado.');
    }

    /** Passo 3: upload do documento de identidade. */
    public function uploadDocument(Request $request): JsonResponse
    {
        $request->validate(['document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120']);

        $user = $request->user();

        if ($user->identity_document_path && Storage::disk('local')->exists($user->identity_document_path)) {
            Storage::disk('local')->delete($user->identity_document_path);
        }

        $user->identity_document_path = $request->file('document')->store('kyc/documents', 'local');
        $user->save();

        $result = (new KycBot())->analyzeAndApply($user);

        return $this->kycResponse($user, $result, 'Documento enviado.');
    }

    /** Passo 4: upload da selfie. */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate(['photo' => 'required|file|mimes:jpg,jpeg,png|max:5120']);

        $user = $request->user();

        if ($user->profile_photo_path && Storage::disk('local')->exists($user->profile_photo_path)) {
            Storage::disk('local')->delete($user->profile_photo_path);
        }

        $user->profile_photo_path = $request->file('photo')->store('kyc/photos', 'local');
        $user->save();

        $result = (new KycBot())->analyzeAndApply($user);

        return $this->kycResponse($user, $result, 'Selfie enviada.');
    }

    /** Resposta uniforme: mensagem amigável + resultado do bot + utilizador. */
    private function kycResponse(User $user, array $result, string $base): JsonResponse
    {
        return response()->json([
            'message' => $this->message($result, $base),
            'kyc'     => [
                'score'  => $result['score'],
                'status' => $result['status'],
            ],
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    private function message(array $result, string $base): string
    {
        return match ($result['status']) {
            'auto_approved' => "{$base} Identidade aprovada automaticamente! Já podes transacionar.",
            'auto_rejected' => "{$base} A verificação automática falhou (score {$result['score']}/100). Revê os dados e tenta de novo.",
            default         => "{$base} Score automático: {$result['score']}/100. Aguarda revisão manual (até 24h).",
        };
    }
}
