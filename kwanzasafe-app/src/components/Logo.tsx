import { Image } from 'expo-image';
import { StyleSheet, View } from 'react-native';

import { colors, radius } from '@/theme';

const ICON = require('../../assets/images/icon.png');

/**
 * Ícone da plataforma KwanzaSafe (escudo verde). Substitui a marca
 * denominativa na barra de topo da home.
 */
export function Logo({ size = 36, framed = true }: { size?: number; framed?: boolean }) {
  if (!framed) {
    return <Image source={ICON} style={{ width: size, height: size }} contentFit="contain" />;
  }
  const box = size;
  const inner = Math.round(size * 0.72);
  return (
    <View style={[styles.frame, { width: box, height: box, borderRadius: radius.md }]}>
      <Image source={ICON} style={{ width: inner, height: inner }} contentFit="contain" />
    </View>
  );
}

const styles = StyleSheet.create({
  frame: {
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
