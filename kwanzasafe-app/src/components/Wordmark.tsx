import { StyleSheet, Text, View } from 'react-native';

import { colors, fonts, fontSize } from '@/theme';

/** Marca denominativa "KwanzaSafe" (display Syne + ponto verde). */
export function Wordmark({ size = fontSize.xxl }: { size?: number }) {
  return (
    <View style={styles.row}>
      <Text style={[styles.word, { fontSize: size }]}>Kwanza</Text>
      <Text style={[styles.word, styles.accent, { fontSize: size }]}>Safe</Text>
      <View style={[styles.dot, { width: size * 0.16, height: size * 0.16, borderRadius: size }]} />
    </View>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'flex-end' },
  word: { fontFamily: fonts.display, color: colors.text, letterSpacing: -0.5 },
  accent: { color: colors.primary },
  dot: { backgroundColor: colors.primary, marginLeft: 3, marginBottom: 6 },
});
