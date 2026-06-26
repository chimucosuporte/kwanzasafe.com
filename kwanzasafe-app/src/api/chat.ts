/**
 * Sala de transação — mensagens e anexos (FASE 1 do backend).
 *
 * O envio usa multipart/form-data. Em React Native, anexa-se o ficheiro como
 * `{ uri, name, type }` ao FormData.
 */
import { api } from '@/api/client';
import type { ChatMessage, PickedFile, Transaction } from '@/types/api';

/** Lista mensagens novas. `after` = id da última conhecida (polling). */
export async function fetchMessages(reference: string, after = 0): Promise<ChatMessage[]> {
  const { data } = await api.get<{ data: ChatMessage[] }>(
    `/transactions/${reference}/messages`,
    { params: { after } },
  );
  return data.data;
}

/** Anexa um ficheiro a um FormData no formato esperado pelo RN. */
function appendFile(form: FormData, field: string, file: PickedFile): void {
  // O cast é necessário: o tipo DOM de FormData não conhece a forma RN.
  form.append(field, { uri: file.uri, name: file.name, type: file.mimeType } as unknown as Blob);
}

/** Envia mensagem (texto e/ou anexo). */
export async function sendMessage(
  reference: string,
  input: { text?: string; file?: PickedFile | null },
): Promise<ChatMessage> {
  const form = new FormData();
  if (input.text) form.append('message_text', input.text);
  if (input.file) appendFile(form, 'attachment', input.file);

  const { data } = await api.post<{ data: ChatMessage }>(
    `/transactions/${reference}/messages`,
    form,
    { headers: { 'Content-Type': 'multipart/form-data' } },
  );
  return data.data;
}

/** Envia o comprovativo de pagamento → transição para `awaiting_payment`. */
export async function uploadReceipt(reference: string, file: PickedFile): Promise<Transaction> {
  const form = new FormData();
  appendFile(form, 'comprovativo', file);

  const { data } = await api.post<{ data: Transaction }>(
    `/transactions/${reference}/receipt`,
    form,
    { headers: { 'Content-Type': 'multipart/form-data' } },
  );
  return data.data;
}
