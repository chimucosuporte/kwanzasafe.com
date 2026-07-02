import { ActivityIndicator, Pressable, StyleSheet, Text, ViewStyle } from 'react-native';

import { colors, fonts, fontSize, radius, spacing } from '@/theme';

type Variant = 'primary' | 'ghost' | 'danger' | 'dark';

export function Button({ label, onPress, loading, disabled, variant = 'primary', style }: {
  label: string; onPress?: () => void; loading?: boolean; disabled?: boolean; variant?: Variant; style?: ViewStyle;
}) {
  const isDisabled = disabled || loading;
  return (
    <Pressable
      onPress={onPress}
      disabled={isDisabled}
      style={[styles.base, styles[variant], isDisabled && styles.disabled, style]}
    >
      {loading ? (
        <ActivityIndicator color={variant === 'ghost' ? colors.text : colors.white} />
      ) : (
        <Text style={[styles.label, variant === 'ghost' && styles.labelGhost]}>{label}</Text>
      )}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  base: { height: 50, borderRadius: radius.md, alignItems: 'center', justifyContent: 'center', paddingHorizontal: spacing.md },
  primary: { backgroundColor: colors.primary },
  dark: { backgroundColor: colors.surfaceAlt, borderWidth: 1, borderColor: colors.border },
  danger: { backgroundColor: colors.danger },
  ghost: { backgroundColor: 'transparent', borderWidth: 1, borderColor: colors.border },
  disabled: { opacity: 0.5 },
  label: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.white },
  labelGhost: { color: colors.text },
});
