<!DOCTYPE html>
<html lang="pt-AO" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title>{{ $title }} | KwanzaSafe — Tecnologia que Transforma</title>
    <meta name="description" content="Consulte os documentos legais da KwanzaSafe. Transparência e segurança em todas as operações de câmbio em Angola.">
    <meta name="robots" content="index, follow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ks: {
                            emerald: '#064e3b',
                            light: '#f0fdf4',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .no-scroll { overflow: hidden !important; height: 100vh !important; }
        .legal-card { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .legal-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(6, 78, 59, 0.05); }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased" x-data="{ mobileMenu: false }" :class="{ 'no-scroll': mobileMenu }">

    <nav class="fixed w-full h-24 z-[100] bg-white/95 backdrop-blur-md border-b border-slate-100 flex items-center">
        <div class="max-w-7xl mx-auto px-6 w-full flex justify-between items-center">
            
            <div class="flex items-center">
                <a href="/">
                    <img src="{{ asset('assets/images/logos/logo.png') }}" class="h-10 w-auto block sm:hidden">
                    <img src="{{ asset('assets/images/logos/logo1.png') }}" class="h-14 w-auto hidden sm:block md:hidden">
                    <img src="{{ asset('assets/images/logos/logo2.PNG') }}" class="h-24 w-auto hidden md:block">
                </a>
            </div>
            
            <div class="hidden md:flex items-center gap-10">
                <a href="/" class="text-sm font-bold text-slate-500 hover:text-ks-emerald transition">Início</a>
                <a href="/dashboard" class="text-sm font-bold text-slate-500 hover:text-ks-emerald transition">Dashboard</a>
                <a href="{{ route('register') }}" class="bg-ks-emerald text-white px-8 py-3.5 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-emerald-900/10 transition transform active:scale-95">
                    Criar Conta
                </a>
            </div>

            <button class="md:hidden p-3 bg-slate-50 rounded-2xl text-slate-900 focus:outline-none" @click="mobileMenu = true">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
        </div>

        <div x-show="mobileMenu" x-cloak class="fixed inset-0 z-[2000] bg-white flex flex-col w-screen h-screen overflow-hidden">
            <div class="h-24 px-8 flex justify-between items-center border-b border-slate-50">
                <img src="{{ asset('assets/images/logos/logo.png') }}" class="h-10 w-auto">
                <button @click="mobileMenu = false" class="p-4 bg-slate-100 rounded-full text-slate-900">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <nav class="flex-1 flex flex-col justify-center px-10 space-y-10">
                <a href="/" class="text-5xl font-black text-slate-900 tracking-tighter italic">Início</a>
                <a href="/login" class="text-5xl font-black text-slate-900 tracking-tighter italic">Entrar</a>
                <a href="/register" class="text-5xl font-black text-ks-emerald tracking-tighter italic underline decoration-4">Registo</a>
            </nav>
            <div class="p-10 text-center bg-slate-50"><p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">KwanzaSafe — Huambo 2026</p></div>
        </div>
    </nav>

    <header class="pt-56 pb-24 bg-ks-light/50 relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-96 h-96 bg-ks-emerald/5 rounded-full blur-[100px]"></div>
        <div class="max-w-5xl mx-auto px-6 text-center relative z-10">
            <span class="inline-block bg-white px-4 py-2 rounded-full text-[10px] font-black text-ks-emerald uppercase tracking-[0.3em] mb-6 shadow-sm border border-emerald-100">Documento Legal Oficial</span>
            <h1 class="text-5xl sm:text-7xl font-black text-slate-900 tracking-tighter leading-tight italic">
                {{ $title }}
            </h1>
            <p class="text-slate-500 font-medium text-lg mt-6">Rigor, Transparência e Segurança Bancária em Angola.</p>
        </div>
    </header>

    <main class="py-24 bg-white min-h-screen">
        <div class="max-w-4xl mx-auto px-6">
            
            <div class="mb-16 pb-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4 text-center sm:text-left">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Data de Vigência</p>
                    <p class="text-lg font-bold text-slate-900">19 de Abril, 2026</p>
                </div>
                <div class="flex gap-4">
                    <button onclick="window.print()" class="px-6 py-3 bg-slate-50 text-slate-600 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-100 transition">Imprimir Documento</button>
                </div>
            </div>

            <div class="space-y-16">
                
                @if($type == 'terms')
                    <section class="legal-card bg-white p-8 sm:p-12 rounded-[2.5rem] border border-slate-50 shadow-sm">
                        <h2 class="font-serif text-3xl font-bold text-ks-emerald mb-8 italic underline decoration-emerald-200 decoration-8 underline-offset-4">1. Objeto do Serviço</h2>
                        <p class="text-slate-600 leading-relaxed text-lg">A KwanzaSafe presta serviços de intermediação e processamento de pagamentos em moedas estrangeiras, nomeadamente Euro (EUR) e Real Brasileiro (BRL), facilitando transações para clientes em Angola. Não realizamos venda de criptomoedas.</p>
                    </section>

                    <section class="legal-card bg-white p-8 sm:p-12 rounded-[2.5rem] border border-slate-50 shadow-sm">
                        <h2 class="font-serif text-3xl font-bold text-ks-emerald mb-8 italic underline decoration-emerald-200 decoration-8 underline-offset-4">2. Identificação do Cliente</h2>
                        <p class="text-slate-600 leading-relaxed text-lg">O nome do titular da conta na plataforma KwanzaSafe deve coincidir obrigatoriamente com o nome da conta utilizada para pagamento em EUR ou BRL. Não aceitamos pagamentos realizados por terceiros.</p>
                    </section>

                    <section class="legal-card bg-white p-8 sm:p-12 rounded-[2.5rem] border border-slate-50 shadow-sm">
                        <h2 class="font-serif text-3xl font-bold text-ks-emerald mb-8 italic underline decoration-emerald-200 decoration-8 underline-offset-4">4. Política de Reembolso</h2>
                        <p class="text-slate-600 leading-relaxed text-lg italic">Devido à natureza dos serviços financeiros e à rapidez das transações, não realizamos reembolsos após a conclusão da operação. Antes de confirmar qualquer pagamento, o cliente deve verificar cuidadosamente todas as informações.</p>
                    </section>
                @else
                    <section class="legal-card bg-white p-8 sm:p-12 rounded-[2.5rem] border border-slate-50 shadow-sm">
                        <h2 class="font-serif text-3xl font-bold text-ks-emerald mb-8 italic underline decoration-emerald-200 decoration-8 underline-offset-4">1. Dados Coletados</h2>
                        <p class="text-slate-600 leading-relaxed text-lg mb-4">Coletamos informações essenciais para a sua segurança:</p>
                        <ul class="space-y-3 text-slate-700 font-bold">
                            <li class="flex items-center gap-3"><span class="w-2 h-2 bg-ks-emerald rounded-full"></span> Nome Completo</li>
                            <li class="flex items-center gap-3"><span class="w-2 h-2 bg-ks-emerald rounded-full"></span> Número de WhatsApp (Suporte)</li>
                            <li class="flex items-center gap-3"><span class="w-2 h-2 bg-ks-emerald rounded-full"></span> Dados de Pagamento Verificados</li>
                        </ul>
                    </section>

                    <section class="legal-card bg-white p-8 sm:p-12 rounded-[2.5rem] border border-slate-50 shadow-sm">
                        <h2 class="font-serif text-3xl font-bold text-ks-emerald mb-8 italic underline decoration-emerald-200 decoration-8 underline-offset-4">3. Proteção e Segurança</h2>
                        <p class="text-slate-600 leading-relaxed text-lg italic">Adotamos medidas rigorosas para proteger os dados dos utilizadores contra acessos não autorizados. Os dados são utilizados exclusivamente para o processamento de transações e segurança das operações.</p>
                    </section>
                @endif

            </div>

            <div class="mt-24 bg-slate-900 rounded-[3rem] p-10 sm:p-16 text-center text-white relative overflow-hidden">
                <div class="absolute inset-0 bg-ks-emerald opacity-10 blur-3xl animate-pulse"></div>
                <h3 class="text-3xl font-black mb-6 relative z-10 italic">Dúvidas sobre os documentos?</h3>
                <p class="text-slate-400 mb-10 relative z-10 max-w-lg mx-auto">A nossa equipa de Huambo está disponível 24/7 para esclarecer qualquer ponto jurídico ou operacional.</p>
                <a href="https://wa.me/244931719207" target="_blank" class="inline-flex items-center gap-4 bg-green-500 px-10 py-5 rounded-2xl font-black text-lg transition transform hover:scale-105 relative z-10">
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Suporte Jurídico WhatsApp
                </a>
            </div>

        </div>
    </main>

    <footer class="bg-slate-50 py-24 px-6 border-t border-slate-100">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-12 text-center md:text-left">
            <div>
                <img src="{{ asset('assets/images/logos/logo2.PNG') }}" class="h-16 w-auto mb-6 mx-auto md:mx-0" alt="Logo">
                <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.4em]">KWANZASAFE © 2026 — TECNOLOGIA QUE TRANSFORMA</p>
            </div>
            <div class="flex gap-8 text-sm font-bold text-slate-500 uppercase tracking-widest">
                <a href="{{ route('terms') }}" class="hover:text-ks-emerald">Termos</a>
                <a href="{{ route('privacy') }}" class="hover:text-ks-emerald">Privacidade</a>
                <a href="https://wa.me/244931719207" class="text-green-600">Suporte: 931 719 207</a>
            </div>
        </div>
    </footer>

</body>
</html>