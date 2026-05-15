# KwanzaSafe — Memória Mestre do Projeto

> Gerado em auditoria completa — 2026-05-12. Actualizar após cada sprint.

---

## 1. Identidade do Produto

| Campo | Valor |
|---|---|
| **Nome** | KwanzaSafe |
| **Domínio** | kwanzasafe.com |
| **Missão** | Conversão segura de EUR, BRL e USDC para AOA (Kwanzas angolanos) |
| **Tagline** | "Conversão segura de Euros, Reais e USDC para Kwanzas — do mundo para Angola, com rapidez e total confiança." |
| **WhatsApp** | +55 11 93357-9009 (Brasil) |
| **Email oficial** | geral@kwanzasafe.com |
| **Idioma** | Português de Portugal (PT, não BR) |

---

## 2. Stack Técnico (Confirmado)

| Camada | Tecnologia | Versão Confirmada |
|---|---|---|
| **Framework** | Laravel | **10.50.2** (não 11!) |
| **PHP** | PHP | ^8.1 (prod usa 8.3) |
| **BD** | MySQL | 8.x |
| **Frontend CSS** | Tailwind CSS | 3.1 via CDN (sem Vite em prod) |
| **Frontend JS** | Alpine.js | 3.4 via CDN |
| **Auth** | Laravel Breeze | 1.29 |
| **API tokens** | Laravel Sanctum | 3.2 |
| **Build tool** | Vite | 4.0 (só dev) |
| **Testes** | PHPUnit | 10.x (0 testes escritos) |

---

## 3. Estrutura de Pastas

```
C:\wamp64\www\kwanzasa.com\          ← Raiz do Git
├── kwanzasafe/                       ← App Laravel
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/          ← 18 controllers
│   │   │   ├── Middleware/           ← IsAdmin.php (custom)
│   │   │   └── Requests/            ← Só ProfileUpdateRequest + Auth/
│   │   ├── Models/                   ← 7 modelos
│   │   ├── Services/                 ← AuditLogger, KycBot, OtpService
│   │   ├── Mail/                     ← OtpEmail.php
│   │   └── helpers.php              ← ks_file(), ks_is_image(), ks_route()
│   ├── database/migrations/          ← 16 migrações
│   ├── resources/views/             ← 51 templates Blade
│   ├── routes/
│   │   ├── web.php                  ← ~30 rotas web
│   │   └── api.php                  ← Apenas GET /user (sanctum)
│   └── public/
│       ├── assets/images/logos/     ← logo.png, logo1.png, logo2.png
│       ├── assets/sounds/           ← notify.wav
│       └── js/                      ← ks-persist.js
├── public_html/                      ← Symlink Laravel → public_html (prod)
└── _DOCS/                           ← Documentação do projeto
```

---

## 4. Base de Dados (Schema Confirmado)

### 4.1 Tabelas e Colunas Reais

**`users`** (campos confirmados na migração):
`id`, `full_name`, `email`(unique), `phone`(unique,nullable), `password`, `role`(enum:client/admin/super_admin), `status`, `kyc_status`, `country`, `avatar_path`, `email_verified_at`, `last_login_at`, `last_login_ip`, `remember_token`, `deleted_at`(softdelete), `is_admin`(bool), `balance`(decimal), `phone_number`, `phone_verified_at`, `identity_document_path`, `identity_verified_at`, `profile_photo_path`, `is_fully_verified`(bool), `birth_date`, `gender`(M/F/Outro), `bi_number`(unique,nullable), `bi_expiry`, `province`, `municipality`, `address`, `data_verified`(bool), `kyc_score`(0-100), `kyc_bot_status`(auto_approved/pending_review/auto_rejected), `kyc_bot_analyzed_at`, `kyc_bot_notes`(JSON)

**`transactions`** (estados ACTUAIS na BD — 6, não 8):
`id`, `reference_id`(unique,KZ+5chars), `user_id`, `assigned_admin`(nullable), `exchange_rate_id`(nullable), `currency_from`, `currency_to`, `amount_sent`, `rate_applied`, `amount_received`, `fee_amount`(default 0), `status`(enum: **pending|awaiting_payment|processing|completed|cancelled|expired**), `expires_at`(nullable), `created_at`, `updated_at`

> ⚠️ Os estados `negotiating`, `payment_received`, `aoa_sent` e os campos `payment_received_at`, `aoa_sent_at`, `client_confirmed_at` ainda **NÃO existem na BD** — pertencem ao fluxo novo ainda a implementar.

**`beneficiaries`**: `id`, `user_id`, `bank_name`, `iban`, `holder_name`, `created_at`, `updated_at`

**`chat_messages`**: `id`, `transaction_id`(cascade), `sender_id`(cascade,nullable), `message_text`, `message_type`(text/image/document), `file_path`, `is_read`, `created_at`, `updated_at`

**`exchange_rates`**: `id`, `currency_from`, `currency_to`(default AOA), `rate`(decimal 15,4), `is_active`, `created_by`, `created_at`, `updated_at`

**`otp_codes`**: `id`, `user_id`(cascade), `type`(email/phone), `destination`, `code_hash`, `attempts`, `verified_at`, `expires_at`, `ip_address`, `created_at`, `updated_at`

**`audit_logs`** (imutável — sem `updated_at`, sem update/delete):
`id`, `user_id`, `user_email`, `user_role`, `action`, `category`(auth/kyc/transaction/admin/profile/beneficiary), `severity`(info/warning/critical), `target_type`, `target_id`, `target_reference`, `description`, `metadata`(JSON), `ip_address`, `user_agent`, `session_id`, `request_method`, `request_url`, `created_at`

**`kyc_documents`** (tabela criada mas **não usada** no código actual — uploads vão para campos do user):
`id`, `user_id`, `type`, `file_path`, `file_hash`(unique), `mime_type`, `file_size`, `status`, `reviewed_by`, `reviewed_at`, `rejection_reason`, `expires_at`, `created_at`, `updated_at`

---

## 5. Controllers (18)

| Controller | Responsabilidade | Linhas |
|---|---|---|
| `AdminController` | Dashboard, transações, KYC, taxas, utilizadores, chat admin | 455 |
| `AuditController` | Vista e filtros de audit logs | 121 |
| `BeneficiaryController` | Gestão de IBANs com anti-fraude | 102 |
| `ChatController` | Mensagens cliente em transação | 101 |
| `OtpController` | Verificação OTP por email | 81 |
| `ProfileController` | Perfil do utilizador | 60 |
| `TransactionController` | Criar, ver, upload comprovativo | 131 |
| `VerificationController` | KYC 4 passos + KycBot | 146 |
| Auth/* | Breeze standard (8 controllers) | — |

---

## 6. Models (7)

| Model | Traits / Notas |
|---|---|
| `User` | HasApiTokens, HasFactory, Notifiable, MustVerifyEmail; soft deletes |
| `Transaction` | belongsTo User, hasMany ChatMessage |
| `Beneficiary` | belongsTo User |
| `ChatMessage` | belongsTo Transaction, accessors para isImage/isPdf/fileUrl |
| `AuditLog` | **Imutável** — booted() bloqueia update/delete; só `created_at` |
| `ExchangeRate` | Modelo simples, sem relações declaradas |
| `OtpCode` | isValid(), markAsVerified(); belongsTo User |

---

## 7. Services (3)

| Service | Função | Notas |
|---|---|---|
| `AuditLogger` | Audit logging imutável com helpers por categoria | Nunca lança exceção |
| `KycBot` | Análise automática KYC (0-100 pontos, 8 critérios) | Limiares: ≥80 aprova, 50-79 review, <50 rejeita |
| `OtpService` | Geração, envio e validação de OTP email | Rate limit 3/hora, max 5 tentativas, expiry 15min |

> ⚠️ **`TransactionFlow` service mencionado no briefing NÃO existe** — a criar na Fase 3.

---

## 8. Views (51 templates Blade)

```
resources/views/
├── welcome.blade.php                 ← Landing page pública
├── dashboard.blade.php               ← Dashboard cliente
├── show.blade.php                    ← ⚠️ Ficheiro suspeito na raiz (verificar)
├── legal.blade.php, privacy.blade.php, terms.blade.php  ← Duplicados!
├── transaction/show.blade.php        ← Sala de transação
├── admin/
│   ├── dashboard.blade.php
│   ├── show.blade.php               ← ⚠️ Devia estar em admin/transactions/show
│   ├── transactions/index.blade.php
│   ├── users/index.blade.php
│   ├── kyc/(index|show).blade.php
│   ├── rates/(index|edit).blade.php
│   ├── messages/unread.blade.php
│   └── audit/(index|show|user).blade.php
├── auth/(login|register|forgot-password|reset-password|verify-email|confirm-password|otp/verify-email).blade.php
├── profile/edit.blade.php + partials/
├── legal/(privacy|terms).blade.php   ← Versões correctas
├── emails/otp.blade.php
├── layouts/(app|guest|navigation).blade.php
└── components/(auth-layout|legal/layout|modal|dropdown|input-*|button-*|nav-link|application-logo).blade.php
```

---

## 9. Rotas Principais

### Públicas
- `GET /` → welcome (landing)
- `GET /termos` → legal.terms
- `GET /privacidade` → legal.privacy

### Cliente (auth)
- `GET /dashboard` → dashboard (auth + verified)
- `GET|PATCH|DELETE /profile` → ProfileController
- `POST /verify/{data|phone|document|photo}` → VerificationController
- `POST /transaction` → TransactionController@store
- `GET /transaction/{ref}` → TransactionController@show
- `POST /transaction/{ref}/upload` → TransactionController@uploadReceipt
- `POST /transaction/{ref}/chat` → ChatController@sendMessage
- `POST /transaction/{ref}/read` → ChatController@markAsRead
- `POST|DELETE /beneficiary` → BeneficiaryController
- `GET|POST /verify/email{/send|/submit}` → OtpController

### Admin (prefix: /admin, name: admin.)
> ⚠️ Só middleware `auth` no grupo de rotas — verificação `is_admin` apenas no constructor do AdminController
- `/admin/dashboard`, `/admin/stats.json`
- `/admin/transactions`, `/admin/transaction/{id}`, `/admin/transaction/{id}/approve`, `/admin/transaction/{id}/chat`
- `/admin/users`
- `/admin/messages/unread`
- `/admin/rates`, `/admin/rates/{id}/edit`, `/admin/rates/{id}` (PUT)
- `/admin/kyc`, `/admin/kyc/{userId}`, `/admin/kyc/{userId}/approve|reject`
- `/admin/audit`, `/admin/audit/{id}`, `/admin/audit/user/{userId}`

---

## 10. Middleware Custom

- `IsAdmin` — verifica `Auth::user()->is_admin`; redireciona com erro se falhar
- Usado: No constructor do `AdminController` (inline, não via alias)
- Alias `is_admin` registado no Kernel mas **não aplicado nas rotas**

---

## 11. Identidade Visual

| Elemento | Detalhe |
|---|---|
| **Verde principal** | `#009d44` |
| **Tipografia display** | Syne (Google Fonts) |
| **Tipografia corpo** | DM Sans (Google Fonts) |
| **Logo mobile** | `public/assets/images/logos/logo.png` |
| **Logo tablet** | `public/assets/images/logos/logo2.png` |
| **Logo desktop** | `public/assets/images/logos/logo1.png` |
| **Som notificação** | `public/assets/sounds/notify.wav` |

---

## 12. Ambiente de Produção

| Item | Valor |
|---|---|
| **Servidor** | Hostinger (HPE ProLiant CloudLinux 8) |
| **SSH** | `u763057780@195.35.41.218` porta `65002` |
| **Laravel path** | `~/domains/kwanzasafe.com/kwanzasafe/` |
| **Public path** | `~/domains/kwanzasafe.com/public_html/` |
| **BD prod** | `u763057780_kwansafe_db` (MySQL) |
| **SMTP** | `smtp.hostinger.com:465 SSL` |
| **Email** | `geral@kwanzasafe.com` |

---

## 13. Utilizadores de Teste

| Role | Email |
|---|---|
| Admin | `chimucogeral@gmail.com` (is_admin=1) |
| Cliente | `valentimjeremias2021@gmail.com` |

---

## 14. Princípios Não-Negociáveis

1. **Lê antes de editar** — sempre abrir e ler ficheiro antes de modificar
2. **AuditLogger** em todas as ações sensíveis (auth, kyc, transactions, admin, beneficiary)
3. **AuditLogger nunca quebra a aplicação** — sempre em try/catch interno
4. **Audit logs são imutáveis** — nunca `update()` nem `delete()`
5. **Anti-fraude** — validação IBAN holder_name vs KYC full_name
6. **CSRF** em todos os forms
7. **Validation explícita** em todos os requests (nunca confiar em input directo)
8. **Mobile-first** — testar a 320px
9. **Português PT** (não BR) em todo o texto visível
10. **Backup antes de migrations destrutivas**
11. **Nunca commitar secrets** — só em `.env` (no `.gitignore`)
12. **APP_DEBUG=false apenas em produção**

---

## 15. Fluxo de Transação Actual (implementado)

```
1. Cliente cria via TransactionController@store → status: pending
2. Cliente envia comprovativo via uploadReceipt → status: processing
3. Admin aprova via AdminController@approve → status: completed
```

## 16. Fluxo de Transação NOVO (a implementar — Fase 3)

```
1. Cliente selecciona moeda + valor na calculadora
2. Clica "Iniciar Transação" → cria pending → redirect Sala de Transação
3. Mensagem automática do sistema na sala
4. [negociação via chat]
5. Cliente confirma pagamento → awaiting_payment
6. Admin clica "Recebi pagamento" → payment_received (novo estado)
7. Admin envia AOA → aoa_sent (novo estado)
8. Cliente confirma recepção → completed
Estados adicionais: negotiating, cancelled, expired
```

> Requer: nova migração (novos estados + campos timestamp), `TransactionFlow` service, lógica de botões faseados

---

## 17. Comandos Úteis

```bash
# Dentro de kwanzasafe/
php artisan migrate
php artisan route:list
php artisan view:clear
php artisan config:clear
php artisan tinker
php artisan test

# Produção (via SSH)
php artisan down --message="Manutenção em curso"
php artisan migrate --force
php artisan optimize
php artisan up
```

---

## 18. Histórico de Sprints

| Sprint | O que foi feito |
|---|---|
| Sprint 1 | Setup inicial, auth Breeze, modelos base, migrações |
| Sprint 2 | KYC 4 passos, KycBot, AuditLogger, OtpService |
| Sprint 3A | Dashboard cliente, painel admin, taxas, chat básico |
| Sprint 3B | Sala de transação, admin vivo com AJAX, audit trail UI |
| **PRÓXIMO** | Refactor layout corporativo, TransactionFlow, testes, SEO |
