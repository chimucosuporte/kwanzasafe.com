<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail | KwanzaSafe Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Syne', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 min-h-screen">

<header class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between sticky top-0 z-50 shadow-xl">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-white transition text-sm flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Painel
        </a>
        <span class="text-slate-600">|</span>
        <h1 class="font-display font-black text-base tracking-tight flex items-center gap-2">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Audit Trail
        </h1>
        <span class="bg-emerald-500/20 text-emerald-300 text-[10px] font-black px-2 py-1 rounded-full tracking-widest uppercase">AML Compliance</span>
    </div>
    <div class="text-xs text-slate-400 font-mono">{{ now()->format('d/m/Y H:i:s') }}</div>
</header>

<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- ===== STATS TOPO ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Eventos 24h</div>
            <div class="font-display text-2xl font-black text-slate-900">{{ number_format($stats['total_24h']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-red-100 p-4 shadow-sm">
            <div class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-1">Críticos 24h</div>
            <div class="font-display text-2xl font-black text-red-600">{{ number_format($stats['critical_24h']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-sm">
            <div class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-1">Logins Falhados</div>
            <div class="font-display text-2xl font-black text-amber-600">{{ number_format($stats['failed_logins']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-red-100 p-4 shadow-sm">
            <div class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-1">Fraudes 7d</div>
            <div class="font-display text-2xl font-black text-red-600">{{ number_format($stats['fraud_attempts']) }}</div>
        </div>
    </div>

    {{-- ===== FILTROS ===== --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Categoria</label>
                <select name="category" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
                    <option value="">Todas</option>
                    @foreach($categories as $c)
                        <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Severidade</label>
                <select name="severity" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
                    <option value="">Todas</option>
                    @foreach($severities as $s)
                        <option value="{{ $s }}" {{ request('severity') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">IP</label>
                <input type="text" name="ip" value="{{ request('ip') }}" placeholder="192.168.0.1" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 font-mono">
            </div>
            <div class="md:col-span-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Pesquisa</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Email, descrição, referência, ação..." class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Desde</label>
                <input type="datetime-local" name="from" value="{{ request('from') }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Até</label>
                <input type="datetime-local" name="to" value="{{ request('to') }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500">
            </div>
            <div class="col-span-2 md:col-span-2 flex items-end gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-display font-black text-sm px-4 py-2 rounded-xl uppercase tracking-widest transition">
                    Filtrar
                </button>
                <a href="{{ route('admin.audit.index') }}" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-display font-bold text-sm px-4 py-2 rounded-xl uppercase tracking-widest transition text-center">
                    Limpar
                </a>
            </div>
        </div>
    </form>

    {{-- ===== TABELA DE LOGS ===== --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <div class="text-xs font-black text-slate-500 uppercase tracking-widest">
                {{ number_format($logs->total()) }} eventos registados
            </div>
            <div class="text-xs text-slate-400 font-mono">Página {{ $logs->currentPage() }} de {{ $logs->lastPage() }}</div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="text-left px-5 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest">Timestamp</th>
                        <th class="text-left px-3 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest">Ação</th>
                        <th class="text-left px-3 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest">Utilizador</th>
                        <th class="text-left px-3 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest hidden lg:table-cell">Descrição</th>
                        <th class="text-left px-3 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest hidden md:table-cell">IP</th>
                        <th class="text-right px-5 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3 whitespace-nowrap">
                            <div class="font-mono text-xs text-slate-900">{{ $log->created_at->format('d/m H:i:s') }}</div>
                            <div class="font-mono text-[10px] text-slate-400">{{ $log->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2">
                                <span class="text-base leading-none">{{ $log->category_icon }}</span>
                                <div>
                                    <div class="font-mono text-xs font-bold text-slate-900">{{ $log->action }}</div>
                                    @if($log->severity === 'critical')
                                        <span class="inline-block bg-red-100 text-red-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-widest mt-0.5">Crítico</span>
                                    @elseif($log->severity === 'warning')
                                        <span class="inline-block bg-amber-100 text-amber-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-widest mt-0.5">Aviso</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            @if($log->user_email)
                                <div class="text-xs font-semibold text-slate-900 truncate max-w-[180px]">{{ $log->user_email }}</div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">{{ $log->user_role ?? '—' }}</div>
                            @else
                                <span class="text-xs text-slate-400 italic">Anónimo</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 hidden lg:table-cell max-w-[360px]">
                            <div class="text-xs text-slate-600 truncate" title="{{ $log->description }}">{{ $log->description }}</div>
                            @if($log->target_reference)
                                <div class="text-[10px] text-emerald-700 font-mono font-bold mt-0.5">→ {{ $log->target_reference }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 hidden md:table-cell">
                            <span class="font-mono text-[10px] text-slate-500 bg-slate-100 px-2 py-1 rounded">{{ $log->ip_address ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.audit.show', $log->id) }}" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold">Ver →</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-16 text-slate-400 text-sm">
                            <div class="text-4xl mb-3">🔍</div>
                            Nenhum evento encontrado com estes filtros.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    <div class="text-center mt-6 text-[10px] text-slate-400 font-mono uppercase tracking-widest">
        KwanzaSafe Audit System • Conformidade AML • Registos imutáveis
    </div>
</div>

</body>
</html>
