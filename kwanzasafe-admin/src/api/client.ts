/**
 * Cliente HTTP único da app admin.
 * - Injecta o token Sanctum (Bearer).
 * - Pede sempre JSON (Laravel devolve 422/403 em vez de redirects).
 * - Em 401, limpa a sessão (o AuthGate redirecciona para o login).
 */
import axios, { AxiosError } from 'axios';

import { API_URL } from '@/config';
import { useAuthStore } from '@/stores/auth';

export const api = axios.create({
  baseURL: API_URL,
  timeout: 20000,
  headers: { Accept: 'application/json' },
});

api.interceptors.request.use((config) => {
  const token = useAuthStore.getState().token;
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    if (error.response?.status === 401) {
      void useAuthStore.getState().clearSession();
    }
    return Promise.reject(error);
  },
);

/** Extrai os erros de validação 422 por campo. */
export function apiFieldErrors(error: unknown): Record<string, string> {
  const out: Record<string, string> = {};
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as { errors?: Record<string, string[]> } | undefined;
    if (data?.errors) {
      for (const [field, msgs] of Object.entries(data.errors)) {
        if (msgs?.[0]) out[field] = msgs[0];
      }
    }
  }
  return out;
}

/** Mensagem legível de um erro axios/Laravel. */
export function apiErrorMessage(error: unknown, fallback = 'Ocorreu um erro. Tenta novamente.'): string {
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as { message?: string; errors?: Record<string, string[]> } | undefined;
    if (data?.errors) {
      const first = Object.values(data.errors)[0]?.[0];
      if (first) return first;
    }
    if (data?.message) return data.message;
    if (error.code === 'ECONNABORTED') return 'O servidor demorou a responder. Verifica a ligação.';
    if (error.message === 'Network Error') return 'Sem ligação ao servidor. Verifica a internet.';
  }
  return fallback;
}
