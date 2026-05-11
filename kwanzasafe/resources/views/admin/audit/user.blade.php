<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico: {{ $user->full_name ?? $user->email }} | Audit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Syne', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
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
        <h1 class="font-display font-black text-base tracking-tight">Histórico do Utilizador</h1>
    </div>
</header>

<div class="max-w-5xl mx-auto px-4 py-6">

    {{-- Cabeçalho do utilizador --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-6 flex items-center gap-5">
        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center font-display font-black text-2xl text-emerald-800 overflow-hidden flex-shrink-0">
            @if($user->profile_photo_path)
                <img src="{{ asset('storage/'.$user->profile_photo_path) }}" class="w-full h-full object-cover">
            @else
                {{ strtoupper(substr($user->full_name ?? $user->email, 0, 1)) }}
            @endif
        </div>
        <div class="flex-1">
            <div class="font-display font-black text-xl text-slate-900">{{ $user->full_name ?? $user->email }}</div>
            <div class="text-sm text-slate-500">{{ $user->email }}</div>
            <div class="flex gap-2 mt-2 flex-wrap">
                <span class="bg-slate-100 text-slate-700 text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-widest">ID #{{ $user->id }}</span>
                @if($user->is_admin)
                    <span class="bg-slate-900 text-white text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-widest">Admin</span>
                @endif
                @if($user->identity_verified_at)
                    <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-widest">KYC ✓</span>
                @endif
            </div>
        </div>
        <div class="text-right">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Eventos</div>
            <div class="font-display font-black text-3xl text-slate-900">{{ number_format($logs->total()) }}</div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 text-xs font-black text-slate-500 uppercase tracking-widest">
            Cronologia de Ações ({{ number_format($logs->total()) }} eventos)
        </div>

        <div class="divide-y divide-slate-50">
            @forelse($logs as $log)
            <a href="{{ route('admin.audit.show', $log->id) }}" class="block px-5 py-4 hover:bg-slate-50 transition">
                <div class="flex items-start gap-4">
                    <div class="text-2xl flex-shrink-0 mt-0.5">{{ $log->category_icon }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono text-xs font-black text-slate-900">{{ $log->action }}</span>
                            @if($log->severity === 'critical')
                                <span class="bg-red-100 text-red-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-widest">Crítico</span>
                            @elseif($log->severity === 'warning')
                                <span class="bg-amber-100 text-amber-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-widest">Aviso</span>
                            @endif
                        </div>
                        <div class="text-sm text-slate-700 mt-1">{{ $log->description }}</div>
                        <div class="flex items-center gap-3 mt-1.5 text-[10px] text-slate-400 font-mono">
                            <span>{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                            <span>•</span>
                            <span>{{ $log->ip_address ?? 'sem IP' }}</span>
                            @if($log->target_reference)
                                <span>•</span>
                                <span class="text-emerald-700 font-bold">{{ $log->target_reference }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div class="text-center py-16 text-slate-400 text-sm">
                Nenhum evento registado para este utilizador.
            </div>
            @endforelse
        </div>

        @if($logs->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
</body>
</html>
