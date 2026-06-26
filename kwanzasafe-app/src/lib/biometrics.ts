/**
 * Desbloqueio biométrico (impressão digital / Face).
 *
 * Requer dev build (módulo nativo `expo-local-authentication`). O módulo é
 * carregado de forma **lazy** (import dinâmico com try/catch) para que a app
 * NUNCA parta num build onde o módulo nativo ainda não exista — degrada
 * graciosamente para "biometria indisponível".
 *
 * A flag de "activado" vive no secure-store; o token Sanctum continua a ser a
 * fonte de sessão — a biometria é só um cadeado local de abertura da app.
 */
import { Platform } from 'react-native';

type LocalAuth = typeof import('expo-local-authentication');

/** Carrega o módulo nativo só quando preciso; devolve null se indisponível. */
async function loadModule(): Promise<LocalAuth | null> {
  if (Platform.OS === 'web') return null;
  try {
    return await import('expo-local-authentication');
  } catch {
    return null;
  }
}

/** Há hardware biométrico E pelo menos uma biometria registada no aparelho? */
export async function isBiometricAvailable(): Promise<boolean> {
  const m = await loadModule();
  if (!m) return false;
  try {
    const hasHardware = await m.hasHardwareAsync();
    if (!hasHardware) return false;
    return await m.isEnrolledAsync();
  } catch {
    return false;
  }
}

/** Rótulo do tipo de biometria disponível (para textos da UI). */
export async function biometricLabel(): Promise<string> {
  const m = await loadModule();
  if (!m) return 'Biometria';
  try {
    const types = await m.supportedAuthenticationTypesAsync();
    if (types.includes(m.AuthenticationType.FACIAL_RECOGNITION)) return 'Reconhecimento facial';
    if (types.includes(m.AuthenticationType.FINGERPRINT)) return 'Impressão digital';
  } catch {
    /* ignore */
  }
  return 'Biometria';
}

/** Pede autenticação biométrica. Devolve true se passou. */
export async function authenticateBiometric(reason = 'Confirma a tua identidade'): Promise<boolean> {
  if (Platform.OS === 'web') return true;
  const m = await loadModule();
  if (!m) return false;
  try {
    const result = await m.authenticateAsync({
      promptMessage: reason,
      cancelLabel: 'Cancelar',
      disableDeviceFallback: false,
    });
    return result.success;
  } catch {
    return false;
  }
}
