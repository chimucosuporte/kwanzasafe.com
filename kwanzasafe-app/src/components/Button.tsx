import {
  ActivityIndicator,
  Pressable,
  StyleSheet,
  Text,
  type PressableProps,
} from 'react-native';

import { colors, fonts, fontSize, radius, spacing } from '@/theme';

interface ButtonProps extends Omit<PressableProps, 'children'> {
  label: string;
  loading?: boolean;
  variant?: 'primary' | 'ghost';
}

/** Botão corporativo KwanzaSafe. */
export function Button({ label, loading, variant = 'primary', disabled, style, ...rest }: ButtonProps) {
  const isPrimary = variant === 'primary';
  const isDisabled = disabled || loading;

  return (
    <Pressable
      accessibilityRole="button"
      disabled={isDisabled}
      style={(state) => [
        styles.base,
        isPrimary ? styles.primary : styles.ghost,
        isDisabled && styles.disabled,
        state.pressed && !isDisabled && styles.pressed,
        typeof style === 'function' ? style(state) : style,
      ]}
      {...rest}
    >
      {loading ? (
        <ActivityIndicator color={isPrimary ? colors.white : colors.primary} />
      ) : (
        <Text style={[styles.label, isPrimary ? styles.labelPrimary : styles.labelGhost]}>{label}</Text>
      )}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  base: {
    height: 54,
    borderRadius: radius.md,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.lg,
  },
  primary: { backgroundColor: colors.primary },
  ghost: { backgroundColor: 'transparent', borderWidth: 1.5, borderColor: colors.border },
  pressed: { opacity: 0.85 },
  disabled: { opacity: 0.5 },
  label: { fontFamily: fonts.bodyBold, fontSize: fontSize.md },
  labelPrimary: { color: colors.white },
  labelGhost: { color: colors.text },
});
