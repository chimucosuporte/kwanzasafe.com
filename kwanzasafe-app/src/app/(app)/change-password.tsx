import { useMutation } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { apiErrorMessage } from '@/api/client';
import { updatePassword } from '@/api/profile';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

/** Alteração de palavra-passe (exige a atual; revoga as outras sessões). */
export default function ChangePasswordScreen() {
  const router = useRouter();
  const [currentPw, setCurrentPw] = useState('');
  const [newPw, setNewPw] = useState('');
  const [confirmPw, setConfirmPw] = useState('');
  const [msg, setMsg] = useState<string | null>(null);
  const [err, setErr] = useState<string | null>(null);

  const passwordM = useMutation({
    mutationFn: () => updatePassword({ current_password: currentPw, password: newPw, password_confirmation: confirmPw }),
    onSuccess: ({ message }) => {
      setErr(null);
      setMsg(message);
      setCurrentPw('');
      setNewPw('');
      setConfirmPw('');
    },
    onError: (e) => {
      setMsg(null);
      setErr(apiErrorMessage(e));
    },
  });

  const save = () => {
    setErr(null);
    setMsg(null);
    if (!currentPw || !newPw) {
      setErr('Preenche os campos da palavra-passe.');
      return;
    }
    if (newPw !== confirmPw) {
      setErr('A confirmação não coincide.');
      return;
    }
    passwordM.mutate();
  };

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Alterar palavra-passe" onBack={() => router.back()} />
      <KeyboardAwareScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false} bottomOffset={24}>
          <Text style={styles.intro}>
            Por segurança, ao alterares a palavra-passe terminamos as restantes sessões.
          </Text>
          <View style={styles.card}>
            <TextField label="Palavra-passe atual" value={currentPw} onChangeText={setCurrentPw} secureTextEntry autoCapitalize="none" />
            <TextField label="Nova palavra-passe" value={newPw} onChangeText={setNewPw} secureTextEntry autoCapitalize="none" placeholder="Mínimo 8 caracteres" />
            <TextField label="Confirmar nova palavra-passe" value={confirmPw} onChangeText={setConfirmPw} secureTextEntry autoCapitalize="none" />
            {!!msg && <Text style={styles.ok}>{msg}</Text>}
            {!!err && <Text style={styles.error}>{err}</Text>}
            <Button label="Alterar palavra-passe" onPress={save} loading={passwordM.isPending} />
          </View>
      </KeyboardAwareScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  scroll: { gap: spacing.md, paddingVertical: spacing.lg },
  intro: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, lineHeight: 20 },
  card: {
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.lg,
    gap: spacing.md,
  },
  ok: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.primaryBright },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger },
});
