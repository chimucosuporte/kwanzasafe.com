/**
 * Tipos TypeScript alinhados com as API Resources do Laravel.
 * Manter em sincronia com app/Http/Resources/*.php no backend.
 */

/** UserResource (kwanzasafe/app/Http/Resources/UserResource.php) */
export interface User {
  id: number;
  full_name: string | null;
  email: string;
  phone_number: string | null;
  country: string | null;
  province: string | null;
  balance: string | null;
  avatar_url: string | null;
  role: 'client' | 'support' | 'super_admin';
  role_label: string;
  is_admin: boolean;
  is_super_admin: boolean;
  email_verified: boolean;
  phone_verified: boolean;
  identity_verified: boolean;
  data_verified: boolean;
  is_fully_verified: boolean;
  kyc_bot_status: string | null;
  kyc_score: number | null;
  two_factor_enabled: boolean;
  created_at: string | null;
}

/** Resposta de /login e /register. */
export interface AuthResponse {
  token: string;
  user: User;
}

/** Envelope de /me. */
export interface MeResponse {
  user: User;
}

export interface LoginPayload {
  email: string;
  password: string;
  two_factor_code?: string;
  device_name?: string;
}

export interface RegisterPayload {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  device_name?: string;
}

export interface ResetPasswordPayload {
  email: string;
  code: string;
  password: string;
  password_confirmation: string;
}

/** Erro de validação 422 do Laravel. */
export interface ValidationErrorBody {
  message: string;
  errors: Record<string, string[]>;
}

/** Estados possíveis de uma transação (espelha o backend). */
export type TransactionStatus =
  | 'pending'
  | 'negotiating'
  | 'awaiting_payment'
  | 'payment_received'
  | 'processing'
  | 'aoa_sent'
  | 'completed'
  | 'cancelled'
  | 'expired';

/** ExchangeRateResource — alimenta a calculadora. */
export interface ExchangeRate {
  id: number;
  currency_from: string;
  currency_to: string;
  /** String para preservar a precisão (nunca float no dinheiro). */
  rate: string;
  is_active: boolean;
}

/** PaymentAccountResource — conta de recepção (onde o cliente paga). */
export interface PaymentAccount {
  currency: string;
  holder: string | null;
  identifier: string | null;
  network: string | null;
  instructions: string | null;
}

/** Destino de recepção da transação (snapshot). */
export interface TransactionDestination {
  type: 'bank' | WalletProvider;
  label: string;
  identifier: string;
  holder: string;
  network: string | null;
}

/** TransactionResource — montantes vêm como string (casts decimal). */
export interface Transaction {
  id: number;
  reference_id: string;
  status: TransactionStatus;
  status_label: string;
  currency_from: string;
  currency_to: string;
  amount_sent: string;
  rate_applied: string;
  amount_received: string;
  fee_amount: string;
  can_cancel: boolean;
  can_upload_receipt: boolean;
  can_confirm: boolean;
  created_at: string | null;
  expires_at: string | null;
  payment_received_at: string | null;
  aoa_sent_at: string | null;
  client_confirmed_at: string | null;
  /** Para onde o cliente recebe os Kwanzas (snapshot). */
  destination?: TransactionDestination | null;
  /** Presente apenas no detalhe. */
  payment_account?: PaymentAccount | null;
}

/** Corpo de POST /transactions (mesmos nomes do backend). */
export interface CreateTransactionPayload {
  moeda: number;
  valor_enviar: number;
  destino_tipo: 'bank' | WalletProvider;
  destino_id: number;
}

/** ChatMessageResource — mensagem da sala de transação. */
export interface ChatMessage {
  id: number;
  text: string | null;
  type: 'text' | 'image' | 'document';
  is_mine: boolean;
  is_system: boolean;
  is_read: boolean;
  /** Aponta para /api/v1/file/... — requer Bearer token ao carregar. */
  file_url: string | null;
  is_image: boolean;
  is_pdf: boolean;
  created_at: string | null;
  time: string | null;
}

/** Ficheiro escolhido (picker) pronto para FormData. */
export interface PickedFile {
  uri: string;
  name: string;
  mimeType: string;
}

/** BeneficiaryResource — conta bancária do utilizador (Cofre IBAN). */
export interface Beneficiary {
  id: number;
  bank_name: string;
  iban: string;
  holder_name: string;
  created_at: string | null;
}

/** Provedores de carteira suportados (espelha PaymentWallet::PROVIDERS). */
export type WalletProvider = 'bybit' | 'binance' | 'redotpay';

/** PaymentWalletResource — carteira de recepção (Bybit/Binance/RedotPay). */
export interface PaymentWallet {
  id: number;
  provider: WalletProvider;
  identifier: string;
  holder_name: string;
  network: string | null;
  is_default: boolean;
  created_at: string | null;
}

/** Notificação do feed (view Notificações). */
export interface UserNotification {
  id: number;
  type: 'security' | 'transaction' | 'recourse' | 'info';
  title: string;
  body: string;
  data: Record<string, unknown> | null;
  is_read: boolean;
  created_at: string | null;
}

/** Estados de um recurso (appeal). */
export type RecourseStatus = 'open' | 'in_review' | 'resolved' | 'rejected' | 'cancelled';

/** RecourseResource — recurso (appeal) de uma transação. */
export interface Recourse {
  id: number;
  status: RecourseStatus;
  status_label: string;
  reason: string;
  resolution: string | null;
  is_active: boolean;
  can_cancel: boolean;
  expires_at: string | null;
  resolved_at: string | null;
  created_at: string | null;
}

/** Resultado da análise automática do KycBot. */
export interface KycResult {
  score: number;
  status: 'auto_approved' | 'pending_review' | 'auto_rejected' | string;
}

/** Resposta dos passos de KYC: mensagem + resultado do bot + utilizador. */
export interface KycResponse {
  message: string;
  kyc: KycResult;
  user: User;
}

/** Dados do passo 1 do KYC. */
export interface PersonalDataPayload {
  full_name: string;
  birth_date: string;
  gender: 'M' | 'F';
  bi_number: string;
  bi_expiry: string;
  province: string;
  municipality: string;
  address: string;
}
