import { Stack, useRouter } from 'expo-router';
import { useEffect } from 'react';

import { registerPushToken } from '@/api/push';
import { registerForPushNotifications, setupNotifications } from '@/lib/push';

/**
 * Stack da área autenticada. As tabs ((tabs)) são o ecrã base; os ecrãs de
 * detalhe e de fluxo (conta, KYC, chat, definições, etc.) empilham por cima,
 * para que "voltar" siga a hierarquia de navegação e o estado das tabs persista.
 */
export default function AppLayout() {
  const router = useRouter();

  // Regista o token de push + configura handler/toque (silencioso sem módulo nativo).
  useEffect(() => {
    let cancelled = false;
    let cleanup: (() => void) | undefined;

    (async () => {
      const token = await registerForPushNotifications();
      if (token && !cancelled) {
        try {
          await registerPushToken(token);
        } catch {
          /* ignora falhas de rede no registo do token */
        }
      }

      cleanup = await setupNotifications((ref) => {
        router.push({ pathname: '/transactions/[ref]', params: { ref } });
      });
    })();

    return () => {
      cancelled = true;
      cleanup?.();
    };
  }, [router]);

  return (
    <Stack screenOptions={{ headerShown: false, animation: 'slide_from_right' }}>
      <Stack.Screen name="(tabs)" />
    </Stack>
  );
}
