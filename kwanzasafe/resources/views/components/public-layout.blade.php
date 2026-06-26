@props([
    'title' => 'KwanzaSafe — Envia Euros, Reais e USDC para Angola com Segurança',
    'description' => 'Carteira internacional para remessas seguras para Angola. Converte Euros (EUR), Reais (BRL) e USDC em Kwanzas (AOA) com rapidez e total confiança.',
    'canonical' => null,
    'ogImage' => null,
    'robots' => 'index, follow',
])
@php
    $url = $canonical ?? url()->current();
    $og  = $ogImage ?? asset('assets/images/banners/banner-1.png');
@endphp
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="{{ $robots }}">
    <meta name="author" content="KwanzaSafe">
    <link rel="canonical" href="{{ $url }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_AO">
    <meta property="og:site_name" content="KwanzaSafe">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:image" content="{{ $og }}">
    <meta property="og:image:width" content="1600">
    <meta property="og:image:height" content="520">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $og }}">

    @include('partials.public-favicons')

    {{-- JSON-LD global: Organization + WebSite --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                'name' => 'KwanzaSafe',
                'url' => url('/'),
                'logo' => asset('assets/images/logos/logo-isotipo.png'),
                'description' => 'Carteira internacional para remessas seguras do mundo para Angola.',
                'areaServed' => ['@type' => 'Country', 'name' => 'Angola'],
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'telephone' => '+55-11-93357-9009',
                    'contactType' => 'customer support',
                    'availableLanguage' => ['Portuguese'],
                ],
                'sameAs' => ['https://wa.me/5511933579009'],
            ],
            [
                '@type' => 'WebSite',
                'name' => 'KwanzaSafe',
                'url' => url('/'),
                'inLanguage' => 'pt',
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    {{-- Fontes --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @include('partials.public-css')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{ $head ?? '' }}
    @stack('head')
</head>
<body>
    <a href="#conteudo" class="ks-skip">Saltar para o conteúdo</a>

    <x-public.header />

    <main id="conteudo">
        {{ $slot }}
    </main>

    <x-public.footer />

    <a href="https://wa.me/5511933579009?text=Olá%2C+preciso+de+ajuda+com+o+KwanzaSafe." target="_blank" rel="noopener" class="ks-wa-float" aria-label="Contactar via WhatsApp">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
    </a>

    {{-- Reveal ao scroll + contadores (partilhado por todas as páginas públicas) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reveals = document.querySelectorAll('.ks-reveal');
            if (!('IntersectionObserver' in window)) { reveals.forEach(function (el) { el.classList.add('ks-in'); }); }
            else {
                const io = new IntersectionObserver(function (es) {
                    es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('ks-in'); io.unobserve(e.target); } });
                }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
                reveals.forEach(function (el) { io.observe(el); });
            }
            const counters = document.querySelectorAll('[data-count]');
            if ('IntersectionObserver' in window) {
                const cio = new IntersectionObserver(function (es) {
                    es.forEach(function (e) {
                        if (!e.isIntersecting) return;
                        const el = e.target, target = parseFloat(el.getAttribute('data-count')), suffix = el.getAttribute('data-suffix') || '', t0 = performance.now();
                        (function tick(now) {
                            const p = Math.min((now - t0) / 1400, 1), eased = 1 - Math.pow(1 - p, 3), v = target * eased;
                            el.textContent = (target % 1 === 0 ? Math.round(v).toLocaleString('pt-PT') : v.toFixed(1).replace('.', ',')) + suffix;
                            if (p < 1) requestAnimationFrame(tick);
                        })(t0);
                        cio.unobserve(el);
                    });
                }, { threshold: 0.4 });
                counters.forEach(function (el) { cio.observe(el); });
            }
        });
    </script>
    {{ $scripts ?? '' }}
    @stack('scripts')
</body>
</html>
