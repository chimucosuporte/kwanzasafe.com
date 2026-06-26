import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useEffect, useState } from 'react';
import { Alert, StyleSheet, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { apiErrorMessage } from '@/api/client';
import { sendEmailOtp, verifyEmailOtp } from '@/api/auth';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, spacing } from '@/theme';

export default function VerifyEmailScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const user = useAuthStore((s) => s.user);
  const setUser = useAuthStore((s) => s.setUser);

  const [code, setCode] = useState('');
  const [info, setInfo] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const sendM = useMutation({
    mutationFn: sendEmailOtp,
    onSuccess: ({ message }) => { setError(null); setInfo(message); },
    onError: (err) => { setInfo(null); setError(apiErrorMessage(err)); },
  });

  const verifyM = useMutation({
    mutationFn: () => verifyEmailOtp(code.trim()),
    onSuccess: ({ user: updated }) => {
      setUser(updated);
      void queryClient.invalidateQueries({ queryKey: ['me'] });
      Alert.alert('Email verificado', 'O teu email foi confirmado com sucesso.', [
        { text: 'Continuar', onPress: () => router.back() },
      ]);
    },
    onError: (err) => setError(apiErrorMessage(err)),
  });

  // Envia o primeiro código automaticamente ao abrir o ecrã.
  useEffect(() => {
    sendM.mutate();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const submit = () => {
    setError(null);
    if (code.trim().length !== 6) {
      setError('Introduz o código de 6 dígitos.');
      return;
    }
    verifyM.mutate();
  };

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Verificar email" onBack={() => router.back()} />
      <KeyboardAwareScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false} bottomOffset={24}>
          <Text style={styles.intro}>
            Enviámos um código de 6 dígitos para{' '}
            <Text style={styles.email}>{user?.email ?? 'o teu email'}</Text>. Introduz o código abaixo para confirmares a tua conta.
          </Text>

          <TextField
            label="Código (6 dígitos)"
            value={code}
            onChangeText={(t) => setCode(t.replace(/[^0-9]/g, '').slice(0, 6))}
            keyboardType="number-pad"
            inputMode="numeric"
            placeholder="000000"
            returnKeyType="go"
            onSubmitEditing={submit}
          />

          {!!info && <Text style={styles.info}>{info}</Text>}
          {!!error && <Text style={styles.error}>{error}</Text>}

          <Button label="Confirmar email" onPress={submit} loading={verifyM.isPending} />

          <View style={styles.resend}>
            <Button
              label={sendM.isPending ? 'A enviar…' : 'Reenviar código'}
              variant="ghost"
              onPress={() => sendM.mutate()}
              loading={sendM.isPending}
            />
          </View>
      </KeyboardAwareScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  scroll: { gap: spacing.md, paddingVertical: spacing.lg },
  intro: { fontFamily: fonts.body, fontSize: fontSize.md, color: colors.textMuted, lineHeight: 22, marginBottom: spacing.sm },
  email: { fontFamily: fonts.bodyBold, color: colors.text },
  info: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.primary },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger },
  resend: { marginTop: spacing.sm },
});
