<x-app-layout>

@push('head')
<title>Transações — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.tx-app { font-family:'DM Sans',sans-serif; min-height:100dvh; }
.tx-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.tx-back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.tx-back:hover { color:white; }
.tx-logo { height:28px; filter:brightness(0) invert(1); }
.tx-title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }

.tx-container { max-width:1400px; margin:0 auto; padding:1.5rem; }

/* Filters */
.tx-filters { background:white; border-radius:14px; padding:1.125rem 1.25rem; border:1px solid #e5e5e5; margin-bottom:1.25rem; display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end; }
.tx-filter-group { display:flex; flex-direction:column; gap:0.375rem; }
.tx-filter-label { font-size:0.6rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#737373; }
.tx-filter-pills { display:flex; gap:4px; flex-wrap:wrap; }
.tx-pill { padding:0.4rem 0.75rem; border-radius:8px; font-family:'Syne',sans-serif; font-size:0.7rem; font-weight:700; cursor:pointer; border:1px solid #e5e5e5; background:white; color:#737373; text-decoration:none; transition:all 0.15s; }
.tx-pill:hover { background:#fafafa; color:#000; }
.tx-pill.active { background:#000; color:white; border-color:#000; }

.tx-search { display:flex; flex:1; max-width:400px; }
.tx-search input { flex:1; padding:0.5rem 0.875rem; border:1px solid #e5e5e5; border-radius:8px 0 0 8px; outline:none; font-size:0.85rem; }
.tx-search input:focus { border-color:#009d44; }
.tx-search button { padding:0.5rem 0.875rem; background:#000; color:white; border:none; border-radius:0 8px 8px 0; cursor:pointer; font-family:'Syne',sans-serif; font-weight:700; font-size:0.75rem; }

.tx-results-count { font-size:0.75rem; color:#737373; padding:0.5rem 0; font-weight:600; }

/* Table */
.tx-table-wrap { background:white; border-radius:14px; overflow:hidden; border:1px solid #e5e5e5; box-shadow:0 1px 3px rgba(0,0,0,0.03); }
.tx-table { width:100%; font-size:0.875rem; border-collapse:collapse; }
.tx-table thead { background:#fafafa; }
.tx-table th { text-align:left; padding:0.75rem 1rem; font-size:0.6rem; font-weight:800; color:#737373; text-transform:uppercase; letter-spacing:0.1em; border-bottom:1px solid #e5e5e5; }
.tx-table tbody tr { border-bottom:1px solid #f5f5f5; transition:background 0.15s; }
.tx-table tbody tr:last-child { border-bottom:none; }
.tx-table tbody tr:hover { background:#fafafa; cursor:pointer; }
.tx-table td { padding:0.75rem 1rem; vertical-align:middle; }

.tx-ref { font-family:'JetBrains Mono',monospace; font-size:0.75rem; font-weight:700; color:#000; }
.tx-user-cell { display:flex; align-items:center; gap:0.5rem; }
.tx-user-avatar { width:28px; height:28px; border-radius:50%; background:#d1f2e0; color:#007a34; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:0.65rem; flex-shrink:0; }
.tx-user-name { font-weight:600; font-size:0.8rem; }
.tx-user-email { font-size:0.7rem; color:#a3a3a3; }
.tx-amount { font-family:'Syne',sans-serif; font-weight:800; }
.tx-aoa { font-size:0.75rem; color:#007a34; font-weight:600; }
.tx-status { display:inline-block; font-size:0.6rem; font-weight:800; padding:3px 8px; border-radius:20px; text-transform:uppercase; letter-spacing:0.05em; }
.tx-status.pending, .tx-status.negotiating { background:#fef3c7; color:#92400e; }
.tx-status.awaiting_payment, .tx-status.processing { background:#dbeafe; color:#1e40af; }
.tx-status.payment_received { background:#d1fae5; color:#065f46; }
.tx-status.aoa_sent { background:#ecfdf5; color:#064e3b; }
.tx-status.completed { background:#d1f2e0; color:#007a34; }
.tx-status.cancelled, .tx-status.expired { background:#fee2e2; color:#991b1b; }

.tx-empty { text-align:center; padding:4rem 1.5rem; color:#a3a3a3; }
.tx-empty__icon { font-size:3rem; margin-bottom:0.75rem; opacity:0.5; }
.tx-empty__text { font-family:'Syne',sans-serif; font-size:1rem; font-weight:800; }
.tx-empty__sub { font-size:0.85rem; margin-top:0.25rem; }

/* Pagination */
.tx-pagination { display:flex; justify-content:center; align-items:center; gap:0.5rem; padding:1.25rem; }
.tx-pagination a, .tx-pagination span { padding:0.4rem 0.75rem; border-radius:6px; font-size:0.75rem; font-weight:600; text-decoration:none; color:#737373; }
.tx-pagination a:hover { background:#fafafa; color:#000; }
.tx-pagination .active span { background:#000; color:white; }

@media(max-width:768px) {
    .tx-table th:nth-child(3), .tx-table td:nth-child(3),
    .tx-table th:nth-child(5), .tx-table td:nth-child(5) { display:none; }
    .tx-filters { flex-direction:column; align-items:stretch; }
    .tx-search { max-width:100%; }
}
</style>
@endpush

<div class="tx-app">

<header class="tx-topbar">
    <a href="{{ route('admin.dashboard') }}" class="tx-back">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Painel
    </a>
    <div style="display:flex;align-items:center;gap:0.625rem;">
        <img src="{{ asset('assets/images/logos/logo1.png') }}" alt="KwanzaSafe" class="tx-logo">
        <span class="tx-title">Transações</span>
    </div>
    <div style="font-size:0.7rem;color:#a3a3a3;">{{ $transactions->total() }} resultados</div>
</header>

<div class="tx-container">

    {{-- FILTROS --}}
    <form method="GET" class="tx-filters">
        <div class="tx-filter-group">
            <span class="tx-filter-label">Estado</span>
            <div class="tx-filter-pills">
                <a href="?{{ http_build_query(['status' => 'pending', 'period' => $period, 'q' => $search]) }}" class="tx-pill {{ $status === 'pending' ? 'active' : '' }}">Pendentes</a>
                <a href="?{{ http_build_query(['status' => 'completed', 'period' => $period, 'q' => $search]) }}" class="tx-pill {{ $status === 'completed' ? 'active' : '' }}">Concluídas</a>
                <a href="?{{ http_build_query(['status' => 'cancelled', 'period' => $period, 'q' => $search]) }}" class="tx-pill {{ $status === 'cancelled' ? 'active' : '' }}">Canceladas</a>
                <a href="?{{ http_build_query(['status' => 'all', 'period' => $period, 'q' => $search]) }}" class="tx-pill {{ $status === 'all' ? 'active' : '' }}">Todas</a>
            </div>
        </div>

        <div class="tx-filter-group">
            <span class="tx-filter-label">Período</span>
            <div class="tx-filter-pills">
                <a href="?{{ http_build_query(['period' => 'today', 'status' => $status, 'q' => $search]) }}" class="tx-pill {{ $period === 'today' ? 'active' : '' }}">Hoje</a>
                <a href="?{{ http_build_query(['period' => 'week', 'status' => $status, 'q' => $search]) }}" class="tx-pill {{ $period === 'week' ? 'active' : '' }}">7 dias</a>
                <a href="?{{ http_build_query(['period' => 'all', 'status' => $status, 'q' => $search]) }}" class="tx-pill {{ $period === 'all' ? 'active' : '' }}">Tudo</a>
            </div>
        </div>

        <div class="tx-search">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="period" value="{{ $period }}">
            <input type="text" name="q" placeholder="Procurar por #ref, email, nome..." value="{{ $search }}">
            <button type="submit">Buscar</button>
        </div>
    </form>

    @if($transactions->isEmpty())
        <div class="tx-table-wrap">
            <div class="tx-empty">
                <div class="tx-empty__icon">📋</div>
                <div class="tx-empty__text">Sem resultados</div>
                <div class="tx-empty__sub">Tenta ajustar os filtros ou a busca.</div>
            </div>
        </div>
    @else
        <div class="tx-table-wrap">
            <table class="tx-table">
                <thead>
                    <tr>
                        <th>Referência</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Estado</th>
                        <th>Valor Origem</th>
                        <th>Valor AOA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                        <tr onclick="window.location='{{ route('admin.transaction.show', $tx->id) }}'">
                            <td><span class="tx-ref">#{{ $tx->reference_id }}</span></td>
                            <td>
                                <div class="tx-user-cell">
                                    <div class="tx-user-avatar">{{ strtoupper(substr(optional($tx->user)->full_name ?? optional($tx->user)->email ?? '?', 0, 1)) }}</div>
                                    <div>
                                        <div class="tx-user-name">{{ Str::limit(optional($tx->user)->full_name ?? '—', 22) }}</div>
                                        <div class="tx-user-email">{{ Str::limit(optional($tx->user)->email ?? '—', 22) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:0.75rem;color:#737373;">
                                {{ $tx->created_at->format('d/m H:i') }}
                                <div style="font-size:0.65rem;opacity:0.7;">{{ $tx->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                @php
                                    $stLabels = [
                                        'pending'          => 'Pendente',
                                        'negotiating'      => 'Negociação',
                                        'awaiting_payment' => 'Aguarda Pag.',
                                        'payment_received' => 'Pag. Recebido',
                                        'processing'       => 'Em Análise',
                                        'aoa_sent'         => 'AOA Enviados',
                                        'completed'        => 'Concluída',
                                        'cancelled'        => 'Cancelada',
                                        'expired'          => 'Expirada',
                                    ];
                                @endphp
                                <span class="tx-status {{ $tx->status }}">{{ $stLabels[$tx->status] ?? $tx->status }}</span>
                            </td>
                            <td><span class="tx-amount">{{ number_format($tx->amount_sent, 2, ',', '.') }} {{ $tx->currency_from }}</span></td>
                            <td><span class="tx-aoa">{{ number_format($tx->amount_received, 0, ',', '.') }} Kz</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="tx-pagination">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

</div>

</x-app-layout>
