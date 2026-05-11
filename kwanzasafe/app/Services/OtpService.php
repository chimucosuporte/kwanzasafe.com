<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpCode;
use App\Mail\OtpEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================================
 * KwanzaSafe — OtpService
 * ============================================================================
 *
 * Gestão completa de códigos OTP (One-Time Password) para verificação.
 *
 * Funcionalidades:
 *   - Geração de códigos de 6 dígitos
 *   - Hash seguro do código (nunca guardado em plain text)
 *   - Envio por email
 *   - Validação com tentativas limitadas (max 5)
 *   - Expiração automática (15 minutos)
 *   - Rate limiting (max 3 códigos novos por hora)
 *
 * Uso:
 *   $service = new OtpService();
 *
 *   // Gerar e enviar
 *   $result = $service->sendEmailOtp($user);
 *   // $result['success'], $result['message']
 *
 *   // Validar
 *   $valid = $service->verifyEmailOtp($user, '123456');
 *
 * ============================================================================
 */
class OtpService
{
    // ============ CONFIGURAÇÃO ============
    const CODE_LENGTH = 6;
    const EXPIRY_MINUTES = 15;
    const MAX_ATTEMPTS = 5;
    const MAX_CODES_PER_HOUR = 3;

    // ========================================================================
    // EMAIL OTP
    // ========================================================================

    /**
     * Gera um código OTP e envia por email
     */
    public function sendEmailOtp(User $user, ?string $ip = null): array
    {
        // 1. Verificar rate limit
        if (!$this->canRequestNewCode($user, 'email')) {
            return [
                'success' => false,
                'message' => 'Demasiados códigos solicitados. Aguarda 1 hora antes de tentar novamente.',
                'code' => 'rate_limited',
            ];
        }

        // 2. Gerar código de 6 dígitos
        $plainCode = $this->generateCode();

        // 3. Invalidar códigos anteriores não usados
        $this->invalidatePreviousCodes($user, 'email');

        // 4. Guardar hash do código
        $otp = OtpCode::create([
            'user_id'     => $user->id,
            'type'        => 'email',
            'destination' => $user->email,
            'code_hash'   => Hash::make($plainCode),
            'attempts'    => 0,
            'expires_at'  => now()->addMinutes(self::EXPIRY_MINUTES),
            'ip_address'  => $ip,
        ]);

        // 5. Enviar email
        try {
            Mail::to($user->email)->send(new OtpEmail($user, $plainCode, self::EXPIRY_MINUTES));

            Log::info('OTP email sent', [
                'user_id' => $user->id,
                'otp_id'  => $otp->id,
                'email'   => $user->email,
            ]);

            return [
                'success' => true,
                'message' => 'Código enviado para ' . $this->maskEmail($user->email),
                'expires_in_minutes' => self::EXPIRY_MINUTES,
            ];
        } catch (\Exception $e) {
            // Em caso de falha de envio, apaga o registo (cliente pode tentar de novo)
            $otp->delete();

            Log::error('Failed to send OTP email', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao enviar email. Verifica o teu endereço ou tenta novamente em alguns minutos.',
                'code' => 'send_failed',
            ];
        }
    }

    /**
     * Valida o código OTP de email submetido pelo utilizador
     */
    public function verifyEmailOtp(User $user, string $submittedCode): array
    {
        // Limpar formato (espaços, traços)
        $submittedCode = preg_replace('/[^0-9]/', '', $submittedCode);

        if (strlen($submittedCode) !== self::CODE_LENGTH) {
            return [
                'success' => false,
                'message' => 'Código deve ter 6 dígitos.',
                'code' => 'invalid_format',
            ];
        }

        // Buscar código válido mais recente
        $otp = OtpCode::where('user_id', $user->id)
                      ->where('type', 'email')
                      ->whereNull('verified_at')
                      ->where('expires_at', '>', now())
                      ->orderBy('created_at', 'desc')
                      ->first();

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'Nenhum código válido encontrado. Solicita um novo código.',
                'code' => 'no_active_code',
            ];
        }

        // Verificar tentativas máximas
        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            return [
                'success' => false,
                'message' => 'Demasiadas tentativas erradas. Solicita um novo código.',
                'code' => 'max_attempts',
            ];
        }

        // Incrementar tentativas
        $otp->increment('attempts');

        // Verificar código
        if (!Hash::check($submittedCode, $otp->code_hash)) {
            $remaining = self::MAX_ATTEMPTS - $otp->attempts;
            return [
                'success' => false,
                'message' => "Código incorreto. Restam {$remaining} tentativa(s).",
                'code' => 'invalid_code',
                'attempts_remaining' => $remaining,
            ];
        }

        // ✅ Código correto!
        $otp->markAsVerified();

        // Marcar email do utilizador como verificado
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        Log::info('OTP email verified successfully', [
            'user_id' => $user->id,
            'otp_id'  => $otp->id,
        ]);

        return [
            'success' => true,
            'message' => 'Email verificado com sucesso!',
        ];
    }

    // ========================================================================
    // HELPERS
    // ========================================================================

    /**
     * Gera código numérico aleatório com 6 dígitos
     * Garante que não começa por 0 (visualmente mais limpo)
     */
    private function generateCode(): string
    {
        $code = '';
        $code .= random_int(1, 9); // primeiro dígito 1-9
        for ($i = 1; $i < self::CODE_LENGTH; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }

    /**
     * Verifica rate limiting (max 3 códigos novos por hora)
     */
    private function canRequestNewCode(User $user, string $type): bool
    {
        $recent = OtpCode::where('user_id', $user->id)
                         ->where('type', $type)
                         ->where('created_at', '>=', now()->subHour())
                         ->count();

        return $recent < self::MAX_CODES_PER_HOUR;
    }

    /**
     * Invalida códigos anteriores não usados (para que apenas o último seja válido)
     */
    private function invalidatePreviousCodes(User $user, string $type): void
    {
        OtpCode::where('user_id', $user->id)
               ->where('type', $type)
               ->whereNull('verified_at')
               ->update(['expires_at' => now()->subSecond()]);
    }

    /**
     * Mascara email para feedback ao utilizador
     * Ex: jose@email.com → j***@email.com
     */
    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email);
        if (strlen($local) <= 2) {
            return $local[0] . '***@' . $domain;
        }
        return substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 2, 3)) . '@' . $domain;
    }

    /**
     * Limpa códigos expirados antigos (chamar via cron / scheduler)
     */
    public function cleanupExpired(): int
    {
        return OtpCode::where('expires_at', '<', now()->subDays(7))->delete();
    }
}
