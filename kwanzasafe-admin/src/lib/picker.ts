import * as DocumentPicker from 'expo-document-picker';

import type { PickedFile } from '@/types/api';

/** Seleciona uma imagem ou PDF (comprovativos/anexos). Devolve null se cancelar. */
export async function pickAttachment(): Promise<PickedFile | null> {
  const res = await DocumentPicker.getDocumentAsync({
    type: ['image/*', 'application/pdf'],
    copyToCacheDirectory: true,
  });
  if (res.canceled || !res.assets?.length) return null;
  const a = res.assets[0];
  return {
    uri: a.uri,
    name: a.name ?? 'anexo',
    mimeType: a.mimeType ?? 'application/octet-stream',
  };
}
