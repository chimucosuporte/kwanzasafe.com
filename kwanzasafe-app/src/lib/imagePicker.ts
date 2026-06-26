/**
 * Captura/seleção de imagens para o KYC (documento e selfie).
 * Usa expo-image-picker — a câmara requer um development build (não Expo Go).
 */
import * as ImagePicker from 'expo-image-picker';

import type { PickedFile } from '@/types/api';

function toPicked(asset: ImagePicker.ImagePickerAsset): PickedFile {
  return {
    uri: asset.uri,
    name: asset.fileName ?? `kyc-${Date.now()}.jpg`,
    mimeType: asset.mimeType ?? 'image/jpeg',
  };
}

/** Abre a câmara. Lança erro se a permissão for negada; null se cancelado. */
export async function capturePhoto(): Promise<PickedFile | null> {
  const perm = await ImagePicker.requestCameraPermissionsAsync();
  if (!perm.granted) {
    throw new Error('Precisamos de acesso à câmara. Ativa a permissão nas definições do telemóvel.');
  }
  const res = await ImagePicker.launchCameraAsync({ quality: 0.7, mediaTypes: ['images'] });
  if (res.canceled || !res.assets?.length) return null;
  return toPicked(res.assets[0]);
}

/** Abre a galeria. Lança erro se a permissão for negada; null se cancelado. */
export async function pickPhoto(): Promise<PickedFile | null> {
  const perm = await ImagePicker.requestMediaLibraryPermissionsAsync();
  if (!perm.granted) {
    throw new Error('Precisamos de acesso às fotos. Ativa a permissão nas definições do telemóvel.');
  }
  const res = await ImagePicker.launchImageLibraryAsync({ quality: 0.7, mediaTypes: ['images'] });
  if (res.canceled || !res.assets?.length) return null;
  return toPicked(res.assets[0]);
}
