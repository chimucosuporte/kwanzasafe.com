/**
 * Estado de UI do cliente (Zustand) — sinais transversais que não pertencem
 * ao servidor. Por agora: conectividade derivada dos pedidos HTTP.
 *
 * Sem dependência nativa de NetInfo: o `api/client` marca `connected=false`
 * quando um pedido falha por "Network Error" e `true` quando algum responde.
 */
import { create } from 'zustand';

interface UiState {
  connected: boolean;
  setConnected: (connected: boolean) => void;
}

export const useUiStore = create<UiState>((set) => ({
  connected: true,
  setConnected: (connected) =>
    set((s) => (s.connected === connected ? s : { connected })),
}));
