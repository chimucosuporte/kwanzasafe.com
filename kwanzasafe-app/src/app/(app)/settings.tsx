import Constants from 'expo-constants';
import { useRouter } from 'expo-router';
import { useEffect, useState } from 'react';
import { Alert, ScrollView, StyleSheet, Switch, Text, View } from 'react-native';

import { apiErrorMessage } from '@/api/client';
import { registerPushToken, sendTestPush } from '@/api/push';
import { Header } from '@/components/Header';
import { MenuRow } from '@/components/MenuRow';
import { Screen } from '@/components/Screen';
import { authenticateBiometric, biometricLabel, isBiometricAvailable } from '@/lib/biometrics';
import { acquirePushToken } from '@/lib/push';
import { getBiometricEnabled, setBiometricEnabled } from '@/lib/secureStore';
import { resetTours } from '@/lib/tour';
import { toast } from '@/stores/toast';
import { colors, fonts, fontSize, spacing } from '@/theme';

export default function SettingsScreen() {
  const router = useRouter();
  const [bioEnabled, setBioEnabled] = useState(false);
  const [bioLabel, setBioLabel] = useState('Biometria');
  const [bioAvailable, setBioAvailable] = useState(false);
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    (async () => {
      setBioEnabled(await getBiometricEnabled());
      setBioAvailable(await isBiometricAvailable());
      setBioLabel(await biometricLabel());
    })();
  }, []);

  const toggleBiometric = async (next: boolean) => {
    if (busy) return;
    setBusy(true);
    try {
      if (next) {
        if (!bioAvailable) {
          Alert.alert(
            'Biometria indisponível',
            'Este aparelho não tem biometria configurada. Regista uma impressão digital ou rosto nas definições do telemóvel.',
          );
          return;
        }
        const ok = await authenticateBiometric('Confirma para activar o desbloqueio biométrico');
        if (!ok) return;
        await setBiometricEnabled(true);
        setBioEnabled(true);
      } else {
        await setBiometricEnabled(false);
        setBioEnabled(false);
      }
    } finally {
      setBusy(false);
    }
  };

  const testPush = async () => {
    toast.info('A verificar notificações…');
    // Re-adquire e re-regista o token (auto-cura) e revela a causa se falhar.
    const { token, reason } = await acquirePushToken();
    if (!token) {
      toast.error(reason);
      return;
    }
    try {
      await registerPushToken(token);
      const { message } = await sendTestPush();
      toast.success(message);
    } catch (e) {
      toast.error(apiErrorMessage(e));
    }
  };

  const version = Constants.expoConfig?.version ?? '1.0.0';

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Definições" onBack={() => router.back()} />
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
        <Text style={styles.section}>Segurança</Text>
        <MenuRow
          icon="finger-print"
          label={bioLabel}
          description={
            bioAvailable
              ? 'Desbloqueia a app com a tua biometria.'
              : 'Configura a biometria no teu telemóvel para activar.'
          }
          right={
            <Switch
              value={bioEnabled}
              onValueChange={toggleBiometric}
              disabled={busy || !bioAvailable}
              trackColor={{ true: colors.primary, false: colors.border }}
              thumbColor={colors.white}
            />
          }
        />
        <MenuRow
          icon="key-outline"
          label="Alterar palavra-passe"
          description="Gere a palavra-passe na tua conta."
          onPress={() => router.push('/change-password')}
        />

        <Text style={styles.section}>Preferências</Text>
        <MenuRow icon="language-outline" label="Idioma" value="Português (PT)" />
        <MenuRow
          icon="help-buoy-outline"
          label="Rever guias da app"
          description="Mostra de novo as dicas de cada ecrã."
          onPress={async () => {
            await resetTours();
            toast.success('Os guias vão reaparecer à medida que navegas.');
          }}
        />
        <MenuRow
          icon="notifications-outline"
          label="Notificações"
          description="Vê os teus avisos e lembretes."
          onPress={() => router.push('/notifications')}
        />
        <MenuRow
          icon="paper-plane-outline"
          label="Enviar notificação de teste"
          description="Confirma que as notificações push funcionam neste telemóvel."
          onPress={testPush}
        />

        <Text style={styles.section}>Sobre</Text>
        <MenuRow icon="information-circle-outline" label="Versão da app" value={version} />

        <Text style={styles.footer}>KwanzaSafe · do mundo para Angola</Text>
      </ScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  scroll: { gap: spacing.sm, paddingVertical: spacing.lg },
  section: {
    fontFamily: fonts.bodyBold,
    fontSize: fontSize.xs,
    color: colors.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginTop: spacing.md,
    marginBottom: spacing.xs,
  },
  footer: {
    fontFamily: fonts.body,
    fontSize: fontSize.xs,
    color: colors.textMuted,
    textAlign: 'center',
    marginTop: spacing.xl,
  },
});
