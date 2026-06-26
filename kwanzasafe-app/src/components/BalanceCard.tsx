import { Ionicons } from '@expo/vector-icons';
import { useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';

import { formatAmount } from '@/lib/format';
import { colors, elevation, fonts, fontSize, radius, spacing } from '@/theme';
import type { User } from '@/types/api';

/**
 * Cartão "herói" do dashboard: saldo da conta em AOA com alternância de
 * privacidade (olho) e estado de verificação da conta.
 */
export function BalanceCard({ user }: { user: User | null }) {
  const [hidden, setHidden] = useState(false);
  const balance = formatAmount(user?.balance ?? '0');
  const verified = !!user?.is_fully_verified;

  return (
    <View style={styles.card}>
      <View style={styles.glow} />

      <View style={styles.topRow}>
        <Text style={styles.label}>Saldo da conta</Text>
        <Pressable onPress={() => setHidden((h) => !h)} hitSlop={10} accessibilityLabel="Mostrar ou esconder saldo">
          <Ionicons name={hidden ? 'eye-off-outline' : 'eye-outline'} size={20} color="rgba(255,255,255,0.85)" />
        </Pressable>
      </View>

      <View style={styles.amountRow}>
        <Text style={styles.amount}>{hidden ? '••••••' : balance}</Text>
        <Text style={styles.currency}>AOA</Text>
      </View>

      <View style={styles.statusRow}>
        <Ionicons
          name={verified ? 'shield-checkmark' : 'shield-half-outline'}
          size={14}
          color={colors.white}
        />
        <Text style={styles.status}>
          {verified ? 'Conta verificada' : 'Verificação pendente'}
        </Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.primaryDark,
    borderRadius: radius.xl,
    padding: spacing.lg,
    gap: spacing.md,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: colors.primaryTintBorder,
    ...elevation.glowPrimary,
  },
  glow: {
    position: 'absolute',
    top: -60,
    right: -40,
    width: 160,
    height: 160,
    borderRadius: radius.pill,
    backgroundColor: colors.primaryBright,
    opacity: 0.45,
  },
  topRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  label: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: 'rgba(255,255,255,0.85)' },
  amountRow: { flexDirection: 'row', alignItems: 'flex-end', gap: spacing.sm },
  amount: { fontFamily: fonts.display, fontSize: fontSize.display, color: colors.white, letterSpacing: -0.5 },
  currency: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: 'rgba(255,255,255,0.85)', marginBottom: 6 },
  statusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    alignSelf: 'flex-start',
    backgroundColor: 'rgba(255,255,255,0.15)',
    borderRadius: radius.pill,
    paddingVertical: 4,
    paddingHorizontal: spacing.sm,
  },
  status: { fontFamily: fonts.bodyMedium, fontSize: fontSize.xs, color: colors.white },
});
