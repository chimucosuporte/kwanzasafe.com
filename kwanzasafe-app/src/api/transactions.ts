/**
 * Transações via API mobile (FASE 1 do backend — /transactions).
 *
 * Recursos do Laravel vêm embrulhados em `{ data: ... }` (colecção ou item).
 */
import { api } from '@/api/client';
import type { CreateTransactionPayload, Transaction } from '@/types/api';

/** Lista as transações do utilizador (mais recentes primeiro). */
export async function fetchTransactions(): Promise<Transaction[]> {
  const { data } = await api.get<{ data: Transaction[] }>('/transactions');
  return data.data;
}

/** Detalhe de uma transação (inclui a conta de recepção activa). */
export async function fetchTransaction(reference: string): Promise<Transaction> {
  const { data } = await api.get<{ data: Transaction }>(`/transactions/${reference}`);
  return data.data;
}

/** Cria uma transação `pending` a partir da calculadora. */
export async function createTransaction(payload: CreateTransactionPayload): Promise<Transaction> {
  const { data } = await api.post<{ data: Transaction }>('/transactions', payload);
  return data.data;
}

/** Cliente confirma recepção dos AOA → `completed`. */
export async function confirmTransaction(reference: string): Promise<Transaction> {
  const { data } = await api.post<{ data: Transaction }>(`/transactions/${reference}/confirm`);
  return data.data;
}

/** Cliente cancela uma transação ainda em fase inicial. */
export async function cancelTransaction(reference: string): Promise<Transaction> {
  const { data } = await api.post<{ data: Transaction }>(`/transactions/${reference}/cancel`);
  return data.data;
}
