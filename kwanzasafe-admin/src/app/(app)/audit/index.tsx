import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, StyleSheet, Text, View } from 'react-native';

import { fetchAudit } from '@/api/admin';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { formatDate } from '@/lib/format';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { AuditEntry } from '@/types/api';

const CATS = ['', 'auth', 'kyc', 'transaction', 'admin', 'profile', 'beneficiary'];
const SEV_COLOR: Record<string, string> = { info: colors.textMuted, warning: colors.warning, critical: colors.danger };

export default function AuditScreen() {
  const router = useRouter();
  const [category, setCategory] = useState('');
  const [severity, setSeverity] = useState('');

  const { data, isLoading, isError, refetch, isRefetching } = useQuery({
    queryKey: ['admin-audit', category, severity],
    queryFn: () => fetchAudit({ category: category || undefined, severity: severity || undefined }),
  });

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Auditoria" subtitle="Registo de segurança (AML)" onBack={() => router.back()} />

      {data?.stats && (
        <View style={styles.stats}>
          <Stat label="24h" value={data.stats.total_24h} />
          <Stat label="Críticos 24h" value={data.stats.critical_24h} tone={colors.danger} />
          <Stat label="Logins falhados" value={data.stats.failed_logins} tone={colors.warning} />
          <Stat label="Fraude 7d" value={data.stats.fraud_attempts} tone={colors.danger} />
        </View>
      )}

      <View style={styles.filters}>
        {CATS.map((c) => (
          <Pressable key={c || 'all'} onPress={() => setCategory(c)} style={[styles.pill, category === c && styles.pillOn]}>
            <Text style={[styles.pillText, category === c && styles.pillTextOn]}>{c || 'Todas'}</Text>
          </Pressable>
        ))}
      </View>
      <View style={styles.filters}>
        {['', 'info', 'warning', 'critical'].map((s) => (
          <Pressable key={s || 'all'} onPress={() => setSeverity(s)} style={[styles.pillSm, severity === s && styles.pillOn]}>
            <Text style={[styles.pillText, severity === s && styles.pillTextOn]}>{s || 'Todas severidades'}</Text>
          </Pressable>
        ))}
      </View>

      {isLoading ? (
        <ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} />
      ) : isError ? (
        <Pressable onPress={() => refetch()}><Text style={styles.err}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable>
      ) : (
        <FlatList
          data={data?.data ?? []}
          keyExtractor={(l) => String(l.id)}
          onRefresh={refetch}
          refreshing={isRefetching}
          contentContainerStyle={{ paddingBottom: spacing.xl, gap: spacing.sm }}
          ListEmptyComponent={<Text style={styles.empty}>Sem registos para este filtro.</Text>}
          renderItem={({ item }) => <Row l={item} />}
        />
      )}
    </Screen>
  );
}

function Row({ l }: { l: AuditEntry }) {
  const tone = SEV_COLOR[l.severity] ?? colors.textMuted;
  return (
    <View style={styles.row}>
      <View style={styles.rowTop}>
        <Text style={styles.action}>{l.action}</Text>
        <View style={[styles.sevDot, { backgroundColor: tone }]} />
      </View>
      {!!l.description && <Text style={styles.desc} numberOfLines={2}>{l.description}</Text>}
      <Text style={styles.meta}>{[l.user_email, l.ip_address, formatDate(l.created_at)].filter(Boolean).join(' · ')}</Text>
    </View>
  );
}

function Stat({ label, value, tone }: { label: string; value: number; tone?: string }) {
  return (
    <View style={styles.stat}>
      <Text style={[styles.statValue, tone ? { color: tone } : null]}>{value}</Text>
      <Text style={styles.statLabel}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  stats: { flexDirection: 'row', gap: spacing.xs, marginBottom: spacing.sm },
  stat: { flex: 1, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.md, padding: spacing.sm, alignItems: 'center' },
  statValue: { fontFamily: fonts.display, fontSize: fontSize.lg, color: colors.text },
  statLabel: { fontFamily: fonts.body, fontSize: 10, color: colors.textMuted, marginTop: 2, textAlign: 'center' },
  filters: { flexDirection: 'row', gap: spacing.xs, marginBottom: spacing.sm, flexWrap: 'wrap' },
  pill: { paddingVertical: 5, paddingHorizontal: 12, borderRadius: radius.pill, borderWidth: 1, borderColor: colors.border },
  pillSm: { paddingVertical: 4, paddingHorizontal: 10, borderRadius: radius.pill, borderWidth: 1, borderColor: colors.border },
  pillOn: { backgroundColor: colors.primaryTint, borderColor: colors.primary },
  pillText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.xs, color: colors.textMuted },
  pillTextOn: { color: colors.primaryBright },
  row: { backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.md, padding: spacing.md, gap: 3 },
  rowTop: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  action: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.text },
  sevDot: { width: 8, height: 8, borderRadius: 4 },
  desc: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  meta: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textFaint },
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginTop: spacing.md },
  empty: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', marginTop: spacing.xl },
});
