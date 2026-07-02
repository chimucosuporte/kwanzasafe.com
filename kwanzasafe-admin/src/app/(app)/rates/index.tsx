import { Ionicons } from '@expo/vector-icons';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { ActivityIndicator, FlatList, Pressable, StyleSheet, Text, View } from 'react-native';

import { fetchRates } from '@/api/admin';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { formatAmount } from '@/lib/format';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { AdminRate } from '@/types/api';

export default function RatesScreen() {
  const router = useRouter();
  const { data, isLoading, isError, refetch, isRefetching } = useQuery({ queryKey: ['admin-rates'], queryFn: fetchRates });

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Taxas de câmbio" onBack={() => router.back()} />
      {isLoading ? (
        <ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} />
      ) : isError ? (
        <Pressable onPress={() => refetch()}><Text style={styles.err}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable>
      ) : (
        <FlatList
          data={data ?? []}
          keyExtractor={(r) => String(r.id)}
          onRefresh={refetch}
          refreshing={isRefetching}
          contentContainerStyle={{ paddingBottom: spacing.xl, gap: spacing.sm }}
          renderItem={({ item }) => <Row r={item} onPress={() => router.push(`/rates/${item.id}`)} />}
        />
      )}
    </Screen>
  );
}

function Row({ r, onPress }: { r: AdminRate; onPress: () => void }) {
  return (
    <Pressable onPress={onPress} style={styles.row}>
      <View style={{ flex: 1 }}>
        <Text style={styles.pair}>1 {r.currency_from} → {r.currency_to}</Text>
        <Text style={styles.rate}>{formatAmount(r.rate, 4)} Kz</Text>
      </View>
      <View style={[styles.badge, r.is_active ? styles.on : styles.off]}>
        <Text style={[styles.badgeText, { color: r.is_active ? colors.success : colors.textMuted }]}>{r.is_active ? 'Ativa' : 'Inativa'}</Text>
      </View>
      <Ionicons name="chevron-forward" size={20} color={colors.textFaint} />
    </Pressable>
  );
}

const styles = StyleSheet.create({
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginTop: spacing.md },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md },
  pair: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  rate: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.primaryBright, marginTop: 2 },
  badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: radius.pill },
  on: { backgroundColor: colors.successTint },
  off: { backgroundColor: colors.surfaceAlt },
  badgeText: { fontFamily: fonts.bodyBold, fontSize: fontSize.xs },
});
