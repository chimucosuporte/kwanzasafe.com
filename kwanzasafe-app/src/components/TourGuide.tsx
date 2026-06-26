import { Ionicons } from '@expo/vector-icons';
import { useEffect, useState } from 'react';
import { Modal, Pressable, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

import { isTourSeen, markTourSeen } from '@/lib/tour';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

type IconName = keyof typeof Ionicons.glyphMap;

export type TourStep = { icon: IconName; title: string; body: string };

/**
 * Guia de funcionalidades: na 1ª visita ao ecrã mostra um conjunto de notas
 * informativas (passos). Não repete depois de visto. Montar no fim do ecrã:
 *   <TourGuide tourKey="notifications" steps={[...]} />
 */
export function TourGuide({ tourKey, steps }: { tourKey: string; steps: TourStep[] }) {
  const insets = useSafeAreaInsets();
  const [visible, setVisible] = useState(false);
  const [step, setStep] = useState(0);

  useEffect(() => {
    let active = true;
    void isTourSeen(tourKey).then((seen) => {
      if (active && !seen && steps.length > 0) setVisible(true);
    });
    return () => {
      active = false;
    };
  }, [tourKey, steps.length]);

  const close = () => {
    void markTourSeen(tourKey);
    setVisible(false);
  };

  if (steps.length === 0) return null;
  const current = steps[Math.min(step, steps.length - 1)];
  const isLast = step >= steps.length - 1;

  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={close}>
      <View style={styles.backdrop}>
        <View style={[styles.sheet, { paddingBottom: insets.bottom + spacing.lg }]}>
          <View style={styles.iconWrap}>
            <Ionicons name={current.icon} size={30} color={colors.primaryBright} />
          </View>

          <Text style={styles.title}>{current.title}</Text>
          <Text style={styles.body}>{current.body}</Text>

          {/* Indicador de passos */}
          {steps.length > 1 && (
            <View style={styles.dots}>
              {steps.map((_, i) => (
                <View key={i} style={[styles.dot, i === step && styles.dotActive]} />
              ))}
            </View>
          )}

          <View style={styles.actions}>
            <Pressable onPress={close} hitSlop={8}>
              <Text style={styles.skip}>{isLast ? '' : 'Saltar'}</Text>
            </Pressable>
            <Pressable
              style={styles.next}
              onPress={() => (isLast ? close() : setStep((s) => s + 1))}
            >
              <Text style={styles.nextText}>{isLast ? 'Entendi' : 'Seguinte'}</Text>
              {!isLast && <Ionicons name="arrow-forward" size={16} color={colors.white} />}
            </Pressable>
          </View>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  backdrop: { flex: 1, backgroundColor: colors.overlay, justifyContent: 'flex-end' },
  sheet: {
    backgroundColor: colors.card,
    borderTopLeftRadius: radius.xl,
    borderTopRightRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border,
    paddingHorizontal: spacing.lg,
    paddingTop: spacing.xl,
    gap: spacing.sm,
    alignItems: 'center',
  },
  iconWrap: {
    width: 64,
    height: 64,
    borderRadius: radius.pill,
    backgroundColor: colors.primaryTint,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.xs,
  },
  title: { fontFamily: fonts.display, fontSize: fontSize.xl, color: colors.text, textAlign: 'center' },
  body: { fontFamily: fonts.body, fontSize: fontSize.md, color: colors.textMuted, textAlign: 'center', lineHeight: 22 },
  dots: { flexDirection: 'row', gap: 6, marginTop: spacing.sm },
  dot: { width: 7, height: 7, borderRadius: 4, backgroundColor: colors.border },
  dotActive: { backgroundColor: colors.primaryBright, width: 18 },
  actions: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', alignSelf: 'stretch', marginTop: spacing.lg },
  skip: { fontFamily: fonts.bodyMedium, fontSize: fontSize.md, color: colors.textMuted, minWidth: 60 },
  next: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    backgroundColor: colors.primary,
    borderRadius: radius.md,
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.xl,
  },
  nextText: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.white },
});
