<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard admin (app) — estatísticas vivas, espelha o AdminController web.
 */
class DashboardController extends Controller
{
    private const ACTIVE = ['pending', 'negotiating', 'awaiting_payment', 'payment_received', 'processing', 'aoa_sent'];

    public function stats(Request $request): JsonResponse
    {
        $period = $request->query('period', 'today');
        $from = match ($period) {
            'week'  => now()->subDays(7),
            'today' => now()->startOfDay(),
            default => null,
        };

        $txQuery = Transaction::query();
        if ($from) $txQuery->where('created_at', '>=', $from);

        $volumeQuery = Transaction::where('status', 'completed');
        if ($from) $volumeQuery->where('created_at', '>=', $from);

        $volumeByCurrency = (clone $volumeQuery)
            ->select('currency_from', DB::raw('SUM(amount_sent) as total'))
            ->groupBy('currency_from')
            ->get()
            ->map(fn ($r) => ['currency' => $r->currency_from, 'total' => (string) $r->total])
            ->values();

        $stats = [
            'total_users'        => User::count(),
            'new_users_period'   => ($from ? User::where('created_at', '>=', $from)->count() : User::count()),
            'kyc_approved'       => User::whereNotNull('identity_verified_at')->count(),
            'kyc_pending'        => User::whereNotNull('identity_document_path')->whereNull('identity_verified_at')->count(),
            'tx_pending'         => (clone $txQuery)->whereIn('status', self::ACTIVE)->count(),
            'tx_completed'       => (clone $txQuery)->where('status', 'completed')->count(),
            'tx_cancelled'       => (clone $txQuery)->whereIn('status', ['cancelled', 'expired'])->count(),
            'volume_aoa'         => (string) (clone $volumeQuery)->sum('amount_received'),
            'total_fees'         => (string) (clone $volumeQuery)->sum('fee_amount'),
            'volume_by_currency' => $volumeByCurrency,
            'unread_chats'       => ChatMessage::whereHas('sender', fn ($q) => $q->where('is_admin', false))->where('is_read', false)->count(),
            'period'             => $period,
            'period_label'       => match ($period) { 'today' => 'Hoje', 'week' => 'Últimos 7 dias', default => 'Total acumulado' },
        ];

        return response()->json([
            'stats' => $stats,
            'chart' => $this->chartData(),
        ]);
    }

    /** Volume/contagem dos últimos 7 dias (transações concluídas). */
    private function chartData(): array
    {
        $rows = Transaction::select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount_received) as volume')
            )
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $row = $rows->get($date);
            $days[] = [
                'date'   => $date,
                'label'  => now()->subDays($i)->isoFormat('dd'),
                'volume' => (string) ($row->volume ?? 0),
                'count'  => (int) ($row->count ?? 0),
            ];
        }

        return $days;
    }
}
