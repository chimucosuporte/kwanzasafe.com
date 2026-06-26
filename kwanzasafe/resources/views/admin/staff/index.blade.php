<x-app-layout>

@push('head')
<title>Funcionários de Suporte — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.sf-app { font-family:'DM Sans',sans-serif; min-height:100dvh; }
.sf-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.sf-back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.sf-back:hover { color:white; }
.sf-logo { height:28px; }
.sf-title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }
.sf-container { max-width:1000px; margin:0 auto; padding:1.5rem; }

.sf-flash { padding:0.875rem 1.125rem; border-radius:12px; font-size:0.85rem; font-weight:600; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem; }
.sf-flash.success { background:#d1f2e0; border:1px solid #a7f3d0; color:#007a34; }
.sf-flash.error { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; }

.sf-head { display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap; }
.sf-head h1 { font-family:'Syne',sans-serif; font-weight:800; font-size:1.35rem; margin:0; }
.sf-head p { font-size:0.8rem; color:#737373; margin:2px 0 0; }
.sf-addbtn { display:inline-flex; align-items:center; gap:0.5rem; background:#009d44; color:white; border:none; padding:0.75rem 1.25rem; border-radius:12px; font-family:'Syne',sans-serif; font-weight:800; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em; cursor:pointer; box-shadow:0 4px 12px rgba(0,157,68,0.2); }
.sf-addbtn:hover { background:#007a34; }

.sf-card { background:white; border:1px solid #e5e5e5; border-radius:16px; box-shadow:0 1px 3px rgba(0,0,0,0.03); margin-bottom:1.25rem; overflow:hidden; }
.sf-card__head { padding:1rem 1.25rem; border-bottom:1px solid #f0f0f0; font-family:'Syne',sans-serif; font-weight:800; font-size:0.9rem; display:flex; align-items:center; gap:0.5rem; }

/* Form criar */
.sf-form { padding:1.25rem; display:grid; gap:0.875rem; grid-template-columns:repeat(2,1fr); }
@media(max-width:640px){ .sf-form { grid-template-columns:1fr; } }
.sf-field { display:flex; flex-direction:column; }
.sf-field.full { grid-column:1 / -1; }
.sf-label { font-size:0.65rem; font-weight:700; color:#404040; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.375rem; }
.sf-input { width:100%; padding:11px 14px; border:2px solid #e5e5e5; border-radius:10px; font-family:'DM Sans',sans-serif; font-size:0.9rem; background:#fafafa; outline:none; transition:all 0.2s; }
.sf-input:focus { border-color:#009d44; background:white; box-shadow:0 0 0 4px rgba(0,157,68,0.1); }
.sf-err { font-size:0.72rem; color:#dc2626; margin-top:0.3rem; font-weight:500; }
.sf-submit { background:#000; color:white; border:none; padding:0.75rem 1.5rem; border-radius:10px; font-family:'Syne',sans-serif; font-weight:800; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.05em; cursor:pointer; }
.sf-submit:hover { background:#009d44; }

/* Lista staff */
.sf-row { display:flex; align-items:center; gap:0.875rem; padding:0.875rem 1.25rem; border-bottom:1px solid #f5f5f5; }
.sf-row:last-child { border-bottom:none; }
.sf-avatar { width:42px; height:42px; border-radius:50%; background:#d1f2e0; color:#007a34; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; flex-shrink:0; }
.sf-row.off .sf-avatar { background:#f5f5f5; color:#a3a3a3; }
.sf-info { flex:1; min-width:0; }
.sf-name { font-family:'Syne',sans-serif; font-weight:800; font-size:0.9rem; }
.sf-email { font-size:0.72rem; color:#737373; }
.sf-meta { display:flex; align-items:center; gap:0.5rem; margin-top:2px; }
.sf-badge { font-size:0.6rem; font-weight:800; padding:2px 8px; border-radius:20px; text-transform:uppercase; letter-spacing:0.05em; }
.sf-badge.on { background:#d1f2e0; color:#007a34; }
.sf-badge.off { background:#fee2e2; color:#991b1b; }
.sf-badge.tickets { background:#fef3c7; color:#92400e; }
.sf-badge.super { background:#000; color:#22c55e; }
.sf-actions { display:flex; gap:0.5rem; flex-shrink:0; }
.sf-btn { border:none; padding:0.45rem 0.75rem; border-radius:8px; font-family:'Syne',sans-serif; font-weight:800; font-size:0.68rem; text-transform:uppercase; letter-spacing:0.04em; cursor:pointer; }
.sf-btn.toggle { background:#f5f5f5; color:#525252; }
.sf-btn.toggle:hover { background:#e5e5e5; color:#000; }
.sf-btn.del { background:#fee2e2; color:#991b1b; }
.sf-btn.del:hover { background:#fca5a5; color:#7f1d1d; }
.sf-empty { padding:2.5rem 1.5rem; text-align:center; color:#a3a3a3; }
.sf-empty__icon { font-size:2.25rem; margin-bottom:0.5rem; }

[x-cloak] { display:none !important; }
</style>
@endpush

<div class="sf-app" x-data="{ showForm: {{ $errors->any() ? 'true' : 'false' }} }">

<x-admin-topbar title="Funcionários de Suporte" meta="{{ count($staff) }} agente(s)" />

<div class="sf-container">

    @if(session('success'))
        <div class="sf-flash success">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="sf-flash error">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="sf-head">
        <div>
            <h1>Funcionários de Suporte</h1>
            <p>Regista, activa/desactiva e elimina contas da equipa de suporte ao cliente.</p>
        </div>
        <button type="button" class="sf-addbtn" @click="showForm = !showForm">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round"/></svg>
            <span x-text="showForm ? 'Fechar' : 'Novo Funcionário'"></span>
        </button>
    </div>

    {{-- FORM CRIAR --}}
    <div class="sf-card" x-show="showForm" x-cloak x-transition>
        <div class="sf-card__head">
            <svg width="18" height="18" fill="none" stroke="#009d44" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7zM20 8v6M23 11h-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Registar Conta de Suporte
        </div>
        <form method="POST" action="{{ route('admin.staff.store') }}" class="sf-form">
            @csrf
            <div class="sf-field">
                <label class="sf-label">Nome completo</label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" required class="sf-input @error('full_name') error @enderror" placeholder="Nome do funcionário">
                @error('full_name') <div class="sf-err">{{ $message }}</div> @enderror
            </div>
            <div class="sf-field">
                <label class="sf-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="sf-input @error('email') error @enderror" placeholder="email@kwanzasafe.com">
                @error('email') <div class="sf-err">{{ $message }}</div> @enderror
            </div>
            <div class="sf-field">
                <label class="sf-label">Password</label>
                <input type="password" name="password" required class="sf-input @error('password') error @enderror" placeholder="Mínimo 8 caracteres">
                @error('password') <div class="sf-err">{{ $message }}</div> @enderror
            </div>
            <div class="sf-field">
                <label class="sf-label">Confirmar password</label>
                <input type="password" name="password_confirmation" required class="sf-input" placeholder="Repete a password">
            </div>
            <div class="sf-field full" style="flex-direction:row; justify-content:flex-end;">
                <button type="submit" class="sf-submit">Criar Conta</button>
            </div>
        </form>
    </div>

    {{-- LISTA STAFF --}}
    <div class="sf-card">
        <div class="sf-card__head">Equipa de Suporte</div>
        @forelse($staff as $member)
            <div class="sf-row {{ $member->is_active ? '' : 'off' }}">
                <div class="sf-avatar">{{ strtoupper(substr($member->full_name ?? $member->email, 0, 1)) }}</div>
                <div class="sf-info">
                    <div class="sf-name">{{ $member->full_name }}</div>
                    <div class="sf-email">{{ $member->email }}</div>
                    <div class="sf-meta">
                        @if($member->is_active)
                            <span class="sf-badge on">Activo</span>
                        @else
                            <span class="sf-badge off">Desactivado</span>
                        @endif
                        @if($member->open_tickets > 0)
                            <span class="sf-badge tickets">{{ $member->open_tickets }} tíquete(s)</span>
                        @endif
                    </div>
                </div>
                <div class="sf-actions">
                    <form method="POST" action="{{ route('admin.staff.toggle_active', $member->id) }}">
                        @csrf
                        <button type="submit" class="sf-btn toggle">{{ $member->is_active ? 'Desactivar' : 'Activar' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.staff.destroy', $member->id) }}"
                          onsubmit="return confirm('Eliminar a conta de {{ $member->full_name }}? Os tíquetes abertos voltam à fila.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="sf-btn del">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="sf-empty">
                <div class="sf-empty__icon">👥</div>
                <div style="font-family:'Syne',sans-serif;font-weight:800;color:#525252;">Sem funcionários de suporte</div>
                <div style="font-size:0.8rem;margin-top:3px;">Clica em "Novo Funcionário" para registar o primeiro.</div>
            </div>
        @endforelse
    </div>

    {{-- SUPER-ADMINS (leitura) --}}
    @if(count($superAdmins))
    <div class="sf-card">
        <div class="sf-card__head">Super-Administradores</div>
        @foreach($superAdmins as $admin)
            <div class="sf-row">
                <div class="sf-avatar" style="background:#000;color:#22c55e;">{{ strtoupper(substr($admin->full_name ?? $admin->email, 0, 1)) }}</div>
                <div class="sf-info">
                    <div class="sf-name">{{ $admin->full_name }}</div>
                    <div class="sf-email">{{ $admin->email }}</div>
                </div>
                <span class="sf-badge super">Super-Admin</span>
            </div>
        @endforeach
    </div>
    @endif

</div>

</div>

</x-app-layout>
