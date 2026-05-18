<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Código de Verificação KwanzaSafe</title>

    <style>
        /* Reset */
        body, table, td, p, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table { border-collapse: collapse !important; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }

        /* Fonts */
        body, table, td { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#fafafa;">

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#fafafa;">
    <tr>
        <td align="center" style="padding: 32px 16px;">

            {{-- ============ CONTAINER ============ --}}
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.04);">

                {{-- ============ HEADER ============ --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#009d44 0%,#007a34 100%);padding:32px 32px;text-align:center;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" style="font-family:Arial,sans-serif;color:#ffffff;font-size:24px;font-weight:800;letter-spacing:-0.02em;">
                                    KwanzaSafe
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-family:Arial,sans-serif;color:rgba(255,255,255,0.85);font-size:11px;font-weight:600;letter-spacing:0.15em;text-transform:uppercase;padding-top:4px;">
                                    Carteira Internacional
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- ============ BODY ============ --}}
                <tr>
                    <td style="padding:40px 32px 24px 32px;">

                        <h1 style="margin:0 0 8px 0;font-family:Arial,sans-serif;font-size:24px;font-weight:800;color:#000000;letter-spacing:-0.02em;line-height:1.2;">
                            Olá{{ $user->full_name ? ', ' . explode(' ', $user->full_name)[0] : '' }} 👋
                        </h1>

                        <p style="margin:0 0 24px 0;font-family:Arial,sans-serif;font-size:15px;color:#404040;line-height:1.6;">
                            @if(isset($context) && $context === 'phone')
                                Recebemos um pedido para confirmar o número de telefone <strong>{{ $contextData ?? '' }}</strong> na <strong>KwanzaSafe</strong>. Usa o código abaixo para concluir a confirmação:
                            @else
                                Recebemos um pedido para verificar o teu endereço de email na <strong>KwanzaSafe</strong>. Usa o código abaixo para concluir a verificação:
                            @endif
                        </p>

                        {{-- ============ CÓDIGO OTP (DESTAQUE) ============ --}}
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td align="center" style="padding:8px 0 24px 0;">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="background-color:#f0faf4;border:2px dashed #009d44;border-radius:14px;padding:24px 40px;">
                                                <div style="font-family:'Courier New',monospace;font-size:36px;font-weight:800;color:#007a34;letter-spacing:0.4em;text-align:center;">
                                                    {{ $code }}
                                                </div>
                                                <div style="font-family:Arial,sans-serif;font-size:11px;color:#737373;text-align:center;margin-top:8px;letter-spacing:0.1em;text-transform:uppercase;font-weight:700;">
                                                    Código de 6 dígitos
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- ============ INFO BOX ============ --}}
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#fafafa;border-radius:10px;margin-bottom:24px;">
                            <tr>
                                <td style="padding:16px 20px;">
                                    <p style="margin:0 0 4px 0;font-family:Arial,sans-serif;font-size:13px;color:#000000;font-weight:700;">
                                        ⏱️ Validade do código
                                    </p>
                                    <p style="margin:0;font-family:Arial,sans-serif;font-size:13px;color:#525252;line-height:1.5;">
                                        Este código é válido por <strong>{{ $expiryMinutes }} minutos</strong>. Após esse período terás de solicitar um novo código.
                                    </p>
                                </td>
                            </tr>
                        </table>

                        {{-- ============ AVISO DE SEGURANÇA ============ --}}
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#fef3c7;border-radius:10px;margin-bottom:8px;">
                            <tr>
                                <td style="padding:14px 18px;">
                                    <p style="margin:0;font-family:Arial,sans-serif;font-size:12px;color:#92400e;line-height:1.5;">
                                        🔒 <strong>Por segurança:</strong> nunca partilhes este código com ninguém, incluindo equipa do suporte da KwanzaSafe. Se não solicitaste este código, ignora este email.
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                {{-- ============ FOOTER ============ --}}
                <tr>
                    <td style="background-color:#000000;padding:24px 32px;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td align="center">
                                    <p style="margin:0 0 12px 0;font-family:Arial,sans-serif;font-size:12px;color:#a3a3a3;line-height:1.6;">
                                        Carteira Internacional para remessas seguras<br>
                                        do mundo para Angola.
                                    </p>
                                    <p style="margin:0 0 8px 0;font-family:Arial,sans-serif;font-size:11px;color:#737373;">
                                        Suporte: <a href="https://wa.me/5511933579009" style="color:#009d44;text-decoration:none;font-weight:600;">+55 11 93357-9009</a>
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

            {{-- ============ NOTA LEGAL ============ --}}
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:560px;margin-top:16px;">
                <tr>
                    <td align="center" style="padding:0 16px;">
                        <p style="margin:0;font-family:Arial,sans-serif;font-size:11px;color:#a3a3a3;line-height:1.5;">
                            Este email foi enviado para <strong style="color:#525252;">{{ $user->email }}</strong>.<br>
                            Se não criaste uma conta KwanzaSafe, podes ignorar com segurança esta mensagem.
                        </p>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

</body>
</html>
