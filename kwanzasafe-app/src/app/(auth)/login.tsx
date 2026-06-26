import { useMutation } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { apiErrorMessage, isTwoFactorRequired } from '@/api/client';
import { login } from '@/api/auth';
import { Button } from '@/components/Button';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { Wordmark } from '@/components/Wordmark';
import { useAuthStore } from '@/stores/auth';
import { toast } from '@/stores/toast';
import { colors, fonts, fontSize, spacing } from '@/theme';

export default function LoginScreen() {
  const setSession = useAuthStore((s) => s.setSession);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [twoFa, setTwoFa] = useState(false);
  const [code, setCode] = useState('');
  const [error, setError] = useState<string | null>(null);

  const mutation = useMutation({
    mutationFn: () =>
      login({ email: email.trim(), password, two_factor_code: twoFa ? code : undefined }),
    onSuccess: async ({ token, user }) => {
      await setSession(token, user);
      // O AuthGate trata do redireccionamento para a área privada.
    },
    onError: (err) => {
      if (isTwoFactorRequired(err)) {
        setTwoFa(true);
        setError(null);
        toast.info('Esta conta tem 2FA. Introduz o código do Google Authenticator.');
        return;
      }
      const m = apiErrorMessage(err);
      setError(m);
      toast.error(m);
    },
  });

  const submit = () => {
    setError(null);
    if (!email.trim() || !password) {
      const m = 'Introduz o email e a palavra-passe.';
      setError(m);
      toast.error(m);
      return;
    }
    if (twoFa && code.replace(/\D/g, '').length !== 6) {
      const m = 'Introduz o código de 6 dígitos do Google Authenticator.';
      setError(m);
      toast.error(m);
      return;
    }
    mutation.mutate();
  };

  return (
    <Screen>
      <KeyboardAwareScrollView
        contentContainerStyle={styles.scroll}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
        bottomOffset={24}
      >
          <View style={styles.header}>
            <Wordmark />
            <Text style={styles.subtitle}>
              Conversão segura de Euros, Reais e USDC para Kwanzas.
            </Text>
          </View>

          <View style={styles.form}>
            <Text style={styles.title}>Entrar</Text>

            <TextField
              label="Email"
              value={email}
              onChangeText={setEmail}
              autoCapitalize="none"
              autoComplete="email"
              keyboardType="email-address"
              inputMode="email"
              placeholder="o-teu@email.com"
              returnKeyType="next"
            />

            <TextField
              label="Palavra-passe"
              value={password}
              onChangeText={setPassword}
              secureTextEntry
              autoCapitalize="none"
              placeholder="••••••••"
              returnKeyType={twoFa ? 'next' : 'go'}
              onSubmitEditing={twoFa ? undefined : submit}
              editable={!twoFa}
            />

            {twoFa && (
              <>
                <Text style={styles.hint}>
                  Esta conta tem autenticação em 2 passos. Introduz o código do Google Authenticator.
                </Text>
                <TextField
                  label="Código (6 dígitos)"
                  value={code}
                  onChangeText={(t) => setCode(t.replace(/\D/g, '').slice(0, 6))}
                  keyboardType="number-pad"
                  placeholder="000000"
                  returnKeyType="go"
                  onSubmitEditing={submit}
                />
              </>
            )}

            {!!error && <Text style={styles.error}>{error}</Text>}

            <Button
              label={twoFa ? 'Confirmar e entrar' : 'Entrar'}
              onPress={submit}
              loading={mutation.isPending}
              style={styles.submit}
            />

            <Link href="/forgot-password" style={styles.forgot}>
              <Text style={styles.forgotText}>Esqueci-me da palavra-passe</Text>
            </Link>
          </View>

          <Link href="/register" style={styles.footer}>
            <Text style={styles.footerText}>Ainda não tens conta? </Text>
            <Text style={styles.footerLink}>Criar conta</Text>
          </Link>
      </KeyboardAwareScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  scroll: { flexGrow: 1, justifyContent: 'center', gap: spacing.xl, paddingVertical: spacing.xl },
  header: { gap: spacing.sm },
  subtitle: { fontFamily: fonts.body, fontSize: fontSize.md, color: colors.textMuted, lineHeight: 22 },
  form: { gap: spacing.md },
  title: { fontFamily: fonts.displaySemi, fontSize: fontSize.xl, color: colors.text },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger },
  hint: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, lineHeight: 19 },
  submit: { marginTop: spacing.sm },
  forgot: { alignSelf: 'center', marginTop: spacing.xs },
  forgotText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.primary },
  footer: { textAlign: 'center' },
  footerText: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  footerLink: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.primary },
});
