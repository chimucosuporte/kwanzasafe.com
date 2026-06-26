@php
    $pageTitle = 'Política de Privacidade';
    $pageDescription = 'Como a KwanzaSafe recolhe, utiliza e protege os teus dados pessoais em conformidade com a legislação angolana e internacional.';
    $pageKeywords = 'política de privacidade KwanzaSafe, proteção de dados Angola, GDPR remessas, dados pessoais fintech, LGPD Angola';
    $pageCanonical = url('/privacidade');
    $lastUpdated = '22 de Abril de 2026';
@endphp

<x-legal.layout
    :pageTitle="$pageTitle"
    :pageDescription="$pageDescription"
    :pageKeywords="$pageKeywords"
    :pageCanonical="$pageCanonical"
    :lastUpdated="$lastUpdated">

    {{-- ÍNDICE --}}
    <x-slot:toc>
        <ol>
            <li><a href="#p1">Introdução</a></li>
            <li><a href="#p2">Responsável pelo Tratamento</a></li>
            <li><a href="#p3">Dados que Recolhemos</a></li>
            <li><a href="#p4">Finalidades do Tratamento</a></li>
            <li><a href="#p5">Base Legal</a></li>
            <li><a href="#p6">Partilha de Dados</a></li>
            <li><a href="#p7">Transferências Internacionais</a></li>
            <li><a href="#p8">Período de Conservação</a></li>
            <li><a href="#p9">Os Teus Direitos</a></li>
            <li><a href="#p10">Segurança dos Dados</a></li>
            <li><a href="#p11">Cookies e Tecnologias</a></li>
            <li><a href="#p12">Menores de Idade</a></li>
            <li><a href="#p13">Alterações à Política</a></li>
            <li><a href="#p14">Contacto</a></li>
        </ol>
    </x-slot:toc>

    <div class="lg-highlight">
        <p><strong>Resumo:</strong> A KwanzaSafe recolhe apenas os dados necessários para cumprir obrigações legais (KYC, AML) e prestar-te o serviço. Não vendemos dados pessoais. Tens sempre o direito de aceder, corrigir ou pedir a eliminação dos teus dados.</p>
    </div>

    {{-- SECÇÃO 1 --}}
    <section class="lg-section" id="p1">
        <h2>1. Introdução</h2>
        <p>A proteção dos teus dados pessoais é uma prioridade para a <strong>KwanzaSafe</strong>. Esta Política de Privacidade explica, de forma clara, como recolhemos, usamos, armazenamos e protegemos as tuas informações quando utilizas a nossa plataforma <a href="https://kwanzasafe.com">kwanzasafe.com</a>.</p>
        <p>Esta Política é elaborada em conformidade com:</p>
        <ul>
            <li>A <strong>Lei n.º 22/11</strong> de 17 de Junho (Lei da Proteção de Dados Pessoais de Angola);</li>
            <li>As melhores práticas do <strong>Regulamento Geral de Proteção de Dados (GDPR)</strong> da União Europeia;</li>
            <li>A <strong>Lei Geral de Proteção de Dados Pessoais (LGPD)</strong> do Brasil (Lei 13.709/2018);</li>
            <li>As normas do <strong>Banco Nacional de Angola (BNA)</strong> aplicáveis a instituições de serviços financeiros.</li>
        </ul>
    </section>

    {{-- SECÇÃO 2 --}}
    <section class="lg-section" id="p2">
        <h2>2. Responsável pelo Tratamento</h2>
        <p>O responsável pelo tratamento dos teus dados pessoais é:</p>
        <ul>
            <li><strong>Entidade:</strong> KwanzaSafe</li>
            <li><strong>Website:</strong> <a href="https://kwanzasafe.com">https://kwanzasafe.com</a></li>
            <li><strong>Email DPO:</strong> <a href="mailto:privacidade@kwanzasafe.com">privacidade@kwanzasafe.com</a></li>
            <li><strong>Suporte geral:</strong> <a href="mailto:suporte@kwanzasafe.com">suporte@kwanzasafe.com</a></li>
        </ul>
    </section>

    {{-- SECÇÃO 3 --}}
    <section class="lg-section" id="p3">
        <h2>3. Dados que Recolhemos</h2>

        <h3>3.1 Dados de identificação (KYC)</h3>
        <ul>
            <li>Nome completo;</li>
            <li>Data de nascimento;</li>
            <li>Género;</li>
            <li>Nacionalidade e país de residência;</li>
            <li>Número, validade e imagem do Bilhete de Identidade (ou Passaporte);</li>
            <li>Fotografia facial (selfie) para validação biométrica;</li>
            <li>Morada completa (província, município, rua).</li>
        </ul>

        <h3>3.2 Dados de contacto</h3>
        <ul>
            <li>Endereço de email;</li>
            <li>Número de telefone (WhatsApp ativo).</li>
        </ul>

        <h3>3.3 Dados financeiros e de transação</h3>
        <ul>
            <li>IBAN de origem (conta a partir da qual envias moeda estrangeira);</li>
            <li>IBAN de destino angolano (onde recebes AOA);</li>
            <li>Endereços de carteira crypto (para operações em USDT/USDC);</li>
            <li>Histórico de transações, montantes, moedas e taxas aplicadas;</li>
            <li>Comprovativos de pagamento enviados por ti.</li>
        </ul>

        <h3>3.4 Dados técnicos e de utilização</h3>
        <ul>
            <li>Endereço IP e localização aproximada;</li>
            <li>Tipo de dispositivo, sistema operativo e browser (User-Agent);</li>
            <li>Páginas visitadas, tempo na plataforma, interações;</li>
            <li>Identificador de sessão e cookies essenciais.</li>
        </ul>

        <h3>3.5 Comunicações</h3>
        <ul>
            <li>Mensagens trocadas com a equipa de suporte (chat, email, WhatsApp);</li>
            <li>Anexos enviados durante conversas (imagens, PDFs);</li>
            <li>Registos de audit trail (ações realizadas na conta).</li>
        </ul>
    </section>

    {{-- SECÇÃO 4 --}}
    <section class="lg-section" id="p4">
        <h2>4. Finalidades do Tratamento</h2>
        <p>Utilizamos os teus dados exclusivamente para as seguintes finalidades:</p>
        <ul>
            <li><strong>Prestar o serviço:</strong> processar as tuas operações de câmbio e depósito em AOA;</li>
            <li><strong>Cumprir obrigações legais:</strong> verificação KYC, prevenção de branqueamento de capitais (AML) e financiamento do terrorismo (CFT), conforme exigido pelo BNA;</li>
            <li><strong>Segurança:</strong> detetar e prevenir fraudes, acessos não autorizados e atividades suspeitas;</li>
            <li><strong>Comunicação:</strong> enviar-te atualizações sobre as tuas transações, notificações de segurança e respostas a pedidos de suporte;</li>
            <li><strong>Auditoria:</strong> manter registos imutáveis de ações (audit trail) para efeitos regulatórios;</li>
            <li><strong>Melhoria contínua:</strong> analisar o uso da plataforma para melhorar funcionalidades (dados agregados e anonimizados);</li>
            <li><strong>Marketing direto:</strong> apenas com o teu consentimento explícito, podemos enviar-te informações sobre novos serviços.</li>
        </ul>

        <div class="lg-highlight">
            <p><strong>O que NÃO fazemos com os teus dados:</strong> não vendemos, alugamos ou trocamos os teus dados pessoais com terceiros para fins publicitários. Nunca.</p>
        </div>
    </section>

    {{-- SECÇÃO 5 --}}
    <section class="lg-section" id="p5">
        <h2>5. Base Legal do Tratamento</h2>
        <p>O tratamento dos teus dados assenta nas seguintes bases legais:</p>
        <ul>
            <li><strong>Execução de contrato:</strong> para prestar o serviço de câmbio que contrataste;</li>
            <li><strong>Obrigação legal:</strong> para cumprir as normas AML/KYC e regulamentação BNA;</li>
            <li><strong>Interesse legítimo:</strong> para prevenir fraudes e proteger a plataforma e os utilizadores;</li>
            <li><strong>Consentimento:</strong> para finalidades específicas como marketing direto, onde é sempre necessário o teu consentimento explícito e revogável.</li>
        </ul>
    </section>

    {{-- SECÇÃO 6 --}}
    <section class="lg-section" id="p6">
        <h2>6. Partilha de Dados</h2>
        <p>Os teus dados podem ser partilhados, apenas na medida estritamente necessária, com:</p>

        <h3>6.1 Parceiros operacionais</h3>
        <ul>
            <li><strong>Bancos parceiros em Angola</strong> — para executar os depósitos em AOA;</li>
            <li><strong>Fornecedores de infraestrutura tecnológica</strong> — alojamento (Hostinger), email transacional, monitorização;</li>
            <li><strong>Fornecedores de verificação de identidade</strong> — para validação automatizada de documentos quando aplicável.</li>
        </ul>

        <h3>6.2 Autoridades</h3>
        <ul>
            <li><strong>Banco Nacional de Angola (BNA)</strong> — em cumprimento de obrigações regulatórias;</li>
            <li><strong>Autoridades judiciais e tributárias</strong> — mediante pedido formal e fundamentado;</li>
            <li><strong>Unidades de Informação Financeira (UIF)</strong> — em caso de suspeita de operações ilícitas.</li>
        </ul>

        <p>Todos os parceiros são obrigados por contrato a respeitar padrões de segurança equivalentes aos nossos e a tratar os dados apenas para as finalidades indicadas.</p>
    </section>

    {{-- SECÇÃO 7 --}}
    <section class="lg-section" id="p7">
        <h2>7. Transferências Internacionais</h2>
        <p>Alguns dos nossos parceiros operam fora de Angola (por exemplo, fornecedores de alojamento cloud na Europa). Quando ocorrem transferências internacionais de dados, garantimos que:</p>
        <ul>
            <li>O destinatário aplica garantias adequadas de proteção de dados (cláusulas contratuais padrão, certificações reconhecidas);</li>
            <li>Os dados transferidos são apenas os estritamente necessários;</li>
            <li>Tens direito a obter informações sobre essas transferências mediante pedido.</li>
        </ul>
    </section>

    {{-- SECÇÃO 8 --}}
    <section class="lg-section" id="p8">
        <h2>8. Período de Conservação dos Dados</h2>
        <p>Conservamos os teus dados pelo período estritamente necessário às finalidades:</p>
        <ul>
            <li><strong>Dados KYC e de transações:</strong> <strong>10 anos</strong> após a última transação, por obrigação legal AML/BNA;</li>
            <li><strong>Registos de audit trail:</strong> <strong>10 anos</strong>, imutáveis, para fins regulatórios;</li>
            <li><strong>Comunicações de suporte:</strong> 5 anos;</li>
            <li><strong>Cookies técnicos:</strong> enquanto durar a sessão ou até 30 dias;</li>
            <li><strong>Dados para marketing:</strong> enquanto consentires (podes revogar a qualquer momento).</li>
        </ul>
        <p>Findo o prazo legal, os dados são eliminados de forma segura ou anonimizados irreversivelmente.</p>
    </section>

    {{-- SECÇÃO 9 --}}
    <section class="lg-section" id="p9">
        <h2>9. Os Teus Direitos</h2>
        <p>Em relação aos teus dados pessoais, tens os seguintes direitos:</p>

        <h3>9.1 Direito de acesso</h3>
        <p>Podes solicitar uma cópia de todos os dados pessoais que temos sobre ti.</p>

        <h3>9.2 Direito de retificação</h3>
        <p>Podes corrigir dados incorretos ou incompletos diretamente na tua conta ou solicitando-nos.</p>

        <h3>9.3 Direito ao apagamento ("direito ao esquecimento")</h3>
        <p>Podes pedir a eliminação dos teus dados, com as seguintes limitações:</p>
        <ul>
            <li>Dados obrigatórios por lei (ex.: registos AML) <strong>não podem</strong> ser eliminados antes do prazo de 10 anos;</li>
            <li>Transações em curso devem ser concluídas antes de qualquer eliminação.</li>
        </ul>

        <h3>9.4 Direito à portabilidade</h3>
        <p>Podes receber os teus dados em formato estruturado (JSON) para transferi-los para outro serviço.</p>

        <h3>9.5 Direito à limitação do tratamento</h3>
        <p>Podes solicitar que limitemos o tratamento dos teus dados em determinadas circunstâncias.</p>

        <h3>9.6 Direito de oposição</h3>
        <p>Podes opor-te ao tratamento dos teus dados para marketing direto a qualquer momento.</p>

        <h3>9.7 Direito de retirar consentimento</h3>
        <p>Se o tratamento se baseia no teu consentimento, podes retirá-lo a qualquer momento, sem afetar a licitude do tratamento anterior.</p>

        <h3>9.8 Direito de reclamação</h3>
        <p>Se considerares que os teus direitos não foram respeitados, podes apresentar reclamação à <strong>Agência de Proteção de Dados de Angola (APD)</strong>, sem prejuízo das vias judiciais.</p>

        <div class="lg-highlight">
            <p><strong>Como exercer os teus direitos?</strong> Envia um email para <a href="mailto:privacidade@kwanzasafe.com">privacidade@kwanzasafe.com</a> com o teu pedido. Responderemos no prazo máximo de <strong>30 dias</strong>.</p>
        </div>
    </section>

    {{-- SECÇÃO 10 --}}
    <section class="lg-section" id="p10">
        <h2>10. Segurança dos Dados</h2>
        <p>Aplicamos medidas técnicas e organizacionais robustas para proteger os teus dados:</p>

        <h3>10.1 Medidas técnicas</h3>
        <ul>
            <li><strong>Criptografia</strong> TLS 1.3 em todas as comunicações;</li>
            <li><strong>Armazenamento seguro</strong> de passwords (hashing bcrypt);</li>
            <li><strong>Firewalls</strong> e sistemas de deteção de intrusão;</li>
            <li><strong>Backups encriptados</strong> regulares;</li>
            <li><strong>Audit trail imutável</strong> de todas as ações críticas;</li>
            <li><strong>Rate limiting</strong> para prevenir ataques de força bruta.</li>
        </ul>

        <h3>10.2 Medidas organizacionais</h3>
        <ul>
            <li>Acesso aos dados limitado a pessoal autorizado e com necessidade de conhecer;</li>
            <li>Todos os colaboradores assinam acordos de confidencialidade;</li>
            <li>Formação regular em segurança e proteção de dados;</li>
            <li>Revisões periódicas de segurança e testes de penetração.</li>
        </ul>

        <h3>10.3 Notificação de violações</h3>
        <p>Em caso de violação de dados pessoais que envolva risco elevado para os titulares, notificaremos a Autoridade de Proteção de Dados e os utilizadores afetados no prazo de <strong>72 horas</strong>.</p>
    </section>

    {{-- SECÇÃO 11 --}}
    <section class="lg-section" id="p11">
        <h2>11. Cookies e Tecnologias Similares</h2>
        <p>Utilizamos cookies apenas para finalidades essenciais ao funcionamento da plataforma:</p>
        <ul>
            <li><strong>Cookies de sessão:</strong> mantêm-te autenticado enquanto navegas;</li>
            <li><strong>Cookies de segurança:</strong> tokens CSRF para prevenir ataques;</li>
            <li><strong>Cookies de preferências:</strong> guardam as tuas preferências de tema/idioma.</li>
        </ul>
        <p>Nesta fase, <strong>não utilizamos cookies de rastreamento publicitário nem analytics de terceiros</strong> (Google Analytics, Facebook Pixel, etc.).</p>
    </section>

    {{-- SECÇÃO 12 --}}
    <section class="lg-section" id="p12">
        <h2>12. Menores de Idade</h2>
        <p>A KwanzaSafe <strong>não é destinada a menores de 18 anos</strong>. Não recolhemos intencionalmente dados de menores. Se descobrirmos que uma conta foi criada por um menor, eliminaremos imediatamente todos os dados associados.</p>
        <p>Se és pai/mãe/tutor e descobriste que o teu educando nos forneceu dados pessoais, contacta-nos em <a href="mailto:privacidade@kwanzasafe.com">privacidade@kwanzasafe.com</a>.</p>
    </section>

    {{-- SECÇÃO 13 --}}
    <section class="lg-section" id="p13">
        <h2>13. Alterações a esta Política</h2>
        <p>Podemos atualizar esta Política de Privacidade para refletir alterações legais, tecnológicas ou de funcionamento da plataforma. Quando as alterações forem <strong>substanciais</strong>, notificar-te-emos por email e banner na plataforma, com pelo menos <strong>15 dias</strong> de antecedência.</p>
        <p>Recomendamos que consultes esta página periodicamente. A data da última atualização está sempre visível no topo do documento.</p>
    </section>

    {{-- SECÇÃO 14 --}}
    <section class="lg-section" id="p14">
        <h2>14. Contacto</h2>
        <p>Para qualquer questão sobre esta Política de Privacidade ou sobre o tratamento dos teus dados, contacta o nosso Encarregado de Proteção de Dados (DPO):</p>
        <ul>
            <li><strong>Email (DPO):</strong> <a href="mailto:privacidade@kwanzasafe.com">privacidade@kwanzasafe.com</a></li>
            <li><strong>Suporte geral:</strong> <a href="mailto:suporte@kwanzasafe.com">suporte@kwanzasafe.com</a></li>
            <li><strong>WhatsApp:</strong> <a href="https://wa.me/5511933579009" target="_blank" rel="noopener">+55 11 93357-9009</a></li>
        </ul>
        <p>Prazo de resposta: <strong>até 30 dias</strong>.</p>
    </section>

    <div class="lg-highlight" style="margin-top: 3rem;">
        <p><strong>Consentimento:</strong> Ao criar uma conta na KwanzaSafe, declaras ter lido e compreendido esta Política de Privacidade, e consentes no tratamento dos teus dados pessoais nas condições aqui descritas e nos <a href="{{ route('terms') }}">Termos de Uso</a>.</p>
    </div>

</x-legal.layout>