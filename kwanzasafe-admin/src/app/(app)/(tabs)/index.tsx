import { Ionicons } from '@expo/vector-icons';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';

import { fetchStats } from '@/api/admin';
import { Screen } from '@/components/Screen';
import { formatAmount } from '@/lib/format';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

const PERIODS = [
  { key: 'today', label: 'Hoje' },
  { key: 'week', label: '7 dias' },
  { key: 'all', label: 'Total' },
];

export default function DashboardScreen() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const [period, setPeriod] = useState('today');
  const { data, isLoading, isError, refetch, isRefetching } = useQuery({
    queryKey: ['admin-stats', period],
    queryFn: () => fetchStats(period),
  });

  const s = data?.stats;

  return (
    <Screen>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: spacing.xl }}>
        <Text style={styles.hi}>Painel de administração</Text>
        <Text style={styles.name}>{user?.full_name ?? user?.email}</Text>

        <View style={styles.periods}>
          {PERIODS.map((p) => (
            <Pressable key={p.key} onPress={() => setPeriod(p.key)} style={[styles.period, period === p.key && styles.periodOn]}>
              <Text style={[styles.periodText, period === p.key && styles.periodTextOn]}>{p.label}</Text>
            </Pressable>
          ))}
        </View>

        {isLoading ? (
          <ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} />
        ) : isError ? (
          <Pressable onPress={() => refetch()} style={styles.errBox}>
            <Text style={styles.errText}>Não foi possível carregar. Toca para tentar de novo.</Text>
          </Pressable>
        ) : s ? (
          <>
            <View style={styles.volCard}>
              <Text style={styles.volLabel}>Volume recebido ({s.period_label})</Text>
              <Text style={styles.volValue}>{formatAmount(s.volume_aoa, 0)} <Text style={styles.volCur}>AOA</Text></Text>
              {s.volume_by_currency.length > 0 && (
                <Text style={styles.volSub}>
                  {s.volume_by_currency.map((v) => `${formatAmount(v.total, 0)} ${v.currency}`).join(' · ')}
                </Text>
              )}
            </View>

            <View style={styles.grid}>
              <Kpi label="Transações pendentes" value={s.tx_pending} tone={colors.warning} />
              <Kpi label="Concluídas" value={s.tx_completed} tone={colors.success} />
              <Kpi label="KYC por rever" value={s.kyc_pending} tone={colors.info} />
              <Kpi label="Mensagens não lidas" value={s.unread_chats} tone={colors.primaryBright} />
              <Kpi label="Utilizadores" value={s.total_users} tone={colors.text} />
              <Kpi label="Canceladas" value={s.tx_cancelled} tone={colors.danger} />
            </View>

            <Text style={styles.mgmtTitle}>Gestão</Text>
            <View style={styles.mgmtGrid}>
              <MgmtCard icon="trending-up" label="Taxas" onPress={() => router.push('/rates')} />
              <MgmtCard icon="people" label="Utilizadores" onPress={() => router.push('/users')} />
              <MgmtCard icon="document-text" label="Auditoria" onPress={() => router.push('/audit')} />
              {user?.is_super_admin && (
                <MgmtCard icon="card" label="Contas de pagamento" onPress={() => router.push('/payment-accounts')} />
              )}
            </View>
          </>
        ) : null}

        {isRefetching && <Text style={styles.refresh}>A atualizar…</Text>}
      </ScrollView>
    </Screen>
  );
}

function Kpi({ label, value, tone }: { label: string; value: number; tone: string }) {
  return (
    <View style={styles.kpi}>
      <Text style={[styles.kpiValue, { color: tone }]}>{value}</Text>
      <Text style={styles.kpiLabel}>{label}</Text>
    </View>
  );
}

function MgmtCard({ icon, label, onPress }: { icon: any; label: string; onPress: () => void }) {
  return (
    <Pressable style={styles.mgmt} onPress={onPress}>
      <Ionicons name={icon} size={22} color={colors.primaryBright} />
      <Text style={styles.mgmtLabel}>{label}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  hi: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, marginTop: spacing.md },
  name: { fontFamily: fonts.display, fontSize: fontSize.xl, color: colors.text, marginBottom: spacing.md },
  periods: { flexDirection: 'row', gap: spacing.sm, marginBottom: spacing.md },
  period: { paddingVertical: 6, paddingHorizontal: 14, borderRadius: radius.pill, borderWidth: 1, borderColor: colors.border },
  periodOn: { backgroundColor: colors.primaryTint, borderColor: colors.primary },
  periodText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.textMuted },
  periodTextOn: { color: colors.primaryBright },

  volCard: { backgroundColor: colors.card, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.lg, marginBottom: spacing.md },
  volLabel: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.textMuted },
  volValue: { fontFamily: fonts.display, fontSize: fontSize.display, color: colors.text, marginTop: spacing.xs },
  volCur: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.textMuted },
  volSub: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, marginTop: spacing.xs },

  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
  kpi: { flexGrow: 1, flexBasis: '47%', backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md },
  kpiValue: { fontFamily: fonts.display, fontSize: fontSize.xxl },
  kpiLabel: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, marginTop: 2 },

  mgmtTitle: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted, marginTop: spacing.lg, marginBottom: spacing.sm },
  mgmtGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
  mgmt: { flexGrow: 1, flexBasis: '47%', flexDirection: 'row', alignItems: 'center', gap: spacing.sm, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md },
  mgmtLabel: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.text, flexShrink: 1 },

  errBox: { backgroundColor: colors.dangerTint, borderRadius: radius.md, padding: spacing.md, marginTop: spacing.md },
  errText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, textAlign: 'center' },
  refresh: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, textAlign: 'center', marginTop: spacing.md },
});
