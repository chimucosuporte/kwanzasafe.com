# KwanzaSafe — Relatório de Auditoria Técnica

**Data:** 2026-05-12  
**Auditor:** Claude Code (claude-sonnet-4-6)  
**Âmbito:** Auditoria completa do codebase — Laravel, BD, vistas, serviços, segurança, SEO  
**Ficheiros analisados:** 192 ficheiros PHP (excluindo vendor), 51 templates Blade, 16 migrações  

---

## RESUMO EXECUTIVO

O projeto KwanzaSafe tem uma **base sólida** para uma fintech: o AuditLogger é bem desenhado, o KycBot é funcional e a estrutura MVC está correcta. Porém, há **inconsistências críticas** entre o que foi descrito no briefing e o que realmente existe no código, além de **vulnerabilidades de segurança** moderadas e **dívida técnica** significativa.

**Estado actual:** Funcional para o fluxo básico. Não production-ready para escala.

---

## SECÇÃO 1 — INCONSISTÊNCIAS CRÍTICAS (Briefing vs Realidade)

### IC-01 — Versão do Framework INCORRECTA [ALTO]
- **Briefing diz:** Laravel 11, PHP 8.3
- **Realidade:** Laravel **10.50.2**, PHP ^8.1
- **Impacto:** Decisões de refactor devem considerar Laravel 10 (não 11). APIs e syntax diferem.
- **Acção:** Actualizar toda a documentação. Avaliar upgrade para Laravel 11 (requer PHP 8.2+).

### IC-02 — Estados de Transação INCOMPLETOS [ALTO]
- **Briefing diz:** 8 estados: `pending`, `negotiating`, `awaiting_payment`, `payment_received`, `aoa_sent`, `completed`, `cancelled`, `expired`
- **Realidade BD:** 6 estados: `pending`, `awaiting_payment`, `processing`, `completed`, `cancelled`, `expired`
- **Ausentes na BD:** `negotiating`, `payment_received`, `aoa_sent`
- **Ausentes também:** Campos `payment_received_at`, `aoa_sent_at`, `client_confirmed_at` não existem na tabela `transactions`
- **Impacto:** O "Fluxo Novo" descrito no briefing é um roadmap, não uma implementação. Requer migração.

### IC-03 — `TransactionFlow` Service NÃO EXISTE [MÉDIO]
- **Briefing menciona** este service para mensagens automáticas em transições de estado
- **Realidade:** Não existe em `app/Services/`. A lógica de mensagens automáticas está inline no `AdminController@approve`
- **Acção:** Criar o service na Fase 3.

### IC-04 — Tabela `kyc_documents` Criada mas IGNORADA [MÉDIO]
- Migration `2026_03_26_213539_create_kyc_documents_table.php` cria a tabela com estrutura robusta
- O código KYC guarda ficheiros directamente em campos do User (`identity_document_path`, `profile_photo_path`)
- A tabela `kyc_documents` tem 0 registos e 0 referências no código
- **Acção:** Decidir: usar a tabela e migrar lógica, ou apagar a migration (se ainda sem dados em prod)

### IC-05 — `assigned_admin` Não é Utilizado [BAIXO]
- A coluna `assigned_admin` existe na tabela `transactions`
- Nenhum controller atribui transações a admins específicos
- O `AdminController@approve` usa `Auth::id()` mas não guarda em `assigned_admin`

---

## SECÇÃO 2 — VULNERABILIDADES DE SEGURANÇA

### S-01 — Protecção Admin Apenas no Controller, Não nas Rotas [ALTO]
```php
// routes/web.php — linha 71
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // ← Falta 'is_admin' aqui!
```
- O middleware `is_admin` está registado em `Kernel.php` com alias mas **nunca é aplicado nas rotas**
- A protecção existe apenas no `__construct()` do `AdminController`
- **Risco:** Se um futuro controller admin não tiver o `__construct()`, as rotas ficam expostas a qualquer utilizador autenticado
- **Correcção:** Adicionar `'is_admin'` ao middleware group das rotas admin

### S-02 — Race Condition em `approve()` [ALTO]
```php
// AdminController.php — linha 314
$transaction = Transaction::findOrFail($id);
$transaction->update(['status' => 'completed']);  // ← Sem lock!
```
- Dois admins a aprovar simultaneamente podem criar double-completion
- **Correcção:** Usar `DB::transaction()` com `lockForUpdate()`

### S-03 — AuditLogger Ausente em Acções Admin Críticas [ALTO]
Os seguintes métodos **não registam no audit log**:
- `AdminController@approve` — aprovação de transação
- `AdminController@kycApprove` — aprovação KYC
- `AdminController@kycReject` — rejeição KYC
- `AdminController@ratesUpdate` — alteração de taxa de câmbio
- `AdminController@sendChatMessage` — mensagem admin

### S-04 — Validação de Upload de Ficheiros Inconsistente [MÉDIO]
- `TransactionController@uploadReceipt`: valida `mimes:pdf,jpg,jpeg,png` (sem webp)
- `AdminController@sendChatMessage`: valida `mimes:pdf,jpg,jpeg,png,webp`
- `VerificationController@uploadDocument`: sem confirmação de lista exacta
- Nenhum verifica **content-type real** (MIME sniffing) — só extensão/declaração do cliente
- **Correcção:** Usar `finfo_file()` para verificar MIME real do ficheiro

### S-05 — Sem Rate Limiting em `transaction.store` [MÉDIO]
- Um utilizador verificado pode criar transações em loop sem limite
- `routes/web.php` não aplica `throttle` na rota `POST /transaction`
- **Correcção:** `Route::post('/transaction', ...)->middleware('throttle:10,1')`

### S-06 — Sem Unique Constraint em `exchange_rates(currency_from, currency_to)` [MÉDIO]
- Possível criar múltiplas taxas activas para o mesmo par de moedas
- A calculadora pode mostrar resultado incorrecto se há duplicados
- **Correcção:** Migration para adicionar `$table->unique(['currency_from', 'currency_to'])`

### S-07 — Sem Unique Constraint em `beneficiaries(user_id, iban)` [BAIXO]
- A verificação de IBAN duplicado está apenas na aplicação (não na BD)
- **Correcção:** `$table->unique(['user_id', 'iban'])`

### S-08 — Soft Deletes Inconsistentes [BAIXO]
- `users` tem `deleted_at` (soft delete) ✅
- `transactions`, `beneficiaries`, `chat_messages` **não têm** soft delete
- Transações canceladas são hard-deleted, perdendo dados para auditoria

### S-09 — Verificação de Telefone é Placeholder [MÉDIO]
```php
// VerificationController.php — updatePhone()
$user->update(['phone_verified_at' => now()]);  // ← Sem enviar SMS!
```
- O campo é marcado como verificado sem qualquer verificação real
- O KycBot conta 10 pontos por `phone_verified_at` não nulo
- Um utilizador pode inflar o score KYC ao submeter qualquer número de telefone

### S-10 — Headers de Segurança HTTP Ausentes [MÉDIO]
Nenhuma configuração encontrada para:
- `Strict-Transport-Security`
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Content-Security-Policy`
- `Referrer-Policy`

---

## SECÇÃO 3 — DÍVIDA TÉCNICA

### DT-01 — Validação em Controllers em vez de Form Requests [MÉDIO]
- `TransactionController@store` tem validação inline
- `AdminController@kycReject` tem validação inline
- `AdminController@ratesUpdate` tem validação inline
- **Padrão correcto:** Um `FormRequest` por endpoint POST/PUT sensível
- **Afecta:** manutenibilidade, testabilidade, reutilização

### DT-02 — Queries N+1 Potenciais [MÉDIO]
```php
// AdminController@buildChartData — 7 iterações × 2 queries cada = 14 queries
for ($i = 6; $i >= 0; $i--) {
    $vol = Transaction::whereDate(...)->sum(...);   // Query 1
    $count = Transaction::whereDate(...)->count(); // Query 2
}
```
- Pode ser resolvido com uma única query GROUP BY
- `ChatMessage` em listas de transação pode causar N+1 se não eager-loaded

### DT-03 — Views Duplicadas / Órfãs [BAIXO]
Ficheiros suspeitos que precisam de limpeza:
- `resources/views/show.blade.php` — o que é? (não referenciado em rotas)
- `resources/views/legal.blade.php` — duplicado de `legal/terms`?
- `resources/views/privacy.blade.php` — duplicado de `legal/privacy`?
- `resources/views/terms.blade.php` — duplicado de `legal/terms`?
- `resources/views/admin/show.blade.php` — devia estar em `admin/transactions/show.blade.php`

### DT-04 — AdminController é Demasiado Grande [MÉDIO]
- 455 linhas, 15+ métodos
- Mistura dashboard, transações, utilizadores, KYC, taxas, chat, mensagens
- **Sugestão:** Dividir em `KycAdminController`, `RatesAdminController`, `UserAdminController`

### DT-05 — Duplicate Cast em User Model [BAIXO]
```php
// User.php — birth_date e bi_expiry aparecem 2x em $casts
'birth_date' => 'date',  // linha X
'bi_expiry'  => 'date',  // linha X+1
// ... mais abaixo, repetido
'birth_date' => 'date',
'bi_expiry'  => 'date',
```

### DT-06 — Geração de `reference_id` Não Garante Unicidade [BAIXO]
```php
$referenceId = 'KZ' . strtoupper(Str::random(5));
// KZ + 5 chars = ~60M combinações
// Com unique constraint na BD, há retry automático? Não.
```
- Se houver colisão (raro), o insert falha com erro 500 sem mensagem útil
- **Correcção:** Wrap em try-catch com retry, ou usar formato mais robusto (UUID truncado)

### DT-07 — 0 Testes Automatizados [ALTO]
- `tests/` existe com estrutura Pest/PHPUnit padrão
- Sem nenhum teste de feature, unit ou browser escrito
- **Impacto:** Impossível refactor seguro; qualquer mudança pode quebrar algo sem saber

### DT-08 — `is_fully_verified` Não é Calculado Automaticamente [BAIXO]
- Campo existe na BD mas nada o actualiza automaticamente
- Dependência entre `phone_verified_at`, `identity_verified_at` e `data_verified` não é encapsulada

### DT-09 — `balance` em Users Não Tem Ledger [BAIXO]
- Campo `balance` existe (decimal 15,2) mas não há tabela de movimentos
- Impossível reconciliar ou auditar saldo sem ledger de transações

### DT-10 — Locales e Timezone Incorrectos na Config [MÉDIO]
```php
// config/app.php
'locale' => 'en',           // Devia ser 'pt'
'faker_locale' => 'en_US',  // Devia ser 'pt_PT' ou 'pt_AO'
'timezone' => 'UTC',        // Considerar 'Africa/Luanda' ou manter UTC explícito
```

---

## SECÇÃO 4 — SEO E PERFORMANCE

### SEO-01 — Sem Meta Tags nas Views Públicas [ALTO]
- `welcome.blade.php` não tem `<meta name="description">`
- Sem Open Graph tags (`og:title`, `og:description`, `og:image`)
- Sem Twitter Card
- Sem Schema.org JSON-LD

### SEO-02 — `robots.txt` e `sitemap.xml` Existem mas Não Verificados [MÉDIO]
- Ficheiros existem em `public/`
- Conteúdo não auditado — podem ter configurações desactualizadas

### SEO-03 — `<html lang>` Provavelmente Incorrecto [MÉDIO]
- Se os layouts usam `lang="en"` (default Breeze), o Google indexa como conteúdo inglês
- **Correcção:** `<html lang="pt">` em todos os layouts

### SEO-04 — Sem Favicon SVG / Apple Touch Icon [BAIXO]
- `favicon.ico` existe
- Sem `apple-touch-icon.png`, sem `favicon.svg`, sem `manifest.json`

### PERF-01 — Tailwind via CDN [MÉDIO]
- CDN carrega ~4MB de CSS não optimizado
- Em produção, devia ser Tailwind compilado (reduz para ~20-50KB)
- **Trade-off:** O briefing menciona problemas com Vite em produção — avaliar alternativa (CLI standalone do Tailwind)

### PERF-02 — 14 Queries para Gráfico de 7 Dias [MÉDIO]
- `buildChartData()` faz 2 queries por dia × 7 dias = 14 queries
- Pode ser uma query: `SELECT DATE(created_at), COUNT(*), SUM(amount_received) FROM transactions WHERE created_at >= ? GROUP BY DATE(created_at)`

### PERF-03 — Sem Cache de Queries Pesadas [MÉDIO]
- Dashboard admin recalcula stats a cada request (e de 30 em 30 segundos via AJAX)
- `buildStats()` faz ~10 queries por chamada
- **Correcção:** `Cache::remember('admin_stats', 60, fn() => $this->buildStats($period))`

### PERF-04 — Sem Índices em `chat_messages` [MÉDIO]
- Faltam índices em `chat_messages(transaction_id)` e `chat_messages(sender_id)`
- Comum em queries de sala de transação

---

## SECÇÃO 5 — PONTOS FORTES (Não Tocar)

1. **AuditLogger** — design excelente, robusto, nunca quebra a app, imutável
2. **OtpService** — bem implementado: hash, rate limit, retry tracking, masking
3. **KycBot** — lógica de scoring clara, auto-decisão configurável, notas em JSON
4. **Anti-fraude IBAN** — comparação holder_name vs KYC full_name com normalização
5. **Soft delete em Users** — dados nunca apagados permanentemente
6. **Eager loading em transações** — `with('user')` usado correctamente em vários locais
7. **`ks_file()` helper** — solução elegante para localhost vs produção
8. **Schema de audit_logs** — índices compostos correctos, context rico (IP, session, URL)
9. **KYC Bot com fallback humano** — score entre 50-79 vai para revisão manual

---

## SECÇÃO 6 — LISTA PRIORIZADA DE PROBLEMAS

### P0 — CRÍTICO (corrigir antes de qualquer deploy)

| ID | Problema | Ficheiro |
|---|---|---|
| S-01 | Rotas admin sem middleware is_admin | routes/web.php |
| S-02 | Race condition em approve() | AdminController.php |
| S-03 | Sem audit log em kycApprove/kycReject/approve | AdminController.php |
| IC-01 | Framework é Laravel 10, não 11 | Documentação |
| IC-02 | Estados de transação incompletos vs roadmap | Toda a app |

### P1 — IMPORTANTE (corrigir no próximo sprint)

| ID | Problema | Ficheiro |
|---|---|---|
| S-04 | MIME real não verificado em uploads | Vários controllers |
| S-05 | Sem rate limiting em transaction.store | routes/web.php |
| S-06 | Sem unique constraint em exchange_rates | Migration nova |
| S-09 | Verificação de telefone é fake | VerificationController |
| S-10 | Sem headers HTTP de segurança | Middleware novo |
| DT-07 | 0 testes automatizados | tests/ |
| DT-04 | AdminController demasiado grande (455 linhas) | AdminController |
| DT-10 | Locale/timezone errados | config/app.php |
| SEO-01 | Sem meta tags / Open Graph | welcome.blade.php |
| PERF-02 | 14 queries para gráfico 7 dias | AdminController |

### P2 — NICE-TO-HAVE (backlog)

| ID | Problema |
|---|---|
| S-07 | Unique constraint em beneficiaries(user_id, iban) |
| S-08 | Soft delete em transactions e beneficiaries |
| DT-01 | Validação em FormRequests dedicados |
| DT-02 | Queries N+1 no gráfico |
| DT-03 | Limpeza de views duplicadas/órfãs |
| DT-05 | Duplicate cast em User model |
| DT-06 | Retry em colisão de reference_id |
| DT-08 | Cálculo automático de is_fully_verified |
| IC-04 | Decisão sobre tabela kyc_documents |
| IC-05 | Usar assigned_admin ou remover coluna |
| SEO-02 | Auditar robots.txt e sitemap.xml |
| SEO-03 | `lang="pt"` nos layouts |
| PERF-01 | Tailwind compilado vs CDN |
| PERF-03 | Cache de stats admin |
| PERF-04 | Índices em chat_messages |

---

## SECÇÃO 7 — RECOMENDAÇÕES ARQUITECTURAIS

### R-01 — Dividir AdminController
Sugestão de divisão:
```
AdminController       → Dashboard + stats (manter)
KycAdminController    → KYC approve/reject/index/show
RateAdminController   → Taxas CRUD
UserAdminController   → Lista utilizadores
MessageAdminController → Mensagens não lidas
```

### R-02 — Form Requests por Endpoint Sensível
```
CreateTransactionRequest
StoreBeneficiaryRequest
UpdateRateRequest
RejectKycRequest
SendChatMessageRequest
```

### R-03 — Implementar TransactionFlow Service
Encapsular toda a lógica de transição de estado + mensagens automáticas:
```php
TransactionFlow::transition($transaction, 'payment_received', $admin);
// → actualiza status, cria chat message sistema, audit log, notificação
```

### R-04 — Middleware para Headers de Segurança
Criar `app/Http/Middleware/SecurityHeaders.php` e registar no Kernel global.

### R-05 — Considerar Upgrade para Laravel 11
- Laravel 10 tem suporte até Março 2025 (EOL)
- Laravel 11 requer PHP 8.2+ e tem melhorias significativas
- Avaliar na Fase 4 com cuidado (quebras de API)

---

## SECÇÃO 8 — FICHEIROS QUE PRECISAM ATENÇÃO IMEDIATA

| Ficheiro | Problema | Prioridade |
|---|---|---|
| `routes/web.php:71` | Middleware admin incompleto | P0 |
| `AdminController.php:314` | approve() sem DB lock | P0 |
| `AdminController.php:425-453` | kycApprove/kycReject sem AuditLogger | P0 |
| `resources/views/show.blade.php` | Ficheiro órfão, propósito desconhecido | P2 |
| `resources/views/legal.blade.php` | View duplicada (3x) | P2 |
| `resources/views/admin/show.blade.php` | Localização errada | P2 |
| `config/app.php` | locale='en', faker='en_US' | P1 |
| `VerificationController.php` | phone_verified_at sem SMS real | P1 |

---

*Relatório gerado em auditoria de Fase 1. Aprovação humana necessária antes de avançar para Fase 2 (Plano de Refactor).*
