import { StyleSheet, Text, TextInput, TextInputProps, View } from 'react-native';

import { colors, fonts, fontSize, radius, spacing } from '@/theme';

export function TextField({ label, error, ...props }: { label?: string; error?: string | null } & TextInputProps) {
  return (
    <View style={styles.wrap}>
      {!!label && <Text style={styles.label}>{label}</Text>}
      <TextInput
        placeholderTextColor={colors.textFaint}
        style={[styles.input, error && styles.inputError]}
        {...props}
      />
      {!!error && <Text style={styles.error}>{error}</Text>}
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { gap: spacing.xs, marginBottom: spacing.md },
  label: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text },
  input: {
    height: 52, borderRadius: radius.md, borderWidth: 1.5, borderColor: colors.border,
    backgroundColor: colors.surfaceAlt, paddingHorizontal: spacing.md,
    fontFamily: fonts.body, fontSize: fontSize.md, color: colors.text,
  },
  inputError: { borderColor: colors.danger },
  error: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.danger },
});
