/**
 * Metadados de apresentação das carteiras de recepção.
 * Os dados em si vêm da API (/wallets, src/api/wallets.ts).
 */
import type { WalletProvider } from '@/types/api';

export type { WalletProvider };

/** Metadados de apresentação por provedor. */
export const WALLET_META: Record<
  WalletProvider,
  { label: string; color: string; idLabel: string; idPlaceholder: string; hasNetwork: boolean }
> = {
  bybit: { label: 'Bybit', color: '#f7a600', idLabel: 'UID Bybit', idPlaceholder: '123456789', hasNetwork: true },
  binance: { label: 'Binance', color: '#f3ba2f', idLabel: 'Binance Pay ID / email', idPlaceholder: 'Pay ID ou email', hasNetwork: true },
  redotpay: { label: 'RedotPay', color: '#ff5a5a', idLabel: 'RedotPay ID / email', idPlaceholder: 'ID ou email', hasNetwork: false },
};
