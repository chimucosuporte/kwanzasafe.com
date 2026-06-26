<x-app-layout>

@push('head')
<title>KYC — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.kyci-app { font-family:'DM Sans',sans-serif; min-height:100dvh; }
.kyci-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.kyci-back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.kyci-back:hover { color:white; }
.kyci-logo { height:28px; }
.kyci-title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }

.kyci-container { max-width:1200px; margin:0 auto; padding:1.5rem; }
.kyci-flash { padding:0.875rem 1.125rem; border-radius:10px; font-size:0.85rem; margin-bottom:1rem; }
.kyci-flash.success { background:#d1f2e0; border:1px solid #a7f3d0; color:#007a34; }

.kyci-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:1.25rem; }
.kyci-stat { background:white; border-radius:12px; padding:1rem 1.125rem; border:1px solid #e5e5e5; box-shadow:0 1px 3px rgba(0,0,0,0.03); }
.kyci-stat__label { font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#737373; }
.kyci-stat__value { font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:800; margin-top:4px; }
.kyci-stat__value.green { color:#007a34; }
.kyci-stat__value.amber { color:#f59e0b; }
.kyci-stat__value.red { color:#dc2626; }

.kyci-section { background:white; border-radius:14px; border:1px solid #e5e5e5; overflow:hidden; margin-bottom:1.25rem; }
.kyci-section__head { padding:1rem 1.25rem; border-bottom:1px solid #e5e5e5; background:#fafafa; display:flex; justify-content:space-between; align-items:center; }
.kyci-section__title { font-family:'Syne',sans-serif; font-size:0.875rem; font-weight:800; }

.kyci-row { padding:0.875rem 1.25rem; border-bottom:1px solid #f5f5f5; display:flex; align-items:center; gap:0.875rem; text-decoration:none; color:inherit; transition:background 0.15s; }
.kyci-row:last-child { border-bottom:none; }
.kyci-row:hover { background:#fafafa; }

.kyci-avatar { width:42px; height:42px; border-radius:50%; background:#d1f2e0; color:#007a34; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:0.85rem; overflow:hidden; flex-shrink:0; }
.kyci-info { flex:1; min-width:0; }
.kyci-name { font-family:'Syne',sans-serif; font-size:0.9rem; font-weight:800; }
.kyci-email { font-size:0.72rem; color:#737373; }
.kyci-time { font-size:0.65rem; color:#a3a3a3; margin-top:1px; }

/* ============ SCORE BADGE (NOVO) ============ */
.kyci-score-badge { display:inline-flex; align-items:center; gap:0.375rem; padding:0.375rem 0.75rem; border-radius:20px; font-family:'Syne',sans-serif; font-size:0.7rem; font-weight:800; flex-shrink:0; }
.kyci-score-badge.high { background:#d1f2e0; color:#007a34; }
.kyci-score-badge.mid { background:#fef3c7; color:#92400e; }
.kyci-score-badge.low { background:#fee2e2; color:#991b1b; }
.kyci-score-badge.none { background:#f5f5f5; color:#737373; }
.kyci-score-badge__icon { width:14px; height:14px; }

.kyci-cta { background:#000; color:white; padding:0.5rem 0.875rem; border-radius:10px; font-family:'Syne',sans-serif; font-size:0.7rem; font-weight:800; text-transform:uppercase; flex-shrink:0; text-decoration:none; }
.kyci-cta:hover { background:#009d44; }

.kyci-empty { padding:3rem 1.5rem; text-align:center; color:#a3a3a3; }
.kyci-empty__icon { font-size:2.5rem; margin-bottom:0.5rem; opacity:0.5; }
.kyci-empty__text { font-family:'Syne',sans-serif; font-size:0.95rem; font-weight:800; }
.kyci-empty__sub { font-size:0.8rem; margin-top:3px; }

@media(max-width:600px) {
    .kyci-cta { display:none; }
    .kyci-score-badge { font-size:0.65rem; padding:3px 7px; }
}
</style>
@endpush

<div class="kyci-app">

<x-admin-topbar title="Verificação KYC" meta="{{ $pendingUsers->count() }} pendentes" />

<div class="kyci-container">

    @if(session('success'))
        <div class="kyci-flash success">{{ session('success') }}</div>
    @endif

    {{-- Estatísticas KYC --}}
    @php
        $autoApproved = \App\Models\User::where('kyc_bot_status', 'auto_approved')->count();
        $autoRejected = \App\Models\User::where('kyc_bot_status', 'auto_rejected')->count();
        $needsReview = \App\Models\User::where('kyc_bot_status', 'pending_review')
                                       ->whereNull('identity_verified_at')->count();
    @endphp
    <div class="kyci-stats">
        <div class="kyci-stat">
            <div class="kyci-stat__label">🤖 Auto-Aprovados</div>
            <div class="kyci-stat__value green">{{ $autoApproved }}</div>
        </div>
        <div class="kyci-stat">
            <div class="kyci-stat__label">⏳ A Rever</div>
            <div class="kyci-stat__value amber">{{ $needsReview }}</div>
        </div>
        <div class="kyci-stat">
            <div class="kyci-stat__label">❌ Auto-Rejeitados</div>
            <div class="kyci-stat__value red">{{ $autoRejected }}</div>
        </div>
        <div class="kyci-stat">
            <div class="kyci-stat__label">✅ Total Aprovados</div>
            <div class="kyci-stat__value green">{{ $approvedUsers->count() }}+</div>
        </div>
    </div>

    {{-- Pendentes --}}
    <div class="kyci-section">
        <div class="kyci-section__head">
            <div class="kyci-section__title">📋 KYC Pendentes ({{ $pendingUsers->count() }})</div>
        </div>

        @if($pendingUsers->isEmpty())
            <div class="kyci-empty">
                <div class="kyci-empty__icon">✅</div>
                <div class="kyci-empty__text">Tudo em dia!</div>
                <div class="kyci-empty__sub">Não há documentos pendentes de revisão.</div>
            </div>
        @else
            @foreach($pendingUsers as $u)
                @php
                    $score = $u->kyc_score ?? 0;
                    $hasScore = !is_null($u->kyc_score);
                    $scoreClass = !$hasScore ? 'none' : ($score >= 80 ? 'high' : ($score >= 50 ? 'mid' : 'low'));
                @endphp
                <a href="{{ route('admin.kyc.show', $u->id) }}" class="kyci-row">
                    <div class="kyci-avatar">
                        @if(ks_file($u->profile_photo_path))
                            <img loading="lazy" decoding="async" src="{{ ks_file($u->profile_photo_path) }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr($u->full_name ?? $u->email, 0, 1)) }}
                        @endif
                    </div>
                    <div class="kyci-info">
                        <div class="kyci-name">{{ $u->full_name ?? '—' }}</div>
                        <div class="kyci-email">{{ Str::limit($u->email, 30) }}</div>
                        <div class="kyci-time">{{ $u->updated_at->diffForHumans() }}</div>
                    </div>
                    <div class="kyci-score-badge {{ $scoreClass }}">
                        <svg class="kyci-score-badge__icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        {{ $hasScore ? $score . '/100' : 'Sem análise' }}
                    </div>
                    <span class="kyci-cta">Rever →</span>
                </a>
            @endforeach
        @endif
    </div>

    {{-- Aprovados recentes --}}
    @if($approvedUsers->isNotEmpty())
    <div class="kyci-section">
        <div class="kyci-section__head">
            <div class="kyci-section__title">✅ Aprovados Recentes</div>
        </div>
        @foreach($approvedUsers as $u)
            <a href="{{ route('admin.kyc.show', $u->id) }}" class="kyci-row">
                <div class="kyci-avatar">
                    @if(ks_file($u->profile_photo_path))
                        <img loading="lazy" decoding="async" src="{{ ks_file($u->profile_photo_path) }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        {{ strtoupper(substr($u->full_name ?? $u->email, 0, 1)) }}
                    @endif
                </div>
                <div class="kyci-info">
                    <div class="kyci-name">{{ $u->full_name ?? '—' }}</div>
                    <div class="kyci-email">{{ Str::limit($u->email, 30) }}</div>
                    <div class="kyci-time">Verificado {{ $u->identity_verified_at->diffForHumans() }}</div>
                </div>
                @if($u->kyc_bot_status === 'auto_approved')
                    <span class="kyci-score-badge high">🤖 {{ $u->kyc_score }}/100</span>
                @endif
            </a>
        @endforeach
    </div>
    @endif

</div>

</div>

</x-app-layout>
