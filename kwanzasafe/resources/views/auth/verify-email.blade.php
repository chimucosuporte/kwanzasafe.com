<x-auth-layout pageTitle="Verificar Email">

    {{--
    Esta página substitui o /verify-email padrão do Laravel Breeze.
    Em vez de pedir para clicar num link enviado por email,
    redirecionamos para o nosso sistema OTP de 6 dígitos.

    Mantida para compatibilidade caso utilizadores cliquem em links de emails antigos.
    --}}

    <script>
        // Redireciona automaticamente para o sistema OTP novo
        window.location.replace("{{ route('otp.email.verify') }}");
    </script>

    <div class="auth-card">

        {{-- ÍCONE --}}
        <div class="auth-icon">
            <svg width="32" height="32" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h1 class="auth-title">Verificar email</h1>
        <p class="auth-subtitle">
            Vais ser redirecionado para o sistema de verificação por código.
        </p>

        {{-- LOADING SPINNER --}}
        <div style="text-align: center; padding: 1rem 0;">
            <svg width="32" height="32" fill="none" stroke="var(--ks-green)" stroke-width="2.5" viewBox="0 0 24 24"
                 style="animation: spin 1s linear infinite;">
                <circle cx="12" cy="12" r="10" stroke-opacity="0.2"/>
                <path d="M22 12a10 10 0 01-10 10" stroke-linecap="round"/>
            </svg>
            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
            <p style="font-size: 0.85rem; color: var(--ks-gray-500); margin-top: 0.75rem;">
                A redirecionar...
            </p>
        </div>

        {{-- LINK MANUAL CASO REDIRECT FALHE --}}
        <a href="{{ route('otp.email.verify') }}" class="auth-btn" style="text-decoration:none;">
            Ir para verificação por código
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>

        <div class="auth-divider">ou</div>

        <div class="auth-links">
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="auth-link" style="background:none; border:none; cursor:pointer; font-family: inherit; font-size: 0.825rem; padding: 0;">
                    Sair da conta
                </button>
            </form>
        </div>

    </div>

</x-auth-layout>
