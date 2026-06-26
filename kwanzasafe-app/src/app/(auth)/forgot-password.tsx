import { useMutation } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { apiErrorMessage } from '@/api/client';
import { forgotPassword } from '@/api/auth';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { colors, fonts, fontSize, spacing } from '@/theme';

export default function ForgotPasswordScreen() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [error, setError] = useState<string | null>(null);

  const mutation = useMutation({
    mutationFn: () => forgotPassword(email.trim()),
    onSuccess: () => {
      // Resposta é genérica; segue para o ecrã de redefinição com o email.
      router.push({ pathname: '/reset-password', params: { email: email.trim() } });
    },
    onError: (err) => setError(apiErrorMessage(err)),
  });

  const submit = () => {
    setError(null);
    if (!email.trim()) {
      setError('Introduz o teu email.');
      return;
    }
    mutation.mutate();
  };

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Recuperar palavra-passe" onBack={() => router.back()} />
      <KeyboardAwareScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false} bottomOffset={24}>
          <Text style={styles.intro}>
            Indica o email da tua conta. Vamos enviar-te um código de 6 dígitos para definires uma nova palavra-passe.
          </Text>

          <TextField
            label="Email"
            value={email}
            onChangeText={setEmail}
            autoCapitalize="none"
            keyboardType="email-address"
            inputMode="email"
            placeholder="o-teu@email.com"
            returnKeyType="go"
            onSubmitEditing={submit}
          />

          {!!error && <Text style={styles.error}>{error}</Text>}

          <Button label="Enviar código" onPress={submit} loading={mutation.isPending} />
      </KeyboardAwareScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  scroll: { gap: spacing.lg, paddingVertical: spacing.lg },
  intro: { fontFamily: fonts.body, fontSize: fontSize.md, color: colors.textMuted, lineHeight: 22 },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger },
});
