import { useEffect, useRef } from 'react';
import { Animated, StyleSheet, View, type DimensionValue, type ViewStyle } from 'react-native';

import { colors, radius, spacing } from '@/theme';

/** Bloco de carregamento (shimmer) — placeholder enquanto os dados chegam. */
export function Skeleton({
  width = '100%',
  height = 16,
  borderRadius = radius.sm,
  style,
}: {
  width?: DimensionValue;
  height?: number;
  borderRadius?: number;
  style?: ViewStyle;
}) {
  const pulse = useRef(new Animated.Value(0.4)).current;

  useEffect(() => {
    const loop = Animated.loop(
      Animated.sequence([
        Animated.timing(pulse, { toValue: 1, duration: 700, useNativeDriver: true }),
        Animated.timing(pulse, { toValue: 0.4, duration: 700, useNativeDriver: true }),
      ]),
    );
    loop.start();
    return () => loop.stop();
  }, [pulse]);

  return (
    <Animated.View
      style={[{ width, height, borderRadius, backgroundColor: colors.border, opacity: pulse }, style]}
    />
  );
}

/** Placeholder de um cartão (linhas de texto). */
export function SkeletonCard() {
  return (
    <View style={styles.card}>
      <Skeleton width="55%" height={14} />
      <Skeleton width="80%" height={20} />
      <Skeleton width="40%" height={12} />
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.lg,
    gap: spacing.sm,
  },
});
