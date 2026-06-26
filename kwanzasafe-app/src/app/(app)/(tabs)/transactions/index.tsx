import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { FlatList, RefreshControl, StyleSheet, View } from 'react-native';

import { fetchTransactions } from '@/api/transactions';
import { EmptyState } from '@/components/EmptyState';
import { ErrorState } from '@/components/ErrorState';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { SkeletonCard } from '@/components/Skeleton';
import { TransactionRow } from '@/components/TransactionRow';
import { colors, spacing } from '@/theme';

/** Lista completa das transações do utilizador. */
export default function TransactionsScreen() {
  const router = useRouter();
  const { data, isLoading, isError, refetch, isRefetching } = useQuery({
    queryKey: ['transactions'],
    queryFn: fetchTransactions,
  });

  return (
    <Screen edges={['top']}>
      <Header title="As minhas transações" />

      {isLoading ? (
        <View style={styles.skeletonList}>
          <SkeletonCard />
          <SkeletonCard />
          <SkeletonCard />
        </View>
      ) : isError ? (
        <ErrorState
          title="Não foi possível carregar"
          description="As tuas transações não carregaram. Verifica a ligação e tenta novamente."
          onRetry={() => refetch()}
          retrying={isRefetching}
        />
      ) : (
        <FlatList
          data={data}
          keyExtractor={(t) => String(t.id)}
          renderItem={({ item }) => (
            <TransactionRow
              tx={item}
              onPress={() =>
                router.push({ pathname: '/transactions/[ref]', params: { ref: item.reference_id } })
              }
            />
          )}
          contentContainerStyle={styles.list}
          ItemSeparatorComponent={() => <View style={styles.sep} />}
          ListEmptyComponent={
            <EmptyState
              icon="swap-horizontal-outline"
              title="Ainda não tens transações"
              description="Quando iniciares uma conversão, ela aparece aqui."
            />
          }
          refreshControl={
            <RefreshControl refreshing={isRefetching} onRefresh={refetch} tintColor={colors.primary} />
          }
          showsVerticalScrollIndicator={false}
        />
      )}
    </Screen>
  );
}

const styles = StyleSheet.create({
  skeletonList: { paddingVertical: spacing.md, gap: spacing.sm },
  list: { paddingVertical: spacing.md, flexGrow: 1 },
  sep: { height: spacing.sm },
});
