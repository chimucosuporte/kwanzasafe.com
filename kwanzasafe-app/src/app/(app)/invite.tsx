import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { Share, StyleSheet, Text, View } from 'react-native';

import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

const SITE = 'https://kwanzasafe.com';

export default function InviteScreen() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const firstName = user?.full_name?.split(' ')[0];

  const share = async () => {
    const message =
      `${firstName ? `${firstName} recomenda a ` : 'Conhece a '}KwanzaSafe 💚\n\n` +
      'Converte Euros, Reais e USDC para Kwanzas com rapidez e total confiança — do mundo para Angola.\n\n' +
      `Junta-te aqui: ${SITE}`;
    try {
      await Share.share({ message });
    } catch {
      /* utilizador cancelou */
    }
  };

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Convidar amigos" onBack={() => router.back()} />
      <View style={styles.body}>
        <View style={styles.iconWrap}>
          <Ionicons name="gift" size={40} color={colors.primary} />
        </View>
        <Text style={styles.title}>Partilha a KwanzaSafe</Text>
        <Text style={styles.text}>
          Convida amigos e familiares a enviar dinheiro para Angola com segurança e rapidez. Partilha o
          link e ajuda-os a começar.
        </Text>

        <View style={styles.card}>
          <Text style={styles.cardLabel}>O teu link</Text>
          <Text style={styles.link}>{SITE}</Text>
        </View>

        <Button label="Partilhar convite" onPress={share} style={styles.btn} />
      </View>
    </Screen>
  );
}

const styles = StyleSheet.create({
  body: { flex: 1, alignItems: 'center', paddingVertical: spacing.xl, gap: spacing.md },
  iconWrap: {
    width: 88,
    height: 88,
    borderRadius: radius.pill,
    backgroundColor: colors.primaryTint,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.sm,
  },
  title: { fontFamily: fonts.display, fontSize: fontSize.xxl, color: colors.text, textAlign: 'center' },
  text: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', lineHeight: 22, paddingHorizontal: spacing.md },
  card: {
    alignSelf: 'stretch',
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.lg,
    gap: spacing.xs,
    alignItems: 'center',
    marginTop: spacing.md,
  },
  cardLabel: { fontFamily: fonts.bodyMedium, fontSize: fontSize.xs, color: colors.textMuted, textTransform: 'uppercase', letterSpacing: 0.5 },
  link: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.primaryDark },
  btn: { alignSelf: 'stretch', marginTop: 'auto' },
});
