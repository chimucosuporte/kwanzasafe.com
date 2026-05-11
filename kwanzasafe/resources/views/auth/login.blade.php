<x-auth-layout pageTitle="Entrar">

    <div class="auth-card">

        {{-- ÍCONE --}}
        <div class="auth-icon">
            <svg width="32" height="32" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                <path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h1 class="auth-title">Bem-vindo de volta</h1>
        <p class="auth-subtitle">Entra na tua conta KwanzaSafe.</p>

        {{-- Mensagem de status (logout, etc.) --}}
        @if (session('status'))
            <div class="auth-msg success">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" x-data="{ showPwd: false, submitting: false }" @submit="submitting = true">
            @csrf

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
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="exemplo@email.com"
                        class="auth-input @error('email') error @enderror">
                </div>
                @error('email')
                    <div class="auth-error">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round"/>
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke-linecap="round"/>
                        </svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- PASSWORD --}}
            <div class="auth-field">
                <label for="password" class="auth-label">Password</label>
                <div class="auth-input-wrap">
                    <svg class="auth-input-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input
                        id="password"
                        :type="showPwd ? 'text' : 'password'"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="A tua password"
                        class="auth-input @error('password') error @enderror"
                        style="padding-right: 44px;">
                    <button type="button" class="auth-toggle-pwd" @click="showPwd = !showPwd" :aria-label="showPwd ? 'Esconder password' : 'Mostrar password'">
                        <svg x-show="!showPwd" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg x-show="showPwd" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" x-cloak>
                            <path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="auth-error">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round"/>
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke-linecap="round"/>
                        </svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- REMEMBER + FORGOT --}}
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                <label style="display:inline-flex; align-items:center; gap: 0.5rem; cursor:pointer; font-size: 0.825rem; color: var(--ks-gray-700);">
                    <input type="checkbox" name="remember" id="remember"
                           style="width: 16px; height: 16px; accent-color: var(--ks-green); cursor: pointer;">
                    <span>Lembrar-me</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-link" style="font-size: 0.825rem;">
                        Esqueci-me da password
                    </a>
                @endif
            </div>

            {{-- BOTÃO --}}
            <button type="submit" class="auth-btn" :disabled="submitting">
                <span x-show="!submitting">Entrar</span>
                <span x-show="submitting" x-cloak>A entrar...</span>
                <svg x-show="!submitting" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

        </form>

        <div class="auth-divider">ou</div>

        <div class="auth-links">
            Não tens conta?
            <a href="{{ route('register') }}" class="auth-link">Criar conta grátis</a>
        </div>

    </div>

</x-auth-layout>
