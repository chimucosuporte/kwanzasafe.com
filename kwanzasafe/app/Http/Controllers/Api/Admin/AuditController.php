<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Consulta de logs de auditoria (AML) via app admin — leitura.
 * Espelha o AuditController web.
 */
class AuditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()->orderByDesc('created_at');

        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('severity')) $query->where('severity', $request->severity);
        if ($request->filled('user_id'))  $query->where('user_id', $request->user_id);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('user_email', 'like', "%{$s}%")
                  ->orWhere('target_reference', 'like', "%{$s}%")
                  ->orWhere('action', 'like', "%{$s}%");
            });
        }

        $page = $query->paginate(50);

        return response()->json([
            'data' => collect($page->items())->map(fn ($l) => [
                'id'          => $l->id,
                'action'      => $l->action,
                'category'    => $l->category,
                'severity'    => $l->severity,
                'description' => $l->description,
                'user_email'  => $l->user_email,
                'ip_address'  => $l->ip_address,
                'created_at'  => $l->created_at?->toIso8601String(),
            ]),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
            'stats' => [
                'total_24h'      => AuditLog::where('created_at', '>=', now()->subDay())->count(),
                'critical_24h'   => AuditLog::where('severity', 'critical')->where('created_at', '>=', now()->subDay())->count(),
                'failed_logins'  => AuditLog::where('action', 'auth.login_failed')->where('created_at', '>=', now()->subDay())->count(),
                'fraud_attempts' => AuditLog::where('action', 'beneficiary.fraud_attempt')->where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'categories' => ['auth', 'kyc', 'transaction', 'admin', 'profile', 'beneficiary'],
            'severities' => ['info', 'warning', 'critical'],
        ]);
    }
}
