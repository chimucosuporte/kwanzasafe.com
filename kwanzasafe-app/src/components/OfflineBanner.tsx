import { Ionicons } from '@expo/vector-icons';
import { StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

import { useUiStore } from '@/stores/ui';
import { colors, fonts, fontSize, spacing } from '@/theme';

/**
 * Faixa global de "sem ligação" — aparece no topo quando os pedidos HTTP
 * falham por falta de rede (sinal vindo de `api/client` via `useUiStore`).
 */
export function OfflineBanner() {
  const connected = useUiStore((s) => s.connected);
  const insets = useSafeAreaInsets();

  if (connected) return null;

  return (
    <View style={[styles.banner, { paddingTop: insets.top + spacing.xs }]}>
      <Ionicons name="cloud-offline-outline" size={16} color={colors.white} />
      <Text style={styles.text}>Sem ligação ao servidor. A tentar reconectar…</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  banner: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    zIndex: 1000,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
    paddingBottom: spacing.sm,
    paddingHorizontal: spacing.md,
    backgroundColor: colors.danger,
  },
  text: { fontFamily: fonts.bodyMedium, fontSize: fontSize.xs, color: colors.white },
});
