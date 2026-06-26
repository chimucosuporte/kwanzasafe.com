import { Ionicons } from '@expo/vector-icons';
import { StyleSheet, Text, View } from 'react-native';

import { Button } from '@/components/Button';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

/** Estado de erro reutilizável com acção "Tentar de novo". */
export function ErrorState({
  title = 'Algo correu mal',
  description = 'Não foi possível carregar. Verifica a ligação e tenta novamente.',
  onRetry,
  retrying,
}: {
  title?: string;
  description?: string;
  onRetry?: () => void;
  retrying?: boolean;
}) {
  return (
    <View style={styles.wrap}>
      <View style={styles.iconWrap}>
        <Ionicons name="cloud-offline-outline" size={32} color={colors.danger} />
      </View>
      <Text style={styles.title}>{title}</Text>
      <Text style={styles.description}>{description}</Text>
      {!!onRetry && (
        <View style={styles.action}>
          <Button label="Tentar de novo" onPress={onRetry} loading={retrying} />
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { alignItems: 'center', justifyContent: 'center', paddingHorizontal: spacing.lg, paddingVertical: spacing.xxl, gap: spacing.sm },
  iconWrap: {
    width: 72,
    height: 72,
    borderRadius: radius.pill,
    backgroundColor: colors.dangerTint,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.xs,
  },
  title: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text, textAlign: 'center' },
  description: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', lineHeight: 20 },
  action: { marginTop: spacing.sm, alignSelf: 'stretch' },
});
