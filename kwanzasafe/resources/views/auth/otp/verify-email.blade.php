<x-app-layout>

@push('head')
<title>Verificar Email — KwanzaSafe</title>
<style>
    body { background:linear-gradient(180deg,#f0faf4 0%,#fafafa 100%); min-height:100dvh; }

    .otp-wrapper {
        min-height:100dvh;
        display:flex;
        flex-direction:column;
        font-family:'DM Sans',sans-serif;
    }

    /* HEADER */
    .otp-header {
        padding:1rem 1.25rem;
        display:flex;
        align-items:center;
        justify-content:space-between;
        background:rgba(255,255,255,0.8);
        backdrop-filter:blur(8px);
        border-bottom:1px solid #e5e5e5;
    }
    .otp-header__logo { height:32px; }
    .otp-header__back {
        color:#525252;
        text-decoration:none;
        font-size:0.75rem;
        font-weight:600;
        display:flex;
        align-items:center;
        gap:0.375rem;
    }

    /* MAIN */
    .otp-main {
        flex:1;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:1.5rem 1rem 4rem;
    }

    .otp-card {
        width:100%;
        max-width:440px;
        background:white;
        border-radius:24px;
        padding:2rem 1.75rem;
        box-shadow:0 20px 60px rgba(0,0,0,0.08);
        border:1px solid #e5e5e5;
    }

    .otp-icon-wrap {
        width:64px;
        height:64px;
        background:linear-gradient(135deg,#009d44 0%,#007a34 100%);
        border-radius:18px;
        display:flex;
        align-items:center;
        justify-content:center;
        margin:0 auto 1.25rem;
        box-shadow:0 8px 24px rgba(0,157,68,0.3);
    }

    .otp-title {
        font-family:'Syne',sans-serif;
        font-size:1.6rem;
        font-weight:800;
        text-align:center;
        margin:0 0 0.5rem;
        letter-spacing:-0.02em;
        color:#000;
    }

    .otp-subtitle {
        font-size:0.875rem;
        color:#525252;
        text-align:center;
        line-height:1.5;
        margin:0 0 0.5rem;
    }

    .otp-email {
        display:inline-flex;
        align-items:center;
        gap:0.375rem;
        background:#f0faf4;
        color:#007a34;
        padding:0.375rem 0.875rem;
        border-radius:999px;
        font-family:'JetBrains Mono',monospace;
        font-size:0.75rem;
        font-weight:700;
        margin:0.5rem auto 1.5rem;
    }

    /* INPUTS OTP */
    .otp-input-row {
        display:flex;
        gap:0.5rem;
        justify-content:center;
        margin-bottom:1.25rem;
    }

    .otp-digit {
        width:48px;
        height:56px;
        border:2px solid #e5e5e5;
        border-radius:12px;
        text-align:center;
        font-family:'Syne',sans-serif;
        font-size:1.5rem;
        font-weight:800;
        color:#000;
        background:#fafafa;
        outline:none;
        transition:all 0.2s;
        -webkit-appearance:none;
        -moz-appearance:textfield;
    }
    .otp-digit::-webkit-inner-spin-button,
    .otp-digit::-webkit-outer-spin-button {
        -webkit-appearance:none;
        margin:0;
    }
    .otp-digit:focus {
        border-color:#009d44;
        background:white;
        box-shadow:0 0 0 4px rgba(0,157,68,0.1);
        transform:scale(1.05);
    }
    .otp-digit.filled {
        border-color:#009d44;
        background:#f0faf4;
        color:#007a34;
    }
    .otp-digit.error {
        border-color:#dc2626;
        animation: shake 0.4s;
    }
    @keyframes shake {
        0%,100% { transform:translateX(0); }
        25% { transform:translateX(-6px); }
        75% { transform:translateX(6px); }
    }

    /* SMALL SCREENS */
    @media (max-width:380px) {
        .otp-digit { width:42px; height:50px; font-size:1.3rem; }
        .otp-input-row { gap:0.375rem; }
    }

    /* ERROR / SUCCESS */
    .otp-msg {
        padding:0.75rem 1rem;
        border-radius:10px;
        font-size:0.825rem;
        margin-bottom:1.25rem;
        display:flex;
        align-items:center;
        gap:0.5rem;
        line-height:1.4;
    }
    .otp-msg.error {
        background:#fee2e2;
        border:1px solid #fca5a5;
        color:#991b1b;
    }
    .otp-msg.success {
        background:#d1f2e0;
        border:1px solid #a7f3d0;
        color:#007a34;
    }

    /* BUTTONS */
    .otp-btn {
        width:100%;
        background:#009d44;
        color:white;
        padding:0.875rem;
        border-radius:12px;
        border:none;
        font-family:'Syne',sans-serif;
        font-weight:800;
        font-size:0.85rem;
        text-transform:uppercase;
        letter-spacing:0.05em;
        cursor:pointer;
        transition:all 0.2s;
        display:flex;
        align-items:center;
        justify-content:center;
        gap:0.5rem;
        box-shadow:0 4px 12px rgba(0,157,68,0.25);
    }
    .otp-btn:hover { background:#007a34; }
    .otp-btn:disabled {
        background:#a3a3a3;
        cursor:not-allowed;
        box-shadow:none;
    }

    .otp-resend {
        text-align:center;
        margin-top:1.25rem;
        padding-top:1.25rem;
        border-top:1px dashed #e5e5e5;
    }
    .otp-resend__text {
        font-size:0.75rem;
        color:#737373;
    }
    .otp-resend__btn {
        background:none;
        border:none;
        color:#007a34;
        font-weight:700;
        cursor:pointer;
        text-decoration:underline;
        font-size:0.75rem;
        padding:0;
        margin-left:0.25rem;
    }
    .otp-resend__btn:disabled {
        color:#a3a3a3;
        cursor:not-allowed;
        text-decoration:none;
    }

    /* COUNTDOWN */
    .otp-countdown {
        display:inline-block;
        background:#fafafa;
        color:#525252;
        padding:0.25rem 0.625rem;
        border-radius:999px;
        font-family:'JetBrains Mono',monospace;
        font-size:0.7rem;
        font-weight:700;
        margin-left:0.25rem;
    }
</style>
@endpush

<div class="otp-wrapper" x-data="otpVerify()" x-init="init()">

    {{-- HEADER --}}
    <div class="otp-header">
        <a href="{{ route('dashboard') }}" class="otp-header__back">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Voltar
        </a>
        <img src="{{ asset('assets/images/logos/logo1.png') }}" alt="KwanzaSafe" class="otp-header__logo">
        <div style="width:50px;"></div>
    </div>

    {{-- MAIN --}}
    <div class="otp-main">
        <div class="otp-card">

            {{-- ÍCONE --}}
            <div class="otp-icon-wrap">
                <svg width="32" height="32" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            {{-- TÍTULO --}}
            <h1 class="otp-title">Verifica o teu email</h1>
            <p class="otp-subtitle">
                Enviámos um código de 6 dígitos para:
            </p>
            <div style="text-align:center;">
                <span class="otp-email">📧 {{ Auth::user()->email }}</span>
            </div>

            {{-- MENSAGENS --}}
            @if(session('success'))
                <div class="otp-msg success">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="otp-msg error">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round"/>
                        <line x1="12" y1="16" x2="12.01" y2="16" stroke-linecap="round"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- FORMULÁRIO --}}
            <form method="POST" action="{{ route('otp.email.verify.submit') }}" @submit="onSubmit($event)">
                @csrf

                <div class="otp-input-row">
                    <template x-for="(digit, i) in 6" :key="i">
                        <input
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            class="otp-digit"
                            :class="{ 'filled': digits[i], 'error': hasError }"
                            x-model="digits[i]"
                            @input="onInput(i, $event)"
                            @keydown="onKeydown(i, $event)"
                            @paste="onPaste($event)"
                            :ref="`d${i}`"
                            autocomplete="one-time-code"
                        >
                    </template>
                </div>

                {{-- Hidden field com o código completo --}}
                <input type="hidden" name="code" :value="digits.join('')">

                <button type="submit" class="otp-btn" :disabled="!isComplete || submitting">
                    <span x-show="!submitting">Verificar Email</span>
                    <span x-show="submitting">A verificar...</span>
                    <svg x-show="!submitting" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>

            {{-- REENVIAR --}}
            <div class="otp-resend">
                <span class="otp-resend__text">
                    Não recebeste o código?
                </span>
                <form method="POST" action="{{ route('otp.email.send') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="otp-resend__btn" :disabled="resendCooldown > 0">
                        <span x-show="resendCooldown === 0">Reenviar</span>
                        <span x-show="resendCooldown > 0">
                            Reenviar em
                            <span class="otp-countdown" x-text="`${resendCooldown}s`"></span>
                        </span>
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
function otpVerify() {
    return {
        digits: ['','','','','',''],
        hasError: {{ $errors->any() ? 'true' : 'false' }},
        submitting: false,
        resendCooldown: {{ session('success') ? 60 : 0 }},
        cooldownInterval: null,

        init() {
            // Foco no primeiro input
            this.$nextTick(() => {
                this.$refs.d0.focus();
            });

            // Cooldown de reenvio
            if (this.resendCooldown > 0) {
                this.startCooldown();
            }

            // Limpar erro ao começar a digitar
            this.$watch('digits', () => {
                this.hasError = false;
            });
        },

        onInput(index, event) {
            // Forçar apenas dígitos
            const value = event.target.value.replace(/\D/g, '');
            this.digits[index] = value.slice(-1); // só o último char

            // Avançar para próximo input
            if (value && index < 5) {
                this.$refs[`d${index + 1}`].focus();
            }

            // Auto-submit quando completo
            if (this.isComplete) {
                this.$nextTick(() => {
                    // Pequeno delay para feedback visual
                    setTimeout(() => {
                        if (this.isComplete && !this.submitting) {
                            this.$el.querySelector('form').submit();
                        }
                    }, 200);
                });
            }
        },

        onKeydown(index, event) {
            // Backspace volta para o anterior
            if (event.key === 'Backspace' && !this.digits[index] && index > 0) {
                this.$refs[`d${index - 1}`].focus();
            }
            // Setas para navegar
            if (event.key === 'ArrowLeft' && index > 0) {
                this.$refs[`d${index - 1}`].focus();
            }
            if (event.key === 'ArrowRight' && index < 5) {
                this.$refs[`d${index + 1}`].focus();
            }
        },

        onPaste(event) {
            event.preventDefault();
            const text = (event.clipboardData || window.clipboardData).getData('text');
            const digits = text.replace(/\D/g, '').slice(0, 6).split('');

            digits.forEach((d, i) => {
                if (i < 6) this.digits[i] = d;
            });

            // Foco no último preenchido ou próximo vazio
            const nextEmpty = this.digits.findIndex(d => !d);
            const focusIndex = nextEmpty === -1 ? 5 : nextEmpty;
            this.$nextTick(() => this.$refs[`d${focusIndex}`].focus());
        },

        onSubmit(event) {
            if (!this.isComplete) {
                event.preventDefault();
                return;
            }
            this.submitting = true;
        },

        get isComplete() {
            return this.digits.every(d => d.length === 1);
        },

        startCooldown() {
            this.cooldownInterval = setInterval(() => {
                this.resendCooldown--;
                if (this.resendCooldown <= 0) {
                    clearInterval(this.cooldownInterval);
                }
            }, 1000);
        }
    };
}
</script>
@endpush

</x-app-layout>
