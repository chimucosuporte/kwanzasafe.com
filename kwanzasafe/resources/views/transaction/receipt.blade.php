<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovativo #{{ $transaction->reference_id }} — KwanzaSafe</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@600&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0fdf4;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
            color: #0f172a;
        }

        .rc-actions {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            width: 100%;
            max-width: 520px;
        }
        .rc-btn {
            flex: 1;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            cursor: pointer;
            border: none;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }
        .rc-btn.print  { background: #064e3b; color: white; }
        .rc-btn.back   { background: white; color: #374151; border: 1px solid #e2e8f0; }
        .rc-btn:hover  { filter: brightness(0.95); }

        .rc-card {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 8px 40px rgba(6,78,59,0.10);
            overflow: hidden;
        }

        .rc-header {
            background: linear-gradient(135deg, #064e3b 0%, #009d44 100%);
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            color: white;
        }
        .rc-header__logo {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
        }
        .rc-header__sub {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            opacity: 0.7;
            margin-bottom: 1.5rem;
        }
        .rc-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1.25rem;
            border-radius: 999px;
            font-family: 'Syne', sans-serif;
            font-size: 0.875rem;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .rc-ref {
            text-align: center;
            padding: 1.5rem 2rem 0;
        }
        .rc-ref__label { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #94a3b8; margin-bottom: 0.25rem; }
        .rc-ref__value { font-family: 'JetBrains Mono', monospace; font-size: 1.25rem; font-weight: 700; color: #064e3b; }

        .rc-amounts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            padding: 1.25rem 2rem;
        }
        .rc-amount-box {
            border-radius: 14px;
            padding: 1rem 1.125rem;
        }
        .rc-amount-box.sent    { background: #f8fafc; border: 1.5px solid #e2e8f0; }
        .rc-amount-box.receive { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 1.5px solid #a7f3d0; }
        .rc-amount-box__label  { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 0.375rem; }
        .rc-amount-box.receive .rc-amount-box__label { color: #065f46; }
        .rc-amount-box__value  { font-family: 'Syne', sans-serif; font-size: 1.25rem; font-weight: 800; color: #0f172a; }
        .rc-amount-box.receive .rc-amount-box__value { color: #064e3b; }

        .rc-rows { padding: 0 2rem; }
        .rc-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            gap: 1rem;
        }
        .rc-row:last-child { border-bottom: none; }
        .rc-row__label { color: #94a3b8; font-weight: 500; }
        .rc-row__value { font-weight: 700; text-align: right; }

        .rc-footer {
            background: #000;
            padding: 1.25rem 2rem;
            text-align: center;
            margin-top: 1.25rem;
        }
        .rc-footer p { font-size: 0.7rem; color: #737373; line-height: 1.6; }
        .rc-footer strong { color: #a3a3a3; }

        .rc-watermark {
            text-align: center;
            padding: 0.75rem 2rem 1.5rem;
            font-size: 0.65rem;
            color: #cbd5e1;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        @media print {
            body { background: white; padding: 0; }
            .rc-actions { display: none; }
            .rc-card { box-shadow: none; border-radius: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

@php
    $client = $transaction->user ?? \App\Models\User::find($transaction->user_id);
@endphp

<div class="rc-actions no-print">
    <a href="{{ url()->previous() }}" class="rc-btn back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Voltar
    </a>
    <button class="rc-btn print" onclick="window.print()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Imprimir / Guardar PDF
    </button>
</div>

<div class="rc-card">

    <div class="rc-header">
        <div class="rc-header__logo">KwanzaSafe</div>
        <div class="rc-header__sub">Carteira Internacional</div>
        <div class="rc-badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Transação Concluída
        </div>
    </div>

    <div class="rc-ref">
        <div class="rc-ref__label">Referência</div>
        <div class="rc-ref__value">#{{ $transaction->reference_id }}</div>
    </div>

    <div class="rc-amounts">
        <div class="rc-amount-box sent">
            <div class="rc-amount-box__label">Valor Enviado</div>
            <div class="rc-amount-box__value">{{ number_format($transaction->amount_sent, 2, ',', '.') }} {{ $transaction->currency_from }}</div>
        </div>
        <div class="rc-amount-box receive">
            <div class="rc-amount-box__label">Valor Recebido</div>
            <div class="rc-amount-box__value">{{ number_format($transaction->amount_received, 0, ',', '.') }} Kz</div>
        </div>
    </div>

    <div class="rc-rows">
        <div class="rc-row">
            <span class="rc-row__label">Cliente</span>
            <span class="rc-row__value">{{ $client?->full_name ?? '—' }}</span>
        </div>
        <div class="rc-row">
            <span class="rc-row__label">Email</span>
            <span class="rc-row__value" style="font-size:0.8rem;">{{ $client?->email ?? '—' }}</span>
        </div>
        <div class="rc-row">
            <span class="rc-row__label">Taxa Aplicada</span>
            <span class="rc-row__value">1 {{ $transaction->currency_from }} = {{ number_format($transaction->rate_applied, 2, ',', '.') }} Kz</span>
        </div>
        <div class="rc-row">
            <span class="rc-row__label">Data de Criação</span>
            <span class="rc-row__value">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
        </div>
        @if($transaction->client_confirmed_at)
        <div class="rc-row">
            <span class="rc-row__label">Data de Conclusão</span>
            <span class="rc-row__value" style="color:#064e3b;">{{ $transaction->client_confirmed_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        <div class="rc-row">
            <span class="rc-row__label">Estado</span>
            <span class="rc-row__value" style="color:#064e3b;">✓ Concluída</span>
        </div>
    </div>

    <div class="rc-watermark">
        Documento gerado automaticamente · Não requer assinatura
    </div>

    <div class="rc-footer">
        <p>
            Dúvidas? <strong>geral@kwanzasafe.com</strong> · WhatsApp <strong>+55 11 93357-9009</strong><br>
            © {{ date('Y') }} KwanzaSafe · BNA Compliant
        </p>
    </div>

</div>

</body>
</html>
