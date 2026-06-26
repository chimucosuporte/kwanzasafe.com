import { useRouter } from 'expo-router';
import * as WebBrowser from 'expo-web-browser';
import { useRef, useState } from 'react';
import {
  NativeScrollEvent,
  NativeSyntheticEvent,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  useWindowDimensions,
  View,
} from 'react-native';

import { Button } from '@/components/Button';
import { Screen } from '@/components/Screen';
import { Wordmark } from '@/components/Wordmark';
import { API_BASE_URL } from '@/config';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, spacing } from '@/theme';

interface Slide {
  icon: string;
  title: string;
  text: string;
}

const SLIDES: Slide[] = [
  {
    icon: '🌍',
    title: 'Do mundo para Angola',
    text: 'Converte Euros, Reais e USDC para Kwanzas com rapidez e total confiança.',
  },
  {
    icon: '💱',
    title: 'Taxas claras, zero surpresas',
    text: 'Vês exactamente quanto recebes antes de iniciar e acompanhas tudo pela app.',
  },
  {
    icon: '💬',
    title: 'Um agente dedicado a ti',
    text: 'Fala no chat, envia o comprovativo e recebe os teus Kwanzas em segurança.',
  },
];

export default function OnboardingScreen() {
  const router = useRouter();
  const { width } = useWindowDimensions();
  const completeOnboarding = useAuthStore((s) => s.completeOnboarding);
  const [index, setIndex] = useState(0);

  // Largura útil do slide = largura do ecrã menos o padding horizontal do Screen.
  const slideWidth = width - spacing.lg * 2;

  const onScroll = (e: NativeSyntheticEvent<NativeScrollEvent>) => {
    const i = Math.round(e.nativeEvent.contentOffset.x / slideWidth);
    if (i !== index) setIndex(i);
  };

  const finish = async (to: '/login' | '/register') => {
    await completeOnboarding();
    router.replace(to);
  };

  const openLegal = (path: string) => {
    void WebBrowser.openBrowserAsync(`${API_BASE_URL}${path}`);
  };

  return (
    <Screen>
      <View style={styles.top}>
        <Wordmark size={fontSize.xl} />
        <Pressable onPress={() => finish('/login')} hitSlop={10}>
          <Text style={styles.skip}>Saltar</Text>
        </Pressable>
      </View>

      <ScrollView
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        onScroll={onScroll}
        scrollEventThrottle={16}
        style={styles.flex}
      >
        {SLIDES.map((slide) => (
          <View key={slide.title} style={[styles.slide, { width: slideWidth }]}>
            <View style={styles.iconCircle}>
              <Text style={styles.icon}>{slide.icon}</Text>
            </View>
            <Text style={styles.title}>{slide.title}</Text>
            <Text style={styles.text}>{slide.text}</Text>
          </View>
        ))}
      </ScrollView>

      <View style={styles.dots}>
        {SLIDES.map((_, i) => (
          <View key={i} style={[styles.dot, i === index && styles.dotActive]} />
        ))}
      </View>

      <View style={styles.actions}>
        <Button label="Criar conta" onPress={() => finish('/register')} />
        <Button label="Já tenho conta" variant="ghost" onPress={() => finish('/login')} />
      </View>

      <Text style={styles.legal}>
        Ao continuar aceitas os{' '}
        <Text style={styles.legalLink} onPress={() => openLegal('/termos')}>Termos</Text>
        {' '}e a{' '}
        <Text style={styles.legalLink} onPress={() => openLegal('/privacidade')}>Política de Privacidade</Text>.
      </Text>
    </Screen>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  top: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: spacing.lg,
  },
  skip: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted },
  slide: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: spacing.lg, paddingHorizontal: spacing.sm },
  iconCircle: {
    width: 120,
    height: 120,
    borderRadius: 120,
    backgroundColor: colors.primaryTint,
    alignItems: 'center',
    justifyContent: 'center',
  },
  icon: { fontSize: 56 },
  title: { fontFamily: fonts.display, fontSize: fontSize.xxl, color: colors.text, textAlign: 'center' },
  text: { fontFamily: fonts.body, fontSize: fontSize.md, color: colors.textMuted, textAlign: 'center', lineHeight: 24, paddingHorizontal: spacing.md },
  dots: { flexDirection: 'row', justifyContent: 'center', gap: spacing.sm, paddingVertical: spacing.lg },
  dot: { width: 8, height: 8, borderRadius: 8, backgroundColor: colors.border },
  dotActive: { backgroundColor: colors.primary, width: 22 },
  actions: { gap: spacing.sm },
  legal: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, textAlign: 'center', paddingTop: spacing.md, lineHeight: 18 },
  legalLink: { fontFamily: fonts.bodyBold, color: colors.primary },
});
