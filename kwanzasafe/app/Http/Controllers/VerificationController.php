<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\KycBot;
use App\Services\OtpService;

class VerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Passo 1: Dados pessoais (nome, BI, província, etc.)
     */
    public function updatePersonalData(Request $request)
    {
        $request->validate([
            'full_name'    => 'required|string|max:255',
            'birth_date'   => 'required|date|before:-18 years',
            'gender'       => 'required|in:M,F',
            'bi_number'    => 'required|string|max:30|unique:users,bi_number,' . Auth::id(),
            'bi_expiry'    => 'required|date|after:today',
            'province'     => 'required|string|max:100',
            'municipality' => 'required|string|max:100',
            'address'      => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->fill($request->only([
            'full_name', 'birth_date', 'gender', 'bi_number', 'bi_expiry',
            'province', 'municipality', 'address'
        ]));
        $user->data_verified = true;
        $user->save();

        // Re-analisar com bot
        $this->reanalyzeKyc($user);

        return back()->with('success', 'Dados pessoais guardados.');
    }

    /**
     * Passo 2: Telefone — guarda o número e envia OTP por email
     */
    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:7|max:30',
        ]);

        $user   = Auth::user();
        $otp    = new OtpService();
        $result = $otp->sendPhoneOtp($user, $request->phone, $request->ip());

        if (!$result['success']) {
            return back()->withErrors(['phone' => $result['message']]);
        }

        // Guardar número temporariamente (sem marcar como verificado ainda)
        $user->phone_number      = $request->phone;
        $user->phone_verified_at = null;
        $user->save();

        return back()->with('success', $result['message'] . ' Introduz o código para confirmar o número.');
    }

    /**
     * Passo 2b: Verificar OTP de telefone
     */
    public function verifyPhone(Request $request)
    {
        $request->validate([
            'phone_otp' => 'required|string|size:6',
        ]);

        $user   = Auth::user();
        $otp    = new OtpService();
        $result = $otp->verifyPhoneOtp($user, $request->phone_otp);

        if (!$result['success']) {
            return back()->withErrors(['phone_otp' => $result['message']]);
        }

        $this->reanalyzeKyc($user);

        return back()->with('success', 'Número de telefone verificado com sucesso!');
    }

    /**
     * Passo 3: Upload do documento de identidade
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = Auth::user();

        // Apagar ficheiro anterior se existir
        if ($user->identity_document_path && Storage::disk('local')->exists($user->identity_document_path)) {
            Storage::disk('local')->delete($user->identity_document_path);
        }

        $path = $request->file('document')->store('kyc/documents', 'local');
        $user->identity_document_path = $path;
        $user->save();

        // 🤖 BOT analisa imediatamente
        $result = $this->reanalyzeKyc($user);

        return back()->with('success', $this->successMessage($result, 'Documento enviado.'));
    }

    /**
     * Passo 4: Upload da selfie
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $user = Auth::user();

        if ($user->profile_photo_path && Storage::disk('local')->exists($user->profile_photo_path)) {
            Storage::disk('local')->delete($user->profile_photo_path);
        }

        $path = $request->file('photo')->store('kyc/photos', 'local');
        $user->profile_photo_path = $path;
        $user->save();

        // 🤖 BOT analisa imediatamente — pode auto-aprovar aqui se já estava tudo OK
        $result = $this->reanalyzeKyc($user);

        return back()->with('success', $this->successMessage($result, 'Selfie enviada.'));
    }

    /**
     * Chama o KycBot e atualiza o utilizador.
     */
    private function reanalyzeKyc($user): array
    {
        $bot = new KycBot();
        return $bot->analyzeAndApply($user);
    }

    /**
     * Constrói mensagem de sucesso amigável conforme decisão do bot.
     */
    private function successMessage(array $result, string $defaultMsg): string
    {
        $score = $result['score'];
        $status = $result['status'];

        if ($status === 'auto_approved') {
            return "✅ {$defaultMsg} Identidade APROVADA automaticamente! Já podes realizar transações.";
        }

        if ($status === 'auto_rejected') {
            return "❌ {$defaultMsg} Mas a verificação automática falhou (score: {$score}/100). Revê os teus dados e tenta novamente.";
        }

        // pending_review
        return "{$defaultMsg} Score automático: {$score}/100. Aguarda revisão manual em até 24h.";
    }
}
