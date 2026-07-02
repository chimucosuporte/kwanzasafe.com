import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, StyleSheet, Text, View } from 'react-native';

import { fetchTransactions } from '@/api/admin';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { StatusBadge } from '@/components/StatusBadge';
import { formatAmount, formatDate } from '@/lib/format';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { AdminTransaction } from '@/types/api';

const FILTERS = [
  { key: 'pending', label: 'Em curso' },
  { key: 'completed', label: 'Concluídas' },
  { key: 'cancelled', label: 'Canceladas' },
];

export default function TransactionsScreen() {
  const router = useRouter();
  const [status, setStatus] = useState('pending');
  const [assigned, setAssigned] = useState('all');

  const { data, isLoading, isError, refetch, isRefetching } = useQuery({
    queryKey: ['admin-transactions', status, assigned],
    queryFn: () => fetchTransactions({ status, assigned }),
  });

  return (
    <Screen>
      <Header title="Transações" />

      <View style={styles.filters}>
        {FILTERS.map((f) => (
          <Pressable key={f.key} onPress={() => setStatus(f.key)} style={[styles.pill, status === f.key && styles.pillOn]}>
            <Text style={[styles.pillText, status === f.key && styles.pillTextOn]}>{f.label}</Text>
          </Pressable>
        ))}
      </View>
      <View style={styles.filters}>
        {[{ k: 'all', l: 'Todas' }, { k: 'mine', l: 'Minhas' }, { k: 'unassigned', l: 'Sem agente' }].map((a) => (
          <Pressable key={a.k} onPress={() => setAssigned(a.k)} style={[styles.pillSm, assigned === a.k && styles.pillOn]}>
            <Text style={[styles.pillText, assigned === a.k && styles.pillTextOn]}>{a.l}</Text>
          </Pressable>
        ))}
      </View>

      {isLoading ? (
        <ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} />
      ) : isError ? (
        <Pressable onPress={() => refetch()} style={styles.errBox}><Text style={styles.errText}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable>
      ) : (
        <FlatList
          data={data?.data ?? []}
          keyExtractor={(t) => String(t.id)}
          onRefresh={refetch}
          refreshing={isRefetching}
          contentContainerStyle={{ paddingBottom: spacing.xl, gap: spacing.sm }}
          ListEmptyComponent={<Text style={styles.empty}>Sem transações neste filtro.</Text>}
          renderItem={({ item }) => <Row tx={item} onPress={() => router.push(`/transaction/${item.id}`)} />}
        />
      )}
    </Screen>
  );
}

function Row({ tx, onPress }: { tx: AdminTransaction; onPress: () => void }) {
  return (
    <Pressable onPress={onPress} style={styles.row}>
      <View style={styles.rowTop}>
        <Text style={styles.ref}>#{tx.reference_id}</Text>
        <StatusBadge status={tx.status} label={tx.status_label} />
      </View>
      <Text style={styles.client} numberOfLines={1}>{tx.client_name} · {tx.client_email}</Text>
      <View style={styles.rowBottom}>
        <Text style={styles.amount}>{formatAmount(tx.amount_sent)} {tx.currency_from} → {formatAmount(tx.amount_received, 0)} AOA</Text>
        {tx.unread_count > 0 && <View style={styles.dot}><Text style={styles.dotText}>{tx.unread_count}</Text></View>}
      </View>
      <Text style={styles.date}>{formatDate(tx.created_at)}{tx.agent_name ? ` · ${tx.agent_name}` : ''}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  filters: { flexDirection: 'row', gap: spacing.sm, marginBottom: spacing.sm, flexWrap: 'wrap' },
  pill: { paddingVertical: 6, paddingHorizontal: 14, borderRadius: radius.pill, borderWidth: 1, borderColor: colors.border },
  pillSm: { paddingVertical: 5, paddingHorizontal: 12, borderRadius: radius.pill, borderWidth: 1, borderColor: colors.border },
  pillOn: { backgroundColor: colors.primaryTint, borderColor: colors.primary },
  pillText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.textMuted },
  pillTextOn: { color: colors.primaryBright },

  row: { backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md, gap: 4 },
  rowTop: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  ref: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  client: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  rowBottom: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 2 },
  amount: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text, flex: 1 },
  dot: { backgroundColor: colors.primary, borderRadius: radius.pill, minWidth: 20, height: 20, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 5 },
  dotText: { fontFamily: fonts.bodyBold, fontSize: 11, color: colors.white },
  date: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textFaint },

  empty: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', marginTop: spacing.xl },
  errBox: { backgroundColor: colors.dangerTint, borderRadius: radius.md, padding: spacing.md, marginTop: spacing.md },
  errText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, textAlign: 'center' },
});
