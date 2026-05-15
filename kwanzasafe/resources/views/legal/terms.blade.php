@php
    $pageTitle = 'Termos de Uso';
    $pageDescription = 'Condições gerais de utilização da plataforma KwanzaSafe — remessas internacionais de EUR, BRL e USDC para Angola.';
    $pageKeywords = 'termos de uso KwanzaSafe, condições de serviço, AML Angola, KYC remessas, política BNA, anti-fraude';
    $pageCanonical = url('/termos');
    $lastUpdated = '22 de Abril de 2026';
@endphp

<x-legal.layout
    :pageTitle="$pageTitle"
    :pageDescription="$pageDescription"
    :pageKeywords="$pageKeywords"
    :pageCanonical="$pageCanonical"
    :lastUpdated="$lastUpdated">

    {{-- ÍNDICE --}}
    <div class="lg-toc">
        <p class="lg-toc-title">Índice</p>
        <ol>
            <li><a href="#s1">Definições e Âmbito</a></li>
            <li><a href="#s2">Elegibilidade</a></li>
            <li><a href="#s3">Verificação de Identidade (KYC)</a></li>
            <li><a href="#s4">Moedas Aceites e Redes</a></li>
            <li><a href="#s5">Processo de Transação</a></li>
            <li><a href="#s6">Taxas de Câmbio e Valores</a></li>
            <li><a href="#s7">Anti-Fraude e AML</a></li>
            <li><a href="#s8">Limites e Valores Mínimos</a></li>
            <li><a href="#s9">Prazos de Processamento</a></li>
            <li><a href="#s10">Responsabilidades do Utilizador</a></li>
            <li><a href="#s11">Responsabilidades da KwanzaSafe</a></li>
            <li><a href="#s12">Cancelamentos e Reembolsos</a></li>
            <li><a href="#s13">Suspensão e Encerramento</a></li>
            <li><a href="#s14">Propriedade Intelectual</a></li>
            <li><a href="#s15">Alterações aos Termos</a></li>
            <li><a href="#s16">Legislação Aplicável</a></li>
            <li><a href="#s17">Contacto</a></li>
        </ol>
    </div>

    <div class="lg-highlight">
        <p><strong>Importante:</strong> Ao criar uma conta e utilizar a plataforma KwanzaSafe, declaras ter lido, compreendido e aceite integralmente estes Termos de Uso. Se não concordas com alguma cláusula, por favor não utilizes os nossos serviços.</p>
    </div>

    {{-- SECÇÃO 1 --}}
    <section class="lg-section" id="s1">
        <h2>1. Definições e Âmbito</h2>
        <p>Estes Termos de Uso ("Termos") regulam a utilização da plataforma <strong>KwanzaSafe</strong> ("Plataforma", "Serviço", "nós"), disponível em <a href="https://kwanzasafe.com">https://kwanzasafe.com</a>, por qualquer pessoa singular ou coletiva ("Utilizador", "Cliente", "tu") que pretenda realizar operações de conversão cambial internacional com destino a Angola.</p>

        <h3>1.1 O que é a KwanzaSafe</h3>
        <p>A KwanzaSafe é uma plataforma digital que permite a conversão segura de moedas estrangeiras — <strong>Euros (EUR), Reais Brasileiros (BRL), USDT e USDC</strong> — em Kwanzas Angolanos (AOA) para depósito em contas bancárias angolanas.</p>

        <h3>1.2 Fase atual de operações</h3>
        <p>Nesta fase, a plataforma opera <strong>exclusivamente no sentido "mundo → Angola"</strong>. Não são disponibilizadas, por enquanto, operações inversas (Angola → exterior) nem transferências entre utilizadores da plataforma.</p>
    </section>

    {{-- SECÇÃO 2 --}}
    <section class="lg-section" id="s2">
        <h2>2. Elegibilidade</h2>
        <p>Para utilizar a KwanzaSafe o Utilizador deve cumprir, cumulativamente, os seguintes requisitos:</p>
        <ul>
            <li>Ter idade igual ou superior a <strong>18 anos</strong>;</li>
            <li>Possuir documento de identidade válido (Bilhete de Identidade Angolano, Passaporte ou documento equivalente);</li>
            <li>Ser titular de uma conta bancária em Angola registada em seu nome;</li>
            <li>Ser titular da conta de origem a partir da qual serão feitos os envios de moeda estrangeira;</li>
            <li>Não constar em listas internacionais de sanções (OFAC, UE, ONU) ou em registos de crimes financeiros.</li>
        </ul>
    </section>

    {{-- SECÇÃO 3 --}}
    <section class="lg-section" id="s3">
        <h2>3. Verificação de Identidade (KYC)</h2>
        <p>Em conformidade com as normas do <strong>Banco Nacional de Angola (BNA)</strong>, regulamentos internacionais <strong>AML/CFT</strong> (Anti-Money Laundering / Counter Financing of Terrorism) e melhores práticas FATF, todos os Utilizadores devem completar o processo de verificação de identidade antes de realizar qualquer transação.</p>

        <h3>3.1 Documentação obrigatória</h3>
        <ul>
            <li>Nome completo e data de nascimento;</li>
            <li>Número e validade do Bilhete de Identidade;</li>
            <li>Morada completa com comprovativo (província, município, endereço);</li>
            <li>Fotografia do documento de identidade;</li>
            <li>Selfie com o rosto claramente visível (validação facial);</li>
            <li>Número de telefone ativo para contacto.</li>
        </ul>

        <h3>3.2 Verificação automática e manual</h3>
        <p>A KwanzaSafe utiliza sistemas automatizados de verificação (OCR, biometria facial) complementados por revisão humana. A aprovação KYC pode demorar até <strong>24 horas úteis</strong>. Casos com documentação duvidosa podem ser escalados para análise manual mais detalhada.</p>

        <div class="lg-warning">
            <p><strong>Aviso:</strong> Qualquer tentativa de fornecer informações falsas, documentos falsificados ou dados de terceiros resultará em <strong>recusa permanente</strong> de acesso à plataforma, retenção de fundos envolvidos e denúncia às autoridades competentes.</p>
        </div>
    </section>

    {{-- SECÇÃO 4 --}}
    <section class="lg-section" id="s4">
        <h2>4. Moedas Aceites e Redes</h2>
        <p>A plataforma aceita, nesta fase, as seguintes moedas de origem:</p>
        <ul>
            <li><strong>EUR (Euro)</strong> — via transferência bancária SEPA ou equivalente;</li>
            <li><strong>BRL (Real Brasileiro)</strong> — via PIX ou TED bancário;</li>
            <li><strong>USDT (Tether)</strong> — via redes blockchain TRC20, BEP20 ou ERC20;</li>
            <li><strong>USDC (USD Coin)</strong> — via redes blockchain TRC20, BEP20 ou ERC20.</li>
        </ul>

        <h3>4.1 Recomendações de rede para criptomoedas</h3>
        <p>Para <strong>USDT e USDC</strong>, recomendamos fortemente a rede <code>TRC20</code> (TRON) pela taxa de rede muito reduzida (~1 USD) e confirmação rápida. A KwanzaSafe <strong>não se responsabiliza por envios em redes incorretas</strong> ou por perdas decorrentes de endereços de carteira erradamente introduzidos.</p>

        <h3>4.2 Moeda de destino</h3>
        <p>Os valores são sempre creditados em <strong>AOA (Kwanzas Angolanos)</strong> na conta bancária angolana indicada pelo Utilizador no momento do registo.</p>
    </section>

    {{-- SECÇÃO 5 --}}
    <section class="lg-section" id="s5">
        <h2>5. Processo de Transação</h2>
        <p>O fluxo padrão de uma operação na KwanzaSafe é o seguinte:</p>
        <ol>
            <li><strong>Simulação:</strong> o Utilizador consulta a taxa de câmbio e simula o valor a receber em AOA;</li>
            <li><strong>Criação da transação:</strong> após confirmar, é gerada uma <strong>referência única</strong> (ex: <code>#KZ-ABC123</code>);</li>
            <li><strong>Envio da moeda de origem:</strong> o Utilizador transfere o valor exato para os dados bancários ou endereço crypto indicados pela plataforma;</li>
            <li><strong>Envio do comprovativo:</strong> o Utilizador carrega na plataforma o comprovativo da transferência;</li>
            <li><strong>Validação:</strong> a equipa KwanzaSafe valida o comprovativo, confirma a origem dos fundos (titularidade) e autoriza a libertação;</li>
            <li><strong>Depósito em AOA:</strong> os Kwanzas são creditados no IBAN angolano registado pelo Utilizador.</li>
        </ol>
    </section>

    {{-- SECÇÃO 6 --}}
    <section class="lg-section" id="s6">
        <h2>6. Taxas de Câmbio e Valores</h2>
        <p>As taxas de câmbio aplicadas pela KwanzaSafe são determinadas com base no mercado oficial angolano e internacional, podendo ser atualizadas ao longo do dia em função das flutuações.</p>

        <h3>6.1 Transparência</h3>
        <p>A taxa mostrada na calculadora no momento da criação da transação é a <strong>taxa final aplicada</strong>. Não aplicamos comissões ocultas ou spreads não divulgados.</p>

        <h3>6.2 Estabilidade das taxas</h3>
        <p>Uma vez confirmada a transação e gerada a referência, o valor a receber em AOA fica garantido por <strong>24 horas</strong>, desde que o comprovativo de pagamento seja enviado dentro desse prazo. Após 24 horas sem comprovativo, a transação expira.</p>
    </section>

    {{-- SECÇÃO 7 --}}
    <section class="lg-section" id="s7">
        <h2>7. Anti-Fraude e AML</h2>
        <p>A KwanzaSafe aplica políticas rigorosas de combate à fraude, branqueamento de capitais e financiamento do terrorismo, em linha com as melhores práticas internacionais.</p>

        <h3>7.1 Titularidade obrigatória</h3>
        <p>O nome do titular da <strong>conta de origem</strong> (seja bancária, PIX, MetaMask, Binance, etc.) tem de coincidir exatamente com o nome do Utilizador registado no KYC. Pagamentos de terceiros são <strong>automaticamente recusados</strong>, com os fundos retidos até esclarecimento.</p>

        <h3>7.2 Monitorização de padrões</h3>
        <p>Todas as transações são monitorizadas em tempo real por sistemas automatizados de deteção de padrões suspeitos: múltiplos envios em períodos curtos, valores atípicos, origens geográficas de risco elevado, entre outros.</p>

        <h3>7.3 Comunicação às autoridades</h3>
        <p>Em caso de deteção de operações potencialmente ilícitas, a KwanzaSafe reporta às autoridades competentes angolanas e internacionais, conforme obrigação legal.</p>
    </section>

    {{-- SECÇÃO 8 --}}
    <section class="lg-section" id="s8">
        <h2>8. Limites e Valores Mínimos</h2>
        <ul>
            <li><strong>Mínimo por operação:</strong> 10 unidades da moeda de origem (10 EUR / 10 BRL / 10 USDT / 10 USDC);</li>
            <li><strong>Valores acima de 5.000 EUR equivalentes:</strong> podem requerer documentação adicional (comprovativo de origem dos fundos, declaração de renda, fatura de serviços, etc.);</li>
            <li><strong>Limites diários e mensais:</strong> podem ser aplicados consoante o perfil KYC do Utilizador e são comunicados individualmente.</li>
        </ul>
    </section>

    {{-- SECÇÃO 9 --}}
    <section class="lg-section" id="s9">
        <h2>9. Prazos de Processamento</h2>
        <p>Após receção e validação do comprovativo de pagamento, os prazos-alvo para depósito em AOA são:</p>
        <ul>
            <li><strong>Dias úteis, antes das 14h00 (GMT):</strong> processamento no mesmo dia;</li>
            <li><strong>Dias úteis, após as 14h00:</strong> até 4 horas úteis, concluído no próximo dia útil se necessário;</li>
            <li><strong>Fins-de-semana e feriados:</strong> processamento iniciado no próximo dia útil.</li>
        </ul>
        <p>Estes prazos podem ser afetados por fatores alheios à KwanzaSafe: problemas em bancos parceiros, congestionamento da rede blockchain (para criptomoedas), feriados bancários em Angola, verificações AML adicionais.</p>
    </section>

    {{-- SECÇÃO 10 --}}
    <section class="lg-section" id="s10">
        <h2>10. Responsabilidades do Utilizador</h2>
        <p>O Utilizador compromete-se a:</p>
        <ul>
            <li>Fornecer informações verdadeiras, atuais e completas no processo de registo e KYC;</li>
            <li>Manter as credenciais de acesso (email, password) em local seguro e não partilhar com terceiros;</li>
            <li>Conferir cuidadosamente o IBAN de destino antes de confirmar qualquer transação;</li>
            <li>Utilizar a plataforma apenas para operações legítimas e de origem comprovada;</li>
            <li>Comunicar imediatamente qualquer atividade suspeita ou não autorizada na sua conta;</li>
            <li>Cumprir a legislação fiscal aplicável no seu país de residência em relação às remessas.</li>
        </ul>
    </section>

    {{-- SECÇÃO 11 --}}
    <section class="lg-section" id="s11">
        <h2>11. Responsabilidades da KwanzaSafe</h2>
        <p>A KwanzaSafe compromete-se a:</p>
        <ul>
            <li>Aplicar medidas técnicas e organizacionais adequadas para proteger os dados e fundos dos Utilizadores;</li>
            <li>Disponibilizar suporte através dos canais oficiais (chat na plataforma, email e WhatsApp);</li>
            <li>Comunicar com transparência todas as taxas, prazos e eventuais atrasos;</li>
            <li>Cumprir integralmente a legislação angolana, nomeadamente as diretrizes do BNA;</li>
            <li>Proteger a privacidade dos dados pessoais conforme detalhado na <a href="{{ route('privacy') }}">Política de Privacidade</a>.</li>
        </ul>

        <h3>11.1 Limitações de responsabilidade</h3>
        <p>A KwanzaSafe <strong>não se responsabiliza</strong> por:</p>
        <ul>
            <li>Erros de IBAN ou endereço de carteira introduzidos incorretamente pelo Utilizador;</li>
            <li>Envios em redes blockchain incorretas (ex: USDT enviado por rede ERC20 quando era esperado TRC20);</li>
            <li>Atrasos causados por bancos parceiros, congestionamento blockchain ou eventos de força maior;</li>
            <li>Flutuações cambiais após o prazo de 24h para envio de comprovativo;</li>
            <li>Uso fraudulento da conta por terceiros em caso de negligência do Utilizador com as credenciais.</li>
        </ul>
    </section>

    {{-- SECÇÃO 12 --}}
    <section class="lg-section" id="s12">
        <h2>12. Cancelamentos e Reembolsos</h2>
        <h3>12.1 Cancelamento antes do envio da moeda</h3>
        <p>O Utilizador pode cancelar livremente uma transação criada enquanto ainda não tiver realizado o envio da moeda de origem. Basta contactar o suporte ou não proceder com o pagamento — a transação expira em 24h.</p>

        <h3>12.2 Reembolsos</h3>
        <p>Em caso de necessidade de reembolso (erro de IBAN, rejeição por AML, cancelamento excecional acordado), o reembolso é efetuado na moeda de origem e no canal de origem, deduzidos <strong>custos bancários e/ou de rede blockchain</strong>. Prazos de reembolso podem variar entre 3 a 15 dias úteis.</p>
    </section>

    {{-- SECÇÃO 13 --}}
    <section class="lg-section" id="s13">
        <h2>13. Suspensão e Encerramento</h2>
        <p>A KwanzaSafe reserva-se o direito de <strong>suspender ou encerrar</strong> uma conta, sem aviso prévio, nos seguintes casos:</p>
        <ul>
            <li>Violação destes Termos ou da Política de Privacidade;</li>
            <li>Suspeita fundamentada de fraude, branqueamento de capitais ou financiamento do terrorismo;</li>
            <li>Fornecimento de informações falsas no KYC;</li>
            <li>Utilização da plataforma para fins ilícitos;</li>
            <li>Tentativas repetidas de burlar os mecanismos de segurança;</li>
            <li>Determinação de autoridades judiciais ou reguladoras competentes.</li>
        </ul>
    </section>

    {{-- SECÇÃO 14 --}}
    <section class="lg-section" id="s14">
        <h2>14. Propriedade Intelectual</h2>
        <p>Todo o conteúdo da plataforma KwanzaSafe — incluindo logotipos, designs, textos, interface, código-fonte e marca "KwanzaSafe" — é propriedade exclusiva da empresa ou dos seus licenciadores, protegido pela legislação angolana e internacional de propriedade intelectual.</p>
        <p>É <strong>expressamente proibido</strong> copiar, reproduzir, modificar, distribuir ou criar trabalhos derivados sem autorização prévia por escrito.</p>
    </section>

    {{-- SECÇÃO 15 --}}
    <section class="lg-section" id="s15">
        <h2>15. Alterações aos Termos</h2>
        <p>A KwanzaSafe pode alterar estes Termos a qualquer momento para refletir alterações legais, regulatórias, de segurança ou de produto. As alterações são comunicadas com antecedência mínima de <strong>15 dias</strong> através de email e banner na plataforma.</p>
        <p>A continuação do uso da plataforma após a data de entrada em vigor das alterações constitui <strong>aceitação tácita</strong> dos novos Termos.</p>
    </section>

    {{-- SECÇÃO 16 --}}
    <section class="lg-section" id="s16">
        <h2>16. Legislação Aplicável e Foro</h2>
        <p>Estes Termos são regidos pela <strong>legislação da República de Angola</strong>. Qualquer litígio emergente da utilização da plataforma será dirimido nos tribunais competentes de Luanda, Angola, com renúncia expressa a qualquer outro foro.</p>
        <p>Caso alguma cláusula destes Termos seja considerada inválida por decisão judicial, as restantes permanecerão em plena vigência.</p>
    </section>

    {{-- SECÇÃO 17 --}}
    <section class="lg-section" id="s17">
        <h2>17. Contacto</h2>
        <p>Para qualquer questão relacionada com estes Termos de Uso, podes contactar-nos através dos canais oficiais:</p>
        <ul>
            <li><strong>Email:</strong> <a href="mailto:suporte@kwanzasafe.com">suporte@kwanzasafe.com</a></li>
            <li><strong>WhatsApp:</strong> <a href="https://wa.me/5511933579009" target="_blank" rel="noopener">+55 11 93357-9009</a></li>
            <li><strong>Chat na plataforma:</strong> disponível após login na tua área de cliente</li>
        </ul>
    </section>

    <div class="lg-highlight" style="margin-top: 3rem;">
        <p><strong>Reconhecimento:</strong> Ao clicar em "Criar Conta" ou ao utilizar a plataforma KwanzaSafe, declaras ter lido integralmente e aceite estes Termos de Uso, assim como a nossa <a href="{{ route('privacy') }}">Política de Privacidade</a>.</p>
    </div>

</x-legal.layout>