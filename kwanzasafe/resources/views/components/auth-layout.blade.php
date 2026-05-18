{{--
|==========================================================================
| KwanzaSafe — Layout Partilhado de Páginas de Auth
|==========================================================================
| Usado por: forgot-password, reset-password, confirm-password, verify-email
|
| Variáveis esperadas:
|   $pageTitle (string) — Título da página
|
| Slots:
|   $slot — Conteúdo principal
|==========================================================================
--}}
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? 'KwanzaSafe' }} — KwanzaSafe</title>
    <meta name="description" content="KwanzaSafe — Carteira internacional para remessas seguras para Angola.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#009d44">

    {{-- FAVICON --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logos/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logos/logo1.png') }}">

    {{-- FONTES --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap" rel="stylesheet">

    {{-- TAILWIND + ALPINE --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --ks-green: #009d44;
            --ks-green-dark: #007a34;
            --ks-green-light: #d1f2e0;
            --ks-green-pale: #f0faf4;
            --ks-black: #000;
            --ks-white: #fff;
            --ks-gray-50: #fafafa;
            --ks-gray-100: #f5f5f5;
            --ks-gray-200: #e5e5e5;
            --ks-gray-300: #d4d4d4;
            --ks-gray-500: #737373;
            --ks-gray-700: #404040;
            --ks-gray-900: #171717;
            --ks-danger: #dc2626;
            --ks-danger-light: #fee2e2;
        }
        * { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            margin: 0;
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            background: linear-gradient(180deg, #f0faf4 0%, #fafafa 100%);
            color: var(--ks-black);
            min-height: 100dvh;
            -webkit-font-smoothing: antialiased;
        }

        /* ============ LAYOUT ============ */
        .auth-page {
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
        }

        /* ============ HEADER ============ */
        .auth-header {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--ks-gray-200);
        }
        .auth-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .auth-logo img { height: 32px; width: auto; }
        .auth-logo-mobile { display: none; }
        .auth-logo-tablet { display: none; }
        .auth-logo-desktop { display: block; }
        @media(max-width: 640px) {
            .auth-logo-mobile { display: block; }
            .auth-logo-tablet, .auth-logo-desktop { display: none; }
        }
        @media(min-width: 641px) and (max-width: 1024px) {
            .auth-logo-tablet { display: block; }
            .auth-logo-mobile, .auth-logo-desktop { display: none; }
        }

        .auth-back {
            color: var(--ks-gray-700);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: all 0.15s;
        }
        .auth-back:hover {
            background: var(--ks-gray-100);
            color: var(--ks-black);
        }

        /* ============ MAIN ============ */
        .auth-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem 2.5rem;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--ks-white);
            border-radius: 24px;
            padding: 2rem 1.75rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            border: 1px solid var(--ks-gray-200);
            animation: fadeInUp 0.4s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ============ HEADER DO CARD ============ */
        .auth-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--ks-green) 0%, var(--ks-green-dark) 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 24px rgba(0,157,68,0.3);
        }
        .auth-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            text-align: center;
            margin: 0 0 0.5rem;
            letter-spacing: -0.02em;
            color: var(--ks-black);
        }
        .auth-subtitle {
            font-size: 0.9rem;
            color: var(--ks-gray-500);
            text-align: center;
            line-height: 1.5;
            margin: 0 0 1.5rem;
        }
        .auth-subtitle strong {
            color: var(--ks-green-dark);
            font-weight: 700;
        }

        /* ============ FORM FIELDS ============ */
        .auth-field { margin-bottom: 1rem; }
        .auth-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--ks-gray-700);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.375rem;
        }
        .auth-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .auth-input-icon {
            position: absolute;
            left: 14px;
            color: var(--ks-gray-500);
            pointer-events: none;
        }
        .auth-input {
            width: 100%;
            padding: 14px 14px 14px 44px;
            border: 2px solid var(--ks-gray-200);
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            color: var(--ks-black);
            background: var(--ks-gray-50);
            outline: none;
            transition: all 0.2s;
            -webkit-appearance: none;
        }
        .auth-input:focus {
            border-color: var(--ks-green);
            background: var(--ks-white);
            box-shadow: 0 0 0 4px rgba(0,157,68,0.1);
        }
        .auth-input::placeholder {
            color: var(--ks-gray-300);
        }
        .auth-input.error {
            border-color: var(--ks-danger);
            background: var(--ks-danger-light);
        }

        .auth-toggle-pwd {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--ks-gray-500);
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-toggle-pwd:hover { color: var(--ks-green); }

        .auth-error {
            font-size: 0.75rem;
            color: var(--ks-danger);
            margin-top: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-weight: 500;
        }

        /* ============ MENSAGENS ============ */
        .auth-msg {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.825rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            line-height: 1.5;
        }
        .auth-msg.error {
            background: var(--ks-danger-light);
            border: 1px solid #fca5a5;
            color: #991b1b;
        }
        .auth-msg.success {
            background: var(--ks-green-light);
            border: 1px solid #a7f3d0;
            color: var(--ks-green-dark);
        }
        .auth-msg.info {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            color: #1e40af;
        }
        .auth-msg svg { flex-shrink: 0; margin-top: 1px; }

        /* ============ BOTÃO PRIMÁRIO ============ */
        .auth-btn {
            width: 100%;
            background: var(--ks-green);
            color: var(--ks-white);
            padding: 14px;
            border-radius: 12px;
            border: none;
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(0,157,68,0.25);
            margin-top: 0.5rem;
        }
        .auth-btn:hover { background: var(--ks-green-dark); transform: translateY(-1px); }
        .auth-btn:active { transform: translateY(0); }
        .auth-btn:disabled {
            background: var(--ks-gray-300);
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        /* ============ DIVIDER ============ */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin: 1.25rem 0;
            font-size: 0.7rem;
            color: var(--ks-gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .auth-divider::before, .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--ks-gray-200);
        }

        /* ============ LINKS ============ */
        .auth-links {
            text-align: center;
            font-size: 0.825rem;
            color: var(--ks-gray-700);
            line-height: 1.6;
        }
        .auth-link {
            color: var(--ks-green-dark);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.15s;
        }
        .auth-link:hover {
            color: var(--ks-green);
            text-decoration: underline;
        }

        /* ============ FOOTER ============ */
        .auth-footer {
            text-align: center;
            padding: 1.5rem 1rem;
            font-size: 0.7rem;
            color: var(--ks-gray-500);
        }
        .auth-footer a {
            color: var(--ks-gray-700);
            text-decoration: none;
            font-weight: 600;
        }
        .auth-footer a:hover { color: var(--ks-green); }
        .auth-footer-sep { margin: 0 0.5rem; opacity: 0.5; }

        /* ============ WHATSAPP FLUTUANTE ============ */
        .auth-wa {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #25d366;
            color: white;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 8px 24px rgba(37,211,102,0.4);
            z-index: 90;
            transition: transform 0.2s;
        }
        .auth-wa:hover { transform: scale(1.1); background: #1eb555; }
        .auth-wa::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 3px solid #25d366;
            opacity: 0.4;
            animation: waPulse 2s infinite;
        }
        @keyframes waPulse {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0; transform: scale(1.3); }
        }

        /* ============ MOBILE ============ */
        @media(max-width: 480px) {
            .auth-card { padding: 1.5rem 1.25rem; border-radius: 18px; }
            .auth-title { font-size: 1.4rem; }
            .auth-icon { width: 56px; height: 56px; border-radius: 14px; margin-bottom: 1rem; }
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body>

<div class="auth-page">

    {{-- HEADER --}}
    <header class="auth-header">
        <a href="{{ url('/') }}" class="auth-logo" aria-label="KwanzaSafe — Início">
            <img src="{{ asset('assets/images/logos/logo.png') }}" alt="KwanzaSafe" class="auth-logo-mobile">
            <img src="{{ asset('assets/images/logos/logo2.png') }}" alt="KwanzaSafe" class="auth-logo-tablet">
            <img src="{{ asset('assets/images/logos/logo1.png') }}" alt="KwanzaSafe" class="auth-logo-desktop">
        </a>
        <a href="{{ url('/') }}" class="auth-back">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Voltar ao site</span>
        </a>
    </header>

    {{-- CONTEÚDO --}}
    <main class="auth-main">
        {{ $slot }}
    </main>

    {{-- FOOTER --}}
    <footer class="auth-footer">
        © {{ date('Y') }} KwanzaSafe
        <span class="auth-footer-sep">·</span>
        <a href="{{ route('terms') }}">Termos</a>
        <span class="auth-footer-sep">·</span>
        <a href="{{ route('privacy') }}">Privacidade</a>
    </footer>

</div>

{{-- WHATSAPP FLUTUANTE --}}
<a href="https://wa.me/5511933579009?text=Olá%2C+preciso+de+ajuda+com+o+KwanzaSafe."
   target="_blank"
   rel="noopener"
   class="auth-wa"
   aria-label="Suporte WhatsApp">
    <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
    </svg>
</a>

</body>
</html>
