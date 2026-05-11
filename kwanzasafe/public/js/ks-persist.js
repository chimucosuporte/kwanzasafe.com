/**
 * ==========================================================================
 * KwanzaSafe — ks-persist.js
 * ==========================================================================
 * Módulo de persistência de estado reutilizável.
 *
 * Funcionalidades:
 *  - Persistência de tab ativa via URL (?tab=history)
 *  - Rascunho automático de mensagens de chat (localStorage)
 *  - Estado de modais, filtros, scroll position
 *  - Limpeza automática de rascunhos após submissão bem-sucedida
 *
 * Uso:
 *   <textarea data-ks-persist="chat:tx-123">...</textarea>
 *   window.ksPersist.saveTab('history')
 *   window.ksPersist.getTab('home')
 *
 * Versão: 1.0 — Sprint 3
 * ==========================================================================
 */
(function() {
    'use strict';

    const STORAGE_PREFIX = 'ks_';
    const STORAGE_VERSION = 'v1';

    /**
     * Helper: Gerar chave com prefixo e versão
     */
    function key(name) {
        return `${STORAGE_PREFIX}${STORAGE_VERSION}_${name}`;
    }

    /**
     * Helper: Guardar no localStorage com try/catch
     */
    function set(name, value) {
        try {
            const k = key(name);
            if (value === null || value === undefined || value === '') {
                localStorage.removeItem(k);
            } else {
                localStorage.setItem(k, JSON.stringify({
                    value: value,
                    timestamp: Date.now()
                }));
            }
            return true;
        } catch (e) {
            console.warn('[ksPersist] localStorage indisponível:', e.message);
            return false;
        }
    }

    /**
     * Helper: Ler do localStorage com try/catch
     */
    function get(name, defaultValue = null) {
        try {
            const raw = localStorage.getItem(key(name));
            if (!raw) return defaultValue;
            const parsed = JSON.parse(raw);
            return parsed.value ?? defaultValue;
        } catch (e) {
            return defaultValue;
        }
    }

    /**
     * Helper: Apagar item
     */
    function remove(name) {
        try {
            localStorage.removeItem(key(name));
            return true;
        } catch (e) {
            return false;
        }
    }

    /**
     * Helper: Limpeza de itens antigos (> 30 dias)
     */
    function cleanupOld() {
        const MAX_AGE = 30 * 24 * 60 * 60 * 1000; // 30 dias
        const now = Date.now();

        try {
            for (let i = localStorage.length - 1; i >= 0; i--) {
                const k = localStorage.key(i);
                if (!k || !k.startsWith(STORAGE_PREFIX)) continue;

                try {
                    const parsed = JSON.parse(localStorage.getItem(k));
                    if (parsed && parsed.timestamp && (now - parsed.timestamp > MAX_AGE)) {
                        localStorage.removeItem(k);
                    }
                } catch (e) { /* ignora itens malformados */ }
            }
        } catch (e) { /* ignora se localStorage indisponível */ }
    }

    /**
     * =====================================================================
     * MÓDULO 1 — PERSISTÊNCIA DE TABS VIA URL
     * =====================================================================
     */
    const Tabs = {
        /**
         * Lê a tab atual da URL (query ?tab=xyz)
         */
        current(defaultTab = 'home') {
            const params = new URLSearchParams(window.location.search);
            return params.get('tab') || defaultTab;
        },

        /**
         * Atualiza a URL com a nova tab sem recarregar a página
         */
        set(tab) {
            const url = new URL(window.location.href);
            if (tab && tab !== 'home') {
                url.searchParams.set('tab', tab);
            } else {
                url.searchParams.delete('tab');
            }
            window.history.replaceState({}, '', url);
        },

        /**
         * Lê parâmetro arbitrário da URL (para subtabs, filtros, etc)
         */
        getParam(name, defaultValue = null) {
            const params = new URLSearchParams(window.location.search);
            return params.get(name) || defaultValue;
        },

        /**
         * Escreve parâmetro arbitrário na URL
         */
        setParam(name, value) {
            const url = new URL(window.location.href);
            if (value === null || value === undefined || value === '') {
                url.searchParams.delete(name);
            } else {
                url.searchParams.set(name, value);
            }
            window.history.replaceState({}, '', url);
        }
    };

    /**
     * =====================================================================
     * MÓDULO 2 — RASCUNHOS DE MENSAGENS (AUTO-SAVE)
     * =====================================================================
     *
     * Qualquer <textarea> ou <input type="text"> com atributo
     * data-ks-persist="chave_unica" é automaticamente persistido.
     *
     * Apagado quando:
     *   - Utilizador submete o form que contém o campo
     *   - Explicitamente via window.ksPersist.clearDraft('chave')
     */
    const Drafts = {
        /**
         * Scanner: encontra todos os inputs persistentes e hidrata/liga-os
         */
        init() {
            document.querySelectorAll('[data-ks-persist]').forEach(el => {
                this._attach(el);
            });

            // Observer para elementos adicionados dinamicamente
            const observer = new MutationObserver(mutations => {
                mutations.forEach(m => {
                    m.addedNodes.forEach(node => {
                        if (node.nodeType !== 1) return;

                        if (node.matches && node.matches('[data-ks-persist]')) {
                            this._attach(node);
                        }

                        if (node.querySelectorAll) {
                            node.querySelectorAll('[data-ks-persist]').forEach(el => this._attach(el));
                        }
                    });
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });
        },

        /**
         * Liga um elemento específico ao sistema de persistência
         */
        _attach(el) {
            if (el.dataset.ksPersistAttached === 'true') return; // já ligado
            el.dataset.ksPersistAttached = 'true';

            const storageKey = 'draft_' + el.dataset.ksPersist;

            // 1. HIDRATAR: repor valor guardado
            const saved = get(storageKey);
            if (saved && !el.value) {
                el.value = saved;

                // Dispara evento para Alpine.js detetar a mudança
                el.dispatchEvent(new Event('input', { bubbles: true }));

                // Auto-resize para textareas
                if (el.tagName === 'TEXTAREA') {
                    setTimeout(() => {
                        el.style.height = 'auto';
                        el.style.height = Math.min(el.scrollHeight, 120) + 'px';
                    }, 50);
                }
            }

            // 2. LIGAR: guardar sempre que o utilizador escreve (com debounce)
            let debounceTimer;
            el.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    set(storageKey, el.value);
                }, 300); // 300ms debounce
            });

            // 3. APAGAR ao submeter o form
            const form = el.closest('form');
            if (form && !form.dataset.ksPersistSubmitAttached) {
                form.dataset.ksPersistSubmitAttached = 'true';

                form.addEventListener('submit', () => {
                    // Limpar TODOS os campos persistentes deste form
                    form.querySelectorAll('[data-ks-persist]').forEach(field => {
                        const k = 'draft_' + field.dataset.ksPersist;
                        remove(k);
                    });
                });
            }
        },

        /**
         * Limpar rascunho manualmente
         */
        clear(name) {
            remove('draft_' + name);
        },

        /**
         * Ver rascunho
         */
        get(name) {
            return get('draft_' + name);
        }
    };

    /**
     * =====================================================================
     * MÓDULO 3 — ESTADO VISUAL (filtros, modais, scroll)
     * =====================================================================
     */
    const State = {
        save(name, value) {
            return set('state_' + name, value);
        },

        load(name, defaultValue = null) {
            return get('state_' + name, defaultValue);
        },

        clear(name) {
            return remove('state_' + name);
        }
    };

    /**
     * =====================================================================
     * MÓDULO 4 — SCROLL POSITION
     * =====================================================================
     */
    const Scroll = {
        /**
         * Guardar scroll antes de navegar
         */
        save(key) {
            set('scroll_' + key, {
                x: window.scrollX,
                y: window.scrollY,
                url: window.location.pathname
            });
        },

        /**
         * Restaurar scroll ao voltar
         */
        restore(key) {
            const data = get('scroll_' + key);
            if (!data) return false;

            // Só restaura se for a mesma página
            if (data.url !== window.location.pathname) return false;

            setTimeout(() => {
                window.scrollTo(data.x || 0, data.y || 0);
            }, 100);

            return true;
        },

        clear(key) {
            remove('scroll_' + key);
        }
    };

    /**
     * =====================================================================
     * INIT — Arranque automático
     * =====================================================================
     */
    function init() {
        cleanupOld();
        Drafts.init();

        // Evento personalizado: outros scripts podem saber que está pronto
        document.dispatchEvent(new CustomEvent('ks-persist:ready'));
    }

    // API Global
    window.ksPersist = {
        tabs: Tabs,
        drafts: Drafts,
        state: State,
        scroll: Scroll,

        // Helpers diretos
        saveTab:    (tab) => Tabs.set(tab),
        getTab:     (def) => Tabs.current(def),
        saveState:  (k, v) => State.save(k, v),
        loadState:  (k, def) => State.load(k, def),
        clearDraft: (name) => Drafts.clear(name),
        getDraft:   (name) => Drafts.get(name),
    };

    // Arranque
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();