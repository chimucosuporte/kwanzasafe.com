<x-app-layout>

@push('head')
<title>Utilizadores — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.usr-app { font-family:'DM Sans',sans-serif; min-height:100dvh; }
.usr-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; }
.usr-back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.usr-back:hover { color:white; }
.usr-logo { height:28px; filter:brightness(0) invert(1); }
.usr-title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }

.usr-container { max-width:1400px; margin:0 auto; padding:1.5rem; }

.usr-filters { background:white; border-radius:14px; padding:1.125rem 1.25rem; border:1px solid #e5e5e5; margin-bottom:1.25rem; display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end; }
.usr-filter-group { display:flex; flex-direction:column; gap:0.375rem; }
.usr-filter-label { font-size:0.6rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#737373; }
.usr-filter-pills { display:flex; gap:4px; flex-wrap:wrap; }
.usr-pill { padding:0.4rem 0.75rem; border-radius:8px; font-family:'Syne',sans-serif; font-size:0.7rem; font-weight:700; cursor:pointer; border:1px solid #e5e5e5; background:white; color:#737373; text-decoration:none; }
.usr-pill:hover { background:#fafafa; color:#000; }
.usr-pill.active { background:#000; color:white; border-color:#000; }

.usr-search { display:flex; flex:1; max-width:400px; }
.usr-search input { flex:1; padding:0.5rem 0.875rem; border:1px solid #e5e5e5; border-radius:8px 0 0 8px; outline:none; font-size:0.85rem; }
.usr-search input:focus { border-color:#009d44; }
.usr-search button { padding:0.5rem 0.875rem; background:#000; color:white; border:none; border-radius:0 8px 8px 0; cursor:pointer; font-family:'Syne',sans-serif; font-weight:700; font-size:0.75rem; }

.usr-table-wrap { background:white; border-radius:14px; overflow:hidden; border:1px solid #e5e5e5; }
.usr-table { width:100%; font-size:0.875rem; border-collapse:collapse; }
.usr-table thead { background:#fafafa; }
.usr-table th { text-align:left; padding:0.75rem 1rem; font-size:0.6rem; font-weight:800; color:#737373; text-transform:uppercase; letter-spacing:0.1em; border-bottom:1px solid #e5e5e5; }
.usr-table tbody tr { border-bottom:1px solid #f5f5f5; }
.usr-table tbody tr:last-child { border-bottom:none; }
.usr-table tbody tr:hover { background:#fafafa; }
.usr-table td { padding:0.75rem 1rem; vertical-align:middle; }

.usr-cell { display:flex; align-items:center; gap:0.625rem; }
.usr-avatar { width:36px; height:36px; border-radius:50%; background:#d1f2e0; color:#007a34; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:0.75rem; overflow:hidden; flex-shrink:0; }
.usr-name { font-weight:600; font-size:0.85rem; }
.usr-email { font-size:0.7rem; color:#a3a3a3; }
.usr-bi { font-family:'JetBrains Mono',monospace; font-size:0.7rem; color:#525252; background:#f5f5f5; padding:2px 7px; border-radius:5px; }

.usr-tag { display:inline-flex; align-items:center; gap:3px; font-size:0.6rem; font-weight:800; padding:3px 8px; border-radius:20px; text-transform:uppercase; letter-spacing:0.05em; }
.usr-tag.approved { background:#d1f2e0; color:#007a34; }
.usr-tag.pending { background:#fef3c7; color:#92400e; }
.usr-tag.none { background:#f5f5f5; color:#737373; }
.usr-tag.admin { background:#000; color:white; }

.usr-action { color:#007a34; text-decoration:none; font-weight:700; font-size:0.75rem; }
.usr-action:hover { color:#009d44; }

.usr-empty { text-align:center; padding:4rem 1.5rem; color:#a3a3a3; }
.usr-empty__icon { font-size:3rem; margin-bottom:0.75rem; opacity:0.5; }
.usr-empty__text { font-family:'Syne',sans-serif; font-size:1rem; font-weight:800; }

.usr-pagination { display:flex; justify-content:center; align-items:center; gap:0.5rem; padding:1.25rem; }
.usr-pagination a, .usr-pagination span { padding:0.4rem 0.75rem; border-radius:6px; font-size:0.75rem; font-weight:600; text-decoration:none; color:#737373; }
.usr-pagination a:hover { background:#fafafa; color:#000; }
.usr-pagination .active span { background:#000; color:white; }

@media(max-width:768px) {
    .usr-table th:nth-child(2), .usr-table td:nth-child(2),
    .usr-table th:nth-child(4), .usr-table td:nth-child(4) { display:none; }
}
</style>
@endpush

<div class="usr-app">

<header class="usr-topbar">
    <a href="{{ route('admin.dashboard') }}" class="usr-back">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Painel
    </a>
    <div style="display:flex;align-items:center;gap:0.625rem;">
        <img src="{{ asset('assets/images/logos/logo1.png') }}" alt="KwanzaSafe" class="usr-logo">
        <span class="usr-title">Utilizadores</span>
    </div>
    <div style="font-size:0.7rem;color:#a3a3a3;">{{ $users->total() }} utilizadores</div>
</header>

<div class="usr-container">
    <form method="GET" class="usr-filters">
        <div class="usr-filter-group">
            <span class="usr-filter-label">Estado KYC</span>
            <div class="usr-filter-pills">
                <a href="?{{ http_build_query(['kyc' => 'all', 'q' => $search]) }}" class="usr-pill {{ $kycFilter === 'all' ? 'active' : '' }}">Todos</a>
                <a href="?{{ http_build_query(['kyc' => 'approved', 'q' => $search]) }}" class="usr-pill {{ $kycFilter === 'approved' ? 'active' : '' }}">Aprovados</a>
                <a href="?{{ http_build_query(['kyc' => 'pending', 'q' => $search]) }}" class="usr-pill {{ $kycFilter === 'pending' ? 'active' : '' }}">Pendentes</a>
                <a href="?{{ http_build_query(['kyc' => 'none', 'q' => $search]) }}" class="usr-pill {{ $kycFilter === 'none' ? 'active' : '' }}">Sem KYC</a>
            </div>
        </div>

        <div class="usr-search">
            <input type="hidden" name="kyc" value="{{ $kycFilter }}">
            <input type="text" name="q" placeholder="Procurar por nome, email, BI..." value="{{ $search }}">
            <button type="submit">Buscar</button>
        </div>
    </form>

    @if($users->isEmpty())
        <div class="usr-table-wrap">
            <div class="usr-empty">
                <div class="usr-empty__icon">👥</div>
                <div class="usr-empty__text">Sem resultados</div>
            </div>
        </div>
    @else
        <div class="usr-table-wrap">
            <table class="usr-table">
                <thead>
                    <tr>
                        <th>Utilizador</th>
                        <th>BI</th>
                        <th>Estado KYC</th>
                        <th>Registado</th>
                        <th style="text-align:right;">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td>
                                <div class="usr-cell">
                                    <div class="usr-avatar">
                                        @if(ks_file($u->profile_photo_path))
                                            <img src="{{ ks_file($u->profile_photo_path) }}" style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            {{ strtoupper(substr($u->full_name ?? $u->email, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="usr-name">
                                            {{ $u->full_name ?? '—' }}
                                            @if($u->is_admin)<span class="usr-tag admin" style="margin-left:0.25rem;">Admin</span>@endif
                                        </div>
                                        <div class="usr-email">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="usr-bi">{{ $u->bi_number ?? '—' }}</span></td>
                            <td>
                                @if($u->identity_verified_at)
                                    <span class="usr-tag approved">✓ Aprovado</span>
                                @elseif($u->identity_document_path)
                                    <span class="usr-tag pending">⏳ Pendente</span>
                                @else
                                    <span class="usr-tag none">— Sem KYC</span>
                                @endif
                            </td>
                            <td style="font-size:0.75rem;color:#737373;">
                                {{ $u->created_at->format('d/m/Y') }}
                                <div style="font-size:0.65rem;opacity:0.7;">{{ $u->created_at->diffForHumans() }}</div>
                            </td>
                            <td style="text-align:right;">
                                @if($u->identity_document_path)
                                    <a href="{{ route('admin.kyc.show', $u->id) }}" class="usr-action">Ver KYC →</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="usr-pagination">{{ $users->links() }}</div>
    @endif
</div>

</div>

</x-app-layout>
