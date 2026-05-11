# KwanzaSafe — Memória do Projeto

## Identidade
Fintech internacional de câmbio EUR/BRL/USDC → AOA Kwanzas.
Foco em remessas mundo→Angola, mobile-first, anti-fraude rigoroso.

## Stack
- PHP 8.3 + Laravel 11
- MySQL (BD: kwanzasafe local; u763057780_kwansafe_db produção)
- Frontend: Tailwind CDN + Alpine.js (sem Vite quando possível)
- Português PT (não BR)

## Identidade Visual
- Cores: #009d44 (verde) + #000 + #fff
- Tipografia: Syne (display) + DM Sans (corpo)
- Logos: logo.png (mobile), logo2.png (tablet), logo1.png (desktop)
- WhatsApp oficial: +55 11 93357-9009

## Arquivos Principais (uploaded para Claude no chat — confirmar com filesystem)
### Controllers
- AdminController, AuditController, BeneficiaryController, ChatController
- OtpController, ProfileController, TransactionController, VerificationController

### Models
- User, Transaction, Beneficiary, ChatMessage, ExchangeRate, OtpCode, AuditLog

### Services
- AuditLogger (auditoria imutável)
- KycBot (validação automática 8 critérios)
- OtpService (códigos 6 dígitos)

## Princípios de Código
- Mobile-first sempre
- Anti-fraude rigoroso (validação titularidade IBAN no BeneficiaryController)
- AuditLogger em todas as ações sensíveis
- Bot KYC com fallback humano
- Refusal de transação se KYC incompleto

## Fluxo de Transação Atual
1. Cliente cria transação via TransactionController@store
2. Status inicial: 'pending'
3. Cliente faz upload comprovativo via uploadReceipt → status 'processing'
4. Admin aprova via AdminController@approve

## Fluxo de Transação NOVO (a implementar)
1. Cliente seleciona moeda+valor na calculadora
2. Clica "Iniciar Transação" → cria pending
3. Redirect para Sala da Transação (chat com admin)
4. Mensagem automática inicial sistema
5. Admin negocia: pergunta IBAN, comprovativo, etc
6. Admin clica "Recebi pagamento" → payment_received
7. Admin envia AOA, clica "Enviei AOA" → aoa_sent
8. Cliente confirma → completed

## Rotas Principais
Ver routes/web.php (uploaded). Grupos: público, auth (cliente), admin/.

## Comandos Úteis
- `php artisan migrate` — correr migrações
- `php artisan view:clear` — limpar cache views
- `php artisan tinker` — REPL Laravel

## Servidor Produção
- kwanzasafe.com (Hostinger)
- Estrutura split: kwanzasafe/ (Laravel) + public_html/ (público)
- BD: u763057780_kwansafe_db
- SMTP: smtp.hostinger.com:465 SSL (geral@kwanzasafe.com)
- SSH: u763057780@195.35.41.218

## Convenções
- Nunca quebrar AuditLogger (try/catch interno)
- Mensagens em PT de Portugal (não BR)
- Validação de titularidade rigorosa em IBANs
- Email OTP via OtpService (rate limit 3/hora, expiry 15min)