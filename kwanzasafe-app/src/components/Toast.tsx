import { Ionicons } from '@expo/vector-icons';
import { useEffect, useRef, useState } from 'react';
import { Animated, Dimensions, Pressable, StyleSheet, Text } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

import { useToastStore, type ToastType } from '@/stores/toast';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

const W = Dimensions.get('window').width;

const TONE: Record<ToastType, { bg: string; border: string; fg: string; icon: keyof typeof Ionicons.glyphMap }> = {
  error: { bg: '#2a1414', border: 'rgba(255,90,90,0.4)', fg: colors.danger, icon: 'alert-circle' },
  success: { bg: colors.primaryTint, border: colors.primaryTintBorder, fg: colors.primaryBright, icon: 'checkmark-circle' },
  info: { bg: colors.infoTint, border: 'rgba(77,155,255,0.4)', fg: colors.info, icon: 'information-circle' },
};

/**
 * Toast global no topo do ecrã. Entra a deslizar pelo topo e sai pelo
 * topo-direito. Fecha-se no X, automaticamente após 3s, ou via toast.hide()
 * (ex.: quando o utilizador edita um campo).
 */
export function Toast() {
  const insets = useSafeAreaInsets();
  const seq = useToastStore((s) => s.seq);
  const message = useToastStore((s) => s.message);
  const type = useToastStore((s) => s.type);
  const hide = useToastStore((s) => s.hide);

  const [shown, setShown] = useState<{ message: string; type: ToastType } | null>(null);

  const translateY = useRef(new Animated.Value(-140)).current;
  const translateX = useRef(new Animated.Value(0)).current;
  const opacity = useRef(new Animated.Value(0)).current;
  const timer = useRef<ReturnType<typeof setTimeout> | null>(null);

  const dismiss = () => {
    if (timer.current) clearTimeout(timer.current);
    Animated.parallel([
      Animated.timing(translateX, { toValue: W, duration: 240, useNativeDriver: true }),
      Animated.timing(translateY, { toValue: -60, duration: 240, useNativeDriver: true }),
      Animated.timing(opacity, { toValue: 0, duration: 240, useNativeDriver: true }),
    ]).start(() => {
      setShown(null);
      hide();
    });
  };

  // Sempre que muda a sequência e há mensagem → mostrar.
  useEffect(() => {
    if (!message) return;
    setShown({ message, type });
    translateX.setValue(0);
    translateY.setValue(-140);
    opacity.setValue(0);
    Animated.parallel([
      Animated.spring(translateY, { toValue: insets.top + 8, useNativeDriver: true, bounciness: 6 }),
      Animated.timing(opacity, { toValue: 1, duration: 200, useNativeDriver: true }),
    ]).start();

    if (timer.current) clearTimeout(timer.current);
    timer.current = setTimeout(dismiss, 3000);

    return () => {
      if (timer.current) clearTimeout(timer.current);
    };
  }, [seq]); // eslint-disable-line react-hooks/exhaustive-deps

  if (!shown) return null;
  const tone = TONE[shown.type];

  return (
    <Animated.View
      pointerEvents="box-none"
      style={[styles.wrap, { transform: [{ translateY }, { translateX }], opacity }]}
    >
      <Pressable
        onPress={dismiss}
        style={[styles.card, { backgroundColor: tone.bg, borderColor: tone.border }]}
      >
        <Ionicons name={tone.icon} size={20} color={tone.fg} />
        <Text style={styles.message} numberOfLines={3}>
          {shown.message}
        </Text>
        <Ionicons name="close" size={18} color={colors.textMuted} />
      </Pressable>
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  wrap: { position: 'absolute', top: 0, left: 0, right: 0, paddingHorizontal: spacing.md, zIndex: 1000 },
  card: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    borderRadius: radius.lg,
    borderWidth: 1,
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.md,
    shadowColor: '#000',
    shadowOpacity: 0.4,
    shadowRadius: 12,
    shadowOffset: { width: 0, height: 6 },
    elevation: 10,
  },
  message: { flex: 1, fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text, lineHeight: 19 },
});
