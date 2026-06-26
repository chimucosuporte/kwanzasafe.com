import { Ionicons } from '@expo/vector-icons';
import { StyleSheet, Text, View } from 'react-native';

import { Button } from '@/components/Button';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

type IconName = keyof typeof Ionicons.glyphMap;

/** Estado vazio reutilizável: ícone + título + descrição + acção opcional. */
export function EmptyState({
  icon = 'file-tray-outline',
  title,
  description,
  actionLabel,
  onAction,
}: {
  icon?: IconName;
  title: string;
  description?: string;
  actionLabel?: string;
  onAction?: () => void;
}) {
  return (
    <View style={styles.wrap}>
      <View style={styles.iconWrap}>
        <Ionicons name={icon} size={32} color={colors.primary} />
      </View>
      <Text style={styles.title}>{title}</Text>
      {!!description && <Text style={styles.description}>{description}</Text>}
      {!!actionLabel && !!onAction && (
        <View style={styles.action}>
          <Button label={actionLabel} variant="ghost" onPress={onAction} />
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
    backgroundColor: colors.primaryTint,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.xs,
  },
  title: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text, textAlign: 'center' },
  description: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', lineHeight: 20 },
  action: { marginTop: spacing.sm, alignSelf: 'stretch' },
});
