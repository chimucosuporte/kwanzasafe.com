<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Actualização — Transação #{{ $transaction->reference_id }}</title>
    <style>
        body, table, td, p, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table { border-collapse: collapse !important; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        body, table, td { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#fafafa;">

@php
    $firstName = $client->full_name ? explode(' ', trim($client->full_name))[0] : explode('@', $client->email)[0];

    $configs = [
        'negotiating' => [
            'icon'    => '💬',
            'title'   => 'O agente está pronto para ti',
            'body'    => 'Um agente KwanzaSafe está a analisar a tua transação e irá contactar-te em breve através da sala de chat.',
            'cta'     => 'Ir para o Chat',
            'color'   => '#009d44',
        ],
        'awaiting_payment' => [
            'icon'    => '💳',
            'title'   => 'Efectua o pagamento agora',
            'body'    => 'O teu agente está à espera do pagamento de <strong>' . number_format($transaction->amount_sent, 2, ',', '.') . ' ' . $transaction->currency_from . '</strong>. Segue as instruções de pagamento na sala de transação e envia o comprovativo.',
            'cta'     => 'Ver Instruções de Pagamento',
            'color'   => '#2563eb',
        ],
        'payment_received' => [
            'icon'    => '✅',
            'title'   => 'Pagamento confirmado!',
            'body'    => 'Recebemos e confirmamos o teu pagamento de <strong>' . number_format($transaction->amount_sent, 2, ',', '.') . ' ' . $transaction->currency_from . '</strong>. Estamos agora a processar o envio de <strong>' . number_format($transaction->amount_received, 0, ',', '.') . ' Kz</strong> para a tua conta.',
            'cta'     => 'Acompanhar Transação',
            'color'   => '#059669',
        ],
        'aoa_sent' => [
            'icon'    => '🏦',
            'title'   => 'Kwanzas enviados — confirma a recepção',
            'body'    => 'Enviámos <strong>' . number_format($transaction->amount_received, 0, ',', '.') . ' Kz</strong> para o teu IBAN. Por favor verifica a tua conta bancária e confirma a recepção na plataforma.',
            'cta'     => 'Confirmar Recepção dos Kwanzas',
            'color'   => '#064e3b',
        ],
        'completed' => [
            'icon'    => '🎉',
            'title'   => 'Transação concluída com sucesso!',
            'body'    => 'A tua transação <strong>#' . $transaction->reference_id . '</strong> foi concluída. <strong>' . number_format($transaction->amount_received, 0, ',', '.') . ' Kz</strong> chegaram à tua conta. Obrigado por usar a KwanzaSafe!',
            'cta'     => 'Ver Comprovativo',
            'color'   => '#007a34',
        ],
        'cancelled' => [
            'icon'    => '❌',
            'title'   => 'Transação cancelada',
            'body'    => 'A transação <strong>#' . $transaction->reference_id . '</strong> foi cancelada. Se tiveres dúvidas ou precisares de ajuda, contacta o nosso suporte.',
            'cta'     => 'Contactar Suporte',
            'color'   => '#dc2626',
        ],
        'expired' => [
            'icon'    => '⏰',
            'title'   => 'Transação expirada',
            'body'    => 'A transação <strong>#' . $transaction->reference_id . '</strong> expirou por inactividade. Cria uma nova transação para continuar.',
            'cta'     => 'Criar Nova Transação',
            'color'   => '#737373',
        ],
    ];

    $cfg = $configs[$newStatus] ?? [
        'icon'  => '🔔',
        'title' => 'Actualização da tua transação',
        'body'  => 'O estado da tua transação foi actualizado.',
        'cta'   => 'Ver Transação',
        'color' => '#009d44',
    ];
@endphp

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#fafafa;">
    <tr>
        <td align="center" style="padding:32px 16px;">

            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.04);">

                {{-- HEADER --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#009d44 0%,#007a34 100%);padding:28px 32px;text-align:center;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" style="font-family:Arial,sans-serif;color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.02em;">
                                    KwanzaSafe
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-family:Arial,sans-serif;color:rgba(255,255,255,0.85);font-size:10px;font-weight:600;letter-spacing:0.15em;text-transform:uppercase;padding-top:4px;">
                                    Carteira Internacional
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- STATUS BADGE --}}
                <tr>
                    <td style="padding:32px 32px 0 32px;text-align:center;">
                        <div style="display:inline-block;font-size:40px;line-height:1;margin-bottom:12px;">{{ $cfg['icon'] }}</div>
                        <h1 style="margin:0 0 8px 0;font-family:Arial,sans-serif;font-size:22px;font-weight:800;color:#000000;letter-spacing:-0.02em;line-height:1.2;">
                            {{ $cfg['title'] }}
                        </h1>
                        <p style="margin:0 0 6px 0;font-family:Arial,sans-serif;font-size:12px;font-weight:700;color:#737373;letter-spacing:0.08em;text-transform:uppercase;">
                            Transação #{{ $transaction->reference_id }}
                        </p>
                    </td>
                </tr>

                {{-- BODY --}}
                <tr>
                    <td style="padding:20px 32px 28px 32px;">

                        <p style="margin:0 0 20px 0;font-family:Arial,sans-serif;font-size:15px;color:#404040;line-height:1.65;">
                            Olá <strong>{{ $firstName }}</strong>,<br><br>
                            {!! $cfg['body'] !!}
                        </p>

                        {{-- RESUMO FINANCEIRO --}}
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f8fafc;border-radius:12px;margin-bottom:24px;border:1px solid #e2e8f0;">
                            <tr>
                                <td style="padding:14px 20px;border-bottom:1px solid #e2e8f0;">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="font-family:Arial,sans-serif;font-size:12px;color:#737373;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Enviado</td>
                                            <td align="right" style="font-family:Arial,sans-serif;font-size:14px;color:#000000;font-weight:800;">{{ number_format($transaction->amount_sent, 2, ',', '.') }} {{ $transaction->currency_from }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:14px 20px;border-bottom:1px solid #e2e8f0;">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="font-family:Arial,sans-serif;font-size:12px;color:#737373;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">A receber</td>
                                            <td align="right" style="font-family:Arial,sans-serif;font-size:14px;color:#007a34;font-weight:800;">{{ number_format($transaction->amount_received, 0, ',', '.') }} Kz</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:14px 20px;">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="font-family:Arial,sans-serif;font-size:12px;color:#737373;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Taxa aplicada</td>
                                            <td align="right" style="font-family:Arial,sans-serif;font-size:13px;color:#000000;font-weight:700;">1 {{ $transaction->currency_from }} = {{ number_format($transaction->rate_applied, 2, ',', '.') }} Kz</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- CTA BUTTON --}}
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td align="center">
                                    <a href="{{ $txUrl }}"
                                       style="display:inline-block;background-color:{{ $cfg['color'] }};color:#ffffff;font-family:Arial,sans-serif;font-size:14px;font-weight:800;text-decoration:none;padding:14px 32px;border-radius:10px;letter-spacing:0.03em;text-transform:uppercase;">
                                        {{ $cfg['cta'] }} →
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:20px 0 0 0;font-family:Arial,sans-serif;font-size:12px;color:#a3a3a3;text-align:center;line-height:1.5;">
                            Ou copia este link: <a href="{{ $txUrl }}" style="color:#009d44;text-decoration:none;">{{ $txUrl }}</a>
                        </p>

                    </td>
                </tr>

                {{-- FOOTER --}}
                <tr>
                    <td style="background-color:#000000;padding:24px 32px;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td align="center">
                                    <p style="margin:0 0 10px 0;font-family:Arial,sans-serif;font-size:12px;color:#a3a3a3;line-height:1.6;">
                                        Dúvidas? Contacta o suporte
                                    </p>
                                    <p style="margin:0 0 8px 0;font-family:Arial,sans-serif;font-size:12px;">
                                        <a href="https://wa.me/5511933579009" style="color:#009d44;text-decoration:none;font-weight:700;">WhatsApp +55 11 93357-9009</a>
                                        &nbsp;·&nbsp;
                                        <a href="mailto:geral@kwanzasafe.com" style="color:#009d44;text-decoration:none;font-weight:700;">geral@kwanzasafe.com</a>
                                    </p>
                                    <p style="margin:8px 0 0 0;font-family:'Courier New',monospace;font-size:10px;color:#525252;letter-spacing:0.1em;text-transform:uppercase;">
                                        © {{ date('Y') }} KwanzaSafe · BNA Compliant
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>

            {{-- NOTA LEGAL --}}
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:560px;margin-top:16px;">
                <tr>
                    <td align="center" style="padding:0 16px;">
                        <p style="margin:0;font-family:Arial,sans-serif;font-size:11px;color:#a3a3a3;line-height:1.5;">
                            Este email foi enviado para <strong style="color:#525252;">{{ $client->email }}</strong>
                            porque tens uma transação activa na KwanzaSafe.<br>
                            Não partilhes os detalhes desta transação com terceiros.
                        </p>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

</body>
</html>
