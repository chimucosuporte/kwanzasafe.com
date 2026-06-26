<x-app-layout>

@push('head')
<title>Editar Taxa — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.re-app { font-family:'DM Sans',sans-serif; min-height:100dvh; }
.re-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.re-back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.re-back:hover { color:white; }
.re-logo { height:28px; }
.re-title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }
.re-main { max-width:460px; margin:0 auto; padding:2rem 1.5rem; }
.re-card { background:white; border:1px solid #e5e5e5; border-radius:20px; padding:1.75rem; box-shadow:0 4px 24px rgba(0,0,0,0.06); }
.re-card__icon { width:56px; height:56px; border-radius:16px; background:linear-gradient(135deg,#009d44,#007a34); color:white; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; box-shadow:0 6px 18px rgba(0,157,68,0.3); }
.re-card__title { font-family:'Syne',sans-serif; font-weight:800; font-size:1.3rem; }
.re-card__sub { font-size:0.85rem; color:#737373; margin-top:2px; margin-bottom:1.5rem; }
.re-card__sub strong { color:#009d44; }
.re-field { margin-bottom:1.125rem; }
.re-label { display:block; font-size:0.7rem; font-weight:700; color:#404040; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.5rem; }
.re-input { width:100%; padding:14px 16px; border:2px solid #e5e5e5; border-radius:12px; font-family:'Syne',sans-serif; font-weight:800; font-size:1.4rem; color:#000; background:#fafafa; outline:none; transition:all 0.2s; }
.re-input:focus { border-color:#009d44; background:white; box-shadow:0 0 0 4px rgba(0,157,68,0.1); }
.re-select { width:100%; padding:12px 16px; border:2px solid #e5e5e5; border-radius:12px; font-family:'DM Sans',sans-serif; font-weight:600; font-size:0.95rem; color:#404040; background:#fafafa; outline:none; cursor:pointer; }
.re-select:focus { border-color:#009d44; background:white; }
.re-err { font-size:0.75rem; color:#dc2626; margin-top:0.375rem; font-weight:500; }
.re-actions { display:flex; gap:0.625rem; margin-top:1.5rem; }
.re-btn { flex:1; text-align:center; padding:0.875rem; border-radius:12px; border:none; font-family:'Syne',sans-serif; font-weight:800; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em; cursor:pointer; text-decoration:none; transition:all 0.2s; }
.re-btn.cancel { background:#f5f5f5; color:#737373; display:flex; align-items:center; justify-content:center; }
.re-btn.cancel:hover { background:#e5e5e5; color:#000; }
.re-btn.save { background:#009d44; color:white; box-shadow:0 4px 12px rgba(0,157,68,0.25); }
.re-btn.save:hover { background:#007a34; transform:translateY(-1px); }
</style>
@endpush

<div class="re-app">

<header class="re-topbar">
    <a href="{{ route('admin.rates.index') }}" class="re-back">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Taxas
    </a>
    <div style="display:flex;align-items:center;gap:0.625rem;">
        <img src="{{ asset('assets/images/logos/logo-icone.png') }}" alt="KwanzaSafe" class="re-logo">
        <span class="re-title">Editar Taxa</span>
    </div>
    <div style="width:48px;"></div>
</header>

<main class="re-main">
    <div class="re-card">
        <div class="re-card__icon">
            <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="re-card__title">Ajustar Taxa</div>
        <div class="re-card__sub">Par <strong>{{ $rate->currency_from }} → {{ $rate->currency_to }}</strong></div>

        <form action="{{ route('admin.rates.update', $rate->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="re-field">
                <label class="re-label">Valor em KZ (por 1 {{ $rate->currency_from }})</label>
                <input type="number" name="rate" value="{{ old('rate', $rate->rate) }}" step="0.01" min="0" required
                       class="re-input @error('rate') error @enderror">
                @error('rate') <div class="re-err">{{ $message }}</div> @enderror
            </div>

            <div class="re-field">
                <label class="re-label">Disponibilidade</label>
                <select name="is_active" class="re-select">
                    <option value="1" {{ $rate->is_active ? 'selected' : '' }}>Disponível para clientes</option>
                    <option value="0" {{ !$rate->is_active ? 'selected' : '' }}>Ocultar da calculadora</option>
                </select>
            </div>

            <div class="re-actions">
                <a href="{{ route('admin.rates.index') }}" class="re-btn cancel">Cancelar</a>
                <button type="submit" class="re-btn save">Guardar</button>
            </div>
        </form>
    </div>
</main>

</div>

</x-app-layout>
