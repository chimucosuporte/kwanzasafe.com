/**
 * Autenticação em 2 passos (TOTP / Google Authenticator) — /2fa.
 */
import { api } from '@/api/client';
import type { User } from '@/types/api';

/** Inicia a configuração: gera segredo + URI otpauth (ainda por confirmar). */
export async function enableTwoFactor(): Promise<{ secret: string; otpauth_uri: string }> {
  const { data } = await api.post<{ secret: string; otpauth_uri: string }>('/2fa/enable');
  return data;
}

/** Confirma o primeiro código e activa o 2FA. */
export async function confirmTwoFactor(code: string): Promise<{ message: string; user: User }> {
  const { data } = await api.post<{ message: string; user: User }>('/2fa/confirm', { code });
  return data;
}

/** Desactiva o 2FA (exige a palavra-passe). */
export async function disableTwoFactor(password: string): Promise<{ message: string; user: User }> {
  const { data } = await api.post<{ message: string; user: User }>('/2fa/disable', { password });
  return data;
}
