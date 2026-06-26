/**
 * Armazenamento seguro do token Sanctum (Keychain no iOS, Keystore no Android).
 *
 * NUNCA usar AsyncStorage para o token — só armazenamento encriptado.
 * Em web (smoke-test no browser) faz fallback para localStorage, pois o
 * expo-secure-store não está disponível nessa plataforma.
 */
import { Platform } from 'react-native';
import * as SecureStore from 'expo-secure-store';

const TOKEN_KEY = 'ks_auth_token';
const ONBOARDING_KEY = 'ks_onboarding_seen';
const BIOMETRIC_KEY = 'ks_biometric_enabled';

const isWeb = Platform.OS === 'web';

export async function getToken(): Promise<string | null> {
  if (isWeb) {
    return globalThis.localStorage?.getItem(TOKEN_KEY) ?? null;
  }
  return SecureStore.getItemAsync(TOKEN_KEY);
}

export async function setToken(token: string): Promise<void> {
  if (isWeb) {
    globalThis.localStorage?.setItem(TOKEN_KEY, token);
    return;
  }
  await SecureStore.setItemAsync(TOKEN_KEY, token);
}

export async function deleteToken(): Promise<void> {
  if (isWeb) {
    globalThis.localStorage?.removeItem(TOKEN_KEY);
    return;
  }
  await SecureStore.deleteItemAsync(TOKEN_KEY);
}

/** Flag de "já viu o onboarding" (não é segredo, mas reutiliza o mesmo módulo). */
export async function getOnboardingSeen(): Promise<boolean> {
  if (isWeb) {
    return globalThis.localStorage?.getItem(ONBOARDING_KEY) === '1';
  }
  return (await SecureStore.getItemAsync(ONBOARDING_KEY)) === '1';
}

export async function setOnboardingSeen(): Promise<void> {
  if (isWeb) {
    globalThis.localStorage?.setItem(ONBOARDING_KEY, '1');
    return;
  }
  await SecureStore.setItemAsync(ONBOARDING_KEY, '1');
}

/** Flag de "desbloqueio por biometria activado". */
export async function getBiometricEnabled(): Promise<boolean> {
  if (isWeb) {
    return globalThis.localStorage?.getItem(BIOMETRIC_KEY) === '1';
  }
  return (await SecureStore.getItemAsync(BIOMETRIC_KEY)) === '1';
}

export async function setBiometricEnabled(enabled: boolean): Promise<void> {
  if (isWeb) {
    if (enabled) globalThis.localStorage?.setItem(BIOMETRIC_KEY, '1');
    else globalThis.localStorage?.removeItem(BIOMETRIC_KEY);
    return;
  }
  if (enabled) await SecureStore.setItemAsync(BIOMETRIC_KEY, '1');
  else await SecureStore.deleteItemAsync(BIOMETRIC_KEY);
}
