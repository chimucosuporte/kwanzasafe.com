import { DMSans_400Regular, DMSans_500Medium, DMSans_700Bold } from '@expo-google-fonts/dm-sans';
import { Syne_700Bold, Syne_800ExtraBold, useFonts } from '@expo-google-fonts/syne';
import { QueryClientProvider } from '@tanstack/react-query';
import { Stack, useRouter, useSegments } from 'expo-router';
import * as SplashScreen from 'expo-splash-screen';
import { StatusBar } from 'expo-status-bar';
import { useEffect } from 'react';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { KeyboardProvider } from 'react-native-keyboard-controller';
import { SafeAreaProvider } from 'react-native-safe-area-context';

import { queryClient } from '@/lib/queryClient';
import { useAuthStore } from '@/stores/auth';

void SplashScreen.preventAutoHideAsync();

function useAuthGate(ready: boolean) {
  const status = useAuthStore((s) => s.status);
  const segments = useSegments();
  const router = useRouter();

  useEffect(() => {
    if (!ready || status === 'loading') return;
    const inAuthGroup = segments[0] === '(auth)';
    if (status === 'guest' && !inAuthGroup) {
      router.replace('/login');
    } else if (status === 'authed' && inAuthGroup) {
      router.replace('/');
    }
  }, [ready, status, segments, router]);
}

export default function RootLayout() {
  const [fontsLoaded] = useFonts({
    Syne_700Bold, Syne_800ExtraBold, DMSans_400Regular, DMSans_500Medium, DMSans_700Bold,
  });

  const status = useAuthStore((s) => s.status);
  const bootstrap = useAuthStore((s) => s.bootstrap);

  useEffect(() => { void bootstrap(); }, [bootstrap]);

  const ready = fontsLoaded && status !== 'loading';

  useEffect(() => { if (ready) void SplashScreen.hideAsync(); }, [ready]);

  useAuthGate(ready);

  if (!ready) return null;

  return (
    <GestureHandlerRootView style={{ flex: 1 }}>
      <KeyboardProvider>
        <QueryClientProvider client={queryClient}>
          <SafeAreaProvider>
            <StatusBar style="light" />
            <Stack screenOptions={{ headerShown: false, contentStyle: { backgroundColor: '#0a0d0b' } }}>
              <Stack.Screen name="(auth)" />
              <Stack.Screen name="(app)" />
            </Stack>
          </SafeAreaProvider>
        </QueryClientProvider>
      </KeyboardProvider>
    </GestureHandlerRootView>
  );
}
