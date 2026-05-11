<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\OtpService;

class OtpController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->middleware('auth');
        $this->otpService = $otpService;
    }

    /**
     * Mostra a página de verificação de email
     */
    public function showEmailVerification()
    {
        $user = Auth::user();

        // Se já está verificado, redirecionar
        if ($user->email_verified_at) {
            return redirect()->route('dashboard')
                ->with('success', 'O teu email já está verificado.');
        }

        return view('auth.otp.verify-email');
    }

    /**
     * Envia código OTP por email
     */
    public function sendEmailOtp(Request $request)
    {
        $user = Auth::user();

        // Se já está verificado
        if ($user->email_verified_at) {
            return redirect()->route('dashboard')
                ->with('success', 'O teu email já está verificado.');
        }

        $result = $this->otpService->sendEmailOtp($user, $request->ip());

        if (!$result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        return redirect()->route('otp.email.verify')
            ->with('success', $result['message']);
    }

    /**
     * Valida o código OTP submetido
     */
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ], [
            'code.required' => 'Introduz o código de 6 dígitos.',
            'code.size'     => 'O código deve ter 6 dígitos.',
        ]);

        $user = Auth::user();
        $result = $this->otpService->verifyEmailOtp($user, $request->input('code'));

        if (!$result['success']) {
            return back()->withErrors(['code' => $result['message']]);
        }

        return redirect()->route('dashboard')
            ->with('success', '✅ ' . $result['message']);
    }
}
