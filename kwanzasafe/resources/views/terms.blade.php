<!DOCTYPE html>
<html lang="pt-AO" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title>Termos de Uso | KwanzaSafe — Tecnologia que Transforma</title>
    <meta name="description" content="Consulte as condições de utilização da KwanzaSafe. Regras de câmbio, prazos de entrega e responsabilidades do cliente.">
    <meta name="robots" content="index, follow">

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
    <style>[x-cloak] { display: none !important; } .no-scroll { overflow: hidden !important; height: 100vh !important; }</style>
</head>
<body class="bg-white text-slate-900 antialiased" x-data="{ mobileMenu: false }" :class="{ 'no-scroll': mobileMenu }">

    <nav class="fixed w-full h-24 z-[100] bg-white/95 backdrop-blur-md border-b border-slate-100 flex items-center">
        <div class="max-w-7xl mx-auto px-6 w-full flex justify-between items-center text-center">
            <a href="/"><img src="{{ asset('assets/images/logos/logo2.PNG') }}" class="h-20 w-auto"></a>
        </div>
    </nav>

    <header class="pt-60 pb-20 bg-slate-50 border-b border-slate-100">
        <div class="max-w-4xl mx-auto px-6">
            <h1 class="font-serif text-5xl md:text-7xl font-black text-slate-900 tracking-tighter italic mb-6">Termos de Utilização</h1>
            <p class="text-slate-500 font-bold uppercase tracking-widest text-xs italic">Compromisso de Transparência KwanzaSafe</p>
        </div>
    </header>

    <main class="py-24 bg-white">
        <div class="max-w-4xl mx-auto px-6 space-y-24">
            
            <section>
                <h2 class="text-3xl font-black text-ks-emerald italic mb-8 underline decoration-emerald-200 decoration-8 underline-offset-4">1. Objeto do Serviço</h2>
                <p class="text-lg text-slate-600 leading-relaxed font-medium">A Kwanzasafe presta serviços de intermediação de pagamentos em Euro (EUR) e Real Brasileiro (BRL) para Angola. <span class="text-red-600 font-black italic">Não realizamos venda de criptomoedas.</span></p>
            </section>

            <section>
                <h2 class="text-3xl font-black text-ks-emerald italic mb-8 underline decoration-emerald-200 decoration-8 underline-offset-4">2. Identificação do Cliente</h2>
                <p class="text-lg text-slate-600 leading-relaxed font-medium">O nome do titular da conta na plataforma deve coincidir obrigatoriamente com o nome da conta de pagamento. <span class="italic">Não aceitamos pagamentos realizados por terceiros.</span></p>
            </section>

            <section>
                <h2 class="text-3xl font-black text-ks-emerald italic mb-8 underline decoration-emerald-200 decoration-8 underline-offset-4">3. Responsabilidade</h2>
                <p class="text-lg text-slate-600 leading-relaxed">O cliente é inteiramente responsável por fornecer dados corretos. Erros nos dados podem resultar em atrasos ou perda de fundos.</p>
            </section>

            <section>
                <h2 class="text-3xl font-black text-ks-emerald italic mb-8 underline decoration-emerald-200 decoration-8 underline-offset-4">4. Reembolsos</h2>
                <p class="text-lg text-slate-600 font-black italic leading-relaxed bg-red-50 p-8 rounded-3xl border-l-4 border-red-600 uppercase tracking-tight">Não realizamos reembolsos após a conclusão da operação devido à natureza imediata das transações financeiras.</p>
            </section>

            <section class="grid md:grid-cols-2 gap-12">
                <div class="bg-slate-50 p-10 rounded-[3rem] border border-slate-100">
                    <h3 class="text-xl font-black mb-4">5. Origem dos Fundos</h3>
                    <p class="text-slate-500 text-sm italic">A Kwanzasafe reserva-se o direito de recusar qualquer operação suspeita ou de origem ilícita.</p>
                </div>
                <div class="bg-slate-50 p-10 rounded-[3rem] border border-slate-100">
                    <h3 class="text-xl font-black mb-4">6. Processamento</h3>
                    <p class="text-slate-500 text-sm italic">O tempo de entrega varia entre 30 a 120 minutos, dependendo da confirmação bancária.</p>
                </div>
            </section>

            <section class="space-y-12 pb-24">
                <div>
                    <h3 class="text-xl font-black text-slate-900 mb-4">7. Limitação de Responsabilidade</h3>
                    <p class="text-slate-500 italic">Não nos responsabilizamos por bloqueios bancários ou erros cometidos pelo cliente no preenchimento dos dados.</p>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900 mb-4">8. Suporte Oficial</h3>
                    <p class="text-slate-500 italic">O atendimento é feito exclusivamente pelos canais indicados no site. Não nos responsabilizamos por contactos externos.</p>
                </div>
                <div class="p-10 bg-ks-emerald text-white rounded-[3rem]">
                    <h3 class="text-xl font-black mb-4 uppercase tracking-widest italic">9. Alteração dos Termos</h3>
                    <p class="opacity-70 text-sm">Reservamo-nos o direito de atualizar estes termos a qualquer momento para garantir a segurança da plataforma.</p>
                </div>
            </section>
        </div>
    </main>

    <footer class="py-12 bg-slate-50 text-center border-t border-slate-100">
        <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.5em]">KWANZASAFE © 2026 — TECNOLOGIA QUE TRANSFORMA</p>
    </footer>

</body>
</html>