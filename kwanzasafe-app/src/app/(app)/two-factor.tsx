import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { fetchMe } from '@/api/auth';
import { apiErrorMessage } from '@/api/client';
import { confirmTwoFactor, disableTwoFactor, enableTwoFactor } from '@/api/twofactor';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { copyToClipboard } from '@/lib/clipboard';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

/**
 * Autenticação em 2 passos com Google Authenticator — ligada à API (/2fa).
 * enable gera o segredo, confirm valida o 1.º código, disable exige a password.
 */
export default function TwoFactorScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const setUser = useAuthStore((s) => s.setUser);

  const { data: me } = useQuery({ queryKey: ['me'], queryFn: async () => (await fetchMe()).user });
  const enabled = !!me?.two_factor_enabled;

  const [code, setCode] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [copied, setCopied] = useState(false);

  // Gera o segredo (só quando ainda não está activo).
  const setup = useQuery({
    queryKey: ['2fa-setup'],
    queryFn: enableTwoFactor,
    enabled: !enabled,
    staleTime: Infinity,
    gcTime: 0,
  });

  const copySecret = async () => {
    const secret = setup.data?.secret;
    if (!secret) return;
    const ok = await copyToClipboard(secret);
    if (ok) {
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };

  const confirmM = useMutation({
    mutationFn: () => confirmTwoFactor(code),
    onSuccess: ({ user }) => {
      setError(null);
      setCode('');
      setUser(user);
      void queryClient.invalidateQueries({ queryKey: ['me'] });
    },
    onError: (e) => setError(apiErrorMessage(e)),
  });

  const disableM = useMutation({
    mutationFn: () => disableTwoFactor(password),
    onSuccess: ({ user }) => {
      setError(null);
      setPassword('');
      setUser(user);
      void queryClient.invalidateQueries({ queryKey: ['me'] });
      void queryClient.invalidateQueries({ queryKey: ['2fa-setup'] });
    },
    onError: (e) => setError(apiErrorMessage(e)),
  });

  const activate = () => {
    if (code.replace(/\D/g, '').length !== 6) {
      setError('Introduz o código de 6 dígitos do Google Authenticator.');
      return;
    }
    confirmM.mutate();
  };

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Autenticação em 2 passos" onBack={() => router.back()} />
      <KeyboardAwareScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false} bottomOffset={24}>
        {/* Estado */}
        <View style={[styles.statusCard, enabled ? styles.statusOn : styles.statusOff]}>
          <View style={[styles.statusIcon, { backgroundColor: enabled ? colors.primary : colors.surfaceAlt }]}>
            <Ionicons name={enabled ? 'shield-checkmark' : 'shield-outline'} size={22} color={enabled ? colors.white : colors.textMuted} />
          </View>
          <View style={styles.flex}>
            <Text style={styles.statusTitle}>{enabled ? 'Proteção ativa' : 'Proteção desativada'}</Text>
            <Text style={styles.statusText}>
              {enabled
                ? 'Pedimos um código do Google Authenticator ao iniciares sessão.'
                : 'Adiciona uma camada extra de segurança com códigos temporários.'}
            </Text>
          </View>
        </View>

        {!!error && <Text style={styles.error}>{error}</Text>}

        {enabled ? (
          <View style={styles.card}>
            <Text style={styles.cardTitle}>Desativar 2FA</Text>
            <Text style={styles.muted}>Confirma com a tua palavra-passe para desativar a proteção.</Text>
            <TextField label="Palavra-passe" value={password} onChangeText={setPassword} secureTextEntry autoCapitalize="none" />
            <Button label="Desativar 2FA" variant="ghost" onPress={() => disableM.mutate()} loading={disableM.isPending} />
          </View>
        ) : setup.isLoading ? (
          <View style={styles.center}>
            <ActivityIndicator color={colors.primary} />
          </View>
        ) : setup.isError ? (
          <View style={styles.card}>
            <Text style={styles.muted}>Não foi possível iniciar a configuração.</Text>
            <Button label="Tentar de novo" variant="ghost" onPress={() => setup.refetch()} />
          </View>
        ) : (
          <>
            <Step n={1} title="Instala a app" text="Instala o Google Authenticator (ou Authy) no teu telemóvel." />

            <Step n={2} title="Adiciona a chave">
              <Text style={styles.muted}>Introduz esta chave no autenticador (ou lê o QR quando disponível):</Text>
              <Pressable style={({ pressed }) => [styles.secretBox, pressed && styles.secretPressed]} onPress={copySecret}>
                <Text style={styles.secret} selectable>
                  {setup.data?.secret}
                </Text>
                <Ionicons
                  name={copied ? 'checkmark-circle' : 'copy-outline'}
                  size={18}
                  color={copied ? colors.primaryBright : colors.textMuted}
                />
              </Pressable>
              <Text style={[styles.hint, copied && styles.hintCopied]}>
                {copied ? 'Chave copiada!' : 'Toca para copiar a chave.'}
              </Text>
            </Step>

            <Step n={3} title="Confirma o código">
              <Text style={styles.muted}>Introduz o código de 6 dígitos que aparece na app.</Text>
              <TextInput
                value={code}
                onChangeText={(t) => setCode(t.replace(/\D/g, '').slice(0, 6))}
                keyboardType="number-pad"
                placeholder="000000"
                placeholderTextColor={colors.textFaint}
                style={styles.codeInput}
                maxLength={6}
              />
            </Step>

            <Button label="Ativar 2FA" onPress={activate} loading={confirmM.isPending} />
          </>
        )}
      </KeyboardAwareScrollView>
    </Screen>
  );
}

/** Passo numerado do assistente. */
function Step({ n, title, text, children }: { n: number; title: string; text?: string; children?: React.ReactNode }) {
  return (
    <View style={styles.card}>
      <View style={styles.stepHead}>
        <View style={styles.stepNum}>
          <Text style={styles.stepNumText}>{n}</Text>
        </View>
        <Text style={styles.cardTitle}>{title}</Text>
      </View>
      {!!text && <Text style={styles.muted}>{text}</Text>}
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  center: { paddingVertical: spacing.xxl, alignItems: 'center' },
  scroll: { gap: spacing.md, paddingVertical: spacing.lg },
  statusCard: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    borderRadius: radius.lg,
    borderWidth: 1,
    padding: spacing.md,
  },
  statusOn: { backgroundColor: colors.primaryTint, borderColor: colors.primaryTintBorder },
  statusOff: { backgroundColor: colors.card, borderColor: colors.border },
  statusIcon: { width: 44, height: 44, borderRadius: radius.pill, alignItems: 'center', justifyContent: 'center' },
  statusTitle: { fontFamily: fonts.displaySemi, fontSize: fontSize.md, color: colors.text },
  statusText: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, lineHeight: 17, marginTop: 2 },
  card: {
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.lg,
    gap: spacing.sm,
  },
  cardTitle: { fontFamily: fonts.displaySemi, fontSize: fontSize.md, color: colors.text },
  stepHead: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
  stepNum: {
    width: 24,
    height: 24,
    borderRadius: radius.pill,
    backgroundColor: colors.primaryTint,
    alignItems: 'center',
    justifyContent: 'center',
  },
  stepNumText: { fontFamily: fonts.bodyBold, fontSize: fontSize.xs, color: colors.primaryBright },
  muted: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, lineHeight: 20 },
  secretBox: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: spacing.sm,
    backgroundColor: colors.surfaceAlt,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.md,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.md,
  },
  secretPressed: { opacity: 0.7 },
  secret: { flex: 1, fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text, letterSpacing: 1 },
  hint: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textFaint },
  hintCopied: { color: colors.primaryBright },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger },
  codeInput: {
    height: 56,
    borderRadius: radius.md,
    borderWidth: 1.5,
    borderColor: colors.border,
    backgroundColor: colors.surfaceAlt,
    fontFamily: fonts.display,
    fontSize: fontSize.xxl,
    color: colors.text,
    textAlign: 'center',
    letterSpacing: 8,
  },
});
