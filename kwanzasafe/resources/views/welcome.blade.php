<x-public-layout
    title="KwanzaSafe — Envia Euros, Reais e USDC para Angola com Segurança"
    description="Carteira internacional para remessas seguras para Angola. Converte Euros, Reais e USDC em Kwanzas (AOA) com rapidez e total confiança."
    :canonical="url('/')">

<x-slot:head>
    {{-- Preload do hero (LCP) --}}
    <link rel="preload" as="image" href="{{ asset('assets/images/banners/banner-1.svg') }}" type="image/svg+xml">
    <style>
        :root {
            --ks-green: #009d44;
            --ks-green-dark: #007a34;
            --ks-green-light: #d1f2e0;
            --ks-green-pale: #f0faf4;
            --ks-black: #000000;
            --ks-white: #ffffff;
            --ks-gray-50: #fafafa;
            --ks-gray-100: #f5f5f5;
            --ks-gray-200: #e5e5e5;
            --ks-gray-500: #737373;
            --ks-gray-700: #404040;
            --ks-gray-900: #171717;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'DM Sans', sans-serif; color: var(--ks-black); background: var(--ks-white); margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .font-display { font-family: 'Syne', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }

        /* Reveal ao scroll */
        .ks-reveal { opacity: 0; transform: translateY(26px); transition: opacity .65s cubic-bezier(.4,0,.2,1), transform .65s cubic-bezier(.4,0,.2,1); will-change: opacity, transform; }
        .ks-reveal.ks-in { opacity: 1; transform: translateY(0); }
        .ks-reveal.d1 { transition-delay: .08s; }
        .ks-reveal.d2 { transition-delay: .16s; }
        .ks-reveal.d3 { transition-delay: .24s; }
        .ks-reveal.d4 { transition-delay: .32s; }
        @media (prefers-reduced-motion: reduce) {
            .ks-reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
            .ks-menu-panel.open .ks-menu-nav a { animation: none; opacity: 1; transform: none; }
        }

        /* ============ ESTATÍSTICAS ============ */
        .ks-stats { background: var(--ks-green-pale); border-top: 1px solid var(--ks-green-light); border-bottom: 1px solid var(--ks-green-light); padding: 3rem 1.5rem; }
        .ks-stats-inner { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
        .ks-stat { text-align: center; }
        .ks-stat-num { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 2.4rem; color: var(--ks-green-dark); line-height: 1; letter-spacing: -0.02em; }
        .ks-stat-label { font-size: 0.82rem; color: var(--ks-gray-700); margin-top: 0.5rem; font-weight: 500; }
        @media (max-width: 720px) { .ks-stats-inner { grid-template-columns: repeat(2, 1fr); gap: 2rem 1rem; } .ks-stat-num { font-size: 2rem; } }

        /* ============ TESTEMUNHOS (carrossel) ============ */
        .ks-test { padding: 5.5rem 1.5rem; background: #fff; }
        .ks-test-inner { max-width: 980px; margin: 0 auto; }
        .ks-test-head { text-align: center; margin-bottom: 2.5rem; }
        .ks-tcar { position: relative; }
        .ks-tcar-viewport { overflow: hidden; border-radius: 22px; }
        .ks-tcar-track { display: flex; transition: transform .55s cubic-bezier(.4,0,.2,1); }
        .ks-tslide { min-width: 100%; padding: 0.25rem; }
        .ks-tcard { background: var(--ks-gray-50); border: 1px solid var(--ks-gray-200); border-radius: 20px; padding: 2.5rem; }
        .ks-tstars { color: #f5b301; font-size: 1rem; letter-spacing: 2px; margin-bottom: 1.1rem; }
        .ks-tquote { font-family: 'Syne', sans-serif; font-weight: 600; font-size: 1.25rem; line-height: 1.5; color: #171717; letter-spacing: -0.01em; }
        .ks-tquote::before { content: '“'; color: var(--ks-green-dark); font-size: 1.6rem; font-weight: 800; }
        .ks-tperson { display: flex; align-items: center; gap: 0.9rem; margin-top: 1.6rem; }
        .ks-tavatar { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.05rem; color: #fff; flex-shrink: 0; }
        .ks-tname { font-weight: 700; font-size: 0.95rem; color: #000; }
        .ks-tloc { font-size: 0.8rem; color: var(--ks-gray-500); }
        .ks-tcar-controls { display: flex; align-items: center; justify-content: center; gap: 1.25rem; margin-top: 1.75rem; }
        .ks-tcar-arrow { width: 44px; height: 44px; border-radius: 50%; border: 1.5px solid var(--ks-gray-200); background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #404040; transition: all .15s; }
        .ks-tcar-arrow:hover { border-color: var(--ks-green-dark); color: var(--ks-green-dark); background: var(--ks-green-pale); }
        .ks-tcar-dots { display: flex; gap: 8px; }
        .ks-tdot { width: 9px; height: 9px; border-radius: 999px; border: none; background: var(--ks-gray-200); cursor: pointer; padding: 0; transition: all .25s; }
        .ks-tdot.active { background: var(--ks-green); width: 26px; }
        @media (max-width: 600px) { .ks-tcard { padding: 1.75rem; } .ks-tquote { font-size: 1.1rem; } }
        @media(min-width: 769px) and (max-width: 1024px) {
            .ks-logo-img { display: none; }
            .ks-logo-img.tablet { display: block; height: 34px; }
        }

        /* ============ CALCULADORA HERO ============ */
        .ks-calc-card { background: var(--ks-white); border-radius: 24px; padding: 2rem; box-shadow: 0 24px 60px rgba(0,0,0,0.12); border: 1px solid var(--ks-gray-200); position: relative; }
        .ks-calc-card::before { content: ''; position: absolute; top: -1px; left: -1px; right: -1px; height: 4px; background: linear-gradient(90deg, var(--ks-green), #00c756, var(--ks-green)); border-radius: 24px 24px 0 0; }
        .ks-calc-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .ks-calc-title { font-family: 'Syne', sans-serif; font-size: 1.125rem; font-weight: 800; margin: 0; }
        .ks-calc-live { background: var(--ks-green-pale); color: var(--ks-green-dark); font-size: 0.65rem; font-weight: 800; padding: 3px 9px; border-radius: 20px; letter-spacing: 0.05em; text-transform: uppercase; display: flex; align-items: center; gap: 5px; }
        .ks-calc-live::before { content: ''; width: 5px; height: 5px; background: var(--ks-green); border-radius: 50%; animation: pulse-dot 1.5s infinite; }

        .ks-field { margin-bottom: 1rem; }
        .ks-field-label { font-size: 0.7rem; font-weight: 700; color: var(--ks-gray-500); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.375rem; display: flex; justify-content: space-between; }
        .ks-field-group { display: flex; background: var(--ks-gray-50); border: 2px solid var(--ks-gray-200); border-radius: 14px; overflow: hidden; transition: border-color 0.2s; }
        .ks-field-group:focus-within { border-color: var(--ks-green-dark); background: var(--ks-white); }
        .ks-field-group select, .ks-field-group input { background: transparent; border: none; outline: none; font-family: 'Syne', sans-serif; font-weight: 700; padding: 0.875rem; font-size: 1.1rem; color: var(--ks-black); }
        .ks-field-group select { width: auto; border-right: 2px solid var(--ks-gray-200); cursor: pointer; font-size: 0.95rem; padding: 0.875rem 0.75rem; min-width: 95px; }
        .ks-field-group input { flex: 1; text-align: right; width: 100%; }

        .ks-arrow-swap { display: flex; justify-content: center; margin: -10px 0; position: relative; z-index: 2; }
        .ks-arrow-swap-btn { width: 36px; height: 36px; background: var(--ks-green); color: var(--ks-white); border-radius: 50%; border: 3px solid var(--ks-white); display: flex; align-items: center; justify-content: center; }

        .ks-rate-info { background: var(--ks-green-pale); border: 1px solid rgba(0,157,68,0.15); border-radius: 10px; padding: 0.75rem 1rem; margin: 0.875rem 0; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; }
        .ks-rate-info-label { color: var(--ks-gray-700); font-weight: 500; }
        .ks-rate-info-value { font-family: 'Syne', sans-serif; font-weight: 800; color: var(--ks-green-dark); }

        .ks-calc-btn { width: 100%; background: var(--ks-green); color: var(--ks-white); border: none; padding: 1rem; border-radius: 14px; font-family: 'Syne', sans-serif; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s; margin-top: 0.5rem; box-shadow: 0 6px 20px rgba(0,157,68,0.25); }
        .ks-calc-btn:hover { background: var(--ks-green-dark); transform: translateY(-1px); }
        .ks-calc-note { font-size: 0.7rem; color: var(--ks-gray-500); text-align: center; margin-top: 0.75rem; line-height: 1.4; }

        @media(max-width: 968px) {
            .ks-hero-inner { grid-template-columns: 1fr; gap: 2rem; }
            .ks-hero { padding: 2.5rem 1rem 3rem; }
            .ks-hero-title { font-size: 2.25rem; }
            .ks-hero-sub { font-size: 1rem; }
        }

        /* ============ CURRENCIES SECTION ============ */
        .ks-section { padding: 5rem 1.5rem; max-width: 1200px; margin: 0 auto; }
        .ks-section-label { display: inline-block; font-size: 0.75rem; font-weight: 700; color: var(--ks-green-dark); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.75rem; }
        .ks-section-title { font-family: 'Syne', sans-serif; font-size: 2.25rem; font-weight: 800; letter-spacing: -0.02em; line-height: 1.1; margin: 0 0 0.875rem; color: var(--ks-black); }
        .ks-section-sub { font-size: 1rem; color: var(--ks-gray-700); line-height: 1.6; max-width: 640px; margin: 0 0 2.5rem; }

        .ks-currencies { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
        .ks-currency-card { background: var(--ks-white); border: 1px solid var(--ks-gray-200); border-radius: 20px; padding: 2rem; text-align: center; transition: all 0.25s; cursor: default; }
        .ks-currency-card:hover { border-color: var(--ks-green-dark); transform: translateY(-4px); box-shadow: 0 16px 36px rgba(0,0,0,0.08); }
        .ks-currency-flag { font-size: 3rem; line-height: 1; margin-bottom: 0.75rem; display: block; }
        .ks-currency-name { font-family: 'Syne', sans-serif; font-size: 1.25rem; font-weight: 800; color: var(--ks-black); }
        .ks-currency-code { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 700; color: var(--ks-green-dark); letter-spacing: 0.1em; margin-top: 0.25rem; }
        .ks-currency-desc { font-size: 0.85rem; color: var(--ks-gray-500); margin-top: 0.75rem; line-height: 1.5; }

        @media(max-width: 768px) { .ks-currencies { grid-template-columns: 1fr; } .ks-section { padding: 3rem 1rem; } .ks-section-title { font-size: 1.75rem; } }

        /* ============ HOW IT WORKS ============ */
        .ks-how-section { background: var(--ks-black); color: var(--ks-white); padding: 5rem 1.5rem; }
        .ks-how-inner { max-width: 1200px; margin: 0 auto; }
        .ks-how-section .ks-section-label { color: var(--ks-green-dark); }
        .ks-how-section .ks-section-title { color: var(--ks-white); }
        .ks-how-section .ks-section-sub { color: #a3a3a3; }

        .ks-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; counter-reset: step; margin-top: 2rem; }
        .ks-step { position: relative; }
        .ks-step-number { width: 44px; height: 44px; background: var(--ks-green); color: var(--ks-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.125rem; margin-bottom: 1rem; }
        .ks-step-title { font-family: 'Syne', sans-serif; font-size: 1.125rem; font-weight: 800; margin: 0 0 0.5rem; color: var(--ks-white); }
        .ks-step-desc { font-size: 0.9rem; line-height: 1.6; color: #a3a3a3; margin: 0; }

        @media(max-width: 768px) { .ks-steps { grid-template-columns: 1fr; gap: 1.5rem; } }

        /* ============ WHY SECTION ============ */
        .ks-why { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
        .ks-why-card { padding: 1.75rem; border-radius: 18px; background: var(--ks-gray-50); border: 1px solid var(--ks-gray-200); transition: all 0.2s; }
        .ks-why-card:hover { background: var(--ks-white); border-color: var(--ks-green-dark); box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
        .ks-why-icon { width: 44px; height: 44px; background: var(--ks-green); color: var(--ks-white); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
        .ks-why-title { font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 800; margin: 0 0 0.375rem; color: var(--ks-black); }
        .ks-why-desc { font-size: 0.85rem; color: var(--ks-gray-700); line-height: 1.5; margin: 0; }

        @media(max-width: 968px) { .ks-why { grid-template-columns: repeat(2, 1fr); } }
        @media(max-width: 480px) { .ks-why { grid-template-columns: 1fr; } }

        /* ============ FAQ ============ */
        .ks-faq-wrap { max-width: 800px; margin: 0 auto; }
        .ks-faq-item { border-bottom: 1px solid var(--ks-gray-200); padding: 1.25rem 0; }
        .ks-faq-q { display: flex; justify-content: space-between; align-items: center; cursor: pointer; list-style: none; font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 700; color: var(--ks-black); }
        .ks-faq-q::-webkit-details-marker { display: none; }
        .ks-faq-q::after { content: '+'; font-size: 1.5rem; color: var(--ks-green-dark); font-weight: 400; transition: transform 0.2s; }
        details[open] .ks-faq-q::after { transform: rotate(45deg); }
        .ks-faq-a { margin-top: 0.75rem; font-size: 0.9rem; color: var(--ks-gray-700); line-height: 1.7; }

        /* ============ CTA FINAL ============ */
        .ks-cta-section { background: linear-gradient(135deg, var(--ks-green) 0%, var(--ks-green-dark) 100%); color: var(--ks-white); padding: 5rem 1.5rem; text-align: center; position: relative; overflow: hidden; }
        .ks-cta-section::before { content: ''; position: absolute; top: -50px; right: -50px; width: 250px; height: 250px; background: rgba(255,255,255,0.08); border-radius: 50%; }
        .ks-cta-title { font-family: 'Syne', sans-serif; font-size: 2.5rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 1rem; color: var(--ks-white); position: relative; z-index: 1; }
        .ks-cta-sub { font-size: 1.125rem; margin: 0 0 2rem; opacity: 0.9; position: relative; z-index: 1; }
        .ks-cta-btn { background: var(--ks-white); color: var(--ks-green-dark); padding: 1rem 2rem; border-radius: 999px; font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: transform 0.2s; position: relative; z-index: 1; }
        .ks-cta-btn:hover { transform: translateY(-2px); background: var(--ks-black); color: var(--ks-white); }

        @media(max-width: 768px) { .ks-cta-title { font-size: 1.75rem; } .ks-cta-sub { font-size: 1rem; } }

    </style>
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"De que países posso enviar?","acceptedAnswer":{"@type":"Answer","text":"Aceitamos transferências de qualquer país do mundo. As moedas suportadas são Euro (EUR) de toda a zona Euro, Real Brasileiro (BRL) do Brasil, e USDT/USDC via blockchain (sem fronteiras geográficas)."}},{"@type":"Question","name":"Quanto tempo demora a receber os Kwanzas?","acceptedAnswer":{"@type":"Answer","text":"Em dias úteis, após recebermos e validarmos o comprovativo de pagamento, os Kwanzas são creditados no IBAN angolano registado em até 4 horas. Operações iniciadas antes das 14h são concluídas no mesmo dia."}},{"@type":"Question","name":"Posso enviar de uma conta de outra pessoa?","acceptedAnswer":{"@type":"Answer","text":"Não. Por segurança e conformidade AML, o nome do titular da conta de origem deve corresponder exatamente ao teu BI registado na KwanzaSafe. Transferências de terceiros são automaticamente recusadas."}},{"@type":"Question","name":"Qual o valor mínimo por operação?","acceptedAnswer":{"@type":"Answer","text":"O mínimo é 10 unidades da moeda de origem (10 EUR, 10 BRL ou 10 USDT). Para valores acima de 5.000 EUR pode ser pedida documentação adicional por exigência regulatória."}},{"@type":"Question","name":"Que redes blockchain aceitam para USDT e USDC?","acceptedAnswer":{"@type":"Answer","text":"Aceitamos as principais redes: TRC20 (TRON), BEP20 (Binance Smart Chain) e ERC20 (Ethereum). Recomendamos TRC20 pela taxa de rede muito baixa."}},{"@type":"Question","name":"Como é calculada a taxa de câmbio?","acceptedAnswer":{"@type":"Answer","text":"As taxas são revistas ao longo do dia com base no mercado internacional e oficial de Angola. A taxa que vês na calculadora é a que será aplicada, sem surpresas."}},{"@type":"Question","name":"O que acontece se errar o IBAN de destino?","acceptedAnswer":{"@type":"Answer","text":"Antes de cada transação verificamos contigo o IBAN de destino. Se mesmo assim houver erro, entra em contacto imediato via chat ou WhatsApp."}}]}
</script>
</x-slot:head>

    {{-- ============ HERO ============ --}}
    {{-- ============ HERO (banner) ============ --}}
    <section class="ks-herobanner" aria-label="Destaque">
        <h1 class="ks-sronly">Envia Euros, Reais e USDC para Angola com segurança — conversão em Kwanzas em horas</h1>
        @php
            $heroDest = auth()->check() ? url('/dashboard') : route('register');
            $heroCta  = auth()->check() ? 'Ir para o meu painel' : 'Criar conta grátis';
            $banners = [
                1 => 'Envia para Angola com segurança — Euros, Reais e USDC convertidos em Kwanzas em horas.',
                2 => 'Taxa de câmbio transparente — vês quanto a tua família recebe antes de enviar.',
                3 => 'Verificação KYC e anti-fraude AML — a tua transferência sempre protegida.',
                4 => 'Recebe os Kwanzas no IBAN angolano em até 4 horas em dias úteis.',
                5 => 'Suporte humano por WhatsApp em cada etapa da tua transferência.',
            ];
        @endphp
        <div class="ks-bcar" x-data="ksBanners()" x-init="init()" @mouseenter="stop()" @mouseleave="start()"
             role="region" aria-roledescription="carrossel" aria-label="Destaques KwanzaSafe">
            <div class="ks-bcar-track" :style="`transform: translateX(-${i * 100}%)`">
                @foreach($banners as $n => $alt)
                    <a href="{{ $heroDest }}" class="ks-bcar-slide" aria-label="{{ $heroCta }}">
                        <img src="{{ asset('assets/images/banners/banner-'.$n.'.svg') }}" alt="{{ $alt }}" width="1600" height="520" loading="{{ $n === 1 ? 'eager' : 'lazy' }}" {{ $n === 1 ? 'fetchpriority=high' : '' }}>
                    </a>
                @endforeach
            </div>
            <button class="ks-bcar-arrow prev" type="button" @click="prev()" aria-label="Destaque anterior">‹</button>
            <button class="ks-bcar-arrow next" type="button" @click="next()" aria-label="Destaque seguinte">›</button>
            <div class="ks-bcar-dots">
                <template x-for="k in n" :key="k">
                    <button class="ks-bcar-dot" type="button" :class="{ active: i === k - 1 }" @click="go(k - 1)" :aria-label="`Ir para o destaque ${k}`"></button>
                </template>
            </div>
        </div>
        <div class="ks-herobanner__trust">
            <span class="ks-htrust"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg> Verificação KYC</span>
            <span class="ks-htrust"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round"/></svg> Anti-fraude AML</span>
            <span class="ks-htrust"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"/></svg> Até 4 horas</span>
            <span class="ks-htrust"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke-linecap="round" stroke-linejoin="round"/></svg> Suporte humano</span>
        </div>
    </section>

    {{-- ============ SIMULADOR (calculadora) ============ --}}
    <section class="ks-sec ks-sec--gray" id="simular">
        <div class="ks-container" style="max-width: 560px;">
            <div style="text-align:center; margin-bottom: 1.75rem;">
                <span class="ks-eyebrow">Calculadora</span>
                <h2 class="ks-h2 ks-reveal" style="margin-top:.5rem;">Simula o teu câmbio</h2>
                <p class="ks-lead ks-reveal d1" style="margin-top:.5rem;">Vê quanto a tua família recebe em Kwanzas, com a taxa real — sem surpresas.</p>
            </div>

            <div class="ks-calc-card ks-reveal" x-data="heroCalc()" x-init="init()">
                <div class="ks-calc-header">
                    <h3 class="ks-calc-title">Simular Câmbio</h3>
                    <span class="ks-calc-live">Tempo Real</span>
                </div>

                <div class="ks-field">
                    <div class="ks-field-label">
                        <span>Envias</span>
                        <span style="color:var(--ks-green-dark);font-weight:700;" x-show="amountSent >= 10">✓ Válido</span>
                    </div>
                    <div class="ks-field-group">
                        <select x-model="currency" @change="recalc()" aria-label="Moeda de origem">
                            @foreach($rates as $r)
                                <option value="{{ $r->currency_from }}">{{ $r->currency_from }}</option>
                            @endforeach
                        </select>
                        <input type="number" x-model="amountSent" @input="recalc()" placeholder="100" min="10" step="0.01" inputmode="decimal" aria-label="Valor a enviar">
                    </div>
                </div>

                <div class="ks-arrow-swap">
                    <div class="ks-arrow-swap-btn">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M19 12l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>

                <div class="ks-field">
                    <div class="ks-field-label"><span>Recebes em Angola</span></div>
                    <div class="ks-field-group" style="background: var(--ks-green-pale); border-color: rgba(0,157,68,0.3);">
                        <div style="display:flex; align-items:center; gap:.4rem; padding: 0.875rem; font-family:'Syne',sans-serif; font-weight: 700; font-size: 0.95rem; border-right: 2px solid rgba(0,157,68,0.15); min-width: 95px;"><x-flag code="aoa" :size="20" /> AOA</div>
                        <div style="flex:1; padding: 0.875rem; text-align: right; font-family:'Syne',sans-serif; font-weight: 800; font-size: 1.25rem; color: var(--ks-green-dark);" x-text="amountReceived > 0 ? formatKz(amountReceived) : '—'"></div>
                    </div>
                </div>

                <div class="ks-rate-info">
                    <span class="ks-rate-info-label">Taxa de câmbio</span>
                    <span class="ks-rate-info-value">1 <span x-text="currency"></span> = <span x-text="formatKz(rate)"></span> Kz</span>
                </div>

                @auth
                    <a href="{{ url('/dashboard') }}" class="ks-calc-btn" style="text-decoration:none;">Iniciar Transação
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="ks-calc-btn" style="text-decoration:none;">Criar Conta e Enviar
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @endauth

                <p class="ks-calc-note">Valor mínimo: 10 unidades. Taxas atualizadas a cada hora.</p>
            </div>
        </div>
    </section>

    {{-- ============ MOEDAS ACEITES ============ --}}
    <section class="ks-section" id="moedas">
        <div style="text-align:center;">
            <span class="ks-section-label">Moedas Aceites</span>
            <h2 class="ks-section-title ks-reveal">De qualquer parte do mundo para Angola</h2>
            <p class="ks-section-sub" style="margin-left:auto;margin-right:auto;">
                Aceitamos as 3 principais moedas usadas pela comunidade angolana no exterior — com conversão transparente em Kwanzas.
            </p>
        </div>

        <div class="ks-currencies">
            <div class="ks-currency-card ks-reveal">
                <span class="ks-currency-flag" style="display:flex;justify-content:center;"><x-flag code="eur" :size="52" /></span>
                <h3 class="ks-currency-name">Euro</h3>
                <div class="ks-currency-code">EUR</div>
                <p class="ks-currency-desc">Envia de Portugal, Espanha, França, Alemanha e restante zona Euro.</p>
            </div>
            <div class="ks-currency-card ks-reveal">
                <span class="ks-currency-flag" style="display:flex;justify-content:center;"><x-flag code="brl" :size="52" /></span>
                <h3 class="ks-currency-name">Real Brasileiro</h3>
                <div class="ks-currency-code">BRL</div>
                <p class="ks-currency-desc">Transferência PIX ou bancária do Brasil com chegada rápida.</p>
            </div>
            <div class="ks-currency-card ks-reveal">
                <span class="ks-currency-flag" style="display:flex;justify-content:center;"><x-flag code="usdc" :size="52" /></span>
                <h3 class="ks-currency-name">USDT / USDC</h3>
                <div class="ks-currency-code">TRC20 · BEP20 · ERC20</div>
                <p class="ks-currency-desc">Stablecoins via blockchain. Entrega em minutos via rede TRON (TRC20).</p>
            </div>
        </div>
    </section>

    {{-- ============ COMO FUNCIONA ============ --}}
    <section class="ks-how-section" id="como-funciona">
        <div class="ks-how-inner">
            <div style="text-align:center;">
                <span class="ks-section-label">Processo Simples</span>
                <h2 class="ks-section-title ks-reveal">Como funciona</h2>
                <p class="ks-section-sub" style="margin-left:auto;margin-right:auto;">
                    Três passos e o destinatário tem os Kwanzas na conta — sem burocracia, sem taxas surpresa.
                </p>
            </div>

            <div class="ks-steps">
                <div class="ks-step ks-reveal">
                    <div class="ks-step-number">1</div>
                    <h3 class="ks-step-title">Regista-te e verifica</h3>
                    <p class="ks-step-desc">Cria a tua conta, envia documento de identidade e comprova titularidade. Verificação em até 24 horas.</p>
                </div>
                <div class="ks-step ks-reveal">
                    <div class="ks-step-number">2</div>
                    <h3 class="ks-step-title">Envia a moeda</h3>
                    <p class="ks-step-desc">Simula na calculadora, regista a transação e envia EUR, BRL ou USDT para a conta que te indicamos.</p>
                </div>
                <div class="ks-step ks-reveal">
                    <div class="ks-step-number">3</div>
                    <h3 class="ks-step-title">Recebe AOA em Angola</h3>
                    <p class="ks-step-desc">Após validação do comprovativo, os Kwanzas são depositados no IBAN angolano registado por ti em até 4 horas úteis.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ PORQUÊ ============ --}}
    <section class="ks-section">
        <div style="text-align:center;">
            <span class="ks-section-label">Porquê KwanzaSafe</span>
            <h2 class="ks-section-title ks-reveal">A tua segurança em primeiro lugar</h2>
            <p class="ks-section-sub" style="margin-left:auto;margin-right:auto;">
                Construímos uma plataforma à altura das exigências internacionais — com processos robustos de verificação e proteção contra fraudes.
            </p>
        </div>

        <div class="ks-why">
            <div class="ks-why-card ks-reveal">
                <div class="ks-why-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="ks-why-title">Anti-Fraude AML</h3>
                <p class="ks-why-desc">O nome na conta de origem tem de coincidir com o teu BI — nenhum pagamento de terceiros é aceite.</p>
            </div>
            <div class="ks-why-card ks-reveal">
                <div class="ks-why-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="ks-why-title">Rápido</h3>
                <p class="ks-why-desc">Processamos em até 4 horas úteis. Transferências USDT chegam em minutos.</p>
            </div>
            <div class="ks-why-card ks-reveal">
                <div class="ks-why-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="ks-why-title">Transparente</h3>
                <p class="ks-why-desc">A taxa que vês na calculadora é a taxa final. Sem comissões ocultas, sem letra pequena.</p>
            </div>
            <div class="ks-why-card ks-reveal">
                <div class="ks-why-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="ks-why-title">Suporte Profissional</h3>
                <p class="ks-why-desc">Equipa dedicada via chat na plataforma e WhatsApp. Resposta em menos de 2 horas.</p>
            </div>
        </div>
    </section>

    {{-- ============ ESTATÍSTICAS ============ --}}
    <section class="ks-stats">
        <div class="ks-stats-inner">
            <div class="ks-stat ks-reveal">
                <div class="ks-stat-num"><span data-count="18">0</span></div>
                <div class="ks-stat-label">Províncias de Angola alcançadas</div>
            </div>
            <div class="ks-stat ks-reveal d1">
                <div class="ks-stat-num"><span data-count="3">0</span></div>
                <div class="ks-stat-label">Moedas + USDC (EUR · BRL · USDC)</div>
            </div>
            <div class="ks-stat ks-reveal d2">
                <div class="ks-stat-num">&lt; 2h</div>
                <div class="ks-stat-label">Tempo médio de envio</div>
            </div>
            <div class="ks-stat ks-reveal d3">
                <div class="ks-stat-num">24/7</div>
                <div class="ks-stat-label">Suporte humano por WhatsApp</div>
            </div>
        </div>
    </section>

    {{-- ============ TESTEMUNHOS ============ --}}
    <section class="ks-test" id="testemunhos">
        <div class="ks-test-inner">
            <div class="ks-test-head">
                <span class="ks-section-label" style="color:var(--ks-green-dark); font-weight:700; font-size:0.78rem; letter-spacing:0.1em; text-transform:uppercase;">Histórias reais</span>
                <h2 class="ks-section-title ks-reveal" style="margin-top:0.5rem;">Quem confia a sua família à KwanzaSafe</h2>
            </div>

            <div class="ks-tcar ks-reveal" x-data="ksTestimonials()" @mouseenter="stop()" @mouseleave="start()">
                <div class="ks-tcar-viewport">
                    <div class="ks-tcar-track" :style="`transform:translateX(-${i*100}%)`">
                        @php
                            $testimonials = [
                                ['q' => 'Mandei euros de Lisboa para a minha mãe no Huambo e em menos de duas horas ela já tinha os kwanzas na conta. O atendimento por WhatsApp foi impecável.', 'n' => 'Edmilson Tavares', 'l' => 'Lisboa, Portugal · envia para o Huambo', 'in' => 'ET', 'c' => '#009d44'],
                                ['q' => 'Desconfiava de tudo o que era online, mas a verificação séria e o recibo a cada passo deram-me confiança. Já é a terceira vez que uso, sempre sem problemas.', 'n' => 'Domingos Kiala', 'l' => 'Luanda, Angola', 'in' => 'DK', 'c' => '#2563eb'],
                                ['q' => 'Envio para a minha irmã em Benguela todos os meses. A taxa aparece clara antes de eu confirmar, sem surpresas no fim. Recomendo a toda a gente da diáspora.', 'n' => 'Cláudia Bumba', 'l' => 'São Paulo, Brasil · envia para Benguela', 'in' => 'CB', 'c' => '#ea580c'],
                                ['q' => 'Recebi o pagamento mesmo num domingo. E o melhor: falei com uma pessoa de verdade no WhatsApp, não com um robô. Isso para nós vale ouro.', 'n' => 'Nelson Quitumba', 'l' => 'Benguela, Angola', 'in' => 'NQ', 'c' => '#7c3aed'],
                                ['q' => 'Uso USDC e nunca foi tão simples chegar a Angola. Tudo transparente, do primeiro clique até a confirmação. Mudou a forma como ajudo a minha família.', 'n' => 'Sandra Mendes', 'l' => 'Roterdão, Países Baixos', 'in' => 'SM', 'c' => '#0d9488'],
                            ];
                        @endphp
                        @foreach($testimonials as $t)
                        <div class="ks-tslide">
                            <div class="ks-tcard">
                                <div class="ks-tstars">★★★★★</div>
                                <p class="ks-tquote">{{ $t['q'] }}”</p>
                                <div class="ks-tperson">
                                    <div class="ks-tavatar" style="background:{{ $t['c'] }};">{{ $t['in'] }}</div>
                                    <div>
                                        <div class="ks-tname">{{ $t['n'] }}</div>
                                        <div class="ks-tloc">{{ $t['l'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="ks-tcar-controls">
                    <button class="ks-tcar-arrow" @click="prev()" aria-label="Anterior">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div class="ks-tcar-dots">
                        <template x-for="k in n" :key="k">
                            <button class="ks-tdot" :class="{ active: i === k - 1 }" @click="go(k - 1)" :aria-label="`Testemunho ${k}`"></button>
                        </template>
                    </div>
                    <button class="ks-tcar-arrow" @click="next()" aria-label="Seguinte">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ FAQ ============ --}}
    <section class="ks-section" id="faq" style="background: var(--ks-gray-50);">
        <div class="ks-faq-wrap">
            <div style="text-align:center; margin-bottom: 2.5rem;">
                <span class="ks-section-label">Perguntas Frequentes</span>
                <h2 class="ks-section-title ks-reveal">Tudo o que precisas saber</h2>
            </div>

            <details class="ks-faq-item">
                <summary class="ks-faq-q">De que países posso enviar?</summary>
                <div class="ks-faq-a">Aceitamos transferências de qualquer país do mundo. As moedas suportadas são Euro (EUR) de toda a zona Euro, Real Brasileiro (BRL) do Brasil, e USDT/USDC via blockchain (sem fronteiras geográficas).</div>
            </details>

            <details class="ks-faq-item">
                <summary class="ks-faq-q">Quanto tempo demora a receber os Kwanzas?</summary>
                <div class="ks-faq-a">Em dias úteis, após recebermos e validarmos o comprovativo de pagamento, os Kwanzas são creditados no IBAN angolano registado em até 4 horas. Operações iniciadas antes das 14h são concluídas no mesmo dia.</div>
            </details>

            <details class="ks-faq-item">
                <summary class="ks-faq-q">Posso enviar de uma conta de outra pessoa?</summary>
                <div class="ks-faq-a">Não. Por segurança e conformidade AML (Anti-Money Laundering), o nome do titular da conta de origem deve corresponder exatamente ao teu BI registado na KwanzaSafe. Transferências de terceiros são automaticamente recusadas.</div>
            </details>

            <details class="ks-faq-item">
                <summary class="ks-faq-q">Qual o valor mínimo por operação?</summary>
                <div class="ks-faq-a">O mínimo é 10 unidades da moeda de origem (10 EUR, 10 BRL ou 10 USDT). Para valores acima de 5.000 EUR (ou equivalente) pode ser pedida documentação adicional por exigência regulatória.</div>
            </details>

            <details class="ks-faq-item">
                <summary class="ks-faq-q">Que redes blockchain aceitam para USDT e USDC?</summary>
                <div class="ks-faq-a">Aceitamos as principais redes: TRC20 (TRON — mais barato), BEP20 (Binance Smart Chain) e ERC20 (Ethereum). Recomendamos TRC20 pela taxa de rede muito baixa.</div>
            </details>

            <details class="ks-faq-item">
                <summary class="ks-faq-q">Como é calculada a taxa de câmbio?</summary>
                <div class="ks-faq-a">As taxas são revistas ao longo do dia com base no mercado internacional e oficial de Angola. A taxa que vês na calculadora é a taxa que será aplicada à tua operação, sem surpresas.</div>
            </details>

            <details class="ks-faq-item">
                <summary class="ks-faq-q">O que acontece se errar o IBAN de destino?</summary>
                <div class="ks-faq-a">Antes de cada transação verificamos contigo o IBAN de destino. Se mesmo assim houver erro, entra em contacto imediato via chat ou WhatsApp — bloquearemos a operação se ainda for possível.</div>
            </details>
        </div>
    </section>

    {{-- ============ CTA FINAL ============ --}}
    <section class="ks-cta-section">
        <h2 class="ks-cta-title">Pronto para enviar dinheiro para Angola?</h2>
        <p class="ks-cta-sub">Cria a tua conta grátis em 2 minutos.</p>
        @auth
            <a href="{{ url('/dashboard') }}" class="ks-cta-btn">
                Ir para o meu painel
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        @else
            <a href="{{ route('register') }}" class="ks-cta-btn">
                Criar conta grátis
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        @endauth
    </section>

    {{-- ============ FOOTER ============ --}}

<x-slot:scripts>
<script>
        function heroCalc() {
            return {
                rates: @json($rates->pluck('rate', 'currency_from')),
                currency: '{{ $rates->first()?->currency_from ?? 'EUR' }}',
                amountSent: 100,
                rate: {{ $rates->first()?->rate ?? 850 }},
                amountReceived: {{ ($rates->first()?->rate ?? 850) * 100 }},

                init() {
                    this.recalc();
                },

                recalc() {
                    this.rate = this.rates[this.currency] || 0;
                    const v = parseFloat(this.amountSent);
                    this.amountReceived = (!isNaN(v) && v > 0) ? v * this.rate : 0;
                },

                formatKz(val) {
                    if (!val && val !== 0) return '0';
                    return parseFloat(val).toLocaleString('pt-PT', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                },
            };
        }

        function ksTestimonials() {
            return {
                i: 0,
                n: 5,
                timer: null,
                init() { this.start(); },
                start() { this.stop(); this.timer = setInterval(() => this.next(), 6000); },
                stop() { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
                next() { this.i = (this.i + 1) % this.n; },
                prev() { this.i = (this.i - 1 + this.n) % this.n; },
                go(k) { this.i = k; this.start(); },
            };
        }

        // Carrossel do hero (5 banners, auto-avanço + pausa no hover)
        function ksBanners() {
            return {
                i: 0,
                n: 5,
                timer: null,
                init() { this.start(); },
                start() { this.stop(); this.timer = setInterval(() => this.next(), 5000); },
                stop() { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
                next() { this.i = (this.i + 1) % this.n; },
                prev() { this.i = (this.i - 1 + this.n) % this.n; },
                go(k) { this.i = k; this.start(); },
            };
        }
</script>
</x-slot:scripts>

</x-public-layout>
