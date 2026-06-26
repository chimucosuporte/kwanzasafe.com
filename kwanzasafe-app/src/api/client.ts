/**
 * Cliente HTTP único da app.
 *
 * - Injecta automaticamente o token Sanctum (Bearer) em cada pedido.
 * - Pede sempre JSON (para o Laravel devolver 422 em vez de redirects).
 * - Em 401, limpa a sessão (o AuthGate redirecciona para o login).
 */
import axios, { AxiosError } from 'axios';

import { API_URL } from '@/config';
import { useAuthStore } from '@/stores/auth';
import { useUiStore } from '@/stores/ui';

export const api = axios.create({
  baseURL: API_URL,
  timeout: 20000,
  headers: { Accept: 'application/json' },
});

api.interceptors.request.use((config) => {
  const token = useAuthStore.getState().token;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => {
    // Houve resposta → há ligação ao servidor.
    useUiStore.getState().setConnected(true);
    return response;
  },
  (error: AxiosError) => {
    if (error.response) {
      // O servidor respondeu (ainda que com erro) → há ligação.
      useUiStore.getState().setConnected(true);
      if (error.response.status === 401) {
        // Token inválido/expirado/revogado → terminar sessão local.
        void useAuthStore.getState().clearSession();
      }
    } else if (error.code === 'ERR_NETWORK' || error.message === 'Network Error') {
      // Sem resposta do servidor → provavelmente offline.
      useUiStore.getState().setConnected(false);
    }
    return Promise.reject(error);
  },
);

/** Extrai os erros de validação 422 por campo (ex.: { full_name: 'obrigatório' }). */
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

/** Indica se o erro de login pede o código de 2 passos (TOTP). */
export function isTwoFactorRequired(error: unknown): boolean {
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as { two_factor_required?: boolean } | undefined;
    return data?.two_factor_required === true;
  }
  return false;
}

/** Extrai uma mensagem legível de um erro do axios/Laravel. */
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
