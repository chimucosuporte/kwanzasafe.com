# KwanzaSafe — Plano de Refactor Detalhado

**Data:** 2026-05-13  
**Baseado em:** AUDITORIA_RELATORIO.md  
**Aprovação necessária:** Antes de iniciar cada fase numerada  

---

## LEITURA RÁPIDA — O QUE VAI MUDAR

```
SPRINT A — Correcções P0 (segurança imediata, ~2h)
SPRINT B — Backend: controllers, services, BD (~4h)
SPRINT C — Frontend: layout corporativo, landing, dashboard (~8h)
SPRINT D — Fluxo Novo de Transação (o grande feature) (~6h)
SPRINT E — SEO + Segurança HTTP (~2h)
SPRINT F — Testes automatizados (~4h)
SPRINT G — Deploy seguro para produção
```

Cada sprint é aprovado individualmente. Não se avança sem confirmação.

---

## SPRINT A — Correcções P0 (Segurança Crítica)

**Risco:** BAIXO (correcções cirúrgicas, sem refactor de lógica)  
**Estimativa:** 1-2 horas  
**Dependências:** Nenhuma — pode ser feito agora  

### A-01 — Adicionar `is_admin` às Rotas Admin

**Antes:**
```php
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(...)
```

**Depois:**
```php
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(...)
```

**Ficheiro:** `kwanzasafe/routes/web.php:71`  
**Risco:** Baixo — o alias `is_admin` já existe no Kernel  

---

### A-02 — Locking em `approve()` para Evitar Double-Approval

**Antes:**
```php
$transaction = Transaction::findOrFail($id);
$transaction->update(['status' => 'completed']);
```

**Depois:**
```php
DB::transaction(function () use ($id) {
    $transaction = Transaction::lockForUpdate()->findOrFail($id);
    if ($transaction->status === 'completed') {
        return back()->with('error', 'Transação já foi aprovada.');
    }
    $transaction->update(['status' => 'completed']);
    // ... chat message + audit log
});
```

**Ficheiro:** `kwanzasafe/app/Http/Controllers/AdminController.php:314`  
**Risco:** Baixo — DB::transaction() é reversível em caso de erro  

---

### A-03 — AuditLogger em Acções Admin Críticas

Adicionar chamadas `AuditLogger::` nos métodos que não as têm:

| Método | Acção a registar | Severidade |
|---|---|---|
| `approve()` | `transaction.approved` | critical |
| `kycApprove()` | `kyc.approved` | critical |
| `kycReject()` | `kyc.rejected` | critical |
| `ratesUpdate()` | `admin.rate_updated` | warning |
| `sendChatMessage()` | `transaction.admin_message` | info |

**Ficheiro:** `kwanzasafe/app/Http/Controllers/AdminController.php`  
**Risco:** Baixo — AuditLogger nunca quebra a aplicação  

---

### A-04 — Corrigir Locale/Timezone em `config/app.php`

**Antes:**
```php
'locale' => 'en',
'faker_locale' => 'en_US',
'timezone' => 'UTC',
```

**Depois:**
```php
'locale' => 'pt',
'faker_locale' => 'pt_PT',
'timezone' => 'UTC', // manter UTC — datas em BD devem ser UTC, converter na vista
```

**Ficheiro:** `kwanzasafe/config/app.php`  
**Risco:** Muito Baixo  

---

### A-05 — Rate Limiting em `transaction.store`

**Antes:**
```php
Route::post('/transaction', [TransactionController::class, 'store'])->name('transaction.store');
```

**Depois:**
```php
Route::post('/transaction', [TransactionController::class, 'store'])
    ->middleware('throttle:5,1')  // 5 por minuto por IP
    ->name('transaction.store');
```

**Risco:** Baixo  

---

## SPRINT B — Backend: Estrutura e Base de Dados

**Risco:** MÉDIO (inclui migrações — requer backup antes)  
**Estimativa:** 3-4 horas  
**Dependências:** Sprint A concluído  

### B-01 — Migração: Novos Estados de Transação + Campos de Timestamp

Esta migração implementa o "Fluxo Novo" descrito no briefing.

**O que adiciona à tabela `transactions`:**
```sql
-- Novos estados no ENUM
ALTER TABLE transactions MODIFY COLUMN status ENUM(
    'pending', 'negotiating', 'awaiting_payment',
    'payment_received', 'processing', 'aoa_sent',
    'completed', 'cancelled', 'expired'
) DEFAULT 'pending';

-- Novos campos de timestamp
ALTER TABLE transactions
    ADD COLUMN payment_received_at TIMESTAMP NULL,
    ADD COLUMN aoa_sent_at TIMESTAMP NULL,
    ADD COLUMN client_confirmed_at TIMESTAMP NULL,
    ADD COLUMN notes TEXT NULL; -- notas internas admin (privadas)
```

**Risco:** MÉDIO — altera enum em tabela existente  
**Pré-requisito:** `mysqldump kwanzasafe > backup_pre_sprint_b.sql` antes de correr  
**Reversão:** Script de rollback incluído na migration `down()`  

---

### B-02 — Migração: Unique Constraints em Falta

```php
// exchange_rates: apenas uma taxa activa por par de moedas
$table->unique(['currency_from', 'currency_to']);

// beneficiaries: um IBAN por utilizador (não global)
$table->unique(['user_id', 'iban']);

// chat_messages: índices para performance
$table->index('transaction_id');
$table->index('sender_id');
```

**Risco:** BAIXO se não há dados duplicados (verificar antes)  
**Verificação prévia:** 
```sql
SELECT currency_from, currency_to, COUNT(*) FROM exchange_rates GROUP BY currency_from, currency_to HAVING COUNT(*) > 1;
SELECT user_id, iban, COUNT(*) FROM beneficiaries GROUP BY user_id, iban HAVING COUNT(*) > 1;
```

---

### B-03 — Criar `TransactionFlow` Service

Novo service em `app/Services/TransactionFlow.php` que encapsula:
- Transição de estado com validação de fluxo (não pode ir de `completed` para `pending`)
- Criação da mensagem automática de sistema (sender_id = null)
- Chamada ao AuditLogger
- (Futuro) Notificação push/email

**Interface pública:**
```php
TransactionFlow::transition(Transaction $t, string $newStatus, ?User $actor = null): bool
TransactionFlow::systemMessage(Transaction $t, string $text): ChatMessage
TransactionFlow::allowedTransitions(): array  // mapa de estado → estados permitidos
```

**Risco:** BAIXO — novo ficheiro, não toca em código existente  

---

### B-04 — Dividir `AdminController` em Controllers Focados

**Antes:** 1 controller, 455 linhas, 15 métodos  

**Depois:**
```
AdminController.php        → Dashboard, stats, chart (~120 linhas)
KycAdminController.php     → kycIndex, kycShow, kycApprove, kycReject
RateAdminController.php    → ratesIndex, ratesEdit, ratesUpdate
UserAdminController.php    → usersIndex
MessageAdminController.php → unreadMessages
```

**Ficheiros de rotas** actualizados para apontar para os novos controllers.

**Risco:** MÉDIO — cuidado com nomes de rota (usar `name()` não muda, só o controller)  
**Estratégia:** Mover método a método, testar cada rota após mover  

---

### B-05 — Form Requests Dedicados

Criar em `app/Http/Requests/`:

| Request | Valida | Usado em |
|---|---|---|
| `CreateTransactionRequest` | moeda, valor_enviar (min:10) | TransactionController@store |
| `StoreBeneficiaryRequest` | bank_name, iban, holder_name | BeneficiaryController@store |
| `UpdateRateRequest` | rate (numeric, min:0.01), is_active | RateAdminController@update |
| `RejectKycRequest` | reason (required, max:500) | KycAdminController@reject |
| `SendChatMessageRequest` | message_text, attachment (file rules) | ChatController@sendMessage |

**Risco:** BAIXO — substituição directa, lógica idêntica  

---

### B-06 — Middleware de Headers de Segurança HTTP

Criar `app/Http/Middleware/SecurityHeaders.php`:
```php
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('X-Frame-Options', 'DENY');
$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
// Em produção (APP_ENV=production):
$response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains');
```

Registar em `Kernel.php` como middleware global.  
**Risco:** BAIXO  

> **⚠️ Decisão necessária:** CSP (Content-Security-Policy) — a app usa CDN Tailwind + Google Fonts + Alpine CDN. Um CSP restritivo bloqueia estes recursos. Proposta:
> - **Opção A:** CSP permissivo agora (`unsafe-inline` permitido), apertar depois ao mudar para Tailwind compilado ← **Recomendado**
> - **Opção B:** Não adicionar CSP até Tailwind estar compilado

---

### B-07 — Verificação de MIME Real em Uploads

Adicionar a todos os controllers que aceitam uploads:
```php
// Verificar MIME real com finfo, não só extensão
$finfo = new \finfo(FILEINFO_MIME_TYPE);
$realMime = $finfo->file($file->getRealPath());
if (!in_array($realMime, ['image/jpeg', 'image/png', 'application/pdf', ...])) {
    abort(422, 'Tipo de ficheiro não permitido.');
}
```

**Risco:** BAIXO — segurança adicional, não quebra fluxo existente  

---

### B-08 — Optimizar `buildChartData()` — 14 Queries → 1

**Antes:** 7 iterações × 2 queries = 14 queries por request AJAX

**Depois:**
```php
$data = Transaction::select(
        DB::raw('DATE(created_at) as date'),
        DB::raw('COUNT(*) as count'),
        DB::raw('SUM(amount_received) as volume')
    )
    ->where('status', 'completed')
    ->where('created_at', '>=', now()->subDays(6)->startOfDay())
    ->groupBy('date')
    ->get()
    ->keyBy('date');
```

**Risco:** BAIXO — optimização pura, resultado idêntico  

---

## SPRINT C — Frontend: Layout Corporativo e UX

**Risco:** MÉDIO (refactor de vistas — sem tocar em lógica backend)  
**Estimativa:** 6-8 horas  
**Dependências:** Sprint B concluído (para ter o fluxo novo disponível)  

> **⚠️ Decisão necessária — Tailwind CDN vs Compilado:**
> - **Opção A (CDN, manter):** Zero build, fácil para produção split-folder. ~4MB CSS. ← Mantém o que funciona
> - **Opção B (Compilado):** ~20-50KB CSS, melhor performance, permite purge de classes. Requer Vite funcional em prod ou Tailwind CLI standalone.
> - **Recomendação:** Opção B com Tailwind CLI (sem Vite) — instalar globalmente em prod, correr `tailwindcss -i input.css -o public/css/app.css --minify`

### C-01 — Layout Base Corporativo (`layouts/app.blade.php`)

Redesign completo em estilo fintech (Wise/Revolut/Remitly):
- Header sticky com logo + nav desktop + avatar dropdown
- Sidebar desktop (≥1024px) com navegação principal
- Bottom navigation mobile (≤1023px) com safe-area-inset
- Footer simples com copyright + links legais
- Sistema de toast notifications unificado (Alpine.js)
- Loading states em botões submit

---

### C-02 — Landing Page (`welcome.blade.php`)

Refactor completo da landing para aumentar conversão:

**Secções a criar/refazer (ordem de cima para baixo):**
1. **Hero** — headline forte + CTA "Começar agora" + calculadora integrada
2. **Stats bar** — "X clientes • Y países • Z transações concluídas" (valores reais ou estimados)
3. **Trust badges** — Compliance, SSL, KYC, Anti-fraude (ícones SVG)
4. **Como funciona** — 3 passos com ícones e animação subtil
5. **Calculadora** (se não no hero) — EUR/BRL/USDC → AOA em tempo real
6. **Comparison table** — KwanzaSafe vs banco tradicional vs hawala
7. **Testimonials** — 3 depoimentos (placeholders reais com nomes angolanos)
8. **FAQs** — Accordion acessível, 6-8 perguntas
9. **Footer rico** — links, social, WhatsApp, sede, regulação

**SEO desta página:**
- `<title>` optimizado: "KwanzaSafe | Enviar Euros para Angola — Câmbio EUR/AOA Seguro"
- `<meta description>` 155 chars
- Open Graph completo
- Schema.org: `Organization` + `FinancialProduct` + `FAQPage`
- `<html lang="pt">`

---

### C-03 — Dashboard Cliente (`dashboard.blade.php`)

Manter a estrutura de 5 abas mas elevar visualmente:
- Skeleton screens durante carregamento de transações
- Cards de transação com status visual claro (badge colorido)
- Empty state ilustrado quando sem transações
- Calculadora integrada na aba Início com CTA directo
- Saudação dinâmica por hora de Luanda (já implementada — manter)
- Unread badge no ícone de Suporte

---

### C-04 — Sala de Transação (`transaction/show.blade.php`)

A experiência mais crítica de confiança:
- Timeline vertical de estados (adaptar para os novos 8 estados)
- Chat com bolhas diferenciadas (cliente / admin / sistema)
- Botões de acção faseados com loading state e confirm modal
- Countdown se `expires_at` definido
- Upload de comprovativo com drag-and-drop + preview
- Som de notificação quando admin responde (já existe `notify.wav`)

---

### C-05 — Painel Admin

Manter lógica, elevar eficiência:
- Tabelas com sorting e filtros inline
- KYC show: layout de duas colunas (dados + documentos lado a lado)
- Audit trail: timeline visual em vez de tabela simples
- Stat cards com sparklines mini (Chart.js inline)
- Quick actions (approve/reject KYC directamente da lista sem entrar no show)

---

### C-06 — Páginas Auth (Login, Registo, etc.)

- Consistência com auth-layout.blade.php existente
- Password strength meter visual no registo
- "Mostrar/esconder password" toggle em todos os campos de password
- Link para Termos na página de registo
- Loading state no botão de submissão

---

### C-07 — Páginas de Erro (404, 500, 503)

Criar em `resources/views/errors/`:
- `404.blade.php` — "Página não encontrada" com link para dashboard/home
- `500.blade.php` — "Erro interno" com botão de reportar problema
- `503.blade.php` — "Manutenção em curso" com countdown estimado

---

### C-08 — Limpeza de Views Órfãs

Remover ou renomear:
- `resources/views/show.blade.php` → Verificar se é referenciada em algum lugar; se não, apagar
- `resources/views/legal.blade.php` → Apagar (duplicado de `legal/terms`)
- `resources/views/privacy.blade.php` → Apagar (duplicado de `legal/privacy`)
- `resources/views/terms.blade.php` → Apagar (duplicado de `legal/terms`)
- `resources/views/admin/show.blade.php` → Mover para `admin/transactions/show.blade.php`

**Risco:** BAIXO se verificarmos primeiro que não são referenciadas  

---

## SPRINT D — Fluxo Novo de Transação (Feature Principal)

**Risco:** ALTO (novo fluxo de estados, afecta BD e lógica core)  
**Estimativa:** 5-6 horas  
**Dependências:** Sprint B-01 (migração de estados) + Sprint B-03 (TransactionFlow service)  

### D-01 — Actualizar `TransactionController@store`

Após criar transação:
1. Criar mensagem automática de sistema (sender_id = null) via `TransactionFlow::systemMessage()`
2. Redirect para `transaction.show` (já feito)

### D-02 — Botões de Acção na Sala de Transação (Cliente)

| Estado Actual | Acção Disponível | Resultado |
|---|---|---|
| `pending` / `negotiating` | Enviar comprovativo | → `awaiting_payment` |
| `aoa_sent` | Confirmar recepção dos Kwanzas | → `completed` |
| `pending` | Cancelar (com confirm modal) | → `cancelled` |

### D-03 — Botões de Acção na Sala de Transação (Admin)

| Estado Actual | Acção Disponível | Resultado |
|---|---|---|
| `awaiting_payment` | "Confirmar Recebimento de Pagamento" | → `payment_received` |
| `payment_received` | "Confirmar Envio de AOA" | → `aoa_sent` |
| Qualquer activo | "Cancelar transação" | → `cancelled` |
| `completed` / `cancelled` | Sem acções | — |

### D-04 — Mensagens Automáticas por Estado

Via `TransactionFlow::systemMessage()`:

| Transição | Mensagem do Sistema |
|---|---|
| `pending` (criação) | "Transação criada. Um agente KwanzaSafe irá contactar-te em breve." |
| `awaiting_payment` | "Comprovativo recebido. A verificar o pagamento..." |
| `payment_received` | "✓ Pagamento confirmado. A processar o envio dos Kwanzas." |
| `aoa_sent` | "✓ Kwanzas enviados para o IBAN indicado. Por favor confirma a recepção." |
| `completed` | "✅ Transação concluída com sucesso. Obrigado por usar a KwanzaSafe!" |
| `cancelled` | "❌ Transação cancelada. Contacta o suporte se tiveres dúvidas." |

### D-05 — Notas Internas Admin

Campo `notes` na tabela (adicionado em B-01):
- Visível apenas para admins (não aparece no chat do cliente)
- Textarea no painel de admin dentro de cada transação

---

## SPRINT E — SEO + Performance

**Risco:** BAIXO  
**Estimativa:** 2-3 horas  
**Dependências:** Sprint C concluído  

### E-01 — Meta Tags em Todas as Páginas Públicas

Layout base com variáveis `@yield('title')`, `@yield('description')`, `@yield('og_image')`.

**Títulos por página (60 chars máx):**
- Home: "KwanzaSafe | Câmbio EUR/BRL para Kwanzas — Angola"
- Login: "Entrar | KwanzaSafe"
- Registo: "Criar Conta | KwanzaSafe"
- Dashboard: "Dashboard | KwanzaSafe"
- Termos: "Termos de Uso | KwanzaSafe"
- Privacidade: "Política de Privacidade | KwanzaSafe"

### E-02 — Schema.org JSON-LD

Adicionar à landing:
```json
{
  "@type": "FinancialService",
  "name": "KwanzaSafe",
  "description": "...",
  "url": "https://kwanzasafe.com",
  "telephone": "+5511933579009",
  "areaServed": ["AO", "PT", "BR", "US"]
}
```

### E-03 — Sitemap Dinâmico

Rota `GET /sitemap.xml` que devolve XML com rotas públicas reais (não hard-coded).

### E-04 — Auditar e Actualizar `robots.txt`

Verificar conteúdo actual, garantir que:
- Permite indexação das páginas públicas
- Bloqueia `/admin/*`, `/dashboard`, `/profile`, `/transaction/*`

### E-05 — Cache de Stats Admin

```php
Cache::remember("admin_stats_{$period}", 60, fn() => $this->buildStats($period));
Cache::remember('admin_chart_7d', 300, fn() => $this->buildChartData());
```

**Invalidar cache** em `TransactionFlow::transition()`.

---

## SPRINT F — Testes Automatizados (PHPUnit / Pest)

**Risco:** ZERO (só adiciona cobertura, não altera código)  
**Estimativa:** 4-5 horas  
**Dependências:** Todos os sprints anteriores  

> **⚠️ Decisão necessária — PHPUnit vs Pest:**
> - **PHPUnit:** Já instalado, sintaxe verbosa mas familiar
> - **Pest:** Sintaxe mais limpa, recomendado para Laravel moderno, requer `composer require pestphp/pest --dev`
> - **Recomendação:** Pest ← mais legível, melhor DX

### F-01 — Testes Unitários (Unit)

```
tests/Unit/
├── OtpServiceTest.php         — geração, hashing, rate limit, tentativas
├── KycBotTest.php             — 8 critérios, thresholds, notas JSON
├── AuditLoggerTest.php        — registo, imutabilidade, try-catch
├── TransactionFlowTest.php    — transições válidas, transições inválidas
└── HelpersTest.php            — ks_file(), ks_is_image(), ks_route()
```

### F-02 — Testes de Feature (Feature)

```
tests/Feature/
├── Auth/
│   ├── LoginTest.php          — login válido, inválido, rate limit
│   ├── RegisterTest.php       — campos obrigatórios, email único
│   └── OtpVerificationTest.php — envio, verificação, expiração
├── Transaction/
│   ├── CreateTransactionTest.php  — KYC incompleto bloqueado, criação válida
│   ├── TransactionFlowTest.php    — todas as transições de estado
│   └── UploadReceiptTest.php      — validação de ficheiro, status update
├── Beneficiary/
│   └── BeneficiaryTest.php    — criação, validação IBAN, fraud check, destroy
├── Admin/
│   ├── AdminAccessTest.php    — acesso negado a não-admin, acesso a admin
│   ├── KycApprovalTest.php    — aprovar, rejeitar com reason, audit logged
│   └── RatesTest.php          — actualizar taxa, visualizar lista
└── AuditLog/
    └── ImmutabilityTest.php   — não pode update, não pode delete
```

### F-03 — Cobertura Mínima

```bash
php artisan test --coverage --min=60
```

Objectivo: ≥60% de cobertura de linhas.

---

## SPRINT G — Deploy Produção

**Risco:** ALTO (produção)  
**Estimativa:** 1-2 horas + 30min monitoring  
**Dependências:** Todos os sprints aprovados e testados  

### Procedimento de Deploy

1. `mysqldump u763057780_kwansafe_db > backup_$(date +%Y%m%d_%H%M).sql`
2. `tar -czf backup_files_$(date +%Y%m%d_%H%M).tar.gz ~/domains/kwanzasafe.com/kwanzasafe/`
3. `php artisan down --message="Manutenção programada. Voltamos em 15 minutos."`
4. Upload via SFTP dos ficheiros alterados
5. `php artisan migrate --force`
6. `php artisan optimize` (config + route + view cache)
7. `php artisan up`
8. Smoke test manual (20 acções chave)
9. Monitor `storage/logs/laravel.log` durante 30 minutos

### Rollback Plan (< 2 minutos)
1. `php artisan down`
2. Restaurar ficheiros do tar
3. `mysql u763057780_kwansafe_db < backup_YYYY.sql`
4. `php artisan up`

---

## TABELA COMPLETA — ANTES → DEPOIS

| Módulo | Antes | Depois | Risco | Sprint |
|---|---|---|---|---|
| `routes/web.php` admin group | `middleware(['auth'])` | `middleware(['auth', 'is_admin'])` | Baixo | A |
| `AdminController@approve` | Sem lock | `DB::transaction()` + `lockForUpdate()` | Baixo | A |
| `AdminController` audit | 3 acções sem log | AuditLogger em todas as acções | Baixo | A |
| `config/app.php` locale | `'en'` | `'pt'` | Baixo | A |
| `transaction.store` rate limit | Sem limite | `throttle:5,1` | Baixo | A |
| `transactions` enum | 6 estados | 8 estados + 3 campos timestamp | Médio | B |
| `exchange_rates` | Sem unique | Unique (currency_from, currency_to) | Médio | B |
| `beneficiaries` | Sem unique | Unique (user_id, iban) | Baixo | B |
| `chat_messages` | Sem índices | Índices em transaction_id, sender_id | Baixo | B |
| Services | 3 services | + `TransactionFlow` (novo) | Baixo | B |
| `AdminController` | 455 linhas, 15 métodos | 5 controllers focados | Médio | B |
| Validação | Inline nos controllers | Form Requests dedicados | Baixo | B |
| Headers HTTP | Sem headers seguros | Middleware SecurityHeaders | Baixo | B |
| Uploads MIME | Só extensão/declaração | `finfo_file()` real | Baixo | B |
| `buildChartData()` | 14 queries | 1 query GROUP BY | Baixo | B |
| `welcome.blade.php` | Layout actual | Layout fintech corporativo + SEO | Médio | C |
| `dashboard.blade.php` | Layout actual | Skeleton screens, empty states | Médio | C |
| `transaction/show.blade.php` | Layout actual | Novos estados, confirm modals | Médio | C |
| Painel admin vistas | Layout actual | Tabelas, quick actions, sparklines | Médio | C |
| Pages de erro | Default Laravel | 404/500/503 branded | Baixo | C |
| Views órfãs | 4 ficheiros duplicados | Removidos/reorganizados | Baixo | C |
| Fluxo de transação | 3 passos lineares | 8 estados, sala negociação | Alto | D |
| Botões admin/cliente | approve() simples | Botões faseados por estado | Médio | D |
| Mensagens sistema | Uma mensagem em approve | Mensagem automática por transição | Médio | D |
| Notas internas admin | Não existe | Campo notes (privado) | Baixo | D |
| Meta tags SEO | Ausentes | Completas em todas as páginas | Baixo | E |
| Schema.org JSON-LD | Ausente | Organization + FinancialProduct + FAQ | Baixo | E |
| Sitemap | Estático | Dinâmico via rota | Baixo | E |
| Cache de stats admin | Sem cache | `Cache::remember()` 60s/5min | Baixo | E |
| Testes | 0 testes | ≥60% cobertura (Pest) | Nulo | F |
| Deploy | Manual | Procedimento documentado + rollback | Alto | G |

---

## PONTOS DE DECISÃO HUMANA OBRIGATÓRIOS

Antes de avançar, precisas de me dizer:

| # | Decisão | Opções |
|---|---|---|
| **D1** | Tailwind CDN ou Compilado? | A) Manter CDN / B) Tailwind CLI compilado ← recomendo B |
| **D2** | CSP agora ou depois do Tailwind compilado? | A) CSP permissivo agora / B) Aguardar ← recomendo A |
| **D3** | PHPUnit ou Pest para testes? | A) PHPUnit (já instalado) / B) Pest ← recomendo B |
| **D4** | Tabela `kyc_documents` — usar ou abandonar? | A) Migrar lógica para a tabela / B) Apagar tabela ← recomendo B (simplificar) |
| **D5** | Fazer backup e correr Sprint A agora, ou rever o plano primeiro? | A) Sprint A agora / B) Rever plano completo primeiro |

---

## ESTIMATIVA TOTAL

| Sprint | Descrição | Tempo Estimado |
|---|---|---|
| A | Correcções P0 críticas | 1-2h |
| B | Backend: BD, services, controllers | 3-4h |
| C | Frontend: layout corporativo | 6-8h |
| D | Fluxo novo de transação | 5-6h |
| E | SEO + performance | 2-3h |
| F | Testes automatizados | 4-5h |
| G | Deploy produção | 1-2h + 30min monitor |
| **Total** | | **22-30 horas** |

---

*Aguarda aprovação humana antes de iniciar qualquer Sprint.*  
*Recomendação de início: Sprint A (correcções P0) — risco baixo, impacto imediato na segurança.*
