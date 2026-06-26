/**
 * Chamadas de autenticação à API (FASE 0 do backend).
 */
import { Platform } from 'react-native';

import { api } from '@/api/client';
import type {
  AuthResponse,
  LoginPayload,
  MeResponse,
  RegisterPayload,
  ResetPasswordPayload,
  User,
} from '@/types/api';

const deviceName = `${Platform.OS}-app`;

export async function login(payload: LoginPayload): Promise<AuthResponse> {
  const { data } = await api.post<AuthResponse>('/login', {
    ...payload,
    device_name: payload.device_name ?? deviceName,
  });
  return data;
}

export async function registerAccount(payload: RegisterPayload): Promise<AuthResponse> {
  const { data } = await api.post<AuthResponse>('/register', {
    ...payload,
    device_name: payload.device_name ?? deviceName,
  });
  return data;
}

/** Recuperação de palavra-passe: envia código OTP para o email. */
export async function forgotPassword(email: string): Promise<{ message: string }> {
  const { data } = await api.post<{ message: string }>('/password/forgot', { email });
  return data;
}

/** Define a nova palavra-passe com o código recebido. */
export async function resetPassword(payload: ResetPasswordPayload): Promise<{ message: string }> {
  const { data } = await api.post<{ message: string }>('/password/reset', payload);
  return data;
}

/** Envia código de verificação para o email do utilizador autenticado. */
export async function sendEmailOtp(): Promise<{ message: string }> {
  const { data } = await api.post<{ message: string }>('/email/verify/send');
  return data;
}

/** Confirma o email com o código de 6 dígitos. */
export async function verifyEmailOtp(code: string): Promise<{ message: string; user: User }> {
  const { data } = await api.post<{ message: string; user: User }>('/email/verify', { code });
  return data;
}

export async function fetchMe(): Promise<MeResponse> {
  const { data } = await api.get<MeResponse>('/me');
  return data;
}

export async function logout(): Promise<void> {
  await api.post('/logout');
}
