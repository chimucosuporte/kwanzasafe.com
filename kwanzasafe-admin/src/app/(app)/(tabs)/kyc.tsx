import { Ionicons } from '@expo/vector-icons';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { ActivityIndicator, FlatList, Pressable, StyleSheet, Text, View } from 'react-native';

import { fetchKycList } from '@/api/admin';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { formatDate } from '@/lib/format';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { KycListItem } from '@/types/api';

export default function KycScreen() {
  const router = useRouter();
  const { data, isLoading, isError, refetch, isRefetching } = useQuery({
    queryKey: ['admin-kyc'],
    queryFn: fetchKycList,
  });

  const pending = data?.pending ?? [];
  const approved = data?.approved ?? [];

  return (
    <Screen>
      <Header title="Verificações KYC" subtitle={`${pending.length} por rever`} />
      {isLoading ? (
        <ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} />
      ) : isError ? (
        <Pressable onPress={() => refetch()} style={styles.errBox}><Text style={styles.errText}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable>
      ) : (
        <FlatList
          data={pending}
          keyExtractor={(u) => String(u.id)}
          onRefresh={refetch}
          refreshing={isRefetching}
          contentContainerStyle={{ paddingBottom: spacing.xl, gap: spacing.sm }}
          ListHeaderComponent={<Text style={styles.section}>Pendentes de revisão</Text>}
          ListEmptyComponent={<Text style={styles.empty}>Nada por rever. 🎉</Text>}
          renderItem={({ item }) => <Row u={item} onPress={() => router.push(`/kyc/${item.id}`)} />}
          ListFooterComponent={
            approved.length > 0 ? (
              <View>
                <Text style={[styles.section, { marginTop: spacing.lg }]}>Aprovados recentemente</Text>
                {approved.map((u) => <Row key={u.id} u={u} onPress={() => router.push(`/kyc/${u.id}`)} />)}
              </View>
            ) : null
          }
        />
      )}
    </Screen>
  );
}

function Row({ u, onPress }: { u: KycListItem; onPress: () => void }) {
  return (
    <Pressable onPress={onPress} style={styles.row}>
      <View style={styles.avatar}><Ionicons name="person" size={18} color={colors.primaryBright} /></View>
      <View style={{ flex: 1 }}>
        <Text style={styles.name} numberOfLines={1}>{u.full_name ?? '—'}</Text>
        <Text style={styles.email} numberOfLines={1}>{u.email}</Text>
        <Text style={styles.meta}>BI: {u.bi_number ?? '—'} · Score {u.kyc_score ?? 0} · {formatDate(u.submitted_at)}</Text>
      </View>
      {u.is_verified
        ? <Ionicons name="checkmark-circle" size={22} color={colors.success} />
        : <Ionicons name="chevron-forward" size={20} color={colors.textFaint} />}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  section: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted, marginBottom: spacing.sm },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md, marginBottom: spacing.sm },
  avatar: { width: 40, height: 40, borderRadius: radius.pill, backgroundColor: colors.primaryTint, alignItems: 'center', justifyContent: 'center' },
  name: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  email: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  meta: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textFaint, marginTop: 2 },
  empty: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', marginTop: spacing.lg },
  errBox: { backgroundColor: colors.dangerTint, borderRadius: radius.md, padding: spacing.md, marginTop: spacing.md },
  errText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, textAlign: 'center' },
});
