export interface AdminUser {
  id: number;
  full_name: string | null;
  email: string;
  is_admin?: boolean;
  is_super_admin?: boolean;
  role?: string;
  role_label?: string;
}

export type TxStatus =
  | 'pending' | 'negotiating' | 'awaiting_payment' | 'payment_received'
  | 'processing' | 'aoa_sent' | 'completed' | 'cancelled' | 'expired';

export interface TxActions {
  can_request_payment: boolean;
  can_payment_received: boolean;
  can_aoa_sent: boolean;
  can_approve: boolean;
  can_cancel: boolean;
  can_assign: boolean;
}

export interface AdminTransaction {
  id: number;
  reference_id: string;
  status: TxStatus;
  status_label: string;
  currency_from: string;
  amount_sent: string;
  amount_received: string;
  client_name: string;
  client_email: string;
  agent_name: string | null;
  unread_count: number;
  created_at: string | null;
  // detalhe
  rate_applied?: string;
  fee_amount?: string;
  client_phone?: string | null;
  assigned_admin?: number | null;
  payment_received_at?: string | null;
  aoa_sent_at?: string | null;
  client_confirmed_at?: string | null;
  expires_at?: string | null;
  actions?: TxActions;
}

export interface AdminChatMessage {
  id: number;
  text: string;
  type: 'text' | 'image' | 'document';
  channel: string;
  is_mine: boolean;
  is_system: boolean;
  file_url: string | null;
  is_image: boolean;
  created_at: string | null;
}

export interface AdminStats {
  total_users: number;
  new_users_period: number;
  kyc_approved: number;
  kyc_pending: number;
  tx_pending: number;
  tx_completed: number;
  tx_cancelled: number;
  volume_aoa: string;
  total_fees: string;
  volume_by_currency: { currency: string; total: string }[];
  unread_chats: number;
  period: string;
  period_label: string;
}

export interface KycListItem {
  id: number;
  full_name: string | null;
  email: string;
  bi_number: string | null;
  kyc_score: number | null;
  kyc_bot_status: string | null;
  is_verified: boolean;
  submitted_at: string | null;
}

export interface KycDetail extends KycListItem {
  birth_date: string | null;
  bi_expiry: string | null;
  gender: string | null;
  province: string | null;
  municipality: string | null;
  address: string | null;
  phone_number: string | null;
  phone_verified: boolean;
  data_verified: boolean;
  kyc_bot_notes: unknown;
  document_url: string | null;
  photo_url: string | null;
  identity_verified_at: string | null;
}

export interface PickedFile {
  uri: string;
  name: string;
  mimeType: string;
}

export interface AdminRate {
  id: number;
  currency_from: string;
  currency_to: string;
  rate: string;
  is_active: boolean;
  updated_at: string | null;
}

export interface AdminUserRow {
  id: number;
  full_name: string | null;
  email: string;
  is_admin: boolean;
  is_verified: boolean;
  kyc_pending: boolean;
  created_at: string | null;
}

export interface AdminPaymentAccount {
  id: number;
  currency: string;
  holder: string;
  identifier: string;
  network: string | null;
  instructions: string | null;
  is_active: boolean;
}

export interface AuditEntry {
  id: number;
  action: string;
  category: string;
  severity: string;
  description: string | null;
  user_email: string | null;
  ip_address: string | null;
  created_at: string | null;
}

export interface AuditResponse {
  data: AuditEntry[];
  meta: { current_page: number; last_page: number; total: number };
  stats: { total_24h: number; critical_24h: number; failed_logins: number; fraud_attempts: number };
  categories: string[];
  severities: string[];
}

export interface AdminUserDetail extends AdminUserRow {
  is_super_admin: boolean;
  role_label: string | null;
  phone_number: string | null;
  phone_verified: boolean;
  email_verified: boolean;
  bi_number: string | null;
  province: string | null;
  country: string | null;
  balance: string;
  kyc_score: number | null;
  tx_stats?: { total: number; completed: number; active: number; cancelled: number };
}
