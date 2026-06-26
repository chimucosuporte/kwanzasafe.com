/**
 * Selector de ficheiros (imagens + PDF) para anexos e comprovativos.
 * Usa expo-document-picker (funciona no Expo Go, sem permissões especiais).
 */
import * as DocumentPicker from 'expo-document-picker';

import type { PickedFile } from '@/types/api';

/** Abre o selector. Devolve o ficheiro escolhido ou null se cancelado. */
export async function pickAttachment(): Promise<PickedFile | null> {
  const res = await DocumentPicker.getDocumentAsync({
    type: ['image/*', 'application/pdf'],
    copyToCacheDirectory: true,
    multiple: false,
  });

  if (res.canceled) return null;

  const asset = res.assets[0];
  if (!asset) return null;

  return {
    uri: asset.uri,
    name: asset.name ?? 'ficheiro',
    mimeType: asset.mimeType ?? 'application/octet-stream',
  };
}
