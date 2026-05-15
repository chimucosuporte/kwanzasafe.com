<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\ChatMessage;
use App\Models\ExchangeRate;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !Auth::user()->is_admin) {
                abort(403, 'Acesso restrito a administradores.');
            }
            return $next($request);
        });
    }

    // ==================================================================
    // PAINEL PRINCIPAL — STATS VIVAS
    // ==================================================================

    public function index(Request $request)
    {
        $period = $request->query('period', 'today');
        $stats  = $this->buildStats($period);

        $pendingTransactions = Transaction::with('user')
            ->whereNotIn('status', ['cancelled', 'expired', 'completed'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        $pendingKycUsers = User::whereNotNull('identity_document_path')
            ->whereNull('identity_verified_at')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        $rates     = ExchangeRate::orderBy('currency_from')->get();
        $chartData = $this->buildChartData();

        return view('admin.dashboard', compact(
            'stats', 'period', 'pendingTransactions', 'pendingKycUsers', 'rates', 'chartData'
        ));
    }

    public function statsJson(Request $request)
    {
        $period = $request->query('period', 'today');

        return response()->json([
            'stats'     => $this->buildStats($period),
            'chartData' => $this->buildChartData(),
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    // ==================================================================
    // PRIVADOS — CÁLCULO DE ESTATÍSTICAS
    // ==================================================================

    public static function clearStatsCache(): void
    {
        Cache::forget('ks.admin.stats.today');
        Cache::forget('ks.admin.stats.week');
        Cache::forget('ks.admin.stats.all');
        Cache::forget('ks.admin.chart_data');
    }

    private function buildStats(string $period): array
    {
        $ttl = $period === 'today' ? 120 : 300;

        return Cache::remember("ks.admin.stats.{$period}", $ttl, function () use ($period) {
            $from = match ($period) {
                'week'  => now()->subDays(7),
                'today' => now()->startOfDay(),
                default => null,
            };

            $totalUsersQuery = User::query();
            if ($from) $totalUsersQuery->where('created_at', '>=', $from);
            $totalUsers = $totalUsersQuery->count();

            $txQuery = Transaction::query();
            if ($from) $txQuery->where('created_at', '>=', $from);

            $activeStatuses = ['pending', 'negotiating', 'awaiting_payment', 'payment_received', 'processing', 'aoa_sent'];

            $txPending   = (clone $txQuery)->whereIn('status', $activeStatuses)->count();
            $txCompleted = (clone $txQuery)->where('status', 'completed')->count();
            $txCancelled = (clone $txQuery)->whereIn('status', ['cancelled', 'expired'])->count();

            $volumeQuery = Transaction::where('status', 'completed');
            if ($from) $volumeQuery->where('created_at', '>=', $from);

            $volumeByCurrency = (clone $volumeQuery)
                ->select('currency_from', DB::raw('SUM(amount_sent) as total'))
                ->groupBy('currency_from')
                ->get()
                ->mapWithKeys(fn($r) => [$r->currency_from => (float) $r->total])
                ->toArray();

            $totalReceivedAOA = (float) (clone $volumeQuery)->sum('amount_received');

            $unreadChats = ChatMessage::whereHas('sender', fn($q) => $q->where('is_admin', false))
                ->where('is_read', false)
                ->count();

            return [
                'total_users'        => User::count(),
                'new_users_period'   => $totalUsers,
                'kyc_approved'       => User::whereNotNull('identity_verified_at')->count(),
                'kyc_pending'        => User::whereNotNull('identity_document_path')->whereNull('identity_verified_at')->count(),
                'tx_pending'         => $txPending,
                'tx_completed'       => $txCompleted,
                'tx_cancelled'       => $txCancelled,
                'volume_aoa'         => $totalReceivedAOA,
                'volume_by_currency' => $volumeByCurrency,
                'unread_chats'       => $unreadChats,
                'period'             => $period,
                'period_label'       => $this->periodLabel($period),
            ];
        });
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'Hoje',
            'week'  => 'Últimos 7 dias',
            default => 'Total acumulado',
        };
    }

    private function buildChartData(): array
    {
        return Cache::remember('ks.admin.chart_data', 300, function () {
            $startDate = now()->subDays(6)->startOfDay();

            $rows = Transaction::select(
                    DB::raw('DATE(created_at) as day'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount_received) as volume')
                )
                ->where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->groupBy('day')
                ->get()
                ->keyBy('day');

            $days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $row  = $rows->get($date);
                $days[] = [
                    'date'   => $date,
                    'label'  => now()->subDays($i)->format('D'),
                    'volume' => $row ? (float) $row->volume : 0,
                    'count'  => $row ? (int) $row->count : 0,
                ];
            }

            return $days;
        });
    }
}
