import { api } from '@/api/client';
import type { AdminUser } from '@/types/api';

/** Erro lançado quando a conta é válida mas não tem acesso administrativo. */
export class NotAdminError extends Error {
  constructor() {
    super('Esta conta não tem acesso administrativo.');
    this.name = 'NotAdminError';
  }
}

/** Login de staff. Rejeita contas sem is_admin (revoga o token gerado). */
export async function loginStaff(email: string, password: string): Promise<{ token: string; user: AdminUser }> {
  const { data } = await api.post('/login', { email, password });
  const user = data.user as AdminUser;

  if (! user?.is_admin) {
    // A conta não é staff — revoga o token acabado de emitir e recusa.
    try {
      await api.post('/logout', {}, { headers: { Authorization: `Bearer ${data.token}` } });
    } catch {
      /* ignora */
    }
    throw new NotAdminError();
  }

  return { token: data.token, user };
}

export async function fetchMe(): Promise<AdminUser> {
  const { data } = await api.get('/me');
  return data.user as AdminUser;
}

export async function logout(): Promise<void> {
  try {
    await api.post('/logout');
  } catch {
    /* ignora — limpamos sempre localmente */
  }
}
