<!DOCTYPE html>
<html lang="pt-AO" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title>Política de Privacidade | KwanzaSafe — Tecnologia que Transforma</title>
    <meta name="description" content="Saiba como a KwanzaSafe protege os seus dados pessoais e financeiros em Angola. Transparência total sob protocolos de segurança bancária.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="http://127.0.0.1:8000/privacidade" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { ks: { emerald: '#064e3b', light: '#f8fafc' } },
                    fontFamily: { sans: ['Inter', 'sans-serif'], serif: ['Playfair Display', 'serif'] }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .no-scroll { overflow: hidden !important; height: 100vh !important; }
        .legal-section { border-left: 2px solid #e2e8f0; transition: all 0.4s ease; }
        .legal-section:hover { border-left-color: #064e3b; background: #f8fafc; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased" x-data="{ mobileMenu: false }" :class="{ 'no-scroll': mobileMenu }">

    <nav class="fixed w-full h-24 z-[100] bg-white/95 backdrop-blur-md border-b border-slate-100 flex items-center no-print">
        <div class="max-w-7xl mx-auto px-6 w-full flex justify-between items-center">
            <div class="flex items-center">
                <a href="/">
                    <img src="{{ asset('assets/images/logos/logo.png') }}" class="h-12 w-auto block sm:hidden">
                    <img src="{{ asset('assets/images/logos/logo1.png') }}" class="h-16 w-auto hidden sm:block md:hidden">
                    <img src="{{ asset('assets/images/logos/logo2.PNG') }}" class="h-24 w-auto hidden md:block">
                </a>
            </div>
            <div class="hidden md:flex items-center gap-10">
                <a href="/" class="text-sm font-bold text-slate-500 hover:text-ks-emerald transition">Início</a>
                <a href="{{ route('terms') }}" class="text-sm font-bold text-slate-500 hover:text-ks-emerald transition">Termos</a>
                <a href="{{ route('register') }}" class="bg-ks-emerald text-white px-8 py-3.5 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-emerald-900/10 transition active:scale-95">Criar Conta</a>
            </div>
            <button class="md:hidden p-3 bg-slate-50 rounded-2xl" @click="mobileMenu = true">
                <svg class="w-8 h-8 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
        </div>

        <div x-show="mobileMenu" x-cloak class="fixed inset-0 z-[2000] bg-white flex flex-col w-screen h-screen overflow-hidden">
            <div class="h-24 px-8 flex justify-between items-center border-b border-slate-50">
                <img src="{{ asset('assets/images/logos/logo.png') }}" class="h-10 w-auto">
                <button @click="mobileMenu = false" class="p-4 bg-slate-100 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <nav class="flex-1 flex flex-col justify-center px-10 space-y-12">
                <a href="/" class="text-5xl font-black italic tracking-tighter">Início</a>
                <a href="{{ route('terms') }}" class="text-5xl font-black italic tracking-tighter">Termos</a>
                <a href="{{ route('register') }}" class="text-5xl font-black italic tracking-tighter text-ks-emerald">Registo</a>
            </nav>
        </div>
    </nav>

    <header class="pt-60 pb-24 bg-ks-light/50 border-b border-slate-50">
        <div class="max-w-4xl mx-auto px-6">
            <h1 class="font-serif text-5xl md:text-7xl font-black text-slate-900 tracking-tighter italic mb-8">
                Política de Privacidade
            </h1>
            <div class="flex items-center gap-6 no-print">
                <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">Vigência: Abril 2026</p>
                <button onclick="window.print()" class="text-ks-emerald font-black text-xs uppercase underline decoration-2 underline-offset-4">Imprimir Cópia</button>
            </div>
        </div>
    </header>

    <main class="py-24 bg-white min-h-screen">
        <div class="max-w-4xl mx-auto px-6 space-y-20">
            <p class="text-xl text-slate-600 leading-relaxed font-medium italic">A Kwanzasafe respeita a privacidade dos seus utilizadores. Este documento descreve as nossas práticas de gestão de dados com total transparência.</p>

            <section class="legal-section pl-8 py-4">
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-6 italic">1. Dados Coletados</h2>
                <p class="text-lg text-slate-500 leading-relaxed mb-6">Coletamos informações essenciais para a segurança e conformidade das transações:</p>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="bg-slate-50 p-6 rounded-3xl font-bold text-ks-emerald border border-emerald-100 flex items-center gap-3">
                        <span class="w-3 h-3 bg-ks-emerald rounded-full"></span> Nome Completo
                    </div>
                    <div class="bg-slate-50 p-6 rounded-3xl font-bold text-ks-emerald border border-emerald-100 flex items-center gap-3">
                        <span class="w-3 h-3 bg-ks-emerald rounded-full"></span> Número WhatsApp
                    </div>
                    <div class="bg-slate-50 p-6 rounded-3xl font-bold text-ks-emerald border border-emerald-100 flex items-center gap-3">
                        <span class="w-3 h-3 bg-ks-emerald rounded-full"></span> Dados de Pagamento
                    </div>
                </div>
            </section>

            <section class="legal-section pl-8 py-4">
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-6 italic">2. Uso dos Dados</h2>
                <p class="text-lg text-slate-500 leading-relaxed">Os dados são utilizados exclusivamente para as seguintes finalidades institucionais:</p>
                <ul class="mt-8 space-y-6">
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-ks-emerald text-white rounded-full flex items-center justify-center shrink-0 font-black">A</div>
                        <p class="text-slate-600 font-medium">Processamento e intermediação de transações de câmbio digital.</p>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-ks-emerald text-white rounded-full flex items-center justify-center shrink-0 font-black">B</div>
                        <p class="text-slate-600 font-medium">Suporte direto ao cliente e resolução de dúvidas operacionais.</p>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-ks-emerald text-white rounded-full flex items-center justify-center shrink-0 font-black">C</div>
                        <p class="text-slate-600 font-medium">Monitorização contra fraudes e garantia da segurança do sistema.</p>
                    </li>
                </ul>
            </section>

            <section class="legal-section pl-8 py-4">
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-6 italic">3. Proteção</h2>
                <div class="bg-ks-emerald p-10 rounded-[3rem] text-white italic text-lg leading-relaxed shadow-2xl">
                    Adotamos medidas técnicas de última geração para proteger os dados dos nossos utilizadores contra acessos não autorizados ou divulgação indevida. O seu capital de informação está seguro connosco.
                </div>
            </section>

            <section class="legal-section pl-8 py-4">
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-6 italic">4. Partilha</h2>
                <p class="text-lg text-slate-500 leading-relaxed">Não partilhamos dados com terceiros, exceto quando estritamente necessário para o processamento de pagamentos ou por exigência legal das autoridades angolanas.</p>
            </section>

            <section class="legal-section pl-8 py-4">
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-6 italic">5. Consentimento</h2>
                <p class="text-xl text-slate-900 font-black italic">Ao utilizar o site e os serviços da KwanzaSafe, o utilizador concorda integralmente com esta política de privacidade.</p>
            </section>
        </div>
    </main>

    <footer class="bg-slate-50 py-24 px-6 border-t border-slate-100 no-print">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-12 text-center md:text-left">
            <div>
                <img src="{{ asset('assets/images/logos/logo2.PNG') }}" class="h-16 w-auto mb-6 mx-auto md:mx-0">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em]">KWANZASAFE © 2026 — HUAMBO, ANGOLA</p>
            </div>
            <div class="flex gap-8 text-sm font-bold text-slate-500 uppercase tracking-widest">
                <a href="{{ route('terms') }}">Termos</a>
                <a href="https://wa.me/244931719207" class="text-green-600">Suporte WhatsApp</a>
            </div>
        </div>
    </footer>
</body>
</html>