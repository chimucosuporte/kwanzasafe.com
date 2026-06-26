import { Ionicons } from '@expo/vector-icons';
import { Pressable, StyleSheet, Text, View } from 'react-native';

import { colors, fonts, fontSize, radius, spacing } from '@/theme';

type IconName = keyof typeof Ionicons.glyphMap;

/** Linha de menu (hub): ícone + título + descrição opcional + chevron/toggle. */
export function MenuRow({
  icon,
  label,
  description,
  value,
  onPress,
  right,
  danger,
}: {
  icon: IconName;
  label: string;
  description?: string;
  value?: string;
  onPress?: () => void;
  right?: React.ReactNode;
  danger?: boolean;
}) {
  const tint = danger ? colors.danger : colors.primary;
  return (
    <Pressable
      onPress={onPress}
      disabled={!onPress}
      style={({ pressed }) => [styles.row, pressed && onPress && styles.pressed]}
      accessibilityRole={onPress ? 'button' : undefined}
    >
      <View style={[styles.iconWrap, danger && styles.iconWrapDanger]}>
        <Ionicons name={icon} size={20} color={tint} />
      </View>
      <View style={styles.texts}>
        <Text style={[styles.label, danger && styles.labelDanger]}>{label}</Text>
        {!!description && <Text style={styles.description}>{description}</Text>}
      </View>
      {right ?? (
        <View style={styles.rightWrap}>
          {!!value && <Text style={styles.value}>{value}</Text>}
          {!!onPress && <Ionicons name="chevron-forward" size={18} color={colors.textMuted} />}
        </View>
      )}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.md,
  },
  pressed: { opacity: 0.7 },
  iconWrap: {
    width: 40,
    height: 40,
    borderRadius: radius.md,
    backgroundColor: colors.primaryTint,
    alignItems: 'center',
    justifyContent: 'center',
  },
  iconWrapDanger: { backgroundColor: colors.dangerTint },
  texts: { flex: 1, gap: 2 },
  label: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  labelDanger: { color: colors.danger },
  description: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, lineHeight: 16 },
  rightWrap: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
  value: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.textMuted },
});
