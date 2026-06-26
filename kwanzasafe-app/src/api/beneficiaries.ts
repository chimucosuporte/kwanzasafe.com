/**
 * Beneficiários / Cofre IBAN (/beneficiaries).
 */
import { api } from '@/api/client';
import type { Beneficiary } from '@/types/api';

/** Lista as contas bancárias do utilizador. */
export async function fetchBeneficiaries(): Promise<Beneficiary[]> {
  const { data } = await api.get<{ data: Beneficiary[] }>('/beneficiaries');
  return data.data;
}

/** Adiciona uma conta bancária (anti-fraude validado no servidor). */
export async function addBeneficiary(payload: {
  bank_name: string;
  iban: string;
  holder_name: string;
}): Promise<Beneficiary> {
  const { data } = await api.post<{ data: Beneficiary }>('/beneficiaries', payload);
  return data.data;
}

/** Remove uma conta bancária. */
export async function removeBeneficiary(id: number): Promise<void> {
  await api.delete(`/beneficiaries/${id}`);
}
