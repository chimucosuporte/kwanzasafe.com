/**
 * Feed de notificações do cliente — /notifications.
 */
import { api } from '@/api/client';
import type { UserNotification } from '@/types/api';

/** Lista as notificações + contagem de não lidas. */
export async function fetchNotifications(): Promise<{ data: UserNotification[]; unread: number }> {
  const { data } = await api.get<{ data: UserNotification[]; unread: number }>('/notifications');
  return data;
}

/** Contagem de notificações por ler (para o badge). */
export async function fetchUnreadCount(): Promise<number> {
  const { data } = await api.get<{ unread: number }>('/notifications/unread');
  return data.unread;
}

/** Marca uma notificação como lida. */
export async function markNotificationRead(id: number): Promise<void> {
  await api.post(`/notifications/${id}/read`);
}

/** Marca todas como lidas. */
export async function markAllNotificationsRead(): Promise<void> {
  await api.post('/notifications/read-all');
}
