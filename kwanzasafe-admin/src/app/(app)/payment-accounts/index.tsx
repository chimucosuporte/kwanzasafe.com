import { Ionicons } from '@expo/vector-icons';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { ActivityIndicator, FlatList, Pressable, StyleSheet, Text, View } from 'react-native';

import { fetchPaymentAccounts } from '@/api/admin';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { AdminPaymentAccount } from '@/types/api';

export default function PaymentAccountsScreen() {
  const router = useRouter();
  const { data, isLoading, isError, refetch, isRefetching } = useQuery({ queryKey: ['admin-payment-accounts'], queryFn: fetchPaymentAccounts });

  return (
    <Screen edges={['top', 'bottom']}>
      <Header
        title="Contas de recepção"
        subtitle="Dados de pagamento por moeda"
        onBack={() => router.back()}
        right={<Pressable onPress={() => router.push('/payment-accounts/new')} hitSlop={8}><Ionicons name="add-circle" size={28} color={colors.primaryBright} /></Pressable>}
      />
      {isLoading ? (
        <ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} />
      ) : isError ? (
        <Pressable onPress={() => refetch()}><Text style={styles.err}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable>
      ) : (
        <FlatList
          data={data?.data ?? []}
          keyExtractor={(a) => String(a.id)}
          onRefresh={refetch}
          refreshing={isRefetching}
          contentContainerStyle={{ paddingBottom: spacing.xl, gap: spacing.sm }}
          ListEmptyComponent={<Text style={styles.empty}>Sem contas de recepção. Adiciona uma para os clientes poderem pagar.</Text>}
          renderItem={({ item }) => <Row a={item} onPress={() => router.push(`/payment-accounts/${item.id}`)} />}
        />
      )}
    </Screen>
  );
}

function Row({ a, onPress }: { a: AdminPaymentAccount; onPress: () => void }) {
  return (
    <Pressable onPress={onPress} style={styles.row}>
      <View style={styles.cur}><Text style={styles.curText}>{a.currency}</Text></View>
      <View style={{ flex: 1 }}>
        <Text style={styles.holder} numberOfLines={1}>{a.holder}</Text>
        <Text style={styles.ident} numberOfLines={1}>{a.identifier}{a.network ? ` · ${a.network}` : ''}</Text>
      </View>
      <View style={[styles.badge, a.is_active ? styles.on : styles.off]}>
        <Text style={[styles.badgeText, { color: a.is_active ? colors.success : colors.textMuted }]}>{a.is_active ? 'Ativa' : 'Inativa'}</Text>
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginTop: spacing.md },
  empty: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', marginTop: spacing.xl, paddingHorizontal: spacing.lg, lineHeight: 20 },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md },
  cur: { width: 48, height: 48, borderRadius: radius.md, backgroundColor: colors.primaryTint, alignItems: 'center', justifyContent: 'center' },
  curText: { fontFamily: fonts.displaySemi, fontSize: fontSize.sm, color: colors.primaryBright },
  holder: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  ident: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: radius.pill },
  on: { backgroundColor: colors.successTint },
  off: { backgroundColor: colors.surfaceAlt },
  badgeText: { fontFamily: fonts.bodyBold, fontSize: fontSize.xs },
});
