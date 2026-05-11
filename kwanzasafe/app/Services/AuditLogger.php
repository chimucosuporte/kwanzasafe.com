<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

/**
 * AuditLogger — Serviço central de registo de auditoria.
 *
 * REGRA DE ROBUSTEZ: Este serviço NUNCA pode quebrar a aplicação.
 * Se a tabela `audit_logs` não existir, ou a BD estiver em baixo, o logger
 * regista o erro no log padrão do Laravel mas deixa a aplicação continuar.
 *
 * USO:
 *   AuditLogger::log('kyc.approved', 'kyc', 'Identidade aprovada', [...]);
 *   AuditLogger::auth('login', 'Utilizador entrou');
 *   AuditLogger::kyc('approved', 'KYC aprovado', $user);
 *   AuditLogger::transaction('created', 'Nova transação', $tx);
 *   AuditLogger::admin('rate.updated', 'Taxa atualizada', $rate);
 */
class AuditLogger
{
    /**
     * Método central — nunca lança exceção para fora.
     */
    public static function log(
        string $action,
        string $category,
        string $description,
        array $options = []
    ): ?AuditLog {
        try {
            $target   = $options['target']    ?? null;
            $metadata = $options['metadata']  ?? null;
            $severity = $options['severity']  ?? 'info';

            $user = Auth::user();
            $userId    = $options['user_id']    ?? $user?->id;
            $userEmail = $options['user_email'] ?? $user?->email;
            $userRole  = $user?->is_admin ? 'admin' : 'client';

            $targetType      = null;
            $targetId        = null;
            $targetReference = null;

            if ($target instanceof Model) {
                $targetType = get_class($target);
                $targetId   = $target->getKey();
                $targetReference = match (true) {
                    isset($target->reference_id) => $target->reference_id,
                    isset($target->full_name)    => $target->full_name,
                    isset($target->bank_name)    => $target->bank_name . ' / ' . ($target->iban ?? ''),
                    isset($target->currency_from)=> $target->currency_from . ' → ' . ($target->currency_to ?? 'AOA'),
                    default                       => null,
                };
            } elseif (is_array($target)) {
                $targetType      = $target['type']      ?? null;
                $targetId        = $target['id']        ?? null;
                $targetReference = $target['reference'] ?? null;
            }

            $request = null;
            try {
                $request = request();
            } catch (\Throwable $e) {
                // CLI — sem request
            }

            return AuditLog::create([
                'user_id'         => $userId,
                'user_email'      => $userEmail,
                'user_role'       => $userRole,
                'action'          => $action,
                'category'        => $category,
                'severity'        => $severity,
                'target_type'     => $targetType,
                'target_id'       => $targetId,
                'target_reference'=> $targetReference,
                'description'     => $description,
                'metadata'        => $metadata,
                'ip_address'      => $request?->ip(),
                'user_agent'      => $request ? substr((string) $request->userAgent(), 0, 500) : null,
                'session_id'      => $request?->hasSession() ? $request->session()->getId() : null,
                'request_method'  => $request?->method(),
                'request_url'     => $request ? substr((string) $request->fullUrl(), 0, 500) : null,
                'created_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            // NUNCA deixar uma falha de auditoria quebrar a aplicação.
            // Regista o erro no log padrão do Laravel (storage/logs/laravel.log)
            Log::warning('[AuditLogger] Falha ao registar log de auditoria: ' . $e->getMessage(), [
                'action'   => $action,
                'category' => $category,
            ]);
            return null;
        }
    }

    // ================================================================
    // Helpers Especializados por Categoria
    // ================================================================

    public static function auth(string $action, string $description, array $options = []): ?AuditLog
    {
        return self::log("auth.{$action}", 'auth', $description, array_merge($options, [
            'severity' => $options['severity'] ?? ($action === 'login_failed' ? 'warning' : 'info'),
        ]));
    }

    public static function kyc(string $action, string $description, ?Model $target = null, array $metadata = []): ?AuditLog
    {
        $severity = in_array($action, ['approved', 'rejected']) ? 'critical' : 'info';
        return self::log("kyc.{$action}", 'kyc', $description, [
            'target'   => $target,
            'metadata' => $metadata,
            'severity' => $severity,
        ]);
    }

    public static function transaction(string $action, string $description, ?Model $target = null, array $metadata = []): ?AuditLog
    {
        $severity = in_array($action, ['approved', 'rejected', 'cancelled']) ? 'critical' : 'info';
        return self::log("transaction.{$action}", 'transaction', $description, [
            'target'   => $target,
            'metadata' => $metadata,
            'severity' => $severity,
        ]);
    }

    public static function admin(string $action, string $description, ?Model $target = null, array $metadata = []): ?AuditLog
    {
        return self::log("admin.{$action}", 'admin', $description, [
            'target'   => $target,
            'metadata' => $metadata,
            'severity' => 'critical',
        ]);
    }

    public static function profile(string $action, string $description, ?Model $target = null, array $metadata = []): ?AuditLog
    {
        return self::log("profile.{$action}", 'profile', $description, [
            'target'   => $target,
            'metadata' => $metadata,
        ]);
    }

    public static function beneficiary(string $action, string $description, ?Model $target = null, array $metadata = []): ?AuditLog
    {
        $severity = $action === 'fraud_attempt' ? 'critical' : 'info';
        return self::log("beneficiary.{$action}", 'beneficiary', $description, [
            'target'   => $target,
            'metadata' => $metadata,
            'severity' => $severity,
        ]);
    }
}