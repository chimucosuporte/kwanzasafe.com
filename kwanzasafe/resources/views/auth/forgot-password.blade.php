<x-auth-layout pageTitle="Recuperar Password">

    <div class="auth-card">

        {{-- ÍCONE --}}
        <div class="auth-icon">
            <svg width="32" height="32" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h1 class="auth-title">Esqueceste-te?</h1>
        <p class="auth-subtitle">
            Não há problema. Diz-nos o teu email e enviamos-te um link para redefinires a password.
        </p>

        {{-- Status (sucesso) --}}
        @if (session('status'))
            <div class="auth-msg success">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            {{-- EMAIL --}}
            <div class="auth-field">
                <label for="email" class="auth-label">Email da conta</label>
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
                        autocomplete="email"
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

            {{-- INFO BOX --}}
            <div class="auth-msg info" style="font-size: 0.75rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 16v-4M12 8h.01" stroke-linecap="round"/>
                </svg>
                <span>O link expira em 60 minutos por segurança.</span>
            </div>

            {{-- BOTÃO --}}
            <button type="submit" class="auth-btn" :disabled="submitting">
                <span x-show="!submitting">Enviar link de recuperação</span>
                <span x-show="submitting" x-cloak>A enviar...</span>
                <svg x-show="!submitting" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

        </form>

        <div class="auth-divider">ou</div>

        <div class="auth-links">
            Lembraste-te?
            <a href="{{ route('login') }}" class="auth-link">Voltar ao login</a>
        </div>

    </div>

</x-auth-layout>
