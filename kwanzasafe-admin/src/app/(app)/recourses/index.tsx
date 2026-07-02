import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { ActivityIndicator, FlatList, Pressable, StyleSheet, Text, View } from 'react-native';

import { fetchRecourses } from '@/api/admin';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { formatDate } from '@/lib/format';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { RecourseItem } from '@/types/api';

const STATUS_COLOR: Record<string, string> = {
  open: colors.warning, in_review: colors.info, resolved: colors.success, rejected: colors.danger, cancelled: colors.textMuted,
};

export default function RecoursesScreen() {
  const router = useRouter();
  const { data, isLoading, isError, refetch, isRefetching } = useQuery({ queryKey: ['admin-recourses'], queryFn: fetchRecourses });

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Recursos" subtitle={data ? `${data.active.length} em aberto` : undefined} onBack={() => router.back()} />
      {isLoading ? (
        <ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} />
      ) : isError ? (
        <Pressable onPress={() => refetch()}><Text style={styles.err}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable>
      ) : (
        <FlatList
          data={data?.active ?? []}
          keyExtractor={(r) => String(r.id)}
          onRefresh={refetch}
          refreshing={isRefetching}
          contentContainerStyle={{ paddingBottom: spacing.xl, gap: spacing.sm }}
          ListHeaderComponent={<Text style={styles.section}>Em aberto</Text>}
          ListEmptyComponent={<Text style={styles.empty}>Sem recursos em aberto. 🎉</Text>}
          renderItem={({ item }) => <Row r={item} onPress={() => router.push(`/recourses/${item.id}`)} />}
          ListFooterComponent={
            (data?.resolved ?? []).length > 0 ? (
              <View>
                <Text style={[styles.section, { marginTop: spacing.lg }]}>Resolvidos recentemente</Text>
                {data!.resolved.map((r) => <Row key={r.id} r={r} onPress={() => router.push(`/recourses/${r.id}`)} />)}
              </View>
            ) : null
          }
        />
      )}
    </Screen>
  );
}

function Row({ r, onPress }: { r: RecourseItem; onPress: () => void }) {
  const tone = STATUS_COLOR[r.status] ?? colors.textMuted;
  return (
    <Pressable onPress={onPress} style={styles.row}>
      <View style={styles.rowTop}>
        <Text style={styles.ref}>#{r.reference_id ?? r.transaction_id}</Text>
        <View style={[styles.badge, { backgroundColor: colors.surfaceAlt }]}><Text style={[styles.badgeText, { color: tone }]}>{r.status_label}</Text></View>
      </View>
      <Text style={styles.client} numberOfLines={1}>{r.client_name}</Text>
      {!!r.reason && <Text style={styles.reason} numberOfLines={2}>{r.reason}</Text>}
      <Text style={styles.date}>{formatDate(r.created_at)}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginTop: spacing.md },
  section: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted, marginBottom: spacing.sm },
  empty: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', marginTop: spacing.lg },
  row: { backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md, gap: 3, marginBottom: spacing.sm },
  rowTop: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  ref: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  client: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  reason: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.text },
  date: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textFaint },
  badge: { paddingHorizontal: 10, paddingVertical: 3, borderRadius: radius.pill },
  badgeText: { fontFamily: fonts.bodyBold, fontSize: fontSize.xs },
});
