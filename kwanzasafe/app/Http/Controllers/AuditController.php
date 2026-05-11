<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;

/**
 * AuditController — Interface de consulta dos logs para cumprimento AML.
 *
 * Permite ao admin filtrar por:
 * - Categoria (auth, kyc, transaction, admin, etc.)
 * - Severidade (info, warning, critical)
 * - Utilizador específico
 * - Intervalo de datas
 * - IP de origem
 */
class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->orderBy('created_at', 'desc');

        // ===== Filtros =====
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('ip')) {
            $query->where('ip_address', $request->ip);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('user_email', 'like', "%{$s}%")
                  ->orWhere('target_reference', 'like', "%{$s}%")
                  ->orWhere('action', 'like', "%{$s}%");
            });
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Estatísticas de topo (últimas 24h)
        $stats = [
            'total_24h'       => AuditLog::where('created_at', '>=', now()->subDay())->count(),
            'critical_24h'    => AuditLog::where('severity', 'critical')->where('created_at', '>=', now()->subDay())->count(),
            'failed_logins'   => AuditLog::where('action', 'auth.login_failed')->where('created_at', '>=', now()->subDay())->count(),
            'fraud_attempts'  => AuditLog::where('action', 'beneficiary.fraud_attempt')->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        // Categorias e severidades disponíveis (para dropdowns de filtro)
        $categories = ['auth', 'kyc', 'transaction', 'admin', 'profile', 'beneficiary'];
        $severities = ['info', 'warning', 'critical'];

        return view('admin.audit.index', compact('logs', 'stats', 'categories', 'severities'));
    }

    public function show($id)
    {
        $log = AuditLog::findOrFail($id);

        // Logs da mesma sessão (útil para investigações forenses)
        $relatedBySession = $log->session_id
            ? AuditLog::where('session_id', $log->session_id)
                      ->where('id', '!=', $log->id)
                      ->orderBy('created_at', 'desc')
                      ->limit(20)
                      ->get()
            : collect();

        // Outros logs do mesmo IP nas últimas 24h
        $relatedByIp = $log->ip_address
            ? AuditLog::where('ip_address', $log->ip_address)
                      ->where('id', '!=', $log->id)
                      ->where('created_at', '>=', now()->subDay())
                      ->orderBy('created_at', 'desc')
                      ->limit(20)
                      ->get()
            : collect();

        return view('admin.audit.show', compact('log', 'relatedBySession', 'relatedByIp'));
    }

    /**
     * Logs específicos de um utilizador (para investigação de caso).
     */
    public function user($userId)
    {
        $user = User::findOrFail($userId);

        $logs = AuditLog::where('user_id', $userId)
                        ->orderBy('created_at', 'desc')
                        ->paginate(50);

        return view('admin.audit.user', compact('user', 'logs'));
    }
}
