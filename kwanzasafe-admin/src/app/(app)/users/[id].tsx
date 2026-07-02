import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { ActivityIndicator, Alert, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';

import { fetchUser, toggleUserAdmin } from '@/api/admin';
import { apiErrorMessage } from '@/api/client';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { formatAmount, formatDate } from '@/lib/format';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

export default function UserDetailScreen() {
  const { id: idParam } = useLocalSearchParams<{ id: string }>();
  const id = Number(idParam);
  const router = useRouter();
  const qc = useQueryClient();

  const { data: u, isLoading, isError, refetch } = useQuery({ queryKey: ['admin-user', id], queryFn: () => fetchUser(id) });

  const toggleM = useMutation({
    mutationFn: () => toggleUserAdmin(id),
    onSuccess: (updated) => {
      qc.setQueryData(['admin-user', id], updated);
      void qc.invalidateQueries({ queryKey: ['admin-users'] });
    },
    onError: (e) => Alert.alert('Erro', apiErrorMessage(e)),
  });

  const onToggle = () => {
    const to = u?.is_admin ? 'remover o acesso de staff' : 'promover a staff (admin)';
    Alert.alert('Alterar papel', `Confirmas ${to} de ${u?.email}?`, [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Confirmar', onPress: () => toggleM.mutate() },
    ]);
  };

  if (isLoading) return <Screen edges={['top', 'bottom']}><ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} /></Screen>;
  if (isError || !u) return <Screen edges={['top', 'bottom']}><Header title="Utilizador" onBack={() => router.back()} /><Pressable onPress={() => refetch()}><Text style={styles.err}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable></Screen>;

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Utilizador" onBack={() => router.back()} />
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: spacing.xxl, gap: spacing.md }}>
        <View style={styles.head}>
          <View style={styles.avatar}><Text style={styles.avatarText}>{(u.full_name ?? u.email).charAt(0).toUpperCase()}</Text></View>
          <Text style={styles.name}>{u.full_name ?? '—'}</Text>
          <Text style={styles.email}>{u.email}</Text>
          <View style={styles.tags}>
            {!!u.role_label && <Tag text={u.role_label} tone={colors.info} />}
            <Tag text={u.is_verified ? 'KYC verificado' : (u.kyc_pending ? 'KYC pendente' : 'Sem KYC')} tone={u.is_verified ? colors.success : (u.kyc_pending ? colors.warning : colors.textMuted)} />
            <Tag text={u.email_verified ? 'Email ✓' : 'Email por confirmar'} tone={u.email_verified ? colors.success : colors.warning} />
          </View>
        </View>

        {u.tx_stats && (
          <View style={styles.statsRow}>
            <Stat label="Transações" value={u.tx_stats.total} />
            <Stat label="Concluídas" value={u.tx_stats.completed} tone={colors.success} />
            <Stat label="Ativas" value={u.tx_stats.active} tone={colors.warning} />
          </View>
        )}

        <View style={styles.card}>
          <Field k="Saldo" v={`${formatAmount(u.balance)} AOA`} />
          <Field k="Telefone" v={u.phone_number ? `${u.phone_number}${u.phone_verified ? ' ✓' : ''}` : null} />
          <Field k="Nº BI" v={u.bi_number} />
          <Field k="Província" v={u.province} />
          <Field k="País" v={u.country} />
          <Field k="Score KYC" v={u.kyc_score != null ? `${u.kyc_score}/100` : null} />
          <Field k="Registo" v={formatDate(u.created_at)} />
        </View>

        <Button
          label={u.is_admin ? 'Remover acesso de staff' : 'Promover a staff (admin)'}
          variant={u.is_admin ? 'danger' : 'primary'}
          onPress={onToggle}
          loading={toggleM.isPending}
        />
      </ScrollView>
    </Screen>
  );
}

function Tag({ text, tone }: { text: string; tone: string }) {
  return <View style={[styles.tag, { borderColor: tone }]}><Text style={[styles.tagText, { color: tone }]}>{text}</Text></View>;
}
function Stat({ label, value, tone }: { label: string; value: number; tone?: string }) {
  return (
    <View style={styles.stat}>
      <Text style={[styles.statValue, tone ? { color: tone } : null]}>{value}</Text>
      <Text style={styles.statLabel}>{label}</Text>
    </View>
  );
}
function Field({ k, v }: { k: string; v: string | null | undefined }) {
  return (
    <View style={styles.field}>
      <Text style={styles.fieldK}>{k}</Text>
      <Text style={styles.fieldV}>{v || '—'}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginTop: spacing.md },
  head: { alignItems: 'center', gap: 2 },
  avatar: { width: 72, height: 72, borderRadius: radius.pill, backgroundColor: colors.primaryTint, alignItems: 'center', justifyContent: 'center', marginBottom: spacing.sm },
  avatarText: { fontFamily: fonts.display, fontSize: fontSize.xxl, color: colors.primaryBright },
  name: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  email: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  tags: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.xs, justifyContent: 'center', marginTop: spacing.sm },
  tag: { borderWidth: 1, borderRadius: radius.pill, paddingHorizontal: 10, paddingVertical: 3 },
  tagText: { fontFamily: fonts.bodyBold, fontSize: 11 },
  statsRow: { flexDirection: 'row', gap: spacing.sm },
  stat: { flex: 1, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md, alignItems: 'center' },
  statValue: { fontFamily: fonts.display, fontSize: fontSize.xl, color: colors.text },
  statLabel: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, marginTop: 2 },
  card: { backgroundColor: colors.card, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md },
  field: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 6, borderBottomWidth: 1, borderBottomColor: colors.border, gap: spacing.md },
  fieldK: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  fieldV: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text, flexShrink: 1, textAlign: 'right' },
});
