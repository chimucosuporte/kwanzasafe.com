/**
 * Carteiras de recepção (Bybit / Binance / RedotPay) — /wallets.
 * Complementa os beneficiários (IBAN). Anti-fraude validado no servidor.
 */
import { api } from '@/api/client';
import type { PaymentWallet, WalletProvider } from '@/types/api';

/** Lista as carteiras do utilizador (predefinida primeiro). */
export async function fetchWallets(): Promise<PaymentWallet[]> {
  const { data } = await api.get<{ data: PaymentWallet[] }>('/wallets');
  return data.data;
}

/** Adiciona uma carteira. */
export async function addWallet(payload: {
  provider: WalletProvider;
  identifier: string;
  holder_name: string;
  network?: string;
  is_default?: boolean;
}): Promise<PaymentWallet> {
  const { data } = await api.post<{ data: PaymentWallet }>('/wallets', payload);
  return data.data;
}

/** Marca uma carteira como predefinida; devolve a lista actualizada. */
export async function setDefaultWallet(id: number): Promise<PaymentWallet[]> {
  const { data } = await api.post<{ data: PaymentWallet[] }>(`/wallets/${id}/default`);
  return data.data;
}

/** Remove uma carteira. */
export async function removeWallet(id: number): Promise<void> {
  await api.delete(`/wallets/${id}`);
}
