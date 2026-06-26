
<footer class="ksf">
    <div class="ks-container">
        <div class="ksf__grid">
            <div>
                <div class="ksf__brand">
                    <img src="{{ asset('assets/images/logos/logo-icone.png') }}" alt="" aria-hidden="true">
                    <span>KwanzaSafe</span>
                </div>
                <p class="ksf__desc">Carteira internacional para remessas seguras do mundo para Angola. Conversão transparente de EUR, BRL e USDC em Kwanzas.</p>
                <a href="https://wa.me/5511933579009?text=Olá%2C+quero+saber+mais+sobre+o+KwanzaSafe." target="_blank" rel="noopener" class="ksf__wa">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606"/></svg>
                    +55 11 93357-9009
                </a>
                <div><span class="ksf__seal">✓ BNA Compliant · KYC/AML</span></div>
            </div>
            <nav aria-label="Plataforma">
                <h4>Plataforma</h4>
                <ul>
                    <li><a href="{{ url('/') }}#como-funciona">Como Funciona</a></li>
                    <li><a href="{{ url('/') }}#moedas">Moedas Aceites</a></li>
                    <li><a href="{{ url('/') }}#testemunhos">Histórias</a></li>
                    <li><a href="{{ url('/') }}#faq">FAQ</a></li>
                    <li><a href="{{ route('register') }}">Criar Conta</a></li>
                </ul>
            </nav>
            <nav aria-label="Suporte">
                <h4>Suporte</h4>
                <ul>
                    <li><a href="https://wa.me/5511933579009" target="_blank" rel="noopener">WhatsApp</a></li>
                    <li><a href="mailto:geral@kwanzasafe.com">geral@kwanzasafe.com</a></li>
                    <li><a href="{{ route('login') }}">Área Cliente</a></li>
                </ul>
            </nav>
            <nav aria-label="Legal">
                <h4>Legal</h4>
                <ul>
                    <li><a href="{{ route('terms') }}">Termos de Uso</a></li>
                    <li><a href="{{ route('privacy') }}">Política de Privacidade</a></li>
                    <li><a href="{{ url('/') }}#faq">Conformidade AML</a></li>
                </ul>
            </nav>
        </div>
        <div class="ksf__bottom">
            <div class="ksf__copy">© {{ date('Y') }} KwanzaSafe · BNA Compliant · Todos os direitos reservados</div>
            <div class="ksf__social">
                <a href="https://wa.me/5511933579009" target="_blank" rel="noopener" aria-label="WhatsApp">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></svg>
                </a>
            </div>
        </div>
    </div>
</footer>
