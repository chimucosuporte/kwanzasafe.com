<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectKycRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KycAdminController extends Controller
{
    public function index()
    {
        $pendingUsers = User::whereNotNull('identity_document_path')
            ->whereNull('identity_verified_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        $approvedUsers = User::whereNotNull('identity_verified_at')
            ->orderBy('identity_verified_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.kyc.index', compact('pendingUsers', 'approvedUsers'));
    }

    public function show($userId)
    {
        $kycUser = User::findOrFail($userId);
        return view('admin.kyc.show', compact('kycUser'));
    }

    public function approve(Request $request, $userId)
    {
        $kycUser = User::findOrFail($userId);

        if (!$kycUser->identity_document_path) {
            return redirect()->route('admin.kyc.show', $userId)
                ->with('error', 'O utilizador não submeteu documento de identidade.');
        }

        $kycUser->update(['identity_verified_at' => now()]);
        $kycUser->syncFullyVerified();

        AuditLogger::kyc('approved',
            "KYC aprovado manualmente para {$kycUser->email} (BI: {$kycUser->bi_number})",
            $kycUser,
            ['kyc_score' => $kycUser->kyc_score, 'admin_id' => Auth::id()]
        );

        return redirect()->route('admin.kyc.index')
            ->with('success', "KYC de {$kycUser->email} aprovado com sucesso.");
    }

    public function reject(RejectKycRequest $request, $userId)
    {
        $kycUser = User::findOrFail($userId);

        $kycUser->update([
            'identity_document_path' => null,
            'profile_photo_path'     => null,
            'identity_verified_at'   => null,
        ]);
        $kycUser->syncFullyVerified();

        AuditLogger::kyc('rejected',
            "KYC rejeitado para {$kycUser->email}. Motivo: {$request->reason}",
            $kycUser,
            ['reason' => $request->reason, 'kyc_score' => $kycUser->kyc_score, 'admin_id' => Auth::id()]
        );

        return redirect()->route('admin.kyc.index')
            ->with('success', "KYC de {$kycUser->email} rejeitado.");
    }
}
