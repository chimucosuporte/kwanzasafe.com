/**
 * Estado de sessão (Zustand) — pouco estado de cliente: token + utilizador.
 *
 * O token vive no armazenamento seguro; aqui mantemos uma cópia em memória
 * para injecção rápida no cliente HTTP. Não importa o `api/client` para
 * evitar ciclos: o cliente é que lê este store.
 */
import { create } from 'zustand';

import { queryClient } from '@/lib/queryClient';
import {
  deleteToken,
  getBiometricEnabled,
  getOnboardingSeen,
  getToken,
  setOnboardingSeen,
  setToken,
} from '@/lib/secureStore';
import type { User } from '@/types/api';

type Status = 'loading' | 'authed' | 'guest';

interface AuthState {
  status: Status;
  token: string | null;
  user: User | null;
  /** Se o utilizador já viu o onboarding (primeira utilização). */
  onboardingSeen: boolean;
  /** App trancada à espera de desbloqueio biométrico (sessão existe mas escondida). */
  locked: boolean;

  /** Lê o token e o estado de onboarding persistidos no arranque da app. */
  bootstrap: () => Promise<void>;
  /** Guarda a sessão após login/registo. */
  setSession: (token: string, user: User) => Promise<void>;
  /** Actualiza o utilizador em memória (ex.: após /me). */
  setUser: (user: User) => void;
  /** Termina a sessão local (logout ou 401). */
  clearSession: () => Promise<void>;
  /** Marca o onboarding como visto (persistente). */
  completeOnboarding: () => Promise<void>;
  /** Desbloqueia a app após biometria bem-sucedida. */
  unlock: () => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  status: 'loading',
  token: null,
  user: null,
  onboardingSeen: false,
  locked: false,

  bootstrap: async () => {
    const [token, onboardingSeen, biometricEnabled] = await Promise.all([
      getToken(),
      getOnboardingSeen(),
      getBiometricEnabled(),
    ]);
    set({
      token,
      onboardingSeen,
      status: token ? 'authed' : 'guest',
      locked: !!token && biometricEnabled,
    });
  },

  setSession: async (token, user) => {
    await setToken(token);
    // Começa com cache limpa para o novo utilizador não ver dados em cache
    // de uma sessão anterior (foto/nome do /me, transações, etc.).
    queryClient.clear();
    set({ token, user, status: 'authed', locked: false });
  },

  setUser: (user) => set({ user }),

  clearSession: async () => {
    await deleteToken();
    // Limpa toda a cache de servidor para o próximo utilizador não herdar
    // dados do anterior (ex.: foto/nome do /me, transações, notificações).
    queryClient.clear();
    set({ token: null, user: null, status: 'guest', locked: false });
  },

  unlock: () => set({ locked: false }),

  completeOnboarding: async () => {
    await setOnboardingSeen();
    set({ onboardingSeen: true });
  },
}));
