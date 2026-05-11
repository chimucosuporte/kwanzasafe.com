<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\ChatMessage;
use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
        $period = $request->query('period', 'today'); // today, week, all
        $stats  = $this->buildStats($period);

        // Top 5 transações pendentes (preview no dashboard)
        $pendingTransactions = Transaction::with('user')
                                ->whereNotIn('status', ['cancelled', 'expired', 'completed'])
                                ->orderBy('updated_at', 'desc')
                                ->limit(5)
                                ->get();

        // Top 5 KYC pendentes
        $pendingKycUsers = User::whereNotNull('identity_document_path')
                                ->whereNull('identity_verified_at')
                                ->orderBy('updated_at', 'desc')
                                ->limit(5)
                                ->get();

        // Taxas
        $rates = ExchangeRate::orderBy('currency_from')->get();

        // Dados do gráfico (últimos 7 dias)
        $chartData = $this->buildChartData();

        return view('admin.dashboard', compact(
            'stats',
            'period',
            'pendingTransactions',
            'pendingKycUsers',
            'rates',
            'chartData'
        ));
    }

    /**
     * AJAX: devolve stats em JSON para auto-refresh
     */
    public function statsJson(Request $request)
    {
        $period = $request->query('period', 'today');

        return response()->json([
            'stats'     => $this->buildStats($period),
            'chartData' => $this->buildChartData(),
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Constrói o conjunto de estatísticas conforme o período escolhido.
     */
    private function buildStats(string $period): array
    {
        // Definir intervalo
        switch ($period) {
            case 'week':
                $from = now()->subDays(7);
                break;
            case 'today':
                $from = now()->startOfDay();
                break;
            default:
                $from = null; // todos os tempos
        }

        // ---- Utilizadores ----
        $totalUsersQuery = User::query();
        if ($from) $totalUsersQuery->where('created_at', '>=', $from);
        $totalUsers = $totalUsersQuery->count();

        $totalUsersAll  = User::count();
        $kycApprovedAll = User::whereNotNull('identity_verified_at')->count();
        $kycPendingAll  = User::whereNotNull('identity_document_path')
                                ->whereNull('identity_verified_at')->count();

        // ---- Transações ----
        $txAll = Transaction::query();
        if ($from) $txAll->where('created_at', '>=', $from);

        $txPending = (clone $txAll)
                        ->whereIn('status', ['pending', 'awaiting_payment', 'processing'])
                        ->count();
        $txCompleted = (clone $txAll)->where('status', 'completed')->count();
        $txCancelled = (clone $txAll)->whereIn('status', ['cancelled', 'expired'])->count();

        // ---- Volumes ----
        $volumeQuery = Transaction::where('status', 'completed');
        if ($from) $volumeQuery->where('created_at', '>=', $from);

        // Volume por moeda
        $volumeByCurrency = (clone $volumeQuery)
                                ->select('currency_from', DB::raw('SUM(amount_sent) as total'))
                                ->groupBy('currency_from')
                                ->get()
                                ->mapWithKeys(fn($r) => [$r->currency_from => (float) $r->total])
                                ->toArray();

        $totalReceivedAOA = (float) (clone $volumeQuery)->sum('amount_received');

        // ---- Chats ----
        $unreadChats = ChatMessage::whereHas('sender', fn($q) => $q->where('is_admin', false))
                                ->where('is_read', false)
                                ->count();

        return [
            // Utilizadores (sempre globais para visão geral)
            'total_users'         => $totalUsersAll,
            'new_users_period'    => $totalUsers,
            'kyc_approved'        => $kycApprovedAll,
            'kyc_pending'         => $kycPendingAll,

            // Transações no período
            'tx_pending'          => $txPending,
            'tx_completed'        => $txCompleted,
            'tx_cancelled'        => $txCancelled,

            // Volumes
            'volume_aoa'          => $totalReceivedAOA,
            'volume_by_currency'  => $volumeByCurrency,

            // Chat
            'unread_chats'        => $unreadChats,

            // Meta
            'period'              => $period,
            'period_label'        => $this->periodLabel($period),
        ];
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'Hoje',
            'week'  => 'Últimos 7 dias',
            default => 'Total acumulado',
        };
    }

    /**
     * Volume diário dos últimos 7 dias (gráfico).
     */
    private function buildChartData(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date  = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('D');

            $vol = (float) Transaction::whereDate('created_at', $date)
                            ->where('status', 'completed')
                            ->sum('amount_received');

            $count = Transaction::whereDate('created_at', $date)
                            ->where('status', 'completed')
                            ->count();

            $days[] = [
                'date'   => $date,
                'label'  => $label,
                'volume' => $vol,
                'count'  => $count,
            ];
        }
        return $days;
    }

    // ==================================================================
    // VISTA: TRANSAÇÕES (filtrada)
    // ==================================================================

    public function transactionsIndex(Request $request)
    {
        $status = $request->query('status', 'pending'); // pending, completed, cancelled, all
        $period = $request->query('period', 'all');     // today, week, all
        $search = $request->query('q', '');

        $query = Transaction::with('user')->orderBy('created_at', 'desc');

        // Filtro de estado
        if ($status === 'pending') {
            $query->whereIn('status', ['pending', 'awaiting_payment', 'processing']);
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->whereIn('status', ['cancelled', 'expired']);
        }

        // Filtro temporal
        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        // Busca textual
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('email', 'like', "%{$search}%")
                         ->orWhere('full_name', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        return view('admin.transactions.index', compact('transactions', 'status', 'period', 'search'));
    }

    // ==================================================================
    // VISTA: UTILIZADORES (lista global)
    // ==================================================================

    public function usersIndex(Request $request)
    {
        $kycFilter = $request->query('kyc', 'all'); // all, approved, pending, none
        $search    = $request->query('q', '');

        $query = User::orderBy('created_at', 'desc');

        if ($kycFilter === 'approved') {
            $query->whereNotNull('identity_verified_at');
        } elseif ($kycFilter === 'pending') {
            $query->whereNotNull('identity_document_path')->whereNull('identity_verified_at');
        } elseif ($kycFilter === 'none') {
            $query->whereNull('identity_document_path');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('bi_number', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users', 'kycFilter', 'search'));
    }

    // ==================================================================
    // VISTA: MENSAGENS POR LER
    // ==================================================================

    public function unreadMessages()
    {
        // Buscar transações com mensagens não lidas do cliente
        $transactionIds = ChatMessage::whereHas('sender', fn($q) => $q->where('is_admin', false))
                            ->where('is_read', false)
                            ->pluck('transaction_id')
                            ->unique()
                            ->values();

        $transactions = Transaction::with(['user'])
                            ->whereIn('id', $transactionIds)
                            ->withCount(['chatMessages as unread_count' => function ($q) {
                                $q->whereHas('sender', fn($qq) => $qq->where('is_admin', false))
                                  ->where('is_read', false);
                            }])
                            ->orderBy('updated_at', 'desc')
                            ->paginate(20);

        return view('admin.messages.unread', compact('transactions'));
    }

    // ==================================================================
    // SHOW DE TRANSAÇÃO INDIVIDUAL
    // ==================================================================

    public function show($id)
    {
        $transaction = Transaction::with('user')->findOrFail($id);

        $receipt = ChatMessage::where('transaction_id', $id)
                                ->where('message_type', 'document')
                                ->orderBy('created_at', 'desc')
                                ->first();

        return view('admin.show', compact('transaction', 'receipt'));
    }

    public function approve(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update(['status' => 'completed']);

        ChatMessage::create([
            'transaction_id' => $transaction->id,
            'sender_id'      => Auth::id(),
            'message_type'   => 'text',
            'message_text'   => '✓ A tua transação foi APROVADA. Os Kwanzas já foram libertados e devem aparecer em breve na tua conta.',
            'is_read'        => false,
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Transação #' . $transaction->reference_id . ' aprovada.');
    }

    public function sendChatMessage(Request $request, $id)
    {
        $request->validate([
            'message_text' => 'nullable|string|max:2000',
            'attachment'   => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ]);

        if (!$request->filled('message_text') && !$request->hasFile('attachment')) {
            return back()->withErrors(['message_text' => 'Escreve uma mensagem ou anexa um ficheiro.']);
        }

        $transaction = Transaction::findOrFail($id);

        $data = [
            'transaction_id' => $transaction->id,
            'sender_id'      => Auth::id(),
            'message_text'   => $request->message_text,
            'message_type'   => 'text',
            'is_read'        => false,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['file_path']    = $file->store('chat_attachments', 'public');
            $ext                  = strtolower($file->getClientOriginalExtension());
            $data['message_type'] = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'image' : 'document';
            if (!$request->filled('message_text')) {
                $data['message_text'] = $data['message_type'] === 'image' ? '📷 Imagem' : '📎 Documento';
            }
        }

        ChatMessage::create($data);

        return back()->with('success', 'Mensagem enviada.');
    }

    // ==================================================================
    // TAXAS
    // ==================================================================

    public function ratesIndex()
    {
        $rates = ExchangeRate::orderBy('currency_from')->get();
        return view('admin.rates.index', compact('rates'));
    }

    public function ratesEdit($id)
    {
        $rate = ExchangeRate::findOrFail($id);
        return view('admin.rates.edit', compact('rate'));
    }

    public function ratesUpdate(Request $request, $id)
    {
        $request->validate([
            'rate'      => 'required|numeric|min:0.01',
            'is_active' => 'required|boolean',
        ]);

        $rate = ExchangeRate::findOrFail($id);
        $rate->rate = $request->rate;
        $rate->is_active = $request->is_active;
        $rate->save();

        return redirect()->route('admin.rates.index')
                         ->with('success', "Taxa de {$rate->currency_from} atualizada.");
    }

    // ==================================================================
    // KYC
    // ==================================================================

    public function kycIndex()
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

    public function kycShow($userId)
    {
        $kycUser = User::findOrFail($userId);
        return view('admin.kyc.show', compact('kycUser'));
    }

    public function kycApprove(Request $request, $userId)
    {
        $kycUser = User::findOrFail($userId);

        if (!$kycUser->identity_document_path) {
            return redirect()->route('admin.kyc.show', $userId)
                             ->with('error', 'Utilizador não submeteu documento.');
        }

        $kycUser->update(['identity_verified_at' => now()]);

        return redirect()->route('admin.kyc.index')
                         ->with('success', "KYC de {$kycUser->email} aprovado.");
    }

    public function kycReject(Request $request, $userId)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $kycUser = User::findOrFail($userId);

        $kycUser->update([
            'identity_document_path' => null,
            'profile_photo_path'     => null,
        ]);

        return redirect()->route('admin.kyc.index')
                         ->with('success', "KYC de {$kycUser->email} rejeitado. Motivo: {$request->reason}");
    }
}
