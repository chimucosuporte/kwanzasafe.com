<script>
    function ksHeader() {
        return {
            menu: false, scrolled: false,
            onScroll() { this.scrolled = window.scrollY > 20; },
            toggle() { this.menu ? this.close() : this.open(); },
            open() {
                this.menu = true; document.body.style.overflow = 'hidden';
                this.$nextTick(() => { const f = this.$refs.drawer?.querySelector('a,button'); f && f.focus(); });
            },
            close() {
                if (!this.menu) return;
                this.menu = false; document.body.style.overflow = '';
                this.$refs.burger?.focus();
            },
            trap(e) {
                if (!this.menu || e.key !== 'Tab') return;
                const items = this.$refs.drawer.querySelectorAll('a[href],button:not([disabled])');
                if (!items.length) return;
                const first = items[0], last = items[items.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            },
        };
    }
</script>

<div x-data="ksHeader()" @scroll.window="onScroll()" @keydown.escape.window="close()" @keydown.tab="trap($event)">
    <header class="ksh" :class="{ scrolled }">
        <div class="ksh__inner">
            <a href="{{ url('/') }}" class="ksh__logo" aria-label="KwanzaSafe — página inicial">
                <img src="{{ asset('assets/images/logos/logo-isotipo.png') }}" alt="KwanzaSafe" class="full" width="180" height="34">
                <img src="{{ asset('assets/images/logos/logo-icone.png') }}" alt="KwanzaSafe" class="ic" width="34" height="34">
            </a>

            <nav class="ksh__nav" aria-label="Navegação principal">
                <a href="#como-funciona">Como Funciona</a>
                <a href="#moedas">Moedas</a>
                <a href="#testemunhos">Histórias</a>
                <a href="#faq">FAQ</a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="ks-btn ks-btn--primary">Meu Painel</a>
                @else
                    <a href="{{ route('login') }}">Entrar</a>
                    <a href="{{ route('register') }}" class="ks-btn ks-btn--primary">Criar Conta</a>
                @endauth
            </nav>

            <button class="ksh__burger" :class="{ open: menu }" @click="toggle()" x-ref="burger"
                    aria-label="Abrir menu" aria-controls="ksh-drawer" :aria-expanded="menu.toString()">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    <div class="ksh-overlay" :class="{ show: menu }" @click="close()" x-cloak aria-hidden="true"></div>
    <aside id="ksh-drawer" class="ksh-drawer" :class="{ open: menu }" x-ref="drawer" x-cloak
           role="dialog" aria-modal="true" aria-label="Menu de navegação" :aria-hidden="(!menu).toString()">
        <button class="ksh-drawer__close" @click="close()" aria-label="Fechar menu">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>
        </button>
        <nav class="ksh-drawer__nav" aria-label="Navegação móvel">
            <a href="#como-funciona" @click="close()" style="--i:1">Como Funciona</a>
            <a href="#moedas" @click="close()" style="--i:2">Moedas</a>
            <a href="#testemunhos" @click="close()" style="--i:3">Histórias</a>
            <a href="#faq" @click="close()" style="--i:4">FAQ</a>
            @auth
                <a href="{{ url('/dashboard') }}" @click="close()" class="cta" style="--i:5">Meu Painel</a>
            @else
                <a href="{{ route('login') }}" @click="close()" style="--i:5">Entrar</a>
                <a href="{{ route('register') }}" @click="close()" class="cta" style="--i:6">Criar Conta</a>
            @endauth
        </nav>
        <div class="ksh-drawer__foot">
            <a href="https://wa.me/5511933579009?text=Olá%2C+quero+saber+mais+sobre+o+KwanzaSafe." target="_blank" rel="noopener" class="ksh-wa">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51"/></svg>
                Falar no WhatsApp
            </a>
        </div>
    </aside>
</div>
