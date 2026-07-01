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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? 'KwanzaSafe' }} — KwanzaSafe</title>
    <meta name="description" content="KwanzaSafe — Carteira internacional para remessas seguras para Angola.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#009d44">

    {{-- FAVICON --}}
    @include('partials.public-favicons')

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
            --ks-gray-400: #a3a3a3;
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
            background: var(--ks-gray-50);
            color: var(--ks-black);
            min-height: 100dvh;
            -webkit-font-smoothing: antialiased;
        }
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 2px solid var(--ks-green); outline-offset: 2px; border-radius: 6px;
        }

        /* ============ LAYOUT (2 colunas) ============ */
        .auth-shell { min-height: 100dvh; display: grid; grid-template-columns: 1.05fr 1fr; }

        /* Painel de marca (esquerda) — escuro, plano, verde só como acento */
        .auth-brand { position: relative; background: #111111; color: #fff; padding: 3rem; display: flex; flex-direction: column; border-right: 1px solid #000; }
        .auth-brand__logo { display: inline-flex; align-items: center; }
        .auth-brand__logo img { height: 34px; width: auto; }
        .auth-brand__mid { margin: auto 0; max-width: 30rem; }
        .auth-brand__title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: clamp(1.5rem, 2.2vw, 2rem); line-height: 1.2; letter-spacing: -0.01em; margin: 0; }
        .auth-brand__sub { margin: 1rem 0 0; font-size: 0.95rem; color: rgba(255,255,255,.7); line-height: 1.6; }
        .auth-quote { margin-top: 2.5rem; border-left: 2px solid var(--ks-green); padding: 0.25rem 0 0.25rem 1.25rem; }
        .auth-quote p { font-family: 'DM Sans', sans-serif; font-weight: 500; font-size: 0.98rem; line-height: 1.55; margin: 0; color: rgba(255,255,255,.9); }
        .auth-quote__who { display: flex; align-items: center; gap: .7rem; margin-top: 1.1rem; }
        .auth-quote__av { width: 40px; height: 40px; border-radius: 50%; background: var(--ks-green); color: #fff; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-weight: 700; font-size: .85rem; flex-shrink: 0; }
        .auth-brand__trust { display: flex; gap: 1.5rem; flex-wrap: wrap; margin-top: 2.5rem; }
        .auth-brand__trust span { display: inline-flex; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 600; color: rgba(255,255,255,.75); }

        /* Conteúdo (direita) */
        .auth-content { display: flex; flex-direction: column; min-height: 100dvh; background: var(--ks-gray-50); }
        .auth-topbar { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; }
        .auth-topbar__logo { display: none; align-items: center; text-decoration: none; }
        .auth-topbar__logo img { height: 28px; width: auto; }
        .auth-back { color: var(--ks-gray-700); text-decoration: none; font-size: .82rem; font-weight: 600; display: inline-flex; align-items: center; gap: .375rem; padding: .5rem .625rem; border-radius: 8px; transition: background .15s, color .15s; margin-left: auto; }
        .auth-back:hover { background: rgba(0,0,0,.05); color: var(--ks-black); }
        .auth-main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 1rem 1.5rem 2.5rem; }

        @media (max-width: 900px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-brand { display: none; }
            .auth-topbar__logo { display: inline-flex; }
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: var(--ks-white);
            border-radius: 16px;
            padding: 2rem 1.75rem;
            border: 1px solid var(--ks-gray-200);
        }

        /* ============ HEADER DO CARD ============ */
        .auth-icon {
            width: 52px;
            height: 52px;
            background: var(--ks-green-pale);
            color: var(--ks-green-dark);
            border: 1px solid var(--ks-green-light);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }
        .auth-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            text-align: center;
            margin: 0 0 0.5rem;
            letter-spacing: -0.01em;
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
            color: var(--ks-black);
            font-weight: 600;
        }

        /* ============ FORM FIELDS ============ */
        .auth-field { margin-bottom: 1rem; }
        .auth-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--ks-gray-700);
            margin-bottom: 0.375rem;
        }
        .auth-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .auth-input-icon {
            position: absolute;
            left: 13px;
            color: var(--ks-gray-400);
            pointer-events: none;
        }
        .auth-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid var(--ks-gray-300);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.925rem;
            color: var(--ks-black);
            background: var(--ks-white);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            -webkit-appearance: none;
        }
        .auth-input:focus {
            border-color: var(--ks-green);
            box-shadow: 0 0 0 3px rgba(0,157,68,0.12);
        }
        .auth-input::placeholder {
            color: var(--ks-gray-400);
        }
        .auth-input.error {
            border-color: var(--ks-danger);
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
        .auth-toggle-pwd:hover { color: var(--ks-black); }

        .auth-error {
            font-size: 0.75rem;
            color: var(--ks-danger);
            margin-top: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* ============ MENSAGENS ============ */
        .auth-msg {
            padding: 0.75rem 0.875rem;
            border-radius: 10px;
            font-size: 0.825rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            line-height: 1.5;
            background: var(--ks-gray-50);
            border: 1px solid var(--ks-gray-200);
        }
        .auth-msg.error { border-left: 3px solid var(--ks-danger); color: #991b1b; }
        .auth-msg.success { border-left: 3px solid var(--ks-green); color: var(--ks-green-dark); }
        .auth-msg.info { border-left: 3px solid #2563eb; color: #1e40af; }
        .auth-msg svg { flex-shrink: 0; margin-top: 1px; }

        /* ============ BOTÃO PRIMÁRIO ============ */
        .auth-btn {
            width: 100%;
            background: var(--ks-green);
            color: var(--ks-white);
            padding: 13px;
            border-radius: 10px;
            border: none;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            font-size: 0.925rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background 0.15s;
            margin-top: 0.5rem;
        }
        .auth-btn:hover { background: var(--ks-green-dark); }
        .auth-btn:disabled {
            background: var(--ks-gray-300);
            cursor: not-allowed;
        }

        /* ============ DIVIDER ============ */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin: 1.25rem 0;
            font-size: 0.72rem;
            color: var(--ks-gray-500);
            font-weight: 600;
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
            font-size: 0.85rem;
            color: var(--ks-gray-700);
            line-height: 1.6;
        }
        .auth-link {
            color: var(--ks-green-dark);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.15s;
        }
        .auth-link:hover { text-decoration: underline; }

        /* ============ FOOTER ============ */
        .auth-footer {
            text-align: center;
            padding: 1.5rem 1rem;
            font-size: 0.75rem;
            color: var(--ks-gray-500);
        }
        .auth-footer a {
            color: var(--ks-gray-700);
            text-decoration: none;
            font-weight: 600;
        }
        .auth-footer a:hover { color: var(--ks-green-dark); }
        .auth-footer-sep { margin: 0 0.5rem; opacity: 0.5; }

        /* ============ WHATSAPP FLUTUANTE ============ */
        .auth-wa {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #25d366;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 90;
            transition: background 0.15s;
        }
        .auth-wa:hover { background: #1eb555; }

        /* ============ MOBILE ============ */
        @media(max-width: 480px) {
            .auth-card { padding: 1.5rem 1.25rem; }
            .auth-title { font-size: 1.3rem; }
            .auth-icon { width: 48px; height: 48px; margin-bottom: 1rem; }
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body>

<div class="auth-shell">

    {{-- PAINEL DE MARCA (esquerda) --}}
    <aside class="auth-brand">
        <a href="{{ url('/') }}" class="auth-brand__logo" aria-label="KwanzaSafe — início">
            <img src="{{ asset('assets/images/logos/logo-isotipo-white.png') }}" alt="KwanzaSafe">
        </a>
        <div class="auth-brand__mid">
            <h2 class="auth-brand__title">A forma mais segura de enviar dinheiro para Angola.</h2>
            <p class="auth-brand__sub">Euros, Reais e USDC convertidos em Kwanzas — em horas, não dias. Com verificação séria e suporte humano de verdade.</p>
            <figure class="auth-quote">
                <blockquote><p>“Mandei euros de Lisboa para a minha mãe no Huambo e em menos de duas horas ela já tinha os kwanzas.”</p></blockquote>
                <figcaption class="auth-quote__who">
                    <span class="auth-quote__av">ET</span>
                    <span>
                        <span style="display:block;font-weight:700;font-size:.9rem;">Edmilson Tavares</span>
                        <span style="display:block;font-size:.78rem;opacity:.82;">Lisboa · envia para o Huambo</span>
                    </span>
                </figcaption>
            </figure>
        </div>
        <div class="auth-brand__trust">
            <span>✓ Verificação KYC</span>
            <span>✓ Anti-fraude AML</span>
            <span>✓ BNA Compliant</span>
        </div>
    </aside>

    {{-- CONTEÚDO (direita) --}}
    <div class="auth-content">
        <div class="auth-topbar">
            <a href="{{ url('/') }}" class="auth-topbar__logo" aria-label="KwanzaSafe — início">
                <img src="{{ asset('assets/images/logos/logo-isotipo.png') }}" alt="KwanzaSafe">
            </a>
            <a href="{{ url('/') }}" class="auth-back">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Voltar ao site</span>
            </a>
        </div>

        <main class="auth-main">
            {{ $slot }}
        </main>

        <footer class="auth-footer">
            © {{ date('Y') }} KwanzaSafe
            <span class="auth-footer-sep">·</span>
            <a href="{{ route('terms') }}">Termos</a>
            <span class="auth-footer-sep">·</span>
            <a href="{{ route('privacy') }}">Privacidade</a>
        </footer>
    </div>

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
