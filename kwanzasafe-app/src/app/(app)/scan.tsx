import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { StyleSheet, Text, View } from 'react-native';

import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

/**
 * Leitor de QR (UI). O enquadramento e instruções estão prontos; a câmara
 * real (expo-camera) liga-se numa fase seguinte sobre o dev build.
 */
export default function ScanScreen() {
  const router = useRouter();

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Ler código QR" onBack={() => router.back()} />

      <View style={styles.body}>
        <View style={styles.viewport}>
          <View style={styles.dim} />
          {/* Cantos do enquadramento */}
          <View style={[styles.corner, styles.tl]} />
          <View style={[styles.corner, styles.tr]} />
          <View style={[styles.corner, styles.bl]} />
          <View style={[styles.corner, styles.br]} />
          <View style={styles.scanLine} />
          <Ionicons name="qr-code-outline" size={64} color={colors.textFaint} />
        </View>

        <Text style={styles.title}>Aponta a câmara ao código</Text>
        <Text style={styles.hint}>
          Lê o QR de uma transação ou de uma conta de pagamento para preencher
          os dados automaticamente.
        </Text>
      </View>

      <View style={styles.footer}>
        <Button
          label="Introduzir referência manualmente"
          variant="ghost"
          onPress={() => router.push('/search')}
        />
      </View>
    </Screen>
  );
}

const FRAME = 260;

const styles = StyleSheet.create({
  body: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: spacing.md },
  viewport: {
    width: FRAME,
    height: FRAME,
    borderRadius: radius.xl,
    backgroundColor: colors.surfaceAlt,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    marginBottom: spacing.lg,
  },
  dim: { ...StyleSheet.absoluteFillObject, backgroundColor: colors.bg, opacity: 0.4 },
  corner: {
    position: 'absolute',
    width: 36,
    height: 36,
    borderColor: colors.primaryBright,
  },
  tl: { top: 14, left: 14, borderTopWidth: 4, borderLeftWidth: 4, borderTopLeftRadius: 12 },
  tr: { top: 14, right: 14, borderTopWidth: 4, borderRightWidth: 4, borderTopRightRadius: 12 },
  bl: { bottom: 14, left: 14, borderBottomWidth: 4, borderLeftWidth: 4, borderBottomLeftRadius: 12 },
  br: { bottom: 14, right: 14, borderBottomWidth: 4, borderRightWidth: 4, borderBottomRightRadius: 12 },
  scanLine: {
    position: 'absolute',
    width: FRAME - 60,
    height: 2,
    backgroundColor: colors.primaryBright,
    opacity: 0.7,
  },
  title: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  hint: {
    fontFamily: fonts.body,
    fontSize: fontSize.sm,
    color: colors.textMuted,
    textAlign: 'center',
    lineHeight: 20,
    paddingHorizontal: spacing.lg,
  },
  footer: { paddingBottom: spacing.md },
});
