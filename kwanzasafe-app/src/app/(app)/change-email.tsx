import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { fetchMe } from '@/api/auth';
import { apiErrorMessage, apiFieldErrors } from '@/api/client';
import { confirmEmailChange, requestEmailChange } from '@/api/profile';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { useAuthStore } from '@/stores/auth';
import { toast } from '@/stores/toast';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

/** Alteração de email em 2 passos com confirmação dupla (email atual + novo). */
export default function ChangeEmailScreen() {
  const router = useRouter();
  const setUser = useAuthStore((s) => s.setUser);
  const { data: me } = useQuery({ queryKey: ['me'], queryFn: async () => (await fetchMe()).user });

  const [step, setStep] = useState<1 | 2>(1);
  const [email, setEmail] = useState('');
  const [codeCurrent, setCodeCurrent] = useState('');
  const [codeNew, setCodeNew] = useState('');
  const [fe, setFe] = useState<Record<string, string>>({});

  const clearFe = (k: string) => setFe((p) => (p[k] ? { ...p, [k]: '' } : p));

  const requestM = useMutation({
    mutationFn: () => requestEmailChange(email.trim()),
    onSuccess: ({ message }) => {
      setFe({});
      setStep(2);
      toast.success(message);
    },
    onError: (e) => {
      const f = apiFieldErrors(e);
      setFe(f);
      toast.error(Object.keys(f).length ? 'Corrige o email indicado.' : apiErrorMessage(e));
    },
  });

  const confirmM = useMutation({
    mutationFn: () => confirmEmailChange({ email: email.trim(), code_current: codeCurrent.trim(), code_new: codeNew.trim() }),
    onSuccess: ({ message, user }) => {
      setUser(user);
      toast.success(message);
      router.back();
    },
    onError: (e) => {
      const f = apiFieldErrors(e);
      setFe(f);
      toast.error(Object.keys(f).length ? 'Verifica os códigos.' : apiErrorMessage(e));
    },
  });

  const sendCodes = () => {
    if (!email.trim()) {
      setFe({ email: 'Indica o novo email.' });
      toast.error('Indica o novo email.');
      return;
    }
    requestM.mutate();
  };

  const confirm = () => {
    const errs: Record<string, string> = {};
    if (codeCurrent.replace(/\D/g, '').length !== 6) errs.code_current = 'Código de 6 dígitos.';
    if (codeNew.replace(/\D/g, '').length !== 6) errs.code_new = 'Código de 6 dígitos.';
    setFe(errs);
    if (Object.keys(errs).length) {
      toast.error('Introduz os dois códigos de 6 dígitos.');
      return;
    }
    confirmM.mutate();
  };

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Alterar email" onBack={() => router.back()} />
      <KeyboardAwareScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false} bottomOffset={24}>
        <View style={styles.infoCard}>
          <Ionicons name="shield-checkmark-outline" size={20} color={colors.primaryBright} />
          <Text style={styles.infoText}>
            Por segurança, enviamos um código ao teu email <Text style={styles.bold}>atual</Text> e ao{' '}
            <Text style={styles.bold}>novo</Text>. Precisas dos dois para confirmar.
          </Text>
        </View>

        {step === 1 ? (
          <View style={styles.card}>
            <Text style={styles.muted}>Email atual: {me?.email}</Text>
            <TextField
              label="Novo email"
              value={email}
              onChangeText={(t) => { setEmail(t); clearFe('email'); }}
              autoCapitalize="none"
              keyboardType="email-address"
              inputMode="email"
              placeholder="novo@email.com"
              error={fe.email}
            />
            <Button label="Enviar códigos" onPress={sendCodes} loading={requestM.isPending} />
          </View>
        ) : (
          <View style={styles.card}>
            <Text style={styles.muted}>Enviámos um código para {me?.email} e para {email}.</Text>
            <TextField
              label="Código do email ATUAL"
              value={codeCurrent}
              onChangeText={(t) => { setCodeCurrent(t.replace(/\D/g, '').slice(0, 6)); clearFe('code_current'); }}
              keyboardType="number-pad"
              placeholder="000000"
              error={fe.code_current}
            />
            <TextField
              label="Código do email NOVO"
              value={codeNew}
              onChangeText={(t) => { setCodeNew(t.replace(/\D/g, '').slice(0, 6)); clearFe('code_new'); }}
              keyboardType="number-pad"
              placeholder="000000"
              error={fe.code_new}
            />
            <Button label="Confirmar alteração" onPress={confirm} loading={confirmM.isPending} />
            <Button label="Reenviar códigos" variant="ghost" onPress={sendCodes} loading={requestM.isPending} />
          </View>
        )}
      </KeyboardAwareScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  scroll: { gap: spacing.md, paddingVertical: spacing.lg },
  infoCard: {
    flexDirection: 'row',
    gap: spacing.sm,
    backgroundColor: colors.primaryTint,
    borderWidth: 1,
    borderColor: colors.primaryTintBorder,
    borderRadius: radius.lg,
    padding: spacing.md,
  },
  infoText: { flex: 1, fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.text, lineHeight: 20 },
  bold: { fontFamily: fonts.bodyBold },
  card: {
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.lg,
    gap: spacing.md,
  },
  muted: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, lineHeight: 20 },
});
