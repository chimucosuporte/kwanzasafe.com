import { useMutation } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { registerAccount } from '@/api/auth';
import { apiErrorMessage, apiFieldErrors } from '@/api/client';
import { Button } from '@/components/Button';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { Wordmark } from '@/components/Wordmark';
import { useAuthStore } from '@/stores/auth';
import { toast } from '@/stores/toast';
import { colors, fonts, fontSize, spacing } from '@/theme';

export default function RegisterScreen() {
  const setSession = useAuthStore((s) => s.setSession);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [fe, setFe] = useState<Record<string, string>>({});

  const clearFe = (field: string) =>
    setFe((prev) => {
      if (!prev[field]) return prev;
      toast.hide();
      return { ...prev, [field]: '' };
    });

  const mutation = useMutation({
    mutationFn: () =>
      registerAccount({ name: name.trim(), email: email.trim(), password, password_confirmation: confirm }),
    onSuccess: async ({ token, user }) => {
      await setSession(token, user);
    },
    onError: (err) => {
      const f = apiFieldErrors(err);
      if (Object.keys(f).length) {
        setFe(f);
        toast.error('Corrige os campos destacados abaixo.');
      } else {
        toast.error(apiErrorMessage(err));
      }
    },
  });

  const submit = () => {
    const errs: Record<string, string> = {};
    if (!name.trim()) errs.name = 'Indica o teu nome completo.';
    if (!email.trim()) errs.email = 'Indica o teu email.';
    if (!password) errs.password = 'Define uma palavra-passe.';
    else if (password.length < 8) errs.password = 'A palavra-passe precisa de pelo menos 8 caracteres.';
    if (password && confirm !== password) errs.confirm = 'As palavras-passe não coincidem.';
    setFe(errs);
    if (Object.keys(errs).length) {
      toast.error('Há campos por corrigir. Verifica os destacados abaixo.');
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
          <Text style={styles.subtitle}>Cria a tua conta para começar a converter para Kwanzas.</Text>
        </View>

        <View style={styles.form}>
          <Text style={styles.title}>Criar conta</Text>

          <TextField label="Nome completo" value={name} onChangeText={(t) => { setName(t); clearFe('name'); }} placeholder="O teu nome" returnKeyType="next" error={fe.name} />
          <TextField
            label="Email"
            value={email}
            onChangeText={(t) => { setEmail(t); clearFe('email'); }}
            autoCapitalize="none"
            keyboardType="email-address"
            inputMode="email"
            placeholder="o-teu@email.com"
            returnKeyType="next"
            error={fe.email}
          />
          <TextField
            label="Palavra-passe"
            value={password}
            onChangeText={(t) => { setPassword(t); clearFe('password'); }}
            secureTextEntry
            autoCapitalize="none"
            placeholder="Mínimo 8 caracteres"
            returnKeyType="next"
            error={fe.password}
            hint="Usa pelo menos 8 caracteres, com letras e números."
          />
          <TextField
            label="Confirmar palavra-passe"
            value={confirm}
            onChangeText={(t) => { setConfirm(t); clearFe('confirm'); }}
            secureTextEntry
            autoCapitalize="none"
            placeholder="Repete a palavra-passe"
            returnKeyType="go"
            onSubmitEditing={submit}
            error={fe.confirm}
          />

          <Button label="Criar conta" onPress={submit} loading={mutation.isPending} style={styles.submit} />
        </View>

        <Link href="/login" style={styles.footer}>
          <Text style={styles.footerText}>Já tens conta? </Text>
          <Text style={styles.footerLink}>Entrar</Text>
        </Link>
      </KeyboardAwareScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  scroll: { flexGrow: 1, justifyContent: 'center', gap: spacing.xl, paddingVertical: spacing.xl },
  header: { gap: spacing.sm },
  subtitle: { fontFamily: fonts.body, fontSize: fontSize.md, color: colors.textMuted, lineHeight: 22 },
  form: { gap: spacing.md },
  title: { fontFamily: fonts.displaySemi, fontSize: fontSize.xl, color: colors.text },
  submit: { marginTop: spacing.sm },
  footer: { textAlign: 'center' },
  footerText: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  footerLink: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.primary },
});
