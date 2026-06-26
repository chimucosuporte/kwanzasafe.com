/**
 * Registo do token de notificações push (Expo) — /push/token.
 */
import { api } from '@/api/client';

/** Envia o token Expo deste dispositivo para o backend. */
export async function registerPushToken(token: string): Promise<void> {
  await api.post('/push/token', { token });
}

/** Remove o token (ex.: ao desactivar notificações). */
export async function removePushToken(): Promise<void> {
  await api.delete('/push/token');
}

/** Envia uma notificação de teste ao próprio utilizador. */
export async function sendTestPush(): Promise<{ message: string }> {
  const { data } = await api.post<{ message: string }>('/push/test');
  return data;
}
