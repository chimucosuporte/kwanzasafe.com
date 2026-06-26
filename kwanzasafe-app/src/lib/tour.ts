/**
 * Persistência do "modo guia": que tours já foram vistos (não repetem).
 * Guardado como JSON (lista de chaves) em expo-secure-store.
 */
import { Platform } from 'react-native';
import * as SecureStore from 'expo-secure-store';

const KEY = 'ks_tours_seen';
const isWeb = Platform.OS === 'web';

async function readSet(): Promise<Set<string>> {
  try {
    const raw = isWeb ? (globalThis.localStorage?.getItem(KEY) ?? null) : await SecureStore.getItemAsync(KEY);
    if (!raw) return new Set();
    const arr = JSON.parse(raw);
    return new Set(Array.isArray(arr) ? (arr as string[]) : []);
  } catch {
    return new Set();
  }
}

async function writeSet(set: Set<string>): Promise<void> {
  const raw = JSON.stringify([...set]);
  if (isWeb) globalThis.localStorage?.setItem(KEY, raw);
  else await SecureStore.setItemAsync(KEY, raw);
}

/** Já viu este tour? */
export async function isTourSeen(key: string): Promise<boolean> {
  return (await readSet()).has(key);
}

/** Marca um tour como visto. */
export async function markTourSeen(key: string): Promise<void> {
  const set = await readSet();
  set.add(key);
  await writeSet(set);
}

/** Repõe todos os tours (para "rever guias"). */
export async function resetTours(): Promise<void> {
  if (isWeb) globalThis.localStorage?.removeItem(KEY);
  else await SecureStore.deleteItemAsync(KEY);
}
