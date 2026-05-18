<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- ============ VERIFICAÇÃO DE TELEFONE ============ --}}
            @php $user = auth()->user(); @endphp
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" id="kyc-phone">
                <div class="max-w-xl">
                    <h2 class="text-lg font-semibold text-gray-900 mb-1">Verificação de Telefone</h2>
                    <p class="text-sm text-gray-600 mb-4">
                        O teu número de telefone é necessário para completar o KYC.
                        Ao submeteres, receberás um código de confirmação no teu email.
                    </p>

                    @if(session('success') && str_contains(session('success'), 'ódigo'))
                        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($user->phone_verified_at)
                        <div class="flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800 mb-4">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>Telefone <strong>{{ $user->phone_number }}</strong> verificado em {{ $user->phone_verified_at->format('d/m/Y') }}.</span>
                        </div>
                    @endif

                    {{-- Formulário do número --}}
                    <form method="POST" action="{{ route('verify.phone') }}" class="mb-4">
                        @csrf
                        <div class="flex gap-2">
                            <input type="tel"
                                   name="phone"
                                   value="{{ old('phone', $user->phone_number) }}"
                                   placeholder="+351 912 345 678"
                                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('phone') border-red-400 @enderror">
                            <button type="submit"
                                    class="px-4 py-2 bg-green-700 text-white text-sm font-semibold rounded-lg hover:bg-green-800 transition">
                                {{ $user->phone_number ? 'Reenviar Código' : 'Enviar Código' }}
                            </button>
                        </div>
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </form>

                    {{-- Formulário de OTP (aparece quando há número mas não está verificado ou houve envio recente) --}}
                    @if($user->phone_number && !$user->phone_verified_at)
                        <form method="POST" action="{{ route('verify.phone.submit') }}">
                            @csrf
                            <p class="text-xs text-gray-500 mb-2">Introduz o código de 6 dígitos enviado para o teu email:</p>
                            <div class="flex gap-2">
                                <input type="text"
                                       name="phone_otp"
                                       maxlength="6"
                                       inputmode="numeric"
                                       placeholder="• • • • • •"
                                       class="w-36 rounded-lg border border-gray-300 px-3 py-2 text-sm text-center tracking-widest font-mono focus:outline-none focus:ring-2 focus:ring-green-500 @error('phone_otp') border-red-400 @enderror">
                                <button type="submit"
                                        class="px-4 py-2 bg-gray-900 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 transition">
                                    Confirmar
                                </button>
                            </div>
                            @error('phone_otp')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </form>
                    @endif
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
