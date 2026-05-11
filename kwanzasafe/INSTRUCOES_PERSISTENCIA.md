# 🔧 PATCH — Persistência de Estado nos Dashboards

**Objetivo:** Adicionar persistência de tab ativa, rascunhos de mensagens e scroll position aos dashboards existentes **sem reescrever tudo**.

---

## 📁 Passo 1 — Instalar o Ficheiro JavaScript

| Ficheiro | Destino |
|---|---|
| `public/js/ks-persist.js` | `public/js/ks-persist.js` *(criar)* |

**Produção:** `public_html/js/ks-persist.js`

---

## 📁 Passo 2 — Atualizar o Layout Base

| Ficheiro | Destino |
|---|---|
| `views/layouts/app.blade.php` | `resources/views/layouts/app.blade.php` *(substituir)* |

⚠️ A única mudança vs versão anterior é **1 linha adicionada** entre Alpine.js e as variáveis CSS:

```html
{{-- ============ PERSISTÊNCIA DE ESTADO (Sprint 3) ============ --}}
<script src="{{ asset('js/ks-persist.js') }}"></script>
```

---

## 📁 Passo 3 — PATCH: `dashboard.blade.php` do Cliente

Abre `resources/views/dashboard.blade.php` e faz estas **3 pequenas alterações**:

### **3.1 — Tab inicial via `ksPersist`**

**PROCURA** (perto do topo, dentro do `@php`):

```php
$allowedTabs = ['home','history','iban','support','profile'];
$initialTab = in_array(request()->query('tab'), $allowedTabs)
              ? request()->query('tab')
              : 'home';
```

Esta parte **já está correta**! O Laravel já lê `?tab=xxx`. Só precisamos garantir que o Alpine.js **atualiza a URL** quando trocas de tab.

### **3.2 — Atualização da função `clientDashboard()`**

**PROCURA** o script no fim do ficheiro:

```javascript
function clientDashboard() {
    return {
        activeTab: '{{ $initialTab }}',
        transactions: @json($txForJs),
        kycApproved: {{ $kycStatus['approved'] ? 'true' : 'false' }},
        openIbanModal: false,
        filter: 'all',
        filters: [
            {key:'all', label:'Todas'},
            // ...
        ],

        init() {
            const urlTab = new URLSearchParams(window.location.search).get('tab');
            if (urlTab && ['home','history','iban','support','profile'].includes(urlTab)) {
                this.activeTab = urlTab;
            }
            this.$watch('activeTab', t => localStorage.setItem('ks_tab', t));
        },

        setTab(t) {
            this.activeTab = t;
            window.scrollTo({top:0, behavior:'smooth'});
        },
```

**SUBSTITUI** pela versão abaixo (usa o `ksPersist`):

```javascript
function clientDashboard() {
    return {
        activeTab: '{{ $initialTab }}',
        transactions: @json($txForJs),
        kycApproved: {{ $kycStatus['approved'] ? 'true' : 'false' }},
        openIbanModal: false,
        filter: window.ksPersist ? window.ksPersist.loadState('client_filter', 'all') : 'all',
        filters: [
            {key:'all', label:'Todas'},
            {key:'completed', label:'Concluídas'},
            {key:'processing', label:'Em Processo'},
            {key:'pending', label:'Pendentes'},
            {key:'cancelled', label:'Canceladas'},
        ],

        init() {
            // Tab inicial vem da URL (PHP já tratou disso via query string)
            // Alpine observa mudanças e atualiza URL sem recarregar
            this.$watch('activeTab', t => {
                if (window.ksPersist) window.ksPersist.saveTab(t);
            });

            // Filtro persistido
            this.$watch('filter', f => {
                if (window.ksPersist) window.ksPersist.saveState('client_filter', f);
            });

            // Listener para botão "voltar" do browser
            window.addEventListener('popstate', () => {
                const urlTab = new URLSearchParams(window.location.search).get('tab') || 'home';
                if (['home','history','iban','support','profile'].includes(urlTab)) {
                    this.activeTab = urlTab;
                }
            });
        },

        setTab(t) {
            this.activeTab = t;
            // Scroll suave para o topo da área de conteúdo (não da página inteira)
            const content = document.querySelector('.ks-content');
            if (content) content.scrollTo({top: 0, behavior: 'smooth'});
            else window.scrollTo({top: 0, behavior: 'smooth'});
        },
```

### **3.3 — Resto do `clientDashboard()` fica igual**

As outras funções (`tabTitle`, `pendingCount`, `completedCount`, etc.) ficam exatamente como estavam.

---

## 📁 Passo 4 — PATCH: `transaction/show.blade.php` (Sala de Chat do Cliente)

Na sala de transação, o cliente escreve mensagens no chat. Vamos persistir esses rascunhos.

**PROCURA** a `<textarea>` dentro do formulário de chat:

```html
<textarea
    name="message_text"
    class="tr-chat-form__textarea"
    placeholder="Escreve a tua mensagem..."
    rows="1"
    @input="$event.target.style.height='auto';$event.target.style.height=Math.min($event.target.scrollHeight,120)+'px';"></textarea>
```

**SUBSTITUI** por (adiciona o atributo `data-ks-persist`):

```html
<textarea
    name="message_text"
    class="tr-chat-form__textarea"
    placeholder="Escreve a tua mensagem..."
    rows="1"
    data-ks-persist="chat_client_{{ $transaction->reference_id }}"
    @input="$event.target.style.height='auto';$event.target.style.height=Math.min($event.target.scrollHeight,120)+'px';"></textarea>
```

**Pronto.** O `ks-persist.js` trata do resto automaticamente:
- Guarda enquanto escreves (debounce 300ms)
- Ao voltar à página, restaura o texto
- Ao submeter com sucesso, apaga o rascunho

---

## 📁 Passo 5 — PATCH: `admin/show.blade.php` (Chat do Admin)

Mesma lógica no painel admin:

**PROCURA** a `<textarea>` do chat admin:

```html
<textarea
    name="message_text"
    id="atx-textarea"
    class="atx-chat-form__textarea"
    placeholder="Escreve a mensagem para o cliente..."
    rows="1"
    @input="$event.target.style.height='auto';$event.target.style.height=Math.min($event.target.scrollHeight,120)+'px';"></textarea>
```

**SUBSTITUI** por:

```html
<textarea
    name="message_text"
    id="atx-textarea"
    class="atx-chat-form__textarea"
    placeholder="Escreve a mensagem para o cliente..."
    rows="1"
    data-ks-persist="chat_admin_{{ $transaction->reference_id }}"
    @input="$event.target.style.height='auto';$event.target.style.height=Math.min($event.target.scrollHeight,120)+'px';"></textarea>
```

---

## 🧪 Como Testar

### **Teste 1: Persistência de Tab**
1. Login como cliente
2. Vai à aba "Histórico"
3. **Atualiza a página (F5)** → deves continuar no "Histórico", não volta para "Início"
4. O URL deve mostrar `?tab=history`
5. Copia o URL, cola em nova aba → abre direto no histórico

### **Teste 2: Rascunho de Chat**
1. Vai à sala de transação `/transaction/KZ-XXX`
2. Escreve metade de uma mensagem: *"Já fiz a transfer..."*
3. **NÃO cliques em enviar**
4. Fecha o separador / desliga o browser / atualiza a página (F5)
5. Volta à mesma transação → o texto *"Já fiz a transfer..."* deve estar lá

### **Teste 3: Apagamento após envio**
1. Escreve mensagem completa
2. Clica enviar
3. Página recarrega → campo está vazio (rascunho foi apagado porque submeteste)

### **Teste 4: Rascunhos independentes**
1. Escreve *"mensagem A"* na transação 1
2. Abre transação 2, escreve *"mensagem B"*
3. Volta à transação 1 → deves ver *"mensagem A"*, não *"mensagem B"*
4. Volta à transação 2 → deves ver *"mensagem B"*

### **Teste 5: Filtro de Histórico Persistido**
1. Vai ao Histórico, seleciona filtro "Concluídas"
2. Atualiza a página
3. Filtro continua em "Concluídas"

### **Teste 6: Verificar no DevTools**
1. F12 → Application → Local Storage → https://kwanzasafe.com
2. Deves ver chaves tipo:
   - `ks_v1_draft_chat_client_KZ-ABC123`
   - `ks_v1_state_client_filter`

---

## 💡 Como Funciona (Resumo Técnico)

| Tipo de Estado | Onde guarda | Quanto tempo |
|---|---|---|
| Tab ativa | URL (`?tab=xxx`) | Permanente (URL visível) |
| Filtros | localStorage | Até user apagar dados |
| Rascunhos de chat | localStorage | Até envio bem-sucedido |
| Scroll position | localStorage | Até nova visita à página |

**Segurança:**
- Prefix `ks_v1_` evita conflito com outros sites
- Rascunhos apagam-se automaticamente após 30 dias
- Debounce de 300ms evita escrever ao localStorage em cada tecla
- Try/catch em todas operações — se localStorage estiver cheio ou bloqueado, a app continua a funcionar normalmente

---

## 📋 Checklist de Aplicação

- [ ] `public/js/ks-persist.js` instalado
- [ ] `views/layouts/app.blade.php` atualizado com script tag
- [ ] `dashboard.blade.php` com `init()` e `setTab()` atualizados
- [ ] `transaction/show.blade.php` com `data-ks-persist` no textarea
- [ ] `admin/show.blade.php` com `data-ks-persist` no textarea
- [ ] `php artisan view:clear` executado
- [ ] Testes 1-5 passados

---

## 🚀 Deploy em Produção

Depois de testar em localhost:

1. Faz upload de `public/js/ks-persist.js` → `public_html/js/ks-persist.js`
2. Faz upload de `resources/views/layouts/app.blade.php` → `kwanzasafe/resources/views/layouts/app.blade.php`
3. Faz upload dos dashboards atualizados
4. Via SSH:
   ```bash
   cd ~/domains/kwanzasafe.com/kwanzasafe
   php artisan view:clear
   php artisan cache:clear
   ```
5. Testa em `https://kwanzasafe.com`