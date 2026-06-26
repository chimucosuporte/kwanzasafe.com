import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';

import { fetchMe } from '@/api/auth';
import { fetchNotifications, markAllNotificationsRead, markNotificationRead } from '@/api/notifications';
import { EmptyState } from '@/components/EmptyState';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { SkeletonCard } from '@/components/Skeleton';
import { TourGuide, type TourStep } from '@/components/TourGuide';
import { formatDate } from '@/lib/format';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { UserNotification } from '@/types/api';

type IconName = keyof typeof Ionicons.glyphMap;

const TYPE_META: Record<UserNotification['type'], { icon: IconName; tint: string; color: string }> = {
  security: { icon: 'shield-checkmark', tint: colors.warningTint, color: colors.warning },
  transaction: { icon: 'swap-horizontal', tint: colors.primaryTint, color: colors.primaryBright },
  recourse: { icon: 'shield-half', tint: colors.infoTint, color: colors.info },
  info: { icon: 'information-circle', tint: colors.surface, color: colors.text },
};

export default function NotificationsScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();

  const { data: me } = useQuery({ queryKey: ['me'], queryFn: async () => (await fetchMe()).user });
  const { data: feed, isLoading } = useQuery({ queryKey: ['notifications'], queryFn: fetchNotifications });

  const refresh = () => {
    void queryClient.invalidateQueries({ queryKey: ['notifications'] });
    void queryClient.invalidateQueries({ queryKey: ['notifications-unread'] });
  };

  const markAllM = useMutation({ mutationFn: markAllNotificationsRead, onSuccess: refresh });

  const onTap = async (n: UserNotification) => {
    if (!n.is_read) {
      try {
        await markNotificationRead(n.id);
        refresh();
      } catch {
        /* ignora */
      }
    }
    const ref = n.data?.reference_id as string | undefined;
    if (n.type === 'transaction' && ref) {
      router.push({ pathname: '/transactions/[ref]', params: { ref } });
    }
  };

  const notifications = feed?.data ?? [];
  const unread = feed?.unread ?? 0;

  // Avisos acionáveis (derivados do estado da conta) ficam no topo.
  const pending: { id: string; icon: IconName; title: string; body: string; onPress: () => void }[] = [];
  if (me && !me.email_verified) {
    pending.push({ id: 'email', icon: 'mail-unread-outline', title: 'Confirma o teu email', body: 'Toca para introduzir o código e ativar a conta.', onPress: () => router.push('/verify-email') });
  }
  if (me && me.email_verified && !me.is_fully_verified) {
    pending.push({ id: 'kyc', icon: 'shield-half-outline', title: 'Verificação pendente', body: 'Conclui o KYC para poderes transacionar.', onPress: () => router.push('/kyc') });
  }

  const empty = !isLoading && pending.length === 0 && notifications.length === 0;

  return (
    <Screen edges={['top', 'bottom']}>
      <Header
        title="Notificações"
        onBack={() => router.back()}
      />

      {unread > 0 && (
        <Pressable style={styles.markAll} onPress={() => markAllM.mutate()}>
          <Ionicons name="checkmark-done" size={16} color={colors.primaryBright} />
          <Text style={styles.markAllText}>Marcar todas como lidas ({unread})</Text>
        </Pressable>
      )}

      {isLoading ? (
        <View style={styles.list}>
          <SkeletonCard />
          <SkeletonCard />
        </View>
      ) : empty ? (
        <EmptyState
          icon="notifications-off-outline"
          title="Sem notificações"
          description="Avisos de acesso, transações e recursos aparecem aqui."
        />
      ) : (
        <ScrollView contentContainerStyle={styles.list} showsVerticalScrollIndicator={false}>
          {pending.map((p) => (
            <Pressable key={p.id} style={({ pressed }) => [styles.item, pressed && styles.pressed]} onPress={p.onPress}>
              <View style={[styles.iconWrap, { backgroundColor: colors.warningTint }]}>
                <Ionicons name={p.icon} size={20} color={colors.warning} />
              </View>
              <View style={styles.texts}>
                <Text style={styles.title}>{p.title}</Text>
                <Text style={styles.body}>{p.body}</Text>
              </View>
              <Ionicons name="chevron-forward" size={18} color={colors.textMuted} />
            </Pressable>
          ))}

          {notifications.map((n) => {
            const meta = TYPE_META[n.type] ?? TYPE_META.info;
            return (
              <Pressable
                key={n.id}
                style={({ pressed }) => [styles.item, !n.is_read && styles.unread, pressed && styles.pressed]}
                onPress={() => onTap(n)}
              >
                <View style={[styles.iconWrap, { backgroundColor: meta.tint }]}>
                  <Ionicons name={meta.icon} size={20} color={meta.color} />
                </View>
                <View style={styles.texts}>
                  <Text style={styles.title}>{n.title}</Text>
                  <Text style={styles.body}>{n.body}</Text>
                  {!!n.created_at && <Text style={styles.time}>{formatDate(n.created_at)}</Text>}
                </View>
                {!n.is_read && <View style={styles.dot} />}
              </Pressable>
            );
          })}
        </ScrollView>
      )}

      <TourGuide tourKey="notifications-v1" steps={NOTIF_TOUR} />
    </Screen>
  );
}

const NOTIF_TOUR: TourStep[] = [
  { icon: 'notifications', title: 'As tuas notificações', body: 'Aqui aparecem os teus avisos: acessos à conta, atualizações das transações e recursos.' },
  { icon: 'shield-checkmark', title: 'Segurança', body: 'Se entrares de um novo dispositivo ou local, avisamos-te aqui e por email. Se não foste tu, muda a palavra-passe.' },
  { icon: 'checkmark-done', title: 'Marcar como lidas', body: 'O ponto verde indica não lidas. Toca numa para abrir, ou usa "Marcar todas como lidas".' },
];

const styles = StyleSheet.create({
  markAll: { flexDirection: 'row', alignItems: 'center', gap: 6, alignSelf: 'flex-end', paddingVertical: spacing.sm },
  markAllText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.primaryBright },
  list: { paddingVertical: spacing.sm, gap: spacing.sm },
  item: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.md,
  },
  unread: { backgroundColor: colors.card, borderColor: colors.borderStrong },
  pressed: { opacity: 0.7 },
  iconWrap: { width: 40, height: 40, borderRadius: radius.md, alignItems: 'center', justifyContent: 'center' },
  texts: { flex: 1, gap: 2 },
  title: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.text },
  body: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, lineHeight: 16 },
  time: { fontFamily: fonts.body, fontSize: 10, color: colors.textFaint, marginTop: 2 },
  dot: { width: 9, height: 9, borderRadius: 5, backgroundColor: colors.primaryBright },
});
