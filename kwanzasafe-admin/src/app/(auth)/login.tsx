import { Ionicons } from '@expo/vector-icons';
import { useMutation } from '@tanstack/react-query';
import { useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { apiErrorMessage } from '@/api/client';
import { loginStaff } from '@/api/auth';
import { Button } from '@/components/Button';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

export default function LoginScreen() {
  const setSession = useAuthStore((s) => s.setSession);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);

  const m = useMutation({
    mutationFn: () => loginStaff(email.trim(), password),
    onSuccess: async ({ token, user }) => {
      await setSession(token, user);
    },
    onError: (e) => setError(apiErrorMessage(e)),
  });

  return (
    <Screen edges={['top', 'bottom']}>
      <KeyboardAwareScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled" bottomOffset={24}>
        <View style={styles.brand}>
          <View style={styles.logo}>
            <Ionicons name="shield-checkmark" size={34} color={colors.primaryBright} />
          </View>
          <Text style={styles.title}>KwanzaSafe</Text>
          <Text style={styles.badge}>ADMINISTRAÇÃO</Text>
        </View>

        <Text style={styles.heading}>Entrar</Text>
        <Text style={styles.sub}>Acesso reservado a staff (suporte e super-admin).</Text>

        {!!error && <Text style={styles.err}>{error}</Text>}

        <TextField
          label="Email" value={email} onChangeText={(t) => { setEmail(t); setError(null); }}
          keyboardType="email-address" autoCapitalize="none" autoComplete="email" placeholder="staff@kwanzasafe.com"
        />
        <TextField
          label="Palavra-passe" value={password} onChangeText={(t) => { setPassword(t); setError(null); }}
          secureTextEntry placeholder="••••••••"
        />
        <Button label="Entrar" onPress={() => m.mutate()} loading={m.isPending} disabled={!email || !password} />
      </KeyboardAwareScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  scroll: { flexGrow: 1, justifyContent: 'center', paddingVertical: spacing.xl, gap: spacing.sm },
  brand: { alignItems: 'center', marginBottom: spacing.xl },
  logo: {
    width: 72, height: 72, borderRadius: radius.lg, backgroundColor: colors.primaryTint,
    borderWidth: 1, borderColor: colors.primaryTintBorder, alignItems: 'center', justifyContent: 'center', marginBottom: spacing.md,
  },
  title: { fontFamily: fonts.display, fontSize: fontSize.xxl, color: colors.text },
  badge: { fontFamily: fonts.bodyBold, fontSize: 11, letterSpacing: 2, color: colors.primaryBright, marginTop: 2 },
  heading: { fontFamily: fonts.displaySemi, fontSize: fontSize.xl, color: colors.text, marginTop: spacing.md },
  sub: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, marginBottom: spacing.md, lineHeight: 20 },
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginBottom: spacing.sm },
});
