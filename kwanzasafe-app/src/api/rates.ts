/**
 * Taxas de câmbio activas (FASE 1 do backend — GET /rates).
 */
import { api } from '@/api/client';
import type { ExchangeRate } from '@/types/api';

/** Lista as taxas activas (EUR/BRL/USDC → AOA). */
export async function fetchRates(): Promise<ExchangeRate[]> {
  // Colecções de Resource do Laravel vêm embrulhadas em `{ data: [...] }`.
  const { data } = await api.get<{ data: ExchangeRate[] }>('/rates');
  return data.data;
}
