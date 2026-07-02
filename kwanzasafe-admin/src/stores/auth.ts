import { create } from 'zustand';

import { deleteToken, getToken, setToken } from '@/lib/secureStore';
import { queryClient } from '@/lib/queryClient';
import type { AdminUser } from '@/types/api';

type Status = 'loading' | 'guest' | 'authed';

interface AuthState {
  status: Status;
  token: string | null;
  user: AdminUser | null;
  bootstrap: () => Promise<void>;
  setSession: (token: string, user: AdminUser) => Promise<void>;
  setUser: (user: AdminUser) => void;
  clearSession: () => Promise<void>;
}

export const useAuthStore = create<AuthState>((set) => ({
  status: 'loading',
  token: null,
  user: null,

  bootstrap: async () => {
    const token = await getToken();
    set({ token, status: token ? 'authed' : 'guest' });
  },

  setSession: async (token, user) => {
    await setToken(token);
    queryClient.clear();
    set({ token, user, status: 'authed' });
  },

  setUser: (user) => set({ user }),

  clearSession: async () => {
    await deleteToken();
    queryClient.clear();
    set({ token: null, user: null, status: 'guest' });
  },
}));
