/**
 * KYC — verificação de identidade em 4 passos (/kyc/*).
 */
import { api } from '@/api/client';
import type { KycResponse, PersonalDataPayload, PickedFile } from '@/types/api';

/** Passo 1: dados pessoais. */
export async function submitPersonalData(payload: PersonalDataPayload): Promise<KycResponse> {
  const { data } = await api.post<KycResponse>('/kyc/personal', payload);
  return data;
}

/** Passo 2: envia o código (por email) para confirmar o telefone. */
export async function sendPhoneCode(phone: string): Promise<{ message: string }> {
  const { data } = await api.post<{ message: string }>('/kyc/phone/send', { phone });
  return data;
}

/** Passo 2b: confirma o código do telefone. */
export async function verifyPhoneCode(phoneOtp: string): Promise<KycResponse> {
  const { data } = await api.post<KycResponse>('/kyc/phone/verify', { phone_otp: phoneOtp });
  return data;
}

function fileForm(field: string, file: PickedFile): FormData {
  const form = new FormData();
  form.append(field, { uri: file.uri, name: file.name, type: file.mimeType } as unknown as Blob);
  return form;
}

/** Passo 3: documento de identidade. */
export async function uploadKycDocument(file: PickedFile): Promise<KycResponse> {
  const { data } = await api.post<KycResponse>('/kyc/document', fileForm('document', file), {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return data;
}

/** Passo 4: selfie. */
export async function uploadKycPhoto(file: PickedFile): Promise<KycResponse> {
  const { data } = await api.post<KycResponse>('/kyc/photo', fileForm('photo', file), {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return data;
}
