import { Pressable, StyleSheet, Text, View } from 'react-native';

import { StatusBadge } from '@/components/StatusBadge';
import { formatAmount, formatDate } from '@/lib/format';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { Transaction } from '@/types/api';

/** Cartão de uma transação na lista / dashboard. */
export function TransactionRow({ tx, onPress }: { tx: Transaction; onPress: () => void }) {
  return (
    <Pressable
      onPress={onPress}
      style={({ pressed }) => [styles.row, pressed && styles.pressed]}
    >
      <View style={styles.top}>
        <Text style={styles.ref}>{tx.reference_id}</Text>
        <StatusBadge status={tx.status} label={tx.status_label} />
      </View>
      <Text style={styles.amount}>
        {formatAmount(tx.amount_sent)} {tx.currency_from}
        <Text style={styles.arrow}>  →  </Text>
        {formatAmount(tx.amount_received)} {tx.currency_to}
      </Text>
      <Text style={styles.date}>{formatDate(tx.created_at)}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  row: {
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.md,
    gap: spacing.xs,
  },
  pressed: { opacity: 0.7 },
  top: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  ref: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  amount: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text },
  arrow: { color: colors.textMuted },
  date: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted },
});
