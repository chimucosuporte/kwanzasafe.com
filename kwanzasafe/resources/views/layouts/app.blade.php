<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ============ SEO BÁSICO ============ --}}
    <title>{{ $title ?? 'KwanzaSafe — Carteira Internacional' }}</title>
    <meta name="description" content="{{ $description ?? 'Plataforma KwanzaSafe — câmbio seguro de EUR, BRL e USDT para Kwanzas em Angola.' }}">
    <meta name="robots" content="{{ $robots ?? 'noindex, nofollow' }}">
    <meta name="author" content="KwanzaSafe">
    <meta name="theme-color" content="#009d44">

    <meta property="og:site_name" content="KwanzaSafe">
    <meta property="og:locale" content="pt_AO">
    <meta property="og:image" content="{{ asset('assets/images/logos/logo1.png') }}">

    {{-- ============ FAVICON ============ --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logos/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logos/logo1.png') }}">

    {{-- ============ PRECONNECT ============ --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com">

    {{-- ============ FONTES OFICIAIS ============ --}}
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    {{-- ============ TAILWIND COMPILADO + ALPINE ============ --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- ============ PERSISTÊNCIA DE ESTADO (Sprint 3) ============ --}}
    <script src="{{ asset('js/ks-persist.js') }}"></script>

    {{-- ============ VARIÁVEIS CSS GLOBAIS ============ --}}
    <style>
        :root {
            --ks-green: #009d44;
            --ks-green-dark: #007a34;
            --ks-green-mid: #00b350;
            --ks-green-light: #d1f2e0;
            --ks-green-pale: #f0faf4;

            --ks-black: #000000;
            --ks-black-soft: #111111;
            --ks-black-mid: #171717;

            --ks-white: #ffffff;
            --ks-off-white: #fafafa;

            --ks-gray-50: #fafafa;
            --ks-gray-100: #f5f5f5;
            --ks-gray-200: #e5e5e5;
            --ks-gray-300: #d4d4d4;
            --ks-gray-400: #a3a3a3;
            --ks-gray-500: #737373;
            --ks-gray-600: #525252;
            --ks-gray-700: #404040;
            --ks-gray-800: #262626;
            --ks-gray-900: #171717;

            --ks-danger: #dc2626;
            --ks-danger-light: #fee2e2;
            --ks-warn: #f59e0b;
            --ks-warn-light: #fef3c7;
            --ks-info: #2563eb;
            --ks-info-light: #dbeafe;
            --ks-success: #009d44;
            --ks-success-light: #d1f2e0;

            --ks-shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --ks-shadow: 0 4px 12px rgba(0,0,0,0.08);
            --ks-shadow-lg: 0 12px 32px rgba(0,0,0,0.12);
            --ks-shadow-xl: 0 24px 60px rgba(0,0,0,0.16);

            --ks-radius-sm: 8px;
            --ks-radius: 12px;
            --ks-radius-md: 16px;
            --ks-radius-lg: 24px;
            --ks-radius-pill: 999px;

            --ks-sidebar-w: 260px;
            --ks-sidebar-w-admin: 240px;
            --ks-bottom-h: 68px;
            --ks-topbar-h: 60px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }
        body {
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            color: var(--ks-black);
            background: var(--ks-off-white);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        .font-display { font-family: 'Syne', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }

        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: var(--ks-gray-100); }
        ::-webkit-scrollbar-thumb { background: var(--ks-gray-300); border-radius: 8px; border: 2px solid var(--ks-gray-100); }
        ::-webkit-scrollbar-thumb:hover { background: var(--ks-gray-400); }

        ::selection { background: var(--ks-green); color: var(--ks-white); }

        :focus-visible { outline: 2px solid var(--ks-green); outline-offset: 2px; }

        .ks-logo-mobile { display: none; }
        .ks-logo-tablet { display: none; }
        .ks-logo-desktop { display: block; }

        @media (max-width: 640px) {
            .ks-logo-mobile { display: block; }
            .ks-logo-desktop { display: none; }
            .ks-logo-tablet { display: none; }
        }
        @media (min-width: 641px) and (max-width: 1024px) {
            .ks-logo-tablet { display: block; }
            .ks-logo-desktop { display: none; }
            .ks-logo-mobile { display: none; }
        }

        .ks-wa-global {
            position: fixed;
            bottom: 90px;
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
        .ks-wa-global:hover { transform: scale(1.1); background: #1eb555; }
        .ks-wa-global::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 3px solid #25d366;
            opacity: 0.4;
            animation: ks-wa-pulse 2s infinite;
        }
        @keyframes ks-wa-pulse {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0; transform: scale(1.3); }
        }

        @media (max-width: 1023px) {
            .ks-wa-global { bottom: calc(var(--ks-bottom-h) + 20px); }
        }

        @keyframes ks-fade-in-up {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .ks-fade-in { animation: ks-fade-in-up 0.3s ease forwards; }

        @keyframes ks-spin { to { transform: rotate(360deg); } }
        .ks-spin { animation: ks-spin 1s linear infinite; }

        @keyframes ks-pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .ks-pulse { animation: ks-pulse-dot 2s infinite; }

        @media print {
            .ks-wa-global, .ks-bottom, .ks-sidebar, nav, button { display: none !important; }
            body { background: white !important; color: black !important; }
        }
    </style>

    @stack('head')
</head>

<body>
    {{ $slot }}

    @auth
        <a
            href="https://wa.me/5511933579009?text=Olá%2C+preciso+de+ajuda+com+o+KwanzaSafe."
            target="_blank"
            rel="noopener"
            class="ks-wa-global"
            aria-label="Contactar suporte via WhatsApp"
            title="Suporte WhatsApp">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
        </a>
    @endauth

    @stack('scripts')

    @auth
    <audio id="ks-notify-sound" preload="auto" style="display:none;">
        <source src="{{ asset('assets/sounds/notify.wav') }}" type="audio/mpeg">
    </audio>

    <script>
        window.ksNotify = function() {
            const audio = document.getElementById('ks-notify-sound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(e => console.log('Notification sound blocked:', e));
            }
        };
    </script>
    @endauth

</body>
</html>