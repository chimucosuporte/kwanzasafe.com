<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-red-800 leading-tight">
                Análise de Transação <span class="text-gray-600">#{{ $transaction->reference_id }}</span>
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700 underline">
                &larr; Voltar ao Painel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2">Detalhes da Operação</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded">
                            <p class="text-xs text-gray-500 uppercase">Cliente Enviou</p>
                            <p class="text-lg font-bold">{{ number_format($transaction->amount_sent, 2, ',', '.') }} {{ $transaction->currency_from }}</p>
                        </div>
                        <div class="bg-green-50 p-3 rounded border border-green-100">
                            <p class="text-xs text-green-700 uppercase font-bold">KwanzaSafe Paga</p>
                            <p class="text-xl font-bold text-green-900">{{ number_format($transaction->amount_received, 2, ',', '.') }} KZ</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-600 mb-4">Estado atual: 
                            <strong class="uppercase text-blue-600">{{ $transaction->status }}</strong>
                        </p>

                        <form action="{{ route('admin.transaction.approve', $transaction->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded transition text-lg flex justify-center items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Aprovar e Concluir Transação
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">Comprovativo Anexado</h3>
                    
                    @if($receipt)
                        <div class="bg-gray-100 p-2 rounded-lg border flex justify-center items-center min-h-[300px]">
                            @if(Str::endsWith(strtolower($receipt->file_path), ['.pdf']))
                                <a href="{{ asset('storage/' . $receipt->file_path) }}" target="_blank" class="text-blue-600 font-bold underline flex items-center gap-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Ver Documento PDF
                                </a>
                            @else
                                <img src="{{ asset('storage/' . $receipt->file_path) }}" alt="Comprovativo" class="max-w-full h-auto rounded shadow-sm border border-gray-200 cursor-pointer hover:opacity-90 transition" onclick="window.open(this.src, '_blank')">
                            @endif
                        </div>
                    @else
                        <div class="text-center py-10 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            O cliente ainda não anexou nenhum comprovativo.
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>