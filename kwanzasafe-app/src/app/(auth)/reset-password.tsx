import { useMutation } from '@tanstack/react-query';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useState } from 'react';
import { Alert, StyleSheet, Text } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { apiErrorMessage } from '@/api/client';
import { resetPassword } from '@/api/auth';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { colors, fonts, fontSize, spacing } from '@/theme';

export default function ResetPasswordScreen() {
  const router = useRouter();
  const params = useLocalSearchParams<{ email?: string }>();
  const [email, setEmail] = useState(params.email ?? '');
  const [code, setCode] = useState('');
  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [error, setError] = useState<string | null>(null);

  const mutation = useMutation({
    mutationFn: () =>
      resetPassword({
        email: email.trim(),
        code: code.trim(),
        password,
        password_confirmation: confirm,
      }),
    onSuccess: ({ message }) => {
      Alert.alert('Palavra-passe redefinida', message, [
        { text: 'Entrar', onPress: () => router.replace('/login') },
      ]);
    },
    onError: (err) => setError(apiErrorMessage(err)),
  });

  const submit = () => {
    setError(null);
    if (!email.trim() || !code.trim() || !password) {
      setError('Preenche todos os campos.');
      return;
    }
    if (password !== confirm) {
      setError('As palavras-passe não coincidem.');
      return;
    }
    mutation.mutate();
  };

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Nova palavra-passe" onBack={() => router.back()} />
      <KeyboardAwareScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false} bottomOffset={24}>
          <Text style={styles.intro}>Introduz o código de 6 dígitos enviado para o teu email e define a nova palavra-passe.</Text>

          <TextField
            label="Email"
            value={email}
            onChangeText={setEmail}
            autoCapitalize="none"
            keyboardType="email-address"
            inputMode="email"
            placeholder="o-teu@email.com"
          />
          <TextField
            label="Código (6 dígitos)"
            value={code}
            onChangeText={(t) => setCode(t.replace(/[^0-9]/g, '').slice(0, 6))}
            keyboardType="number-pad"
            inputMode="numeric"
            placeholder="000000"
          />
          <TextField
            label="Nova palavra-passe"
            value={password}
            onChangeText={setPassword}
            secureTextEntry
            autoCapitalize="none"
            placeholder="Mínimo 8 caracteres"
          />
          <TextField
            label="Confirmar palavra-passe"
            value={confirm}
            onChangeText={setConfirm}
            secureTextEntry
            autoCapitalize="none"
            placeholder="Repete a palavra-passe"
            returnKeyType="go"
            onSubmitEditing={submit}
          />

          {!!error && <Text style={styles.error}>{error}</Text>}

          <Button label="Redefinir palavra-passe" onPress={submit} loading={mutation.isPending} />
      </KeyboardAwareScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  scroll: { gap: spacing.md, paddingVertical: spacing.lg },
  intro: { fontFamily: fonts.body, fontSize: fontSize.md, color: colors.textMuted, lineHeight: 22, marginBottom: spacing.sm },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger },
});
