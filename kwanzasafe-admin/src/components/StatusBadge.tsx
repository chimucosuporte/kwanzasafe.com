import { StyleSheet, Text, View } from 'react-native';

import { colors, fonts, fontSize, radius } from '@/theme';
import type { TxStatus } from '@/types/api';

const MAP: Record<string, { bg: string; fg: string }> = {
  pending:          { bg: colors.warningTint, fg: colors.warning },
  negotiating:      { bg: colors.warningTint, fg: colors.warning },
  awaiting_payment: { bg: colors.infoTint, fg: colors.info },
  payment_received: { bg: colors.primaryTint, fg: colors.primaryBright },
  processing:       { bg: colors.infoTint, fg: colors.info },
  aoa_sent:         { bg: colors.primaryTint, fg: colors.primaryBright },
  completed:        { bg: colors.successTint, fg: colors.success },
  cancelled:        { bg: colors.dangerTint, fg: colors.danger },
  expired:          { bg: colors.surface, fg: colors.textMuted },
};

export function StatusBadge({ status, label }: { status: TxStatus; label: string }) {
  const c = MAP[status] ?? { bg: colors.surface, fg: colors.textMuted };
  return (
    <View style={[styles.badge, { backgroundColor: c.bg }]}>
      <Text style={[styles.text, { color: c.fg }]}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: { alignSelf: 'flex-start', paddingHorizontal: 10, paddingVertical: 4, borderRadius: radius.pill },
  text: { fontFamily: fonts.bodyBold, fontSize: fontSize.xs },
});
