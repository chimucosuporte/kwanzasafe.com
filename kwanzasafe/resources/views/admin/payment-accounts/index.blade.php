<x-app-layout>

@push('head')
<title>Contas de Recepção — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.pa-app { font-family:'DM Sans',sans-serif; min-height:100dvh; }
.pa-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.pa-back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.pa-back:hover { color:white; }
.pa-logo { height:28px; }
.pa-title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }
.pa-container { max-width:840px; margin:0 auto; padding:1.5rem; }

.pa-flash { background:#d1f2e0;border:1px solid #a7f3d0;color:#007a34;padding:0.875rem 1.125rem;border-radius:12px;font-size:0.85rem;font-weight:600;margin-bottom:1.25rem;display:flex;align-items:center;gap:0.5rem; }
.pa-head h1 { font-family:'Syne',sans-serif;font-weight:800;font-size:1.35rem;margin:0; }
.pa-head p { font-size:0.8rem;color:#737373;margin:2px 0 1.25rem;max-width:60ch;line-height:1.5; }

.pa-card { background:white;border:1px solid #e5e5e5;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.03);margin-bottom:1.25rem;overflow:hidden; }
.pa-card__head { padding:0.875rem 1.25rem;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;justify-content:space-between;gap:0.75rem; }
.pa-cur { display:flex;align-items:center;gap:0.5rem;font-family:'Syne',sans-serif;font-weight:800;font-size:0.95rem; }
.pa-cur__tag { background:#f0faf4;color:#009d44;font-size:0.65rem;font-weight:800;padding:3px 9px;border-radius:20px;text-transform:uppercase;letter-spacing:0.05em; }
.pa-badge { font-size:0.6rem;font-weight:800;padding:3px 9px;border-radius:20px;text-transform:uppercase;letter-spacing:0.05em; }
.pa-badge.on { background:#d1f2e0;color:#007a34; }
.pa-badge.off { background:#fee2e2;color:#991b1b; }

.pa-form { padding:1.25rem;display:grid;gap:0.875rem;grid-template-columns:1fr 1fr; }
@media(max-width:600px){ .pa-form { grid-template-columns:1fr; } }
.pa-field { display:flex;flex-direction:column; }
.pa-field.full { grid-column:1/-1; }
.pa-label { font-size:0.65rem;font-weight:700;color:#404040;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.375rem; }
.pa-input,.pa-select,.pa-textarea { width:100%;padding:11px 14px;border:2px solid #e5e5e5;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:0.9rem;background:#fafafa;outline:none;transition:all 0.2s; }
.pa-input:focus,.pa-select:focus,.pa-textarea:focus { border-color:#009d44;background:white;box-shadow:0 0 0 4px rgba(0,157,68,0.1); }
.pa-textarea { resize:vertical;min-height:60px; }
.pa-mono { font-family:'JetBrains Mono',monospace; }
.pa-err { font-size:0.72rem;color:#dc2626;margin-top:0.3rem;font-weight:500; }
.pa-check { display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;font-weight:600;color:#404040; }
.pa-actions { grid-column:1/-1;display:flex;gap:0.5rem;justify-content:flex-end; }
.pa-btn { border:none;padding:0.7rem 1.25rem;border-radius:10px;font-family:'Syne',sans-serif;font-weight:800;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;cursor:pointer; }
.pa-btn.save { background:#009d44;color:white; }
.pa-btn.save:hover { background:#007a34; }
.pa-btn.del { background:#fee2e2;color:#991b1b; }
.pa-btn.del:hover { background:#fca5a5;color:#7f1d1d; }
.pa-empty { padding:2rem;text-align:center;color:#a3a3a3;font-size:0.85rem; }
.pa-add { background:white;border:2px dashed #d4d4d4;border-radius:16px; }
.pa-add .pa-card__head { border-bottom:1px solid #f0f0f0; }
</style>
@endpush

<div class="pa-app">

<x-admin-topbar title="Contas de Recepção" meta="{{ count($accounts) }} conta(s)" />

<div class="pa-container">

    @if(session('success'))
        <div class="pa-flash">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="pa-head">
        <h1>Contas de Recepção</h1>
        <p>Define, por moeda, para onde o cliente envia o pagamento. Estes dados aparecem automaticamente na Sala de Transação conforme a moeda. Ex.: EUR → IBAN, BRL → chave Pix, USDC → carteira + rede.</p>
    </div>

    {{-- CONTAS EXISTENTES --}}
    @forelse($accounts as $a)
        <div class="pa-card">
            <div class="pa-card__head">
                <div class="pa-cur"><span class="pa-cur__tag">{{ $a->currency }}</span> {{ $a->holder }}</div>
                <span class="pa-badge {{ $a->is_active ? 'on' : 'off' }}">{{ $a->is_active ? 'Activa' : 'Inactiva' }}</span>
            </div>
            <form method="POST" action="{{ route('admin.payment_accounts.update', $a->id) }}" class="pa-form">
                @csrf @method('PUT')
                <input type="hidden" name="currency" value="{{ $a->currency }}">
                <div class="pa-field">
                    <label class="pa-label">Titular</label>
                    <input type="text" name="holder" value="{{ old('holder', $a->holder) }}" required class="pa-input">
                </div>
                <div class="pa-field">
                    <label class="pa-label">Rede / BIC (opcional)</label>
                    <input type="text" name="network" value="{{ old('network', $a->network) }}" placeholder="TRC-20 · ERC-20 · BIC…" class="pa-input">
                </div>
                <div class="pa-field full">
                    <label class="pa-label">IBAN / Conta / Carteira</label>
                    <input type="text" name="identifier" value="{{ old('identifier', $a->identifier) }}" required class="pa-input pa-mono">
                </div>
                <div class="pa-field full">
                    <label class="pa-label">Instruções (opcional)</label>
                    <textarea name="instructions" class="pa-textarea">{{ old('instructions', $a->instructions) }}</textarea>
                </div>
                <div class="pa-field full">
                    <label class="pa-check"><input type="checkbox" name="is_active" value="1" {{ $a->is_active ? 'checked' : '' }} style="width:16px;height:16px;accent-color:#009d44;"> Activa (visível ao cliente)</label>
                </div>
                <div class="pa-actions">
                    <button type="submit" class="pa-btn save">Guardar</button>
                </div>
            </form>
            <form method="POST" action="{{ route('admin.payment_accounts.destroy', $a->id) }}" style="padding:0 1.25rem 1.25rem;text-align:right;"
                  onsubmit="return confirm('Eliminar a conta de recepção {{ $a->currency }}?');">
                @csrf @method('DELETE')
                <button type="submit" class="pa-btn del">Eliminar</button>
            </form>
        </div>
    @empty
        <div class="pa-card"><div class="pa-empty">Ainda não há contas de recepção. Adiciona a primeira abaixo.</div></div>
    @endforelse

    {{-- ADICIONAR --}}
    <div class="pa-add">
        <div class="pa-card__head"><div class="pa-cur" style="color:#525252;">➕ Adicionar conta de recepção</div></div>
        <form method="POST" action="{{ route('admin.payment_accounts.store') }}" class="pa-form">
            @csrf
            <div class="pa-field">
                <label class="pa-label">Moeda</label>
                @if($availableCurrencies->count())
                    <select name="currency" required class="pa-select">
                        <option value="">Escolhe…</option>
                        @foreach($availableCurrencies as $c)
                            <option value="{{ $c }}" {{ old('currency') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="currency" value="{{ old('currency') }}" required placeholder="EUR / BRL / USDC" class="pa-input" maxlength="10">
                @endif
                @error('currency') <div class="pa-err">{{ $message }}</div> @enderror
            </div>
            <div class="pa-field">
                <label class="pa-label">Titular</label>
                <input type="text" name="holder" value="{{ old('holder') }}" required placeholder="KwanzaSafe Lda" class="pa-input">
                @error('holder') <div class="pa-err">{{ $message }}</div> @enderror
            </div>
            <div class="pa-field full">
                <label class="pa-label">IBAN / Conta / Carteira</label>
                <input type="text" name="identifier" value="{{ old('identifier') }}" required class="pa-input pa-mono" placeholder="PT50… · chave Pix · 0x… / T…">
                @error('identifier') <div class="pa-err">{{ $message }}</div> @enderror
            </div>
            <div class="pa-field">
                <label class="pa-label">Rede / BIC (opcional)</label>
                <input type="text" name="network" value="{{ old('network') }}" placeholder="TRC-20 · ERC-20 · BIC…" class="pa-input">
            </div>
            <div class="pa-field">
                <label class="pa-check" style="margin-top:1.6rem;"><input type="checkbox" name="is_active" value="1" checked style="width:16px;height:16px;accent-color:#009d44;"> Activa</label>
            </div>
            <div class="pa-field full">
                <label class="pa-label">Instruções (opcional)</label>
                <textarea name="instructions" class="pa-textarea" placeholder="Ex.: coloca a referência da transação no descritivo do pagamento.">{{ old('instructions') }}</textarea>
            </div>
            <div class="pa-actions">
                <button type="submit" class="pa-btn save">Adicionar Conta</button>
            </div>
        </form>
    </div>

</div>

</div>

</x-app-layout>
