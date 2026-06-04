<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log #{{ $log->id }} | Audit Trail</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Syne', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* ===== Alinhamento à paleta da marca KwanzaSafe ===== */
        .bg-slate-900 { background-color:#000 !important; }
        .bg-emerald-600 { background-color:#009d44 !important; }
        .hover\:bg-emerald-700:hover { background-color:#007a34 !important; }
        .bg-emerald-100 { background-color:#d1f2e0 !important; }
        .bg-emerald-500\/20 { background-color:rgba(0,157,68,0.2) !important; }
        .text-emerald-300 { color:#34d399 !important; }
        .text-emerald-400 { color:#22c55e !important; }
        .text-emerald-700 { color:#007a34 !important; }
        .text-emerald-800 { color:#005a26 !important; }
        .hover\:text-emerald-900:hover { color:#005a26 !important; }
        .border-emerald-500 { border-color:#009d44 !important; }
        .focus\:border-emerald-500:focus { border-color:#009d44 !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

<header class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between sticky top-0 z-50 shadow-xl">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.audit.index') }}" class="text-slate-400 hover:text-white transition text-sm flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Audit Trail
        </a>
        <span class="text-slate-600">|</span>
        <h1 class="font-display font-black text-base tracking-tight">Log #{{ $log->id }}</h1>
    </div>
    @if($log->severity === 'critical')
        <span class="bg-red-500 text-white text-xs font-black px-3 py-1 rounded-full uppercase tracking-widest">🚨 Crítico</span>
    @elseif($log->severity === 'warning')
        <span class="bg-amber-500 text-white text-xs font-black px-3 py-1 rounded-full uppercase tracking-widest">⚠️ Aviso</span>
    @else
        <span class="bg-slate-700 text-slate-300 text-xs font-black px-3 py-1 rounded-full uppercase tracking-widest">ℹ️ Info</span>
    @endif
</header>

<div class="max-w-5xl mx-auto px-4 py-6">

    {{-- ===== EVENTO PRINCIPAL ===== --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-4">
            <div class="text-4xl">{{ $log->category_icon }}</div>
            <div class="flex-1">
                <div class="font-mono text-sm font-bold text-slate-900">{{ $log->action }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-widest font-bold mt-0.5">{{ $log->category }}</div>
            </div>
            <div class="text-right">
                <div class="font-mono text-sm font-bold text-slate-900">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                <div class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</div>
            </div>
        </div>

        <div class="p-6">
            <div class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Descrição</div>
            <p class="text-slate-900 text-base leading-relaxed">{{ $log->description }}</p>
        </div>
    </div>

    {{-- ===== CONTEXTO DE QUEM ===== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Utilizador</div>
            @if($log->user_id)
                <div class="space-y-2 text-sm">
                    <div><span class="text-slate-500">ID:</span> <span class="font-mono font-bold text-slate-900">#{{ $log->user_id }}</span></div>
                    <div><span class="text-slate-500">Email:</span> <span class="font-semibold text-slate-900">{{ $log->user_email }}</span></div>
                    <div><span class="text-slate-500">Papel:</span> <span class="font-bold text-emerald-700 uppercase text-xs">{{ $log->user_role ?? '—' }}</span></div>
                </div>
                <a href="{{ route('admin.audit.user', $log->user_id) }}" class="inline-block mt-4 text-xs font-black text-emerald-700 hover:text-emerald-900">Ver histórico completo deste user →</a>
            @else
                <div class="text-sm text-slate-400 italic">Ação anónima (sem utilizador autenticado)</div>
                @if($log->user_email)
                    <div class="text-xs text-slate-500 mt-2">Email tentado: <span class="font-mono font-bold">{{ $log->user_email }}</span></div>
                @endif
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Alvo da Ação</div>
            @if($log->target_type)
                <div class="space-y-2 text-sm">
                    <div><span class="text-slate-500">Tipo:</span> <span class="font-mono text-xs text-slate-900">{{ class_basename($log->target_type) }}</span></div>
                    <div><span class="text-slate-500">ID:</span> <span class="font-mono font-bold text-slate-900">#{{ $log->target_id }}</span></div>
                    @if($log->target_reference)
                        <div><span class="text-slate-500">Referência:</span> <span class="font-mono font-bold text-emerald-700">{{ $log->target_reference }}</span></div>
                    @endif
                </div>
            @else
                <div class="text-sm text-slate-400 italic">Sem alvo específico</div>
            @endif
        </div>
    </div>

    {{-- ===== CONTEXTO TÉCNICO ===== --}}
    <div class="bg-slate-900 text-slate-100 rounded-2xl shadow-xl p-5 mb-6">
        <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Contexto Forense</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-mono text-xs">
            <div>
                <div class="text-slate-500 mb-1">IP Address</div>
                <div class="text-emerald-400 font-bold">{{ $log->ip_address ?? '—' }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Session ID</div>
                <div class="text-slate-300 truncate" title="{{ $log->session_id }}">{{ $log->session_id ? substr($log->session_id, 0, 20).'...' : '—' }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">HTTP Method</div>
                <div class="text-amber-400 font-bold">{{ $log->request_method ?? '—' }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">URL</div>
                <div class="text-slate-300 truncate" title="{{ $log->request_url }}">{{ $log->request_url ?? '—' }}</div>
            </div>
            <div class="md:col-span-2">
                <div class="text-slate-500 mb-1">User Agent</div>
                <div class="text-slate-400 text-[11px] leading-relaxed">{{ $log->user_agent ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- ===== METADATA JSON ===== --}}
    @if($log->metadata)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-slate-100">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Metadata (Payload Forense)</div>
        </div>
        <pre class="bg-slate-50 p-5 text-xs font-mono text-slate-700 overflow-x-auto leading-relaxed">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif

    {{-- ===== LOGS RELACIONADOS ===== --}}
    @if($relatedBySession->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-slate-100">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">🔗 Mesma Sessão ({{ $relatedBySession->count() }})</div>
        </div>
        <div class="divide-y divide-slate-50">
            @foreach($relatedBySession as $related)
            <a href="{{ route('admin.audit.show', $related->id) }}" class="block px-5 py-3 hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <span class="text-lg">{{ $related->category_icon }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="font-mono text-xs font-bold text-slate-900">{{ $related->action }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ $related->description }}</div>
                    </div>
                    <div class="font-mono text-xs text-slate-400">{{ $related->created_at->format('H:i:s') }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($relatedByIp->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">🌐 Mesmo IP nas Últimas 24h ({{ $relatedByIp->count() }})</div>
        </div>
        <div class="divide-y divide-slate-50">
            @foreach($relatedByIp as $related)
            <a href="{{ route('admin.audit.show', $related->id) }}" class="block px-5 py-3 hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <span class="text-lg">{{ $related->category_icon }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="font-mono text-xs font-bold text-slate-900">{{ $related->action }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ $related->user_email ?? 'Anónimo' }} — {{ $related->description }}</div>
                    </div>
                    <div class="font-mono text-xs text-slate-400">{{ $related->created_at->format('d/m H:i') }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
</body>
</html>
