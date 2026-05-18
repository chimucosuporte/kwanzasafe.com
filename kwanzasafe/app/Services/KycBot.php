<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * ============================================================================
 * KwanzaSafe — KycBot (Nível 1)
 * ============================================================================
 *
 * Service de verificação automática de identidade.
 *
 * Calcula um score de 0-100 baseado em 8 validações:
 *   1. Dados pessoais completos (15 pts)
 *   2. Formato BI angolano válido (15 pts)
 *   3. BI ainda válido / não expirado (10 pts)
 *   4. Idade >= 18 anos (10 pts)
 *   5. Documento submetido + tamanho OK (15 pts)
 *   6. Selfie submetida + tamanho OK (15 pts)
 *   7. Telefone verificado (10 pts)
 *   8. Email verificado (10 pts)
 *
 * Decisão automática:
 *   - score >= 80  → AUTO-APROVA (identity_verified_at = now())
 *   - score 50-79  → PENDING_REVIEW (admin revê manualmente)
 *   - score < 50   → AUTO-REJEITA (limpa documentos)
 *
 * Uso:
 *   $bot = new KycBot();
 *   $result = $bot->analyze($user);
 *
 *   $result['score']    // 0-100
 *   $result['status']   // 'auto_approved', 'pending_review', 'auto_rejected'
 *   $result['notes']    // array detalhado de validações
 * ============================================================================
 */
class KycBot
{
    // ============ THRESHOLDS ============
    const THRESHOLD_AUTO_APPROVE = 80;
    const THRESHOLD_AUTO_REJECT  = 50;

    // ============ TAMANHOS DE FICHEIRO (em bytes) ============
    const MIN_FILE_SIZE = 30 * 1024;   // 30 KB (qualquer coisa menor é suspeito)
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB

    // ============ FORMATO BI ANGOLANO ============
    // Formato típico: 9 dígitos + 2 letras + 3 dígitos (ex: 000123456LA041)
    const BI_REGEX = '/^\d{9}[A-Z]{2}\d{3}$/i';

    // ============ IDADE MÍNIMA ============
    const MIN_AGE = 18;

    /**
     * Analisa o KYC de um utilizador e devolve resultado completo.
     */
    public function analyze(User $user): array
    {
        $checks = [];
        $score = 0;

        // 1. DADOS PESSOAIS COMPLETOS (15 pts)
        $check1 = $this->checkPersonalData($user);
        $checks[] = $check1;
        $score += $check1['points'];

        // 2. FORMATO BI VÁLIDO (15 pts)
        $check2 = $this->checkBiFormat($user);
        $checks[] = $check2;
        $score += $check2['points'];

        // 3. BI NÃO EXPIRADO (10 pts)
        $check3 = $this->checkBiExpiry($user);
        $checks[] = $check3;
        $score += $check3['points'];

        // 4. IDADE >= 18 (10 pts)
        $check4 = $this->checkAge($user);
        $checks[] = $check4;
        $score += $check4['points'];

        // 5. DOCUMENTO SUBMETIDO (15 pts)
        $check5 = $this->checkDocument($user);
        $checks[] = $check5;
        $score += $check5['points'];

        // 6. SELFIE SUBMETIDA (15 pts)
        $check6 = $this->checkSelfie($user);
        $checks[] = $check6;
        $score += $check6['points'];

        // 7. TELEFONE VERIFICADO (10 pts)
        $check7 = $this->checkPhone($user);
        $checks[] = $check7;
        $score += $check7['points'];

        // 8. EMAIL VERIFICADO (10 pts)
        $check8 = $this->checkEmail($user);
        $checks[] = $check8;
        $score += $check8['points'];

        // Determinar status
        $status = $this->determineStatus($score);

        return [
            'score'  => $score,
            'status' => $status,
            'notes'  => $checks,
            'analyzed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Analisa e ATUALIZA o utilizador em BD.
     * Aplica auto-aprovação ou auto-rejeição se aplicável.
     */
    public function analyzeAndApply(User $user): array
    {
        $result = $this->analyze($user);

        $user->kyc_score           = $result['score'];
        $user->kyc_bot_status      = $result['status'];
        $user->kyc_bot_analyzed_at = now();
        $user->kyc_bot_notes       = $result['notes'];

        // Aplicar decisões automáticas
        if ($result['status'] === 'auto_approved' && !$user->identity_verified_at) {
            $user->identity_verified_at = now();
        }

        if ($result['status'] === 'auto_rejected') {
            // Limpar documentos para forçar reupload
            $user->identity_document_path = null;
            $user->profile_photo_path     = null;
        }

        $user->save();

        // Sincronizar is_fully_verified após qualquer alteração KYC
        $user->syncFullyVerified();

        return $result;
    }

    // ========================================================================
    // VALIDAÇÕES INDIVIDUAIS
    // ========================================================================

    private function checkPersonalData(User $user): array
    {
        $required = ['full_name', 'birth_date', 'gender', 'bi_number', 'province', 'municipality', 'address'];
        $filled = 0;
        $missing = [];

        foreach ($required as $field) {
            if (!empty($user->$field)) {
                $filled++;
            } else {
                $missing[] = $field;
            }
        }

        $totalRequired = count($required);
        $points = (int) round(($filled / $totalRequired) * 15);

        return [
            'check'  => 'personal_data',
            'label'  => 'Dados pessoais completos',
            'max'    => 15,
            'points' => $points,
            'passed' => $filled === $totalRequired,
            'detail' => "{$filled}/{$totalRequired} campos preenchidos",
            'missing'=> $missing,
        ];
    }

    private function checkBiFormat(User $user): array
    {
        $bi = trim($user->bi_number ?? '');

        if (empty($bi)) {
            return [
                'check'  => 'bi_format',
                'label'  => 'Formato BI angolano',
                'max'    => 15,
                'points' => 0,
                'passed' => false,
                'detail' => 'BI não submetido',
            ];
        }

        $valid = (bool) preg_match(self::BI_REGEX, $bi);

        return [
            'check'  => 'bi_format',
            'label'  => 'Formato BI angolano',
            'max'    => 15,
            'points' => $valid ? 15 : 0,
            'passed' => $valid,
            'detail' => $valid
                ? "BI válido: {$bi}"
                : "Formato inválido (esperado: 9 dígitos + 2 letras + 3 dígitos): {$bi}",
        ];
    }

    private function checkBiExpiry(User $user): array
    {
        if (empty($user->bi_expiry)) {
            return [
                'check'  => 'bi_expiry',
                'label'  => 'BI não expirado',
                'max'    => 10,
                'points' => 0,
                'passed' => false,
                'detail' => 'Data de expiração não submetida',
            ];
        }

        try {
            $expiry = Carbon::parse($user->bi_expiry);
            $valid = $expiry->isFuture();
            $days = (int) now()->diffInDays($expiry, false);

            return [
                'check'  => 'bi_expiry',
                'label'  => 'BI não expirado',
                'max'    => 10,
                'points' => $valid ? 10 : 0,
                'passed' => $valid,
                'detail' => $valid
                    ? "Válido por mais {$days} dias (até {$expiry->format('d/m/Y')})"
                    : "EXPIRADO há " . abs($days) . " dias (em {$expiry->format('d/m/Y')})",
            ];
        } catch (\Exception $e) {
            return [
                'check'  => 'bi_expiry',
                'label'  => 'BI não expirado',
                'max'    => 10,
                'points' => 0,
                'passed' => false,
                'detail' => 'Data inválida',
            ];
        }
    }

    private function checkAge(User $user): array
    {
        if (empty($user->birth_date)) {
            return [
                'check'  => 'age',
                'label'  => 'Idade ≥ 18 anos',
                'max'    => 10,
                'points' => 0,
                'passed' => false,
                'detail' => 'Data de nascimento não submetida',
            ];
        }

        try {
            $birth = Carbon::parse($user->birth_date);
            $age = (int) $birth->age;
            $valid = $age >= self::MIN_AGE && $age <= 120;

            return [
                'check'  => 'age',
                'label'  => 'Idade ≥ 18 anos',
                'max'    => 10,
                'points' => $valid ? 10 : 0,
                'passed' => $valid,
                'detail' => $valid
                    ? "{$age} anos"
                    : ($age < self::MIN_AGE ? "Menor de idade ({$age} anos)" : "Idade improvável ({$age} anos)"),
            ];
        } catch (\Exception $e) {
            return [
                'check'  => 'age',
                'label'  => 'Idade ≥ 18 anos',
                'max'    => 10,
                'points' => 0,
                'passed' => false,
                'detail' => 'Data inválida',
            ];
        }
    }

    private function checkDocument(User $user): array
    {
        return $this->checkFile($user->identity_document_path, 'document', 'Documento de identidade', 15);
    }

    private function checkSelfie(User $user): array
    {
        return $this->checkFile($user->profile_photo_path, 'selfie', 'Selfie / Foto de rosto', 15);
    }

    /**
     * Validação genérica de ficheiro
     */
    private function checkFile(?string $path, string $check, string $label, int $maxPoints): array
    {
        if (empty($path)) {
            return [
                'check'  => $check,
                'label'  => $label,
                'max'    => $maxPoints,
                'points' => 0,
                'passed' => false,
                'detail' => 'Ficheiro não submetido',
            ];
        }

        try {
            if (!Storage::disk('public')->exists($path)) {
                return [
                    'check'  => $check,
                    'label'  => $label,
                    'max'    => $maxPoints,
                    'points' => 0,
                    'passed' => false,
                    'detail' => 'Ficheiro registado mas não encontrado no disco',
                ];
            }

            $size = Storage::disk('public')->size($path);

            // Tamanho muito pequeno → suspeito (provavelmente imagem corrompida)
            if ($size < self::MIN_FILE_SIZE) {
                return [
                    'check'  => $check,
                    'label'  => $label,
                    'max'    => $maxPoints,
                    'points' => (int) round($maxPoints * 0.3), // 30% (parcial)
                    'passed' => false,
                    'detail' => 'Ficheiro muito pequeno (' . $this->formatBytes($size) . ') — qualidade duvidosa',
                ];
            }

            // Tamanho muito grande → também suspeito
            if ($size > self::MAX_FILE_SIZE) {
                return [
                    'check'  => $check,
                    'label'  => $label,
                    'max'    => $maxPoints,
                    'points' => (int) round($maxPoints * 0.7), // 70% (passa mas com aviso)
                    'passed' => true,
                    'detail' => 'Ficheiro grande (' . $this->formatBytes($size) . ') — verificar manualmente',
                ];
            }

            // Verificar extensão
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $validExts = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];

            if (!in_array($ext, $validExts)) {
                return [
                    'check'  => $check,
                    'label'  => $label,
                    'max'    => $maxPoints,
                    'points' => 0,
                    'passed' => false,
                    'detail' => "Extensão suspeita: .{$ext}",
                ];
            }

            return [
                'check'  => $check,
                'label'  => $label,
                'max'    => $maxPoints,
                'points' => $maxPoints,
                'passed' => true,
                'detail' => 'OK — ' . $this->formatBytes($size) . ' (.' . $ext . ')',
            ];

        } catch (\Exception $e) {
            return [
                'check'  => $check,
                'label'  => $label,
                'max'    => $maxPoints,
                'points' => 0,
                'passed' => false,
                'detail' => 'Erro ao verificar: ' . $e->getMessage(),
            ];
        }
    }

    private function checkPhone(User $user): array
    {
        $verified = !empty($user->phone_verified_at);

        return [
            'check'  => 'phone',
            'label'  => 'Telefone verificado',
            'max'    => 10,
            'points' => $verified ? 10 : 0,
            'passed' => $verified,
            'detail' => $verified
                ? "Verificado em {$user->phone_verified_at->format('d/m/Y')}"
                : 'Telefone não verificado',
        ];
    }

    private function checkEmail(User $user): array
    {
        $verified = !empty($user->email_verified_at);

        return [
            'check'  => 'email',
            'label'  => 'Email verificado',
            'max'    => 10,
            'points' => $verified ? 10 : 0,
            'passed' => $verified,
            'detail' => $verified
                ? "Verificado em {$user->email_verified_at->format('d/m/Y')}"
                : 'Email não verificado',
        ];
    }

    /**
     * Determina status baseado no score
     */
    private function determineStatus(int $score): string
    {
        if ($score >= self::THRESHOLD_AUTO_APPROVE) {
            return 'auto_approved';
        }
        if ($score < self::THRESHOLD_AUTO_REJECT) {
            return 'auto_rejected';
        }
        return 'pending_review';
    }

    /**
     * Formatador de bytes legível
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Helper estático para uso simples noutros sítios
     */
    public static function for(User $user): array
    {
        return (new self())->analyze($user);
    }
}
