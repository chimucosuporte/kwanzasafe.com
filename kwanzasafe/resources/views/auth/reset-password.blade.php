<x-auth-layout pageTitle="Definir Nova Password">

    <div class="auth-card">

        {{-- ÍCONE --}}
        <div class="auth-icon">
            <svg width="32" height="32" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h1 class="auth-title">Nova password</h1>
        <p class="auth-subtitle">
            Define uma password nova e segura para a tua conta.
        </p>

        <form method="POST" action="{{ route('password.store') }}"
              x-data="{ showPwd: false, showPwd2: false, submitting: false }"
              @submit="submitting = true">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            {{-- EMAIL --}}
            <div class="auth-field">
                <label for="email" class="auth-label">Email</label>
                <div class="auth-input-wrap">
                    <svg class="auth-input-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $request->email) }}"
                        required
                        autofocus
                        autocomplete="username"
                        readonly
                        class="auth-input @error('email') error @enderror"
                        style="opacity: 0.7;">
                </div>
                @error('email')
                    <div class="auth-error">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round"/></svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- NOVA PASSWORD --}}
            <div class="auth-field">
                <label for="password" class="auth-label">Nova password (mínimo 8 caracteres)</label>
                <div class="auth-input-wrap">
                    <svg class="auth-input-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input
                        id="password"
                        :type="showPwd ? 'text' : 'password'"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="Define uma password forte"
                        class="auth-input @error('password') error @enderror"
                        style="padding-right: 44px;">
                    <button type="button" class="auth-toggle-pwd" @click="showPwd = !showPwd">
                        <svg x-show="!showPwd" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <svg x-show="showPwd" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" x-cloak><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
                @error('password')
                    <div class="auth-error">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round"/></svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- CONFIRMAR --}}
            <div class="auth-field">
                <label for="password_confirmation" class="auth-label">Confirmar nova password</label>
                <div class="auth-input-wrap">
                    <svg class="auth-input-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input
                        id="password_confirmation"
                        :type="showPwd2 ? 'text' : 'password'"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Repete a password"
                        class="auth-input @error('password_confirmation') error @enderror"
                        style="padding-right: 44px;">
                    <button type="button" class="auth-toggle-pwd" @click="showPwd2 = !showPwd2">
                        <svg x-show="!showPwd2" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <svg x-show="showPwd2" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" x-cloak><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
            </div>

            {{-- BOTÃO --}}
            <button type="submit" class="auth-btn" :disabled="submitting">
                <span x-show="!submitting">Redefinir password</span>
                <span x-show="submitting" x-cloak>A guardar...</span>
                <svg x-show="!submitting" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

        </form>

        <div class="auth-divider">ou</div>

        <div class="auth-links">
            <a href="{{ route('login') }}" class="auth-link">Voltar ao login</a>
        </div>

    </div>

</x-auth-layout>
