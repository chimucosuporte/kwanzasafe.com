/**
 * Notificações push (Expo).
 *
 * IMPORTANTE: `expo-notifications`/`expo-device` são módulos NATIVOS. Para a app
 * nunca crashar numa build sem eles (ou no Expo Go), importamo-los de forma
 * LAZY (import dinâmico dentro de try/catch). Sem módulo → degrada para null.
 */
import Constants from 'expo-constants';
import { Platform } from 'react-native';

/**
 * Adquire o Expo push token, devolvendo SEMPRE a razão (para diagnóstico).
 * Não engole o erro real (FCM em falta, permissão, etc.).
 */
export async function acquirePushToken(): Promise<{ token: string | null; reason: string }> {
  try {
    const Notifications = await import('expo-notifications');
    const Device = await import('expo-device');

    if (!Device.isDevice) {
      return { token: null, reason: 'Precisas de um telemóvel físico (o emulador não emite token de push).' };
    }

    if (Platform.OS === 'android') {
      await Notifications.setNotificationChannelAsync('default', {
        name: 'Geral',
        importance: Notifications.AndroidImportance.DEFAULT,
        lightColor: '#00b454',
      });
    }

    const current = await Notifications.getPermissionsAsync();
    let status = current.status;
    if (status !== 'granted') {
      const requested = await Notifications.requestPermissionsAsync();
      status = requested.status;
    }
    if (status !== 'granted') {
      return { token: null, reason: 'Permissão de notificações não concedida.' };
    }

    const projectId =
      Constants.expoConfig?.extra?.eas?.projectId ?? (Constants as { easConfig?: { projectId?: string } }).easConfig?.projectId;
    if (!projectId) {
      return { token: null, reason: 'projectId EAS em falta na configuração da app.' };
    }

    try {
      const token = await Notifications.getExpoPushTokenAsync({ projectId });
      return { token: token.data, reason: 'ok' };
    } catch (e) {
      const msg = e instanceof Error ? e.message : String(e);
      return { token: null, reason: `Falha ao obter token: ${msg}` };
    }
  } catch (e) {
    const msg = e instanceof Error ? e.message : String(e);
    return { token: null, reason: `Módulo de notificações indisponível: ${msg}` };
  }
}

/**
 * Pede permissão e devolve o Expo push token (ou null se indisponível).
 */
export async function registerForPushNotifications(): Promise<string | null> {
  return (await acquirePushToken()).token;
}

/**
 * Configura o comportamento das notificações (mostrar com a app aberta) e
 * regista o toque numa notificação → callback com a referência da transação.
 * Devolve uma função de limpeza. Tudo lazy/protegido (não crasha sem módulo).
 */
export async function setupNotifications(onTapReference: (ref: string) => void): Promise<() => void> {
  try {
    const Notifications = await import('expo-notifications');

    Notifications.setNotificationHandler({
      handleNotification: async () => ({
        shouldShowBanner: true,
        shouldShowList: true,
        shouldPlaySound: true,
        shouldSetBadge: false,
      }),
    });

    const sub = Notifications.addNotificationResponseReceivedListener((response) => {
      const ref = response.notification.request.content.data?.reference_id;
      if (typeof ref === 'string' && ref) onTapReference(ref);
    });

    return () => sub.remove();
  } catch {
    return () => {};
  }
}
