<x-app-layout>

@push('head')
<title>{{ $user->full_name ?? $user->email }} — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.ud-app { font-family:'DM Sans',sans-serif; min-height:100dvh; }
.ud-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; }
.ud-back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.ud-back:hover { color:white; }
.ud-logo { height:28px; }
.ud-title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }

.ud-container { max-width:1100px; margin:0 auto; padding:1.5rem; }
.ud-flash { padding:0.875rem 1.125rem; border-radius:12px; font-size:0.85rem; margin-bottom:1rem; }
.ud-flash.success { background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; }
.ud-flash.error   { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; }

.ud-grid { display:grid; grid-template-columns:320px 1fr; gap:1.25rem; }
@media(max-width:900px) { .ud-grid { grid-template-columns:1fr; } }

.ud-card { background:white; border-radius:16px; border:1px solid #e5e5e5; overflow:hidden; margin-bottom:1.25rem; }
.ud-card__head { padding:0.875rem 1.25rem; border-bottom:1px solid #f1f5f9; background:#fafafa; }
.ud-card__title { font-family:'Syne',sans-serif; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:#64748b; }

/* Profile card */
.ud-profile { padding:1.5rem; text-align:center; border-bottom:1px solid #f1f5f9; }
.ud-avatar { width:72px; height:72px; border-radius:50%; background:#d1f2e0; color:#007a34; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:1.5rem; margin:0 auto 0.875rem; overflow:hidden; border:3px solid #a7f3d0; }
.ud-avatar img { width:100%; height:100%; object-fit:cover; }
.ud-fullname { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:800; color:#0f172a; }
.ud-email    { font-size:0.8rem; color:#64748b; margin-top:2px; }
.ud-tags     { display:flex; gap:4px; justify-content:center; flex-wrap:wrap; margin-top:0.625rem; }
.ud-tag { font-size:0.6rem; font-weight:800; padding:3px 8px; border-radius:20px; text-transform:uppercase; letter-spacing:0.05em; }
.ud-tag.kyc-ok   { background:#d1fae5; color:#065f46; }
.ud-tag.kyc-pend { background:#fef3c7; color:#92400e; }
.ud-tag.kyc-no   { background:#f5f5f5; color:#737373; }
.ud-tag.admin    { background:#000; color:white; }
.ud-tag.client   { background:#e0f2fe; color:#0369a1; }

.ud-datarow { padding:0.75rem 1.25rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; font-size:0.8rem; gap:0.75rem; }
.ud-datarow:last-child { border-bottom:none; }
.ud-datarow__label { color:#94a3b8; font-weight:500; }
.ud-datarow__value { font-weight:700; text-align:right; }

/* Stats */
.ud-stats { display:grid; grid-template-columns:repeat(4, 1fr); gap:0.75rem; margin-bottom:1.25rem; }
@media(max-width:600px) { .ud-stats { grid-template-columns:repeat(2, 1fr); } }
.ud-stat { background:white; border-radius:14px; border:1px solid #e5e5e5; padding:1rem; text-align:center; }
.ud-stat__num   { font-family:'Syne',sans-serif; font-size:1.75rem; font-weight:800; color:#0f172a; }
.ud-stat__label { font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; margin-top:2px; }

/* Tx table */
.ud-tx-table { width:100%; font-size:0.8rem; border-collapse:collapse; }
.ud-tx-table th { text-align:left; padding:0.625rem 1rem; font-size:0.6rem; font-weight:800; color:#737373; text-transform:uppercase; letter-spacing:0.1em; border-bottom:1px solid #e5e5e5; background:#fafafa; }
.ud-tx-table td { padding:0.625rem 1rem; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
.ud-tx-table tbody tr:last-child td { border-bottom:none; }
.ud-tx-table tbody tr:hover { background:#fafafa; cursor:pointer; }
.ud-tx-ref { font-family:'JetBrains Mono',monospace; font-size:0.7rem; font-weight:700; }
.ud-tx-status { font-size:0.6rem; font-weight:800; padding:2px 7px; border-radius:20px; text-transform:uppercase; display:inline-block; }
.ud-tx-status.completed { background:#d1fae5; color:#065f46; }
.ud-tx-status.pending, .ud-tx-status.negotiating { background:#fef3c7; color:#92400e; }
.ud-tx-status.awaiting_payment { background:#dbeafe; color:#1e40af; }
.ud-tx-status.payment_received { background:#d1fae5; color:#059669; }
.ud-tx-status.aoa_sent { background:#ecfdf5; color:#064e3b; }
.ud-tx-status.cancelled, .ud-tx-status.expired { background:#fee2e2; color:#991b1b; }

/* Actions */
.ud-actions { padding:1.25rem; display:flex; flex-direction:column; gap:0.625rem; }
.ud-action-btn { width:100%; padding:0.75rem; border-radius:10px; font-family:'Syne',sans-serif; font-weight:800; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; cursor:pointer; border:none; }
.ud-action-btn.kyc     { background:#064e3b; color:white; text-decoration:none; display:block; text-align:center; }
.ud-action-btn.promote { background:#000; color:white; }
.ud-action-btn.demote  { background:white; border:1px solid #e5e5e5; color:#374151; }
</style>
@endpush

<div class="ud-app">

<header class="ud-topbar">
    <a href="{{ route('admin.users.index') }}" class="ud-back">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Utilizadores
    </a>
    <div style="display:flex;align-items:center;gap:0.625rem;">
        <img src="{{ asset('assets/images/logos/logo-icone.png') }}" alt="KwanzaSafe" class="ud-logo">
        <span class="ud-title">Perfil do Utilizador</span>
    </div>
    <div></div>
</header>

<div class="ud-container">

    @if(session('success'))
        <div class="ud-flash success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="ud-flash error">⚠ {{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <div class="ud-stats">
        <div class="ud-stat">
            <div class="ud-stat__num">{{ $txStats['total'] }}</div>
            <div class="ud-stat__label">Total</div>
        </div>
        <div class="ud-stat">
            <div class="ud-stat__num" style="color:#064e3b;">{{ $txStats['completed'] }}</div>
            <div class="ud-stat__label">Concluídas</div>
        </div>
        <div class="ud-stat">
            <div class="ud-stat__num" style="color:#1e40af;">{{ $txStats['active'] }}</div>
            <div class="ud-stat__label">Activas</div>
        </div>
        <div class="ud-stat">
            <div class="ud-stat__num" style="color:#dc2626;">{{ $txStats['cancelled'] }}</div>
            <div class="ud-stat__label">Canceladas</div>
        </div>
    </div>

    <div class="ud-grid">

        {{-- Coluna esquerda --}}
        <div>
            <div class="ud-card">
                <div class="ud-profile">
                    <div class="ud-avatar">
                        @if(ks_file($user->display_photo_path))
                            <img loading="lazy" decoding="async" src="{{ ks_file($user->display_photo_path) }}" alt="foto">
                        @else
                            {{ strtoupper(substr($user->full_name ?? $user->email, 0, 1)) }}
                        @endif
                    </div>
                    <div class="ud-fullname">{{ $user->full_name ?? '—' }}</div>
                    <div class="ud-email">{{ $user->email }}</div>
                    <div class="ud-tags" style="margin-top:0.625rem;">
                        @if($user->is_admin)
                            <span class="ud-tag admin">Admin</span>
                        @else
                            <span class="ud-tag client">Cliente</span>
                        @endif
                        @if($user->identity_verified_at)
                            <span class="ud-tag kyc-ok">✓ KYC</span>
                        @elseif($user->identity_document_path)
                            <span class="ud-tag kyc-pend">⏳ KYC Pendente</span>
                        @else
                            <span class="ud-tag kyc-no">Sem KYC</span>
                        @endif
                    </div>
                </div>

                <div class="ud-datarow">
                    <span class="ud-datarow__label">Telefone</span>
                    <span class="ud-datarow__value">{{ $user->phone_number ?? '—' }}</span>
                </div>
                <div class="ud-datarow">
                    <span class="ud-datarow__label">BI</span>
                    <span class="ud-datarow__value" style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;">{{ $user->bi_number ?? '—' }}</span>
                </div>
                <div class="ud-datarow">
                    <span class="ud-datarow__label">País</span>
                    <span class="ud-datarow__value">{{ $user->country ?? '—' }}</span>
                </div>
                <div class="ud-datarow">
                    <span class="ud-datarow__label">Registado</span>
                    <span class="ud-datarow__value">{{ $user->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="ud-datarow">
                    <span class="ud-datarow__label">Último login</span>
                    <span class="ud-datarow__value">{{ $user->last_login_at?->diffForHumans() ?? '—' }}</span>
                </div>
            </div>

            {{-- Ações --}}
            <div class="ud-card">
                <div class="ud-card__head"><div class="ud-card__title">Ações</div></div>
                <div class="ud-actions">
                    @if($user->identity_document_path)
                        <a href="{{ route('admin.kyc.show', $user->id) }}" class="ud-action-btn kyc">Ver KYC →</a>
                    @endif
                    <form method="POST" action="{{ route('admin.users.toggle_admin', $user->id) }}"
                          onsubmit="return confirm('{{ $user->is_admin ? 'Remover privilégios de admin?' : 'Promover a administrador?' }}');">
                        @csrf
                        <button type="submit" class="ud-action-btn {{ $user->is_admin ? 'demote' : 'promote' }}">
                            {{ $user->is_admin ? '↓ Remover Admin' : '↑ Promover a Admin' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Coluna direita: transações --}}
        <div>
            <div class="ud-card">
                <div class="ud-card__head"><div class="ud-card__title">Últimas Transações</div></div>
                @if($transactions->isEmpty())
                    <div style="padding:3rem;text-align:center;color:#94a3b8;font-size:0.85rem;">Sem transações</div>
                @else
                    <table class="ud-tx-table">
                        <thead>
                            <tr>
                                <th>Referência</th>
                                <th>Estado</th>
                                <th>Enviado</th>
                                <th>Recebido (Kz)</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $tx)
                            @php
                                $stLabels = [
                                    'pending'          => 'Pendente',
                                    'negotiating'      => 'Negociação',
                                    'awaiting_payment' => 'Aguarda Pag.',
                                    'payment_received' => 'Pag. Recebido',
                                    'aoa_sent'         => 'AOA Enviados',
                                    'completed'        => 'Concluída',
                                    'cancelled'        => 'Cancelada',
                                    'expired'          => 'Expirada',
                                ];
                            @endphp
                            <tr onclick="window.location='{{ route('admin.transaction.show', $tx->id) }}'">
                                <td><span class="ud-tx-ref">#{{ $tx->reference_id }}</span></td>
                                <td><span class="ud-tx-status {{ $tx->status }}">{{ $stLabels[$tx->status] ?? $tx->status }}</span></td>
                                <td style="font-weight:700;">{{ number_format($tx->amount_sent, 2, ',', '.') }} {{ $tx->currency_from }}</td>
                                <td style="color:#064e3b;font-weight:700;">{{ number_format($tx->amount_received, 0, ',', '.') }} Kz</td>
                                <td style="color:#94a3b8;font-size:0.75rem;">{{ $tx->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

    </div>
</div>

</div>

</x-app-layout>
