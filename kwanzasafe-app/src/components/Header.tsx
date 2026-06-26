import { Pressable, StyleSheet, Text, View } from 'react-native';

import { colors, fonts, fontSize, spacing } from '@/theme';

/** Cabeçalho simples com título centrado e seta de voltar opcional. */
export function Header({ title, onBack }: { title: string; onBack?: () => void }) {
  return (
    <View style={styles.row}>
      {onBack ? (
        <Pressable onPress={onBack} hitSlop={12} style={styles.side} accessibilityRole="button">
          <Text style={styles.chevron}>‹</Text>
        </Pressable>
      ) : (
        <View style={styles.side} />
      )}
      <Text style={styles.title} numberOfLines={1}>
        {title}
      </Text>
      <View style={styles.side} />
    </View>
  );
}

const styles = StyleSheet.create({
  row: {
    height: 48,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  side: { width: 32, alignItems: 'flex-start', justifyContent: 'center' },
  chevron: { fontFamily: fonts.display, fontSize: 34, lineHeight: 38, color: colors.text },
  title: { flex: 1, textAlign: 'center', fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
});
