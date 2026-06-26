<?php

namespace App\Services;

/**
 * TOTP (RFC 6238) — códigos compatíveis com o Google Authenticator / Authy.
 *
 * Implementação autocontida (HMAC-SHA1, passo de 30s, 6 dígitos), sem
 * dependências externas. Usado pela autenticação em 2 passos da app mobile.
 */
class TotpService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // base32 (RFC 4648)
    private const DIGITS = 6;
    private const PERIOD = 30;

    /** Gera uma chave secreta base32 (160 bits por defeito). */
    public function generateSecret(int $chars = 32): string
    {
        $secret = '';
        $max = strlen(self::ALPHABET) - 1;
        for ($i = 0; $i < $chars; $i++) {
            $secret .= self::ALPHABET[random_int(0, $max)];
        }

        return $secret;
    }

    /**
     * Verifica um código TOTP, tolerando ±$window passos (relógio dessincronizado).
     */
    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== self::DIGITS) {
            return false;
        }

        $counter = (int) floor(time() / self::PERIOD);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->codeAt($secret, $counter + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    /** Código TOTP actual para um segredo (útil para testes/depuração). */
    public function currentCode(string $secret): string
    {
        return $this->codeAt($secret, (int) floor(time() / self::PERIOD));
    }

    /** URI otpauth:// para gerar o QR no autenticador. */
    public function otpauthUri(string $secret, string $label, string $issuer): string
    {
        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d',
            rawurlencode($issuer.':'.$label),
            $secret,
            rawurlencode($issuer),
            self::DIGITS,
            self::PERIOD,
        );
    }

    /** Calcula o código de 6 dígitos para um dado contador. */
    private function codeAt(string $secret, int $counter): string
    {
        $key = $this->base32Decode($secret);
        // Contador como 8 bytes big-endian.
        $binCounter = pack('N*', 0, $counter);
        $hash = hash_hmac('sha1', $binCounter, $key, true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
        $truncated = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        );

        return str_pad((string) ($truncated % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /** Descodifica uma chave base32 (RFC 4648) para bytes. */
    private function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
        if ($secret === '') {
            return '';
        }

        $bits = '';
        foreach (str_split($secret) as $char) {
            $bits .= str_pad(decbin(strpos(self::ALPHABET, $char)), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $bytes .= chr(bindec($byte));
            }
        }

        return $bytes;
    }
}
