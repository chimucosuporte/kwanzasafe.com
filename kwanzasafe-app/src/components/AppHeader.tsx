import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import { Pressable, StyleSheet, Text, View } from 'react-native';

import { Logo } from '@/components/Logo';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { User } from '@/types/api';

type IconName = keyof typeof Ionicons.glyphMap;

/** Botão de ícone circular para a barra de topo. */
function IconButton({
  name,
  onPress,
  label,
  dot,
}: {
  name: IconName;
  onPress: () => void;
  label: string;
  dot?: boolean;
}) {
  return (
    <Pressable
      onPress={onPress}
      hitSlop={8}
      accessibilityRole="button"
      accessibilityLabel={label}
      style={({ pressed }) => [styles.iconBtn, pressed && styles.pressed]}
    >
      <Ionicons name={name} size={20} color={colors.text} />
      {dot && <View style={styles.dot} />}
    </Pressable>
  );
}

/**
 * Barra de topo global da app: ícone da plataforma à esquerda e o conjunto
 * de acções rápidas à direita (pesquisa · ler QR · notificações · conta).
 */
export function AppHeader({ user, alerts }: { user?: User | null; alerts?: boolean }) {
  const router = useRouter();
  const token = useAuthStore((s) => s.token);
  const initial = (user?.full_name?.trim()?.[0] ?? 'K').toUpperCase();

  return (
    <View style={styles.row}>
      <Logo size={38} />

      <View style={styles.actions}>
        <IconButton name="search-outline" label="Pesquisar" onPress={() => router.push('/search')} />
        <IconButton name="scan-outline" label="Ler código QR" onPress={() => router.push('/scan')} />
        <IconButton
          name="notifications-outline"
          label="Notificações"
          dot={alerts}
          onPress={() => router.push('/notifications')}
        />
        <Pressable
          onPress={() => router.push('/profile')}
          hitSlop={8}
          accessibilityRole="button"
          accessibilityLabel="A minha conta"
          style={({ pressed }) => [styles.avatar, pressed && styles.pressed]}
        >
          {user?.avatar_url ? (
            <Image
              source={{ uri: user.avatar_url, headers: token ? { Authorization: `Bearer ${token}` } : undefined }}
              style={styles.avatarImg}
              contentFit="cover"
            />
          ) : (
            <Text style={styles.avatarText}>{initial}</Text>
          )}
        </Pressable>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  row: {
    height: 52,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  actions: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
  iconBtn: {
    width: 40,
    height: 40,
    borderRadius: radius.pill,
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  pressed: { opacity: 0.7 },
  dot: {
    position: 'absolute',
    top: 9,
    right: 9,
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: colors.danger,
    borderWidth: 1.5,
    borderColor: colors.bg,
  },
  avatar: {
    width: 40,
    height: 40,
    borderRadius: radius.pill,
    backgroundColor: colors.primaryTint,
    borderWidth: 1,
    borderColor: colors.primaryTintBorder,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: { fontFamily: fonts.display, fontSize: fontSize.md, color: colors.primaryBright },
  avatarImg: { width: 40, height: 40, borderRadius: radius.pill },
});
