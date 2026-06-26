import { Ionicons } from '@expo/vector-icons';
import { useCallback, useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';

import { Button } from '@/components/Button';
import { Wordmark } from '@/components/Wordmark';
import { authenticateBiometric } from '@/lib/biometrics';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

/**
 * Cadeado biométrico de abertura: cobre a app enquanto `locked` é true.
 * Pede a biometria ao montar; em falha, oferece repetir ou terminar sessão.
 */
export function LockScreen() {
  const unlock = useAuthStore((s) => s.unlock);
  const clearSession = useAuthStore((s) => s.clearSession);
  const [failed, setFailed] = useState(false);

  const prompt = useCallback(async () => {
    setFailed(false);
    const ok = await authenticateBiometric('Desbloqueia a KwanzaSafe');
    if (ok) unlock();
    else setFailed(true);
  }, [unlock]);

  useEffect(() => {
    void prompt();
  }, [prompt]);

  return (
    <View style={styles.overlay}>
      <View style={styles.content}>
        <Wordmark size={fontSize.xxl} />
        <View style={styles.iconWrap}>
          <Ionicons name="lock-closed" size={40} color={colors.primary} />
        </View>
        <Text style={styles.title}>App protegida</Text>
        <Text style={styles.text}>
          {failed
            ? 'Não foi possível confirmar a tua biometria. Tenta de novo.'
            : 'Confirma a tua identidade para continuar.'}
        </Text>
        <View style={styles.actions}>
          <Button label="Desbloquear" onPress={prompt} />
          <Button label="Terminar sessão" variant="ghost" onPress={() => clearSession()} />
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  overlay: { ...StyleSheet.absoluteFillObject, backgroundColor: colors.bg, zIndex: 2000 },
  content: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: spacing.xl, gap: spacing.md },
  iconWrap: {
    width: 88,
    height: 88,
    borderRadius: radius.pill,
    backgroundColor: colors.primaryTint,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: spacing.lg,
  },
  title: { fontFamily: fonts.display, fontSize: fontSize.xxl, color: colors.text },
  text: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', lineHeight: 22 },
  actions: { alignSelf: 'stretch', gap: spacing.sm, marginTop: spacing.lg },
});
