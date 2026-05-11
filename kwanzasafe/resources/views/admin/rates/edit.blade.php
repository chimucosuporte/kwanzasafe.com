<x-app-layout>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6">
        <div class="max-w-md w-full bg-white rounded-[2.5rem] p-10 shadow-2xl border border-slate-100">
            <h3 class="text-2xl font-black text-slate-800 mb-2 tracking-tight">Ajustar Taxa</h3>
            <p class="text-slate-500 text-sm mb-8 italic">Par: {{ $rate->currency_from }} para {{ $rate->currency_to }}</p>

            <form action="{{ route('admin.rates.update', $rate->id) }}" method="POST" class="space-y-6">
                @csrf @method('PUT')
                
                <div>
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 block">Novo Valor em KZ (por 1 {{ $rate->currency_from }})</label>
                    <input type="number" name="rate" value="{{ $rate->rate }}" step="0.01" required class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 font-black text-2xl text-slate-800 focus:ring-4 focus:ring-red-500/20">
                </div>

                <div>
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 block">Disponibilidade</label>
                    <select name="is_active" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 font-bold text-slate-700 focus:ring-4 focus:ring-red-500/20">
                        <option value="1" {{ $rate->is_active ? 'selected' : '' }}>Disponível para Clientes</option>
                        <option value="0" {{ !$rate->is_active ? 'selected' : '' }}>Ocultar da Calculadora</option>
                    </select>
                </div>

                <div class="flex gap-4 pt-4">
                    <a href="{{ route('admin.rates.index') }}" class="flex-1 text-center py-4 text-slate-400 font-bold hover:text-slate-600 transition">Cancelar</a>
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-red-200 transition-all active:scale-95">GUARDAR</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>