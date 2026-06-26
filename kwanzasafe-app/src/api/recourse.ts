/**
 * Recursos (appeal) de uma transação — /transactions/{ref}/recourse.
 * A conversa do recurso decorre no chat da transação (canal 'recourse').
 */
import { api } from '@/api/client';
import type { Recourse } from '@/types/api';

/** Recurso mais recente da transação (ou null). */
export async function fetchRecourse(reference: string): Promise<Recourse | null> {
  const { data } = await api.get<{ data: Recourse | null }>(`/transactions/${reference}/recourse`);
  return data.data;
}

/** Abre um recurso com o motivo. */
export async function openRecourse(reference: string, reason: string): Promise<Recourse> {
  const { data } = await api.post<{ data: Recourse }>(`/transactions/${reference}/recourse`, { reason });
  return data.data;
}

/** Cancela o recurso activo. */
export async function cancelRecourse(reference: string): Promise<Recourse> {
  const { data } = await api.post<{ message: string; data: Recourse }>(`/transactions/${reference}/recourse/cancel`);
  return data.data;
}
