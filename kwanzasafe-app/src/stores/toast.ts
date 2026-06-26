/**
 * Toast global (notificação no topo). Usado sobretudo para avisar que há
 * erros nos campos de um formulário, sem cobrir o ecrã.
 */
import { create } from 'zustand';

export type ToastType = 'error' | 'success' | 'info';

interface ToastState {
  /** Muda a cada show() para reanimar mesmo com a mesma mensagem. */
  seq: number;
  message: string | null;
  type: ToastType;
  show: (message: string, type?: ToastType) => void;
  hide: () => void;
}

export const useToastStore = create<ToastState>((set) => ({
  seq: 0,
  message: null,
  type: 'error',
  show: (message, type = 'error') => set((s) => ({ seq: s.seq + 1, message, type })),
  hide: () => set({ message: null }),
}));

/** Atalho fora de componentes. */
export const toast = {
  error: (m: string) => useToastStore.getState().show(m, 'error'),
  success: (m: string) => useToastStore.getState().show(m, 'success'),
  info: (m: string) => useToastStore.getState().show(m, 'info'),
  hide: () => useToastStore.getState().hide(),
};
