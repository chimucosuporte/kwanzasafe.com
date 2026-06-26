# KwanzaSafe — Memória Mestre do Projeto

> Gerado em auditoria completa — 2026-05-12. Última actualização — 2026-06-04. Actualizar após cada sprint.

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
| **Testes** | PHPUnit + Pest | 10.x (**141 testes a passar**) |

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

**`transactions`** (fluxo completo já implementado):
`id`, `reference_id`(unique,KZ+5chars), `user_id`, `assigned_admin`(nullable), `exchange_rate_id`(nullable), `currency_from`, `currency_to`, `amount_sent`, `rate_applied`, `amount_received`, `fee_amount`(default 0), `status`(enum: **pending|negotiating|awaiting_payment|payment_received|processing|aoa_sent|completed|cancelled|expired**), `expires_at`(nullable), `payment_received_at`, `aoa_sent_at`, `client_confirmed_at`, `created_at`, `updated_at`

> ✅ Os estados `negotiating`, `payment_received`, `aoa_sent` e os timestamps `payment_received_at`, `aoa_sent_at`, `client_confirmed_at` **já existem na BD** e são geridos pelo serviço `TransactionFlow` (ver §7).

**`beneficiaries`**: `id`, `user_id`, `bank_name`, `iban`, `holder_name`, `created_at`, `updated_at`

**`chat_messages`**: `id`, `transaction_id`(cascade), `sender_id`(cascade,nullable), `message_text`, `message_type`(text/image/document), `file_path`, `is_read`, `created_at`, `updated_at`

**`exchange_rates`**: `id`, `currency_from`, `currency_to`(default AOA), `rate`(decimal 15,4), `is_active`, `created_by`, `created_at`, `updated_at`

**`otp_codes`**: `id`, `user_id`(cascade), `type`(email/phone), `destination`, `code_hash`, `attempts`, `verified_at`, `expires_at`, `ip_address`, `created_at`, `updated_at`

**`audit_logs`** (imutável — sem `updated_at`, sem update/delete):
`id`, `user_id`, `user_email`, `user_role`, `action`, `category`(auth/kyc/transaction/admin/profile/beneficiary), `severity`(info/warning/critical), `target_type`, `target_id`, `target_reference`, `description`, `metadata`(JSON), `ip_address`, `user_agent`, `session_id`, `request_method`, `request_url`, `created_at`

**`kyc_documents`** (tabela criada mas **não usada** no código actual — uploads vão para campos do user):
`id`, `user_id`, `type`, `file_path`, `file_hash`(unique), `mime_type`, `file_size`, `status`, `reviewed_by`, `reviewed_at`, `rejection_reason`, `expires_at`, `created_at`, `updated_at`

---

## 5. Controllers

> O painel admin já **não é monolítico**: foi dividido em controllers dedicados no namespace `App\Http\Controllers\Admin\*`.

| Controller | Responsabilidade |
|---|---|
| `AdminController` | Dashboard admin + `statsJson` (stats AJAX) |
| `Admin\TransactionAdminController` | Lista/tíquetes, detalhe, transições de estado, chat admin, poll |
| `Admin\KycAdminController` | Revisão KYC (index/show/approve/reject) |
| `Admin\RateAdminController` | Gestão de taxas |
| `Admin\UserAdminController` | Utilizadores (index/show/toggle-admin) |
| `Admin\MessageAdminController` | Mensagens não lidas |
| `AuditController` | Vista e filtros de audit logs |
| `BeneficiaryController` | Gestão de IBANs com anti-fraude |
| `ChatController` | Mensagens cliente em transação + poll/markAsRead |
| `OtpController` | Verificação OTP por email |
| `ProfileController` | Perfil do utilizador |
| `TransactionController` | Criar, ver, upload comprovativo, confirmar, cancelar, recibo |
| `VerificationController` | KYC 4 passos + KycBot |
| Auth/* | Breeze standard |

**Form Requests dedicados:** `CreateTransactionRequest`, `SendChatMessageRequest`, `ProfileUpdateRequest`.

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

## 7. Services (6)

| Service | Função | Notas |
|---|---|---|
| `AuditLogger` | Audit logging imutável com helpers por categoria | Nunca lança exceção |
| `KycBot` | Análise automática KYC (0-100 pontos, 8 critérios) | Limiares: ≥80 aprova, 50-79 review, <50 rejeita |
| `OtpService` | Geração, envio e validação de OTP email | Rate limit 3/hora, max 5 tentativas, expiry 15min |
| `TicketAssignment` | Round-robin "menos ocupado" de tíquetes a agentes de suporte activos | Fallback: sem agente → fila |
| `TransactionFlow` | Máquina de estados da transação | Valida transições, mensagem de sistema automática, timestamps, audit, email de estado, ledger ao concluir |
| `LedgerService` | Registo contabilístico ao concluir transação | `recordTransactionCompleted()` |

> ✅ **`TransactionFlow` já existe.** Usar sempre `TransactionFlow::transition($tx, $novoEstado, $actor)` para mudar estado e `TransactionFlow::systemMessage($tx, $texto)` para mensagens de sistema (sender_id null). Transições válidas definidas em `ALLOWED_TRANSITIONS`.

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
> ✅ O grupo aplica middleware `['auth', 'is_admin']` directamente nas rotas.
- `/admin/dashboard`, `/admin/stats.json`
- `/admin/transactions`, `/admin/transaction/{id}`, `/admin/transaction/{id}/{approve|cancel|assign|request-payment|payment-received|aoa-sent|chat|poll|receipt}`
- `/admin/users`, `/admin/users/{id}`, `/admin/users/{id}/toggle-admin`
- `/admin/messages/unread`
- `/admin/rates`, `/admin/rates/{id}/edit`, `/admin/rates/{id}` (PUT)
- `/admin/kyc`, `/admin/kyc/{userId}`, `/admin/kyc/{userId}/approve|reject`
- `/admin/audit`, `/admin/audit/{id}`, `/admin/audit/user/{userId}`

---

## 10. Middleware Custom

- `IsAdmin` — verifica `Auth::user()->is_admin` (staff: suporte ou super-admin); aplicado no grupo `/admin`
- `IsSuperAdmin` — verifica `Auth::user()->isSuperAdmin()`; aplicado a sub-rotas exclusivas (staff, recursos, reassign). Suporte → redirect `admin.dashboard`; cliente → `dashboard`
- Aliases `is_admin` e `is_super_admin` registados no Kernel

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

## 15. Fluxo de Transação (IMPLEMENTADO — gerido por `TransactionFlow`)

```
1. Cliente usa a calculadora no dashboard (moeda + valor → vê AOA a receber)
2. Clica "Iniciar Transação" → TransactionController@store cria pending
   → mensagem automática de sistema → redirect Sala de Transação
   (bloqueado se email/KYC incompletos)
3. Chat bidireccional cliente ↔ admin (polling 5s, som, recibos ✓✓)
4. Admin vê o tíquete em /admin/transactions (filtro Pendentes + badge de não lidas)
5. Cliente envia comprovativo (uploadReceipt) → awaiting_payment
6. Admin "Recebi pagamento" → payment_received
7. Admin "AOA enviados" → aoa_sent (+ email ao cliente)
8. Cliente "Confirmar Recepção" → completed (+ registo no ledger + recibo imprimível)
Estados extra: negotiating, cancelled (cliente/admin), expired
```

> Transições válidas e mensagens de sistema centralizadas em `TransactionFlow::ALLOWED_TRANSITIONS` / `SYSTEM_MESSAGES`.
> Cobertura: `tests/Feature/TransactionTest.php` e `ChatTest.php` (criação, KYC/email gating, valor mínimo, mensagem de sistema automática, chat cliente e admin, confirmação de recepção).

## 16. Pontos em aberto / a confirmar antes de produção

- **IBAN de pagamento** na Sala de Transação é placeholder (`PT50 0000 …`) — substituir pelo IBAN real.
- **Views Breeze órfãs**: `layouts/navigation.blade.php` e `layouts/guest.blade.php` não são usados (auth migrou para `x-auth-layout`); `guest` ainda referencia `@vite`.
- **Perfil** (`profile/edit` + partials) ainda em estilo Breeze cinzento e parcialmente em inglês — alvo do refactor corporativo.

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
| Sprint K–N | Cancelar/auto-expiry, comprovativo imprimível, perfil admin, agente, painel financeiro, soft deletes, OTP telefone, ledger, `is_fully_verified`, Tailwind compilado |
| Sprint N+ | `TransactionFlow` (máquina de estados), controllers admin divididos, `is_admin` nas rotas |
| Refactor views | Perfil corporativo, admin rates/edit, audit alinhado à marca |
| **Multi-equipa (Fases 0–5)** | Papéis (suporte/super-admin), gestão de funcionários, round-robin, canais (notas internas, recurso, staff DMs), navegação por papel — **141 testes** (ver §19) |
| **PRÓXIMO** | SEO, aplicar migrações em prod, substituir IBAN placeholder |

---

## 19. Arquitectura Multi-Equipa (Fases 0–5)

> Plataforma com 3 entidades e 4 canais de comunicação. **6 migrações novas** (`2026_06_04_*`) ainda por aplicar fora do ambiente de teste.

### Papéis (booleanos como fonte da verdade; `role` é espelho)
| Papel | Flags | Pode |
|---|---|---|
| **Cliente** | `is_admin=0` | Transacionar, falar no tíquete, abrir recurso |
| **Suporte** | `is_admin=1, is_super_admin=0` | Atender tíquetes, notas internas, DM a super-admin, escalar |
| **Super-Admin** | `is_super_admin=1` (⇒`is_admin=1`) | Tudo + gerir funcionários, arbitrar recursos, DM a qualquer staff |

- `User`: `isClient()/isSupport()/isStaff()/isSuperAdmin()`, `roleLabel()`; `booted()` força super⇒admin e espelha `role`. `SoftDeletes` activo (cliente self-delete = `forceDelete`; staff = soft).
- Coluna `users.is_active` desactiva contas de suporte sem eliminar.

### Canais de conversa — `chat_messages.channel` + `staff_messages`
| Canal | Coluna/tabela | Visível a |
|---|---|---|
| Tíquete cliente↔suporte | `channel='client'` | cliente, suporte, super-admin |
| Notas internas | `channel='internal'` | suporte, super-admin (**nunca** cliente) |
| Recurso | `channel='recourse'` | cliente, super-admin (**nunca** suporte) |
| Staff geral (DM) | tabela `staff_messages` | os 2 participantes |

> Regra central: `ChatMessage::visibleChannelsFor($user)`. Os polls (cliente e admin) e as páginas filtram **sempre** por aqui — testes de privacidade em `RecourseTest`.

### Tabelas novas
- `recourses` — caso de recurso (`status`: open→in_review→resolved|rejected, `reason`, `resolution`, `resolved_at`).
- `staff_messages` — DM staff (`sender_id`, `recipient_id`, `body`, `is_read`).

### Controllers/Serviços novos
- `Admin\StaffAdminController` (gerir funcionários, super-admin), `Admin\RecourseAdminController` (arbitrar, super-admin), `Admin\StaffChatController` (DMs, staff), `RecourseController` (cliente).
- `TicketAssignment` (round-robin), `StaffMessage::canMessage()` (suporte→só super-admin).

### Atribuição
- Round-robin "menos ocupado" entre suporte activo na criação da transação (`assigned_admin`). Super-admin reatribui via `/admin/transaction/{id}/reassign`. Index admin com filtro `assigned` (mine/unassigned/all).

---

## 20. API Mobile (Sanctum) — para a app React Native/Expo

> A app mobile (projeto Expo em `kwanzasafe-app/`, irmã de `kwanzasafe/`) **não consome Blade**: fala com uma API REST JSON. Esta camada é **100% aditiva** — nenhuma view/rota web existente é alterada. O site continua intacto.

### Princípios da API
- **Versionada** em `/api/v1/*` (prefixo). A rota legacy `GET /api/user` mantém-se.
- **Autenticação por tokens Sanctum (Bearer)** — *personal access tokens*, não cookies/sessão. O guard `auth:sanctum` faz fallback para o bearer token; `EnsureFrontendRequestsAreStateful` fica **comentado** (correcto para app nativa). Sem CSRF nos endpoints de token.
- **Novos ficheiros** em `app/Http/Controllers/Api/`, `app/Http/Requests/Api/`, `app/Http/Resources/`. Reutilizam Models, `AuditLogger` e validação do Breeze web (não duplicam lógica).
- Respostas JSON: login/registo → `{ token, user }`; `/me` → `{ user }`. Validação falha → 422 (`Accept: application/json`).
- `config/sanctum.php`: expiração configurável via `SANCTUM_TOKEN_EXPIRATION` (default `null` = não expira).

### Endpoints (FASE 0 — auth)
| Método | Rota | Auth | Controller | Notas |
|---|---|---|---|---|
| POST | `/api/v1/register` | — | `Api\AuthController@register` | `throttle:6,1`; dispara `Registered` (email de verificação); 201 `{token,user}` |
| POST | `/api/v1/login` | — | `Api\AuthController@login` | `throttle:6,1` + rate-limit por email/IP (espelha `LoginRequest` web) |
| POST | `/api/v1/logout` | `auth:sanctum` | `Api\AuthController@logout` | revoga **só** o token do pedido (`currentAccessToken()->delete()`) |
| GET | `/api/v1/me` | `auth:sanctum` | `Api\AuthController@me` | devolve `UserResource` |

- `UserResource` expõe papel/flags e estado KYC para gating na app; **nunca** password, token nem notas internas do bot.
- Auditoria: `AuditLogger::auth('register'|'login'|'logout', …, metadata:{channel:'mobile'})`.
- Testes: `tests/Feature/Api/AuthApiTest.php` (8 testes — registo, duplicado, login ok/falha, `/me` com/sem token, logout revoga, token revogado → 401).

### Endpoints (FASE 1 — domínio) — todos sob `auth:sanctum`
| Método | Rota | Controller | Notas |
|---|---|---|---|
| GET | `/api/v1/rates` | `Api\RateController@index` | taxas activas (calculadora); `rate` como string (precisão) |
| GET | `/api/v1/transactions` | `Api\TransactionController@index` | só as do próprio utilizador, desc |
| POST | `/api/v1/transactions` | `Api\TransactionController@store` | `throttle:5,1`; gating email/KYC → 403 (`code: email_unverified`/`kyc_incomplete`); cria `pending` + msg sistema + round-robin; 201 |
| GET | `/api/v1/transactions/{ref}` | `Api\TransactionController@show` | detalhe + `payment_account` (conta de recepção activa) |
| POST | `/api/v1/transactions/{ref}/receipt` | `…@uploadReceipt` | `throttle:20,1`; valida MIME real; → `awaiting_payment` |
| POST | `/api/v1/transactions/{ref}/confirm` | `…@confirm` | só em `aoa_sent` → `completed` (+ledger) |
| POST | `/api/v1/transactions/{ref}/cancel` | `…@cancel` | só em `pending`/`negotiating` |
| GET | `/api/v1/transactions/{ref}/messages` | `Api\ChatController@index` | `throttle:90,1`; `?after=<id>` p/ polling; marca lidas; só canais `client`+`recourse` |
| POST | `/api/v1/transactions/{ref}/messages` | `Api\ChatController@store` | `throttle:30,1`; texto e/ou anexo; 201 |
| POST | `/api/v1/transactions/{ref}/read` | `Api\ChatController@markAsRead` | marca recebidas como lidas |
| GET | `/api/v1/file/{path}` | `FileController@show` (reutilizado) | anexos/comprovativos privados, autorizados por token |

- Toda a lógica de negócio reutiliza `TransactionFlow`, `TicketAssignment`, `AuditLogger`, `PaymentAccount::activeFor()` — **não duplica** o web.
- Resources: `TransactionResource` (rótulo PT + flags `can_cancel`/`can_upload_receipt`/`can_confirm` + `payment_account`), `ChatMessageResource` (espelha o poll web; `file_url` aponta p/ `/api/v1/file/...`), `ExchangeRateResource`, `PaymentAccountResource`. Valores monetários como **string** (casts decimal) — nunca float.
- Form Requests: `Api\{CreateTransaction,UploadReceipt,SendChatMessage}Request`.
- Testes: `tests/Feature/Api/TransactionApiTest.php` (16 testes). **Suite total: 177 testes** a passar.
- ✅ Resolve o ponto em aberto §16 (IBAN placeholder): a app usa a conta de recepção real via `payment_account`.

### Servir localmente
- `php artisan serve` **não funciona** a partir de `kwanzasafe/` (o `public/index.php`/symlink `public_html` usa caminhos do layout de produção). Usar `php -S 127.0.0.1:8000 router.local.php` a partir da raiz do repo (espelha `public_html/`). Verificação automatizada é via `php artisan test` (boota o framework directamente, sem passar pelo `index.php`).

### App Expo (`kwanzasafe-app/`) — FASE 2 ✅
Projecto **React Native + TypeScript + Expo SDK 54** (irmão de `kwanzasafe/`, no mesmo repo). SDK 54 (não o 55/56) porque a **Expo Go publicada na Play Store / App Store só suporta o SDK 54** (Junho 2026) — a Expo Go corre apenas **um** SDK de cada vez. Para SDK 55/56 num telemóvel físico já **não basta** a Expo Go da loja: é preciso `eas go` (build próprio) ou um development build. Por isso mantemos o SDK 54 enquanto testamos via Expo Go. Downgrade/upgrade: editar `expo` no `package.json` → `npx expo install --fix` (reescreve as versões) → `npm install --legacy-peer-deps`. No Windows, apagar `node_modules` pode falhar por *long paths* (xcframeworks do `expo-image`) — usar o truque `robocopy <pasta-vazia> node_modules /MIR` antes do `Remove-Item`.
- Navegação: **Expo Router** (file-based, `src/app/`, layout `src/`); grupos `(auth)` e `(app)` com `AuthGate` (redirecção por sessão no `_layout` raiz).
- Estado: **TanStack Query** (servidor) + **Zustand** (`src/stores/auth.ts`, sessão). HTTP: **axios** (`src/api/client.ts`) injecta o Bearer e trata 401. Token em **expo-secure-store** (nunca AsyncStorage).
- Tema `src/theme/` (verde `#009d44`/preto/branco); fontes **Syne** + **DM Sans** via `@expo-google-fonts/*`. Base da API por `EXPO_PUBLIC_API_URL` (`src/config.ts`); ver `kwanzasafe-app/README.md` e `.env.example`.
- Entregue: ecrã de **login** real contra `/api/v1/login` (token → secure-store) + home autenticada (`/me`) com logout. `node_modules` fora do git.
- Verificação: `tsc --noEmit` limpo, `expo-doctor` 21/21, e `expo export` (Android) gera bundle Hermes válido (todo o grafo resolve). Nota: o `export`/Metro precisa de heap grande no Node 24 — usar `NODE_OPTIONS=--max-old-space-size=8192`.

### App Expo — FASE 3 ✅ (calculadora + transações)
Dashboard do cliente com **calculadora** (`GET /rates`), **criar transação** (`POST /transactions` → navega para o detalhe), **lista** (`GET /transactions`) e **detalhe** (`GET /transactions/{ref}`) com conta de pagamento e acções **confirmar**/**cancelar**.
- Rotas Expo Router: `(app)/index.tsx` (dashboard+calculadora+recentes), `(app)/transactions/index.tsx` (lista, FlatList+pull-to-refresh), `(app)/transactions/[ref].tsx` (detalhe). Navegação dinâmica via objecto `{ pathname: '/transactions/[ref]', params: { ref } }` (typed routes).
- Camada nova: `src/api/{rates,transactions}.ts` (desembrulha `{ data }` do Laravel Resource), `src/lib/format.ts` (dinheiro pt `1.234,56` + datas), componentes `Calculator`, `StatusBadge`, `TransactionRow`, `Header`. Tipos em `src/types/api.ts` (ExchangeRate/Transaction/PaymentAccount). Montantes sempre tratados como **string** vinda da API; conversão para número só para apresentação.
- Gating: calculadora só permite criar se `user.is_fully_verified` (backend reforça com 403 `email_unverified`/`kyc_incomplete`).
- ⚠️ **Typegen das rotas (`.expo/types/router.d.ts`)**: gerado de forma fiável pelo **`expo start`**, NÃO pelo `expo export` (o export por vezes produz tipos poluídos com `/../...` e `/transactions/index` — ignorar; é bug do export). Para validar tipos das rotas: arrancar `expo start` uma vez (gera o ficheiro) e só depois `tsc --noEmit`. O ficheiro está no `.gitignore`.
- Verificação desta fase: `tsc --noEmit` limpo (com router.d.ts gerado) + `expo export` Android OK (1484 módulos, bundle Hermes válido).

### App Expo — FASE 4 ✅ (sala de transação: chat + comprovativo)
Conversa cliente↔agente com **polling** e envio de **anexos/comprovativo**.
- Rotas reestruturadas: `(app)/transactions/[ref]/index.tsx` (detalhe + botões "Abrir conversa", "Enviar comprovativo") e `(app)/transactions/[ref]/chat.tsx` (sala). O antigo `[ref].tsx` foi convertido em pasta `[ref]/`.
- `src/api/chat.ts`: `fetchMessages(ref, after)` (polling delta a 4s), `sendMessage` (texto e/ou anexo, multipart), `uploadReceipt` (multipart → `awaiting_payment`). `src/lib/picker.ts` usa **expo-document-picker** (imagens + PDF; funciona no Expo Go).
- Chat: estado local + `setInterval` a 4s com cursor `after=<lastId>` (TanStack Query não encaixa bem no padrão delta); merge sem duplicados por id; auto-scroll. Bolhas mine/theirs/system. **Imagens inline carregadas com `expo-image` + header `Authorization: Bearer`** (o `file_url` aponta para `/api/v1/file/...` que exige token). Anexos: `FormData.append(field, { uri, name, type } as unknown as Blob)` + header `Content-Type: multipart/form-data`.
- Tipos novos em `src/types/api.ts`: `ChatMessage`, `PickedFile`.
- Verificação: `tsc` limpo (router.d.ts regenerado via `expo start`) + `expo export` Android OK (1501 módulos).
- ⏳ Por fazer nesta área (refinamento): abrir anexos PDF (download autenticado + share), câmara directa (expo-image-picker), badge de não lidas.

### App Expo — Onboarding ✅ (registo + recuperar password + verificação email)
Fluxos de entrada completos na app.
- **Backend (aditivo):** novas rotas `POST /api/v1/password/forgot` + `/password/reset` (públicas, OTP por email — não link), `POST /api/v1/email/verify/send` + `/email/verify` (auth). Controllers `Api\PasswordResetController` e `Api\EmailVerificationController`. `OtpService` ganhou `sendPasswordResetOtp`/`verifyPasswordResetOtp` (type `password_reset`; coluna `otp_codes.type` é string(20), sem enum). `forgot` devolve sempre resposta genérica (não revela emails); `reset` revoga todos os tokens após mudar password.
- **Traduções PT criadas** (`kwanzasafe/lang/pt/{validation,auth,passwords}.php`): NÃO existiam — `APP_LOCALE=pt` sem pasta `lang/` fazia aparecer chaves cruas (`auth.failed`, `validation.required`) em TODA a app (web+API). Agora resolvido.
- **App:** ecrãs `(auth)/register.tsx`, `(auth)/forgot-password.tsx`, `(auth)/reset-password.tsx` (recebe `email` por param), `(app)/verify-email.tsx` (envia código ao abrir + reenviar). Login com links "Criar conta"/"Esqueci-me da palavra-passe". Dashboard mostra banner tocável de confirmação de email quando `!email_verified`. API em `src/api/auth.ts` (`registerAccount`/`forgotPassword`/`resetPassword`/`sendEmailOtp`/`verifyEmailOtp`).
- Verificação: backend testado via tinker (send/verify/reset OK, Auth::validate OK) + curl (forgot 200 genérico, 401 sem token, 422 PT); app `tsc` limpo + `expo export` Android OK.

### App Expo — Perfil + Beneficiários ✅
- **Backend (aditivo):** `Api\ProfileController` (`PATCH /api/v1/profile` nome/email — muda email reinicia verificação; `PUT /api/v1/profile/password` exige atual, revoga outros tokens; `DELETE /api/v1/profile` forceDelete com password). `Api\BeneficiaryController` (`GET/POST/DELETE /api/v1/beneficiaries`) com **anti-fraude** (holder_name vs full_name do KYC → 422) + duplicado 422. `BeneficiaryResource`. **Reutiliza os Form Requests web** `ProfileUpdateRequest` e `StoreBeneficiaryRequest` (não duplica). Verificação da password atual é manual via `Hash::check` (guard sanctum é stateless; rule `current_password` não funciona aqui).
- **App:** ecrãs `(app)/profile.tsx` (dados, alterar password, link beneficiários, logout, eliminar conta) e `(app)/beneficiaries.tsx` (lista + adicionar com titular pré-preenchido do KYC + remover). Dashboard: header "Sair" → "Conta" (→ /profile, onde está o logout). `src/api/profile.ts` + `src/api/beneficiaries.ts`, tipo `Beneficiary`.
- Verificação: backend via curl autenticado (lista vazia, anti-fraude 422, criação 201 c/ IBAN normalizado, duplicado 422, delete 200, validação perfil PT) + app tsc limpo + expo export Android OK.

### App Expo — Onboarding intro (boas-vindas) ✅
Carrossel de primeira utilização (distinto dos ecrãs de auth).
- `(auth)/onboarding.tsx`: 3 slides (ScrollView horizontal `pagingEnabled` + dots, sem dependências), CTAs "Criar conta"/"Já tenho conta", botão "Saltar", e links **Termos/Privacidade** que abrem as páginas web via `expo-web-browser` (URL = `API_BASE_URL` + `/termos` `/privacidade`, servidas pelo Laravel web).
- Flag persistente `ks_onboarding_seen` em `secureStore.ts` (`getOnboardingSeen`/`setOnboardingSeen`). Store de auth ganhou `onboardingSeen` + `completeOnboarding()`; `bootstrap` lê token+flag em paralelo.
- **AuthGate** (`_layout.tsx`) actualizado: guest sem onboarding visto → `/onboarding`; guest com flag → `/login`; authed em grupo auth → `/`. Os CTAs chamam `completeOnboarding()` antes de navegar (evita loop do gate).
- Verificação: tsc limpo + expo export Android OK.

### App Expo — EAS dev build preparado ✅ (falta só o build com login Expo)
Infra de development build pronta para destrancar o KYC com câmara.
- Instalados `expo-dev-client` (runtime do dev build) e `expo-image-picker` (câmara + galeria p/ KYC).
- `eas.json` criado: perfis `development` (developmentClient + APK + distribution internal + channel development), `preview` (APK) e `production` (app-bundle, autoIncrement). `cli.appVersionSource=local`.
- `app.json`: adicionado `android.package` e `ios.bundleIdentifier` = **`com.kwanzasafe.app`** (obrigatório para EAS); plugin `expo-image-picker` com permissões PT (câmara/fotos).
- Verificado: `expo-doctor` 18/18, `expo install --check` OK, `tsc` limpo, `expo export` Android OK.
- **Falta (só o utilizador pode):** `npx eas-cli@latest login` (conta Expo grátis) → `eas build --profile development --platform android` (corre na cloud da Expo, ~10-20min, cria `extra.eas.projectId` no 1.º run; gera APK). Instalar APK no emulador via `adb install` ou no telemóvel; depois `npx expo start --dev-client` (já NÃO Expo Go). Build local (`--local`) desaconselhado neste PC (RAM/disco; precisa JDK17+SDK).

### App Expo — KYC 4 passos ✅ (escrito; câmara via dev build)
- **Backend (aditivo):** `Api\VerificationController` espelha o web — `POST /api/v1/kyc/personal` (dados pessoais → data_verified), `/kyc/phone/send` + `/kyc/phone/verify` (OTP telefone via OtpService), `/kyc/document`, `/kyc/photo` (multipart → disco privado). Cada passo corre `KycBot::analyzeAndApply` e devolve `{ message, kyc:{score,status}, user }`. Validação espelha o web (BI único, idade ≥18, BI futuro). Rótulos PT acrescentados a `lang/pt/validation.php` (full_name, bi_number, etc.).
- **App:** `(app)/kyc.tsx` — wizard de 4 passos (indicador de progresso): dados pessoais (datas como texto AAAA-MM-DD, género M/F), telefone (enviar/reenviar/confirmar OTP), documento (câmara ou ficheiro PDF/imagem via `pickAttachment`), selfie (câmara ou galeria). Ecrã final mostra score/status do bot (aprovado/revisão/rejeitado→recomeçar). `src/api/kyc.ts` + `src/lib/imagePicker.ts` (expo-image-picker: `capturePhoto`/`pickPhoto`, pede permissões). Banner KYC do dashboard agora abre `/kyc`.
- ⚠️ A **câmara** corre no **dev build** (expo-image-picker pode também funcionar no Expo Go, mas o caminho suportado é o dev build).
- Verificação: backend via curl (validação PT, personal→score 60 pending_review, phone OTP) + app tsc limpo + expo export Android OK.

### EAS dev build — CONCLUÍDO ✅ (2026-06-19)
Primeiro APK de development gerado na cloud (conta `edsonchimuco`, projectId `801f2c1f-...`).
- **2 falhas até acertar:** (1) `eas build` pediu `expo-updates` por causa de `channel` no perfil → instalou e mandou re-correr; (2) build errou em ~88s (UNKNOWN_ERROR, fase "Build complete hook") — relacionado com a config do EAS Update/channel. **Resolução: removido `"channel"` do perfil `development` no `eas.json`** → build passou (~14 min, FINISHED).
- Também adicionado `kwanzasafe-app/.npmrc` com `legacy-peer-deps=true` (rede de segurança p/ instalação na cloud, dado o downgrade SDK54 ter exigido legacy peers) e permissões Android limpas (só `CAMERA`).
- **Nota monorepo:** o `eas build` corre dentro de `kwanzasafe-app/` mas o git root é o repo Laravel pai → faz upload de ~81MB (working tree); funciona à mesma. Considerar `.easignore` no futuro p/ reduzir.
- APK instala-se no emulador via `adb install` e corre-se com `npx expo start --dev-client`. Comandos `eas` precisam do login Expo (já feito); builds correm na cloud (quota do plano grátis).

### Próximas fases mobile
- ~~FASE 0–4~~ ✅ · ~~Onboarding (registo+password+OTP)~~ ✅ · ~~Perfil + beneficiários~~ ✅ · ~~Onboarding intro~~ ✅ · ~~EAS dev build (infra)~~ ✅ · ~~KYC 4 passos~~ ✅ (falta só correr no dev build)
- **PRÓXIMO:** correr o EAS dev build (login Expo) e testar KYC com câmara; páginas legais nativas; histórico/recursos. Objectivo: **app cliente com todas as views da web** (quase completo).
- **FASES 5–6:** KYC com câmara (EAS dev build), push + biometria.
