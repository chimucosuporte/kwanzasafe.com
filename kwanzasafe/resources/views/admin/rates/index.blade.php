<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-slate-800 uppercase tracking-tighter italic">
                Gestão de Câmbio
            </h2>
            <img src="{{ asset('assets/images/logos/logo.png') }}" class="h-8 w-auto">
        </div>
    </x-slot>

    <div class="py-12 px-6 max-w-7xl mx-auto animate-fade-in">
        
        @if(session('success'))
            <div class="mb-8 p-6 bg-emerald-50 border-l-8 border-ks-emerald rounded-2xl shadow-sm text-ks-emerald font-black uppercase text-xs tracking-widest">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-2xl rounded-[3rem] overflow-hidden border border-slate-100">
            <table class="w-full text-left">
                <thead class="bg-slate-900 text-white text-[10px] uppercase font-black tracking-[0.2em]">
                    <tr>
                        <th class="p-8">Par de Moedas</th>
                        <th class="p-8">Taxa Atual (1 Unid.)</th>
                        <th class="p-8 text-center">Estado</th>
                        <th class="p-8 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rates as $r)
                    <tr class="hover:bg-slate-50 transition duration-300">
                        <td class="p-8">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center font-black text-slate-400">
                                    {{ substr($r->currency_from, 0, 1) }}
                                </div>
                                <span class="font-black text-slate-800 text-lg uppercase tracking-tighter">
                                    {{ $r->currency_from }} &rarr; {{ $r->currency_to }}
                                </span>
                            </div>
                        </td>
                        <td class="p-8">
                            <span class="font-black text-ks-emerald text-2xl tracking-tighter">
                                {{ number_format($r->rate, 2, ',', '.') }} <small class="text-xs">KZ</small>
                            </span>
                        </td>
                        <td class="p-8 text-center">
                            @if($r->is_active)
                                <span class="px-5 py-2 rounded-full bg-emerald-100 text-ks-emerald text-[10px] font-black uppercase tracking-widest">Operacional</span>
                            @else
                                <span class="px-5 py-2 rounded-full bg-red-100 text-red-600 text-[10px] font-black uppercase tracking-widest">Pausado</span>
                            @endif
                        </td>
                        <td class="p-8 text-right">
                            <a href="{{ route('admin.rates.edit', $r->id) }}" 
                               class="bg-slate-900 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-ks-emerald transition shadow-lg shadow-slate-900/10">
                                Editar Taxa
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-12 text-center">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.5em]">KwanzaSafe Control Panel — Huambo 2026</p>
        </div>
    </div>
</x-app-layout>