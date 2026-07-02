import { api } from '@/api/client';
import type {
  AdminChatMessage, AdminPaymentAccount, AdminRate, AdminStaff, AdminStats, AdminTransaction,
  AdminUserDetail, AdminUserRow, AuditResponse, KycDetail, KycListItem, PickedFile,
  RecourseDetail, RecourseItem,
} from '@/types/api';

// ---- Dashboard ----
export async function fetchStats(period = 'today'): Promise<{ stats: AdminStats; chart: unknown[] }> {
  const { data } = await api.get('/admin/stats', { params: { period } });
  return data;
}

// ---- Transações ----
export async function fetchTransactions(params: { status?: string; q?: string; assigned?: string }): Promise<{ data: AdminTransaction[]; meta: { total: number } }> {
  const { data } = await api.get('/admin/transactions', { params });
  return data;
}

export async function fetchTransaction(id: number): Promise<AdminTransaction> {
  const { data } = await api.get(`/admin/transactions/${id}`);
  return data.data;
}

export type TxAction = 'request-payment' | 'payment-received' | 'aoa-sent' | 'approve' | 'cancel' | 'assign';

export async function actOnTransaction(id: number, action: TxAction): Promise<AdminTransaction> {
  const { data } = await api.post(`/admin/transactions/${id}/${action}`);
  return data.data;
}

export async function fetchMessages(id: number, after = 0): Promise<AdminChatMessage[]> {
  const { data } = await api.get(`/admin/transactions/${id}/messages`, { params: { after } });
  return data.data;
}

export async function sendMessage(id: number, payload: { text?: string; channel?: 'client' | 'internal'; file?: PickedFile | null }): Promise<AdminChatMessage> {
  const form = new FormData();
  if (payload.text) form.append('message_text', payload.text);
  if (payload.channel) form.append('channel', payload.channel);
  if (payload.file) {
    form.append('attachment', { uri: payload.file.uri, name: payload.file.name, type: payload.file.mimeType } as unknown as Blob);
  }
  const { data } = await api.post(`/admin/transactions/${id}/messages`, form, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return data.data;
}

// ---- KYC ----
export async function fetchKycList(): Promise<{ pending: KycListItem[]; approved: KycListItem[] }> {
  const { data } = await api.get('/admin/kyc');
  return data;
}

export async function fetchKycDetail(userId: number): Promise<KycDetail> {
  const { data } = await api.get(`/admin/kyc/${userId}`);
  return data.data;
}

export async function approveKyc(userId: number): Promise<void> {
  await api.post(`/admin/kyc/${userId}/approve`);
}

export async function rejectKyc(userId: number, reason: string): Promise<void> {
  await api.post(`/admin/kyc/${userId}/reject`, { reason });
}

// ---- Taxas ----
export async function fetchRates(): Promise<AdminRate[]> {
  const { data } = await api.get('/admin/rates');
  return data.data;
}

export async function updateRate(id: number, payload: { rate: number; is_active: boolean }): Promise<AdminRate> {
  const { data } = await api.put(`/admin/rates/${id}`, payload);
  return data.data;
}

// ---- Utilizadores ----
export async function fetchUsers(params: { kyc?: string; q?: string }): Promise<{ data: AdminUserRow[]; meta: { total: number } }> {
  const { data } = await api.get('/admin/users', { params });
  return data;
}

export async function fetchUser(id: number): Promise<AdminUserDetail> {
  const { data } = await api.get(`/admin/users/${id}`);
  return data.data;
}

export async function toggleUserAdmin(id: number): Promise<AdminUserDetail> {
  const { data } = await api.post(`/admin/users/${id}/toggle-admin`);
  return data.data;
}

// ---- Contas de pagamento (super-admin) ----
export interface PaymentAccountPayload {
  currency: string; holder: string; identifier: string; network?: string; instructions?: string; is_active: boolean;
}

export async function fetchPaymentAccounts(): Promise<{ data: AdminPaymentAccount[]; available_currencies: string[] }> {
  const { data } = await api.get('/admin/payment-accounts');
  return data;
}

export async function savePaymentAccount(id: number | null, payload: PaymentAccountPayload): Promise<AdminPaymentAccount> {
  const { data } = id ? await api.put(`/admin/payment-accounts/${id}`, payload) : await api.post('/admin/payment-accounts', payload);
  return data.data;
}

export async function deletePaymentAccount(id: number): Promise<void> {
  await api.delete(`/admin/payment-accounts/${id}`);
}

// ---- Auditoria ----
export async function fetchAudit(params: { category?: string; severity?: string; search?: string }): Promise<AuditResponse> {
  const { data } = await api.get('/admin/audit', { params });
  return data;
}

// ---- Staff (super-admin) ----
export async function fetchStaff(): Promise<{ staff: AdminStaff[]; super_admins: { id: number; full_name: string | null; email: string }[] }> {
  const { data } = await api.get('/admin/staff');
  return data;
}

export async function createStaff(payload: { full_name: string; email: string; password: string; password_confirmation: string }): Promise<AdminStaff> {
  const { data } = await api.post('/admin/staff', payload);
  return data.data;
}

export async function toggleStaffActive(id: number): Promise<AdminStaff> {
  const { data } = await api.post(`/admin/staff/${id}/toggle-active`);
  return data.data;
}

export async function deleteStaff(id: number): Promise<void> {
  await api.delete(`/admin/staff/${id}`);
}

// ---- Recursos (super-admin) ----
export async function fetchRecourses(): Promise<{ active: RecourseItem[]; resolved: RecourseItem[] }> {
  const { data } = await api.get('/admin/recourses');
  return data;
}

export async function fetchRecourse(id: number): Promise<RecourseDetail> {
  const { data } = await api.get(`/admin/recourses/${id}`);
  return data.data;
}

export async function replyRecourse(id: number, message_text: string): Promise<void> {
  await api.post(`/admin/recourses/${id}/reply`, { message_text });
}

export async function closeRecourse(id: number, action: 'resolve' | 'reject', resolution: string): Promise<void> {
  await api.post(`/admin/recourses/${id}/${action}`, { resolution });
}
