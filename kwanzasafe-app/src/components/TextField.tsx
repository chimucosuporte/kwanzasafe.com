import { Ionicons } from '@expo/vector-icons';
import { useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, type TextInputProps, View } from 'react-native';

import { colors, fonts, fontSize, radius, spacing } from '@/theme';

interface TextFieldProps extends TextInputProps {
  label: string;
  error?: string | null;
  /** Sugestão curta por baixo do campo (quando não há erro). */
  hint?: string;
}

/** Campo de texto rotulado com estado de foco, erro e (em passwords) olho. */
export function TextField({ label, error, hint, style, secureTextEntry, ...rest }: TextFieldProps) {
  const [focused, setFocused] = useState(false);
  const [hidden, setHidden] = useState(true);
  const isPassword = !!secureTextEntry;

  return (
    <View style={styles.wrapper}>
      <Text style={styles.label}>{label}</Text>
      <View style={styles.inputRow}>
        <TextInput
          placeholderTextColor={colors.textMuted}
          secureTextEntry={isPassword && hidden}
          style={[
            styles.input,
            isPassword && styles.inputWithIcon,
            focused && styles.inputFocused,
            !!error && styles.inputError,
            style,
          ]}
          onFocus={() => setFocused(true)}
          onBlur={() => setFocused(false)}
          {...rest}
        />
        {isPassword && (
          <Pressable
            onPress={() => setHidden((h) => !h)}
            hitSlop={10}
            style={styles.eye}
            accessibilityLabel={hidden ? 'Mostrar palavra-passe' : 'Ocultar palavra-passe'}
          >
            <Ionicons name={hidden ? 'eye-outline' : 'eye-off-outline'} size={20} color={colors.textMuted} />
          </Pressable>
        )}
      </View>
      {!!error ? (
        <Text style={styles.error}>{error}</Text>
      ) : (
        !!hint && <Text style={styles.hint}>{hint}</Text>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: { gap: spacing.xs },
  label: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text },
  inputRow: { justifyContent: 'center' },
  input: {
    height: 54,
    borderRadius: radius.md,
    borderWidth: 1.5,
    borderColor: colors.border,
    backgroundColor: colors.surfaceAlt,
    paddingHorizontal: spacing.md,
    fontFamily: fonts.body,
    fontSize: fontSize.md,
    color: colors.text,
  },
  inputWithIcon: { paddingRight: 48 },
  inputFocused: { borderColor: colors.primary, backgroundColor: colors.card },
  inputError: { borderColor: colors.danger },
  eye: { position: 'absolute', right: 0, height: 54, width: 48, alignItems: 'center', justifyContent: 'center' },
  error: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.danger },
  hint: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted },
});
