<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Revisão de KYC via app admin — espelha o Admin\KycAdminController web.
 */
class KycController extends Controller
{
    /** Pendentes de revisão + aprovados recentes. */
    public function index(): JsonResponse
    {
        $pending = User::whereNotNull('identity_document_path')
            ->whereNull('identity_verified_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($u) => $this->listItem($u));

        $approved = User::whereNotNull('identity_verified_at')
            ->orderByDesc('identity_verified_at')
            ->limit(20)
            ->get()
            ->map(fn ($u) => $this->listItem($u));

        return response()->json([
            'pending'  => $pending->values(),
            'approved' => $approved->values(),
        ]);
    }

    /** Detalhe KYC de um utilizador. */
    public function show($userId): JsonResponse
    {
        $u = User::findOrFail($userId);
        return response()->json(['data' => $this->detail($u)]);
    }

    /** Aprova o KYC manualmente. */
    public function approve(Request $request, $userId): JsonResponse
    {
        $u = User::findOrFail($userId);

        if (! $u->identity_document_path) {
            return response()->json(['message' => 'O utilizador não submeteu documento de identidade.'], 422);
        }

        $u->update(['identity_verified_at' => now()]);
        $u->syncFullyVerified();

        AuditLogger::kyc('approved',
            "KYC aprovado manualmente para {$u->email} (BI: {$u->bi_number}) (app)",
            $u,
            ['kyc_score' => $u->kyc_score, 'admin_id' => $request->user()->id]
        );

        return response()->json(['message' => "KYC de {$u->email} aprovado.", 'data' => $this->detail($u->fresh())]);
    }

    /** Rejeita o KYC (limpa documento/selfie) com motivo. */
    public function reject(Request $request, $userId): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $u = User::findOrFail($userId);

        $u->update([
            'identity_document_path' => null,
            'profile_photo_path'     => null,
            'identity_verified_at'   => null,
        ]);
        $u->syncFullyVerified();

        AuditLogger::kyc('rejected',
            "KYC rejeitado para {$u->email}. Motivo: {$data['reason']} (app)",
            $u,
            ['reason' => $data['reason'], 'kyc_score' => $u->kyc_score, 'admin_id' => $request->user()->id]
        );

        return response()->json(['message' => "KYC de {$u->email} rejeitado.", 'data' => $this->detail($u->fresh())]);
    }

    // ---- serialização ----

    private function listItem(User $u): array
    {
        return [
            'id'            => $u->id,
            'full_name'     => $u->full_name,
            'email'         => $u->email,
            'bi_number'     => $u->bi_number,
            'kyc_score'     => $u->kyc_score,
            'kyc_bot_status' => $u->kyc_bot_status,
            'is_verified'   => (bool) $u->identity_verified_at,
            'submitted_at'  => $u->updated_at?->toIso8601String(),
        ];
    }

    private function detail(User $u): array
    {
        return array_merge($this->listItem($u), [
            'birth_date'      => $u->birth_date ? \Illuminate\Support\Carbon::parse($u->birth_date)->format('Y-m-d') : null,
            'bi_expiry'       => $u->bi_expiry ? \Illuminate\Support\Carbon::parse($u->bi_expiry)->format('Y-m-d') : null,
            'gender'          => $u->gender,
            'province'        => $u->province,
            'municipality'    => $u->municipality,
            'address'         => $u->address,
            'phone_number'    => $u->phone_number,
            'phone_verified'  => (bool) $u->phone_verified_at,
            'data_verified'   => (bool) $u->data_verified,
            'kyc_bot_notes'   => $u->kyc_bot_notes,
            'document_url'    => $u->identity_document_path ? url('/api/v1/file/' . $u->identity_document_path) : null,
            'photo_url'       => $u->profile_photo_path ? url('/api/v1/file/' . $u->profile_photo_path) : null,
            'identity_verified_at' => $u->identity_verified_at?->toIso8601String(),
        ]);
    }
}
