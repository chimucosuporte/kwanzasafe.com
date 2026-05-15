<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- ============ SEO PRIMARY ============ --}}
    <title>KwanzaSafe — Envia Euros, Reais e USDC para Angola com Segurança</title>
    <meta name="description" content="Carteira internacional para remessas seguras para Angola. Converte Euros (EUR), Reais Brasileiros (BRL) e USDC em Kwanzas (AOA) com rapidez e total confiança.">
    <meta name="keywords" content="enviar dinheiro para Angola, remessa Angola, câmbio Kwanza, EUR para AOA, BRL para Kwanza, USDC Angola, carteira internacional, remessa segura, cambio online Angola">
    <meta name="author" content="KwanzaSafe">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://kwanzasafe.com/">

    {{-- ============ OPEN GRAPH (Facebook, LinkedIn, WhatsApp) ============ --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_AO">
    <meta property="og:url" content="https://kwanzasafe.com/">
    <meta property="og:site_name" content="KwanzaSafe">
    <meta property="og:title" content="KwanzaSafe — Envia Euros, Reais e USDC para Angola">
    <meta property="og:description" content="Conversão segura de Euros, Reais e USDC para Kwanzas — do mundo para Angola, com rapidez e total confiança.">
    <meta property="og:image" content="{{ asset('assets/images/logos/logo1.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    {{-- ============ TWITTER CARD ============ --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="KwanzaSafe — Remessas Internacionais para Angola">
    <meta name="twitter:description" content="Envia Euros, Reais e USDC para Angola de forma segura e rápida. Conversão transparente em Kwanzas.">
    <meta name="twitter:image" content="{{ asset('assets/images/logos/logo1.png') }}">

    {{-- ============ SCHEMA.ORG / JSON-LD ============ --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FinancialService",
      "name": "KwanzaSafe",
      "description": "Carteira internacional para remessas seguras do mundo para Angola. Converte Euros, Reais e USDC em Kwanzas.",
      "url": "https://kwanzasafe.com",
      "logo": "{{ asset('assets/images/logos/logo1.png') }}",
      "image": "{{ asset('assets/images/logos/logo1.png') }}",
      "telephone": "+55-11-93357-9009",
      "priceRange": "$",
      "address": {
        "@type": "PostalAddress",
        "addressCountry": "AO",
        "addressLocality": "Angola"
      },
      "areaServed": {
        "@type": "Country",
        "name": "Angola"
      },
      "serviceType": [
        "Remessa Internacional",
        "Câmbio de Divisas",
        "Conversão de Criptomoedas",
        "EUR para AOA",
        "BRL para AOA",
        "USDC para AOA"
      ],
      "sameAs": [
        "https://wa.me/5511933579009"
      ]
    }
    </script>

    {{-- ============ FAVICON ============ --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logos/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logos/logo1.png') }}">

    {{-- ============ FONTES ============ --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@500;600;700;800&family=DM+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    {{-- ============ TAILWIND + ALPINE ============ --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

        /* ============ HEADER ============ */
        .ks-header { position: sticky; top: 0; z-index: 50; background: rgba(255,255,255,0.95); backdrop-filter: blur(12px); border-bottom: 1px solid var(--ks-gray-200); }
        .ks-header-inner { max-width: 1200px; margin: 0 auto; padding: 0.875rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
        .ks-logo-link { display: flex; align-items: center; gap: 0.625rem; text-decoration: none; }
        .ks-logo-img { height: 36px; width: auto; }
        .ks-logo-img.mobile { display: none; }
        .ks-logo-img.tablet { display: none; }
        .ks-nav { display: flex; gap: 2rem; align-items: center; }
        .ks-nav a { color: var(--ks-gray-700); font-weight: 500; font-size: 0.9rem; text-decoration: none; transition: color 0.15s; }
        .ks-nav a:hover { color: var(--ks-green); }
        .ks-nav-cta { background: var(--ks-green); color: var(--ks-white) !important; padding: 0.5rem 1.125rem; border-radius: 999px; font-weight: 700 !important; transition: background 0.15s; }
        .ks-nav-cta:hover { background: var(--ks-green-dark); color: var(--ks-white) !important; }
        .ks-mobile-toggle { display: none; background: none; border: none; cursor: pointer; }

        @media(max-width: 768px) {
            .ks-logo-img { display: none; }
            .ks-logo-img.mobile { display: block; height: 32px; }
            .ks-nav { display: none; }
            .ks-mobile-toggle { display: block; }
            .ks-nav.open { display: flex; position: absolute; top: 100%; left: 0; right: 0; background: white; flex-direction: column; padding: 1.5rem; box-shadow: 0 8px 24px rgba(0,0,0,0.08); border-top: 1px solid var(--ks-gray-200); }
        }
        @media(min-width: 769px) and (max-width: 1024px) {
            .ks-logo-img { display: none; }
            .ks-logo-img.tablet { display: block; height: 34px; }
        }

        /* ============ HERO ============ */
        .ks-hero { background: linear-gradient(180deg, #ffffff 0%, #f0faf4 100%); padding: 4rem 1.5rem 5rem; position: relative; overflow: hidden; }
        .ks-hero::before { content: ''; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(0,157,68,0.08) 0%, transparent 70%); pointer-events: none; }
        .ks-hero-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1.1fr 1fr; gap: 3rem; align-items: center; position: relative; z-index: 1; }

        .ks-hero-badge { display: inline-flex; align-items: center; gap: 0.5rem; background: var(--ks-green-light); color: var(--ks-green-dark); padding: 0.375rem 0.875rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 1.25rem; border: 1px solid rgba(0,157,68,0.2); }
        .ks-hero-badge::before { content: ''; width: 6px; height: 6px; background: var(--ks-green); border-radius: 50%; box-shadow: 0 0 8px var(--ks-green); animation: pulse-dot 2s infinite; }
        @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:0.4} }

        .ks-hero-title { font-family: 'Syne', sans-serif; font-size: 3.5rem; font-weight: 800; letter-spacing: -0.03em; line-height: 1.05; margin: 0 0 1.125rem; color: var(--ks-black); }
        .ks-hero-title .highlight { color: var(--ks-green); }
        .ks-hero-sub { font-size: 1.125rem; line-height: 1.6; color: var(--ks-gray-700); margin: 0 0 2rem; max-width: 540px; }
        .ks-hero-ctas { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 2rem; }
        .ks-btn-primary { background: var(--ks-black); color: var(--ks-white); padding: 0.875rem 1.75rem; border-radius: 999px; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 0.95rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s; border: 2px solid var(--ks-black); }
        .ks-btn-primary:hover { background: var(--ks-green); border-color: var(--ks-green); transform: translateY(-1px); }
        .ks-btn-ghost { background: transparent; color: var(--ks-black); padding: 0.875rem 1.75rem; border-radius: 999px; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 0.95rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; border: 2px solid var(--ks-black); transition: all 0.2s; }
        .ks-btn-ghost:hover { background: var(--ks-black); color: var(--ks-white); }

        .ks-hero-trust { display: flex; gap: 1.5rem; flex-wrap: wrap; padding-top: 1.5rem; border-top: 1px solid var(--ks-gray-200); }
        .ks-trust-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--ks-gray-700); font-weight: 500; }
        .ks-trust-item svg { color: var(--ks-green); flex-shrink: 0; }

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
        .ks-field-group:focus-within { border-color: var(--ks-green); background: var(--ks-white); }
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
        .ks-section-label { display: inline-block; font-size: 0.75rem; font-weight: 700; color: var(--ks-green); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.75rem; }
        .ks-section-title { font-family: 'Syne', sans-serif; font-size: 2.25rem; font-weight: 800; letter-spacing: -0.02em; line-height: 1.1; margin: 0 0 0.875rem; color: var(--ks-black); }
        .ks-section-sub { font-size: 1rem; color: var(--ks-gray-700); line-height: 1.6; max-width: 640px; margin: 0 0 2.5rem; }

        .ks-currencies { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
        .ks-currency-card { background: var(--ks-white); border: 1px solid var(--ks-gray-200); border-radius: 20px; padding: 2rem; text-align: center; transition: all 0.25s; cursor: default; }
        .ks-currency-card:hover { border-color: var(--ks-green); transform: translateY(-4px); box-shadow: 0 16px 36px rgba(0,0,0,0.08); }
        .ks-currency-flag { font-size: 3rem; line-height: 1; margin-bottom: 0.75rem; display: block; }
        .ks-currency-name { font-family: 'Syne', sans-serif; font-size: 1.25rem; font-weight: 800; color: var(--ks-black); }
        .ks-currency-code { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 700; color: var(--ks-green); letter-spacing: 0.1em; margin-top: 0.25rem; }
        .ks-currency-desc { font-size: 0.85rem; color: var(--ks-gray-500); margin-top: 0.75rem; line-height: 1.5; }

        @media(max-width: 768px) { .ks-currencies { grid-template-columns: 1fr; } .ks-section { padding: 3rem 1rem; } .ks-section-title { font-size: 1.75rem; } }

        /* ============ HOW IT WORKS ============ */
        .ks-how-section { background: var(--ks-black); color: var(--ks-white); padding: 5rem 1.5rem; }
        .ks-how-inner { max-width: 1200px; margin: 0 auto; }
        .ks-how-section .ks-section-label { color: var(--ks-green); }
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
        .ks-why-card:hover { background: var(--ks-white); border-color: var(--ks-green); box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
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
        .ks-faq-q::after { content: '+'; font-size: 1.5rem; color: var(--ks-green); font-weight: 400; transition: transform 0.2s; }
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

        /* ============ FOOTER ============ */
        .ks-footer { background: var(--ks-black); color: var(--ks-white); padding: 4rem 1.5rem 2rem; }
        .ks-footer-inner { max-width: 1200px; margin: 0 auto; }
        .ks-footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem; margin-bottom: 3rem; }
        .ks-footer-brand { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
        .ks-footer-brand img { height: 36px; filter: brightness(0) invert(1); }
        .ks-footer-brand-name { font-family: 'Syne', sans-serif; font-size: 1.25rem; font-weight: 800; }
        .ks-footer-desc { font-size: 0.875rem; color: #a3a3a3; line-height: 1.6; margin: 0 0 1.25rem; max-width: 320px; }
        .ks-footer-wa { display: inline-flex; align-items: center; gap: 0.5rem; background: #25d366; color: var(--ks-white); padding: 0.5rem 1rem; border-radius: 999px; font-size: 0.8rem; font-weight: 700; text-decoration: none; }
        .ks-footer-wa:hover { background: #1eb555; }
        .ks-footer h4 { font-family: 'Syne', sans-serif; font-size: 0.75rem; font-weight: 800; color: var(--ks-green); text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 1rem; }
        .ks-footer ul { list-style: none; padding: 0; margin: 0; }
        .ks-footer ul li { margin-bottom: 0.625rem; }
        .ks-footer ul a { color: #a3a3a3; font-size: 0.875rem; text-decoration: none; transition: color 0.15s; }
        .ks-footer ul a:hover { color: var(--ks-white); }
        .ks-footer-bottom { padding-top: 2rem; border-top: 1px solid #262626; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .ks-footer-copy { font-size: 0.75rem; color: #737373; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em; }
        .ks-footer-social { display: flex; gap: 0.75rem; }
        .ks-footer-social a { width: 32px; height: 32px; background: #262626; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--ks-white); text-decoration: none; transition: background 0.15s; }
        .ks-footer-social a:hover { background: var(--ks-green); }

        @media(max-width: 968px) { .ks-footer-grid { grid-template-columns: 1fr 1fr; } }
        @media(max-width: 480px) { .ks-footer-grid { grid-template-columns: 1fr; } .ks-footer-bottom { flex-direction: column; text-align: center; } }

        /* ============ WHATSAPP FLUTUANTE ============ */
        .ks-wa-float { position: fixed; bottom: 24px; right: 24px; background: #25d366; color: white; width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 8px 24px rgba(37,211,102,0.4); z-index: 100; transition: transform 0.2s; }
        .ks-wa-float:hover { transform: scale(1.1); background: #1eb555; }
        .ks-wa-float::before { content: ''; position: absolute; inset: -4px; border-radius: 50%; border: 3px solid #25d366; opacity: 0.4; animation: wa-pulse 2s infinite; }
        @keyframes wa-pulse { 0%,100%{opacity:0.4;transform:scale(1)} 50%{opacity:0;transform:scale(1.3)} }
    </style>
</head>
<body>

    {{-- ============ HEADER ============ --}}
    <header class="ks-header" x-data="{ mobileMenu: false }">
        <div class="ks-header-inner">
            <a href="{{ url('/') }}" class="ks-logo-link" aria-label="KwanzaSafe — página inicial">
                <img src="{{ asset('assets/images/logos/logo.png') }}" alt="KwanzaSafe" class="ks-logo-img mobile">
                <img src="{{ asset('assets/images/logos/logo2.png') }}" alt="KwanzaSafe" class="ks-logo-img tablet">
                <img src="{{ asset('assets/images/logos/logo1.png') }}" alt="KwanzaSafe" class="ks-logo-img">
            </a>

            <nav class="ks-nav" :class="{'open': mobileMenu}" aria-label="Navegação principal">
                <a href="#como-funciona">Como Funciona</a>
                <a href="#moedas">Moedas</a>
                <a href="#faq">FAQ</a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="ks-nav-cta">Meu Painel</a>
                @else
                    <a href="{{ route('login') }}">Entrar</a>
                    <a href="{{ route('register') }}" class="ks-nav-cta">Criar Conta</a>
                @endauth
            </nav>

            <button class="ks-mobile-toggle" @click="mobileMenu = !mobileMenu" aria-label="Abrir menu">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/></svg>
            </button>
        </div>
    </header>

    {{-- ============ HERO ============ --}}
    <section class="ks-hero">
        <div class="ks-hero-inner">
            <div>
                <span class="ks-hero-badge">🌍 Plataforma Internacional</span>

                <h1 class="ks-hero-title">
                    Envia <span class="highlight">Euros, Reais e USDC</span> para Angola com segurança.
                </h1>

                <p class="ks-hero-sub">
                    Conversão segura de Euros, Reais e USDC para Kwanzas — do mundo para Angola, com rapidez e total confiança. Soluções modernas para pagamentos internacionais, com segurança, eficiência e suporte profissional.
                </p>

                <div class="ks-hero-ctas">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="ks-btn-primary">
                            Meu Painel
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="ks-btn-primary">
                            Começar agora
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                        <a href="#como-funciona" class="ks-btn-ghost">Como funciona</a>
                    @endauth
                </div>

                <div class="ks-hero-trust">
                    <div class="ks-trust-item">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Verificação KYC
                    </div>
                    <div class="ks-trust-item">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Anti-fraude AML
                    </div>
                    <div class="ks-trust-item">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Até 4 horas
                    </div>
                </div>
            </div>

            {{-- CALCULADORA --}}
            <div class="ks-calc-card" x-data="heroCalc()" x-init="init()">
                <div class="ks-calc-header">
                    <h2 class="ks-calc-title">Simular Câmbio</h2>
                    <span class="ks-calc-live">Tempo Real</span>
                </div>

                <div class="ks-field">
                    <div class="ks-field-label">
                        <span>Envias</span>
                        <span style="color:var(--ks-green);font-weight:700;" x-show="amountSent >= 10">✓ Válido</span>
                    </div>
                    <div class="ks-field-group">
                        <select x-model="currency" @change="recalc()" aria-label="Moeda de origem">
                            @foreach($rates as $r)
                                @php
                                    $flags = ['EUR'=>'🇪🇺','BRL'=>'🇧🇷','USDT'=>'₮'];
                                    $flag  = $flags[$r->currency_from] ?? '💱';
                                @endphp
                                <option value="{{ $r->currency_from }}">{{ $flag }} {{ $r->currency_from }}</option>
                            @endforeach
                        </select>
                        <input type="number" x-model="amountSent" @input="recalc()" placeholder="100" min="10" step="0.01" inputmode="decimal" aria-label="Valor a enviar">
                    </div>
                </div>

                <div class="ks-arrow-swap">
                    <div class="ks-arrow-swap-btn">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M19 12l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>

                <div class="ks-field">
                    <div class="ks-field-label">
                        <span>Recebes em Angola</span>
                    </div>
                    <div class="ks-field-group" style="background: var(--ks-green-pale); border-color: rgba(0,157,68,0.3);">
                        <div style="padding: 0.875rem; font-family:'Syne',sans-serif; font-weight: 700; font-size: 0.95rem; border-right: 2px solid rgba(0,157,68,0.15); min-width: 95px;">🇦🇴 AOA</div>
                        <div style="flex:1; padding: 0.875rem; text-align: right; font-family:'Syne',sans-serif; font-weight: 800; font-size: 1.25rem; color: var(--ks-green-dark);" x-text="amountReceived > 0 ? formatKz(amountReceived) : '—'"></div>
                    </div>
                </div>

                <div class="ks-rate-info">
                    <span class="ks-rate-info-label">Taxa de câmbio</span>
                    <span class="ks-rate-info-value">1 <span x-text="currency"></span> = <span x-text="formatKz(rate)"></span> Kz</span>
                </div>

                @auth
                    <a href="{{ url('/dashboard') }}" class="ks-calc-btn" style="text-decoration:none;">
                        Iniciar Transação
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="ks-calc-btn" style="text-decoration:none;">
                        Criar Conta e Enviar
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
            <h2 class="ks-section-title">De qualquer parte do mundo para Angola</h2>
            <p class="ks-section-sub" style="margin-left:auto;margin-right:auto;">
                Aceitamos as 3 principais moedas usadas pela comunidade angolana no exterior — com conversão transparente em Kwanzas.
            </p>
        </div>

        <div class="ks-currencies">
            <div class="ks-currency-card">
                <span class="ks-currency-flag" role="img" aria-label="União Europeia">🇪🇺</span>
                <h3 class="ks-currency-name">Euro</h3>
                <div class="ks-currency-code">EUR</div>
                <p class="ks-currency-desc">Envia de Portugal, Espanha, França, Alemanha e restante zona Euro.</p>
            </div>
            <div class="ks-currency-card">
                <span class="ks-currency-flag" role="img" aria-label="Brasil">🇧🇷</span>
                <h3 class="ks-currency-name">Real Brasileiro</h3>
                <div class="ks-currency-code">BRL</div>
                <p class="ks-currency-desc">Transferência PIX ou bancária do Brasil com chegada rápida.</p>
            </div>
            <div class="ks-currency-card">
                <span class="ks-currency-flag" role="img" aria-label="Stablecoin">₮</span>
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
                <h2 class="ks-section-title">Como funciona</h2>
                <p class="ks-section-sub" style="margin-left:auto;margin-right:auto;">
                    Três passos e o destinatário tem os Kwanzas na conta — sem burocracia, sem taxas surpresa.
                </p>
            </div>

            <div class="ks-steps">
                <div class="ks-step">
                    <div class="ks-step-number">1</div>
                    <h3 class="ks-step-title">Regista-te e verifica</h3>
                    <p class="ks-step-desc">Cria a tua conta, envia documento de identidade e comprova titularidade. Verificação em até 24 horas.</p>
                </div>
                <div class="ks-step">
                    <div class="ks-step-number">2</div>
                    <h3 class="ks-step-title">Envia a moeda</h3>
                    <p class="ks-step-desc">Simula na calculadora, regista a transação e envia EUR, BRL ou USDT para a conta que te indicamos.</p>
                </div>
                <div class="ks-step">
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
            <h2 class="ks-section-title">A tua segurança em primeiro lugar</h2>
            <p class="ks-section-sub" style="margin-left:auto;margin-right:auto;">
                Construímos uma plataforma à altura das exigências internacionais — com processos robustos de verificação e proteção contra fraudes.
            </p>
        </div>

        <div class="ks-why">
            <div class="ks-why-card">
                <div class="ks-why-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="ks-why-title">Anti-Fraude AML</h3>
                <p class="ks-why-desc">O nome na conta de origem tem de coincidir com o teu BI — nenhum pagamento de terceiros é aceite.</p>
            </div>
            <div class="ks-why-card">
                <div class="ks-why-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="ks-why-title">Rápido</h3>
                <p class="ks-why-desc">Processamos em até 4 horas úteis. Transferências USDT chegam em minutos.</p>
            </div>
            <div class="ks-why-card">
                <div class="ks-why-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="ks-why-title">Transparente</h3>
                <p class="ks-why-desc">A taxa que vês na calculadora é a taxa final. Sem comissões ocultas, sem letra pequena.</p>
            </div>
            <div class="ks-why-card">
                <div class="ks-why-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="ks-why-title">Suporte Profissional</h3>
                <p class="ks-why-desc">Equipa dedicada via chat na plataforma e WhatsApp. Resposta em menos de 2 horas.</p>
            </div>
        </div>
    </section>

    {{-- ============ FAQ ============ --}}
    <section class="ks-section" id="faq" style="background: var(--ks-gray-50);">
        <div class="ks-faq-wrap">
            <div style="text-align:center; margin-bottom: 2.5rem;">
                <span class="ks-section-label">Perguntas Frequentes</span>
                <h2 class="ks-section-title">Tudo o que precisas saber</h2>
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
    <footer class="ks-footer">
        <div class="ks-footer-inner">
            <div class="ks-footer-grid">
                <div>
                    <div class="ks-footer-brand">
                        <img src="{{ asset('assets/images/logos/logo1.png') }}" alt="KwanzaSafe">
                        <span class="ks-footer-brand-name">KwanzaSafe</span>
                    </div>
                    <p class="ks-footer-desc">
                        Carteira internacional para remessas seguras do mundo para Angola. Conversão transparente de EUR, BRL e USDT em Kwanzas.
                    </p>
                    <a href="https://wa.me/5511933579009?text=Olá%2C+quero+saber+mais+sobre+o+KwanzaSafe." target="_blank" rel="noopener" class="ks-footer-wa">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        +55 11 93357-9009
                    </a>
                </div>

                <div>
                    <h4>Plataforma</h4>
                    <ul>
                        <li><a href="#como-funciona">Como Funciona</a></li>
                        <li><a href="#moedas">Moedas Aceites</a></li>
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="{{ route('register') }}">Criar Conta</a></li>
                    </ul>
                </div>

                <div>
                    <h4>Suporte</h4>
                    <ul>
                        <li><a href="https://wa.me/5511933579009" target="_blank" rel="noopener">WhatsApp</a></li>
                        <li><a href="mailto:suporte@kwanzasafe.com">Email</a></li>
                        <li><a href="{{ route('login') }}">Área Cliente</a></li>
                    </ul>
                </div>

                <div>
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="{{ route('terms') }}">Termos de Uso</a></li>
                        <li><a href="{{ route('privacy') }}">Política de Privacidade</a></li>
                        <li><a href="#faq">Conformidade AML</a></li>
                    </ul>
                </div>
            </div>

            <div class="ks-footer-bottom">
                <div class="ks-footer-copy">
                    © 2026 KWANZASAFE · BNA Compliant · All Rights Reserved
                </div>
                <div class="ks-footer-social">
                    <a href="https://wa.me/5511933579009" target="_blank" rel="noopener" aria-label="WhatsApp">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    {{-- ============ WHATSAPP FLUTUANTE ============ --}}
    <a href="https://wa.me/5511933579009?text=Olá%2C+preciso+de+ajuda+com+o+KwanzaSafe." target="_blank" rel="noopener" class="ks-wa-float" aria-label="Contactar via WhatsApp">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
    </a>

    <script>
        // Calculadora em tempo real
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
    </script>

</body>
</html>