import { api } from '@/api/client';
import type {
  AdminChatMessage, AdminStats, AdminTransaction, KycDetail, KycListItem, PickedFile,
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
