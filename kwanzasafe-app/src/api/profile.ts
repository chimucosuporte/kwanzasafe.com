/**
 * Perfil do utilizador (FASE de onboarding/perfil — /profile).
 */
import { api } from '@/api/client';
import type { PickedFile, User } from '@/types/api';

/** Actualiza nome/email. Devolve o utilizador actualizado. */
export async function updateProfile(payload: { name: string; email: string }): Promise<User> {
  const { data } = await api.patch<{ message: string; user: User }>('/profile', payload);
  return data.user;
}

/** Pede a alteração de email — envia código ao email atual e ao novo. */
export async function requestEmailChange(email: string): Promise<{ message: string }> {
  const { data } = await api.post<{ message: string }>('/profile/email/request', { email });
  return data;
}

/** Confirma a alteração de email com os dois códigos. */
export async function confirmEmailChange(payload: {
  email: string;
  code_current: string;
  code_new: string;
}): Promise<{ message: string; user: User }> {
  const { data } = await api.post<{ message: string; user: User }>('/profile/email/confirm', payload);
  return data;
}

/** Envia a foto de perfil (multipart). Devolve o utilizador actualizado. */
export async function uploadAvatar(file: PickedFile): Promise<User> {
  const form = new FormData();
  form.append('photo', { uri: file.uri, name: file.name, type: file.mimeType } as unknown as Blob);
  const { data } = await api.post<{ message: string; user: User }>('/profile/photo', form, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return data.user;
}

/** Altera a palavra-passe (exige a atual). */
export async function updatePassword(payload: {
  current_password: string;
  password: string;
  password_confirmation: string;
}): Promise<{ message: string }> {
  const { data } = await api.put<{ message: string }>('/profile/password', payload);
  return data;
}

/** Elimina definitivamente a conta (exige a palavra-passe). */
export async function deleteAccount(password: string): Promise<{ message: string }> {
  const { data } = await api.delete<{ message: string }>('/profile', { data: { password } });
  return data;
}
