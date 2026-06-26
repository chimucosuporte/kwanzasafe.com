import { StyleSheet, Text, View } from 'react-native';

import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { TransactionStatus } from '@/types/api';

/** Tonalidade (fundo/texto) por estado da transação. */
const TONE: Record<TransactionStatus, { bg: string; fg: string }> = {
  pending:          { bg: colors.warningTint, fg: colors.warning },
  negotiating:      { bg: colors.warningTint, fg: colors.warning },
  awaiting_payment: { bg: colors.infoTint, fg: colors.info },
  payment_received: { bg: colors.infoTint, fg: colors.info },
  processing:       { bg: colors.infoTint, fg: colors.info },
  aoa_sent:         { bg: colors.primaryTint, fg: colors.primaryBright },
  completed:        { bg: colors.primaryTint, fg: colors.primaryBright },
  cancelled:        { bg: colors.dangerTint, fg: colors.danger },
  expired:          { bg: colors.dangerTint, fg: colors.danger },
};

/** Etiqueta colorida com o estado (PT) de uma transação. */
export function StatusBadge({ status, label }: { status: TransactionStatus; label: string }) {
  const tone = TONE[status] ?? { bg: colors.surface, fg: colors.textMuted };
  return (
    <View style={[styles.badge, { backgroundColor: tone.bg }]}>
      <Text style={[styles.text, { color: tone.fg }]}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    alignSelf: 'flex-start',
    paddingHorizontal: spacing.sm,
    paddingVertical: 4,
    borderRadius: radius.pill,
  },
  text: { fontFamily: fonts.bodyBold, fontSize: fontSize.xs },
});
