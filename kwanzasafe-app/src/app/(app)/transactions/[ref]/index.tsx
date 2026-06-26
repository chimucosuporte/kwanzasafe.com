import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useState } from 'react';
import {
  Alert,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import { apiErrorMessage } from '@/api/client';
import { uploadReceipt } from '@/api/chat';
import { cancelTransaction, confirmTransaction, fetchTransaction } from '@/api/transactions';
import { Button } from '@/components/Button';
import { Countdown } from '@/components/Countdown';
import { ErrorState } from '@/components/ErrorState';
import { Header } from '@/components/Header';
import { RecourseSection } from '@/components/RecourseSection';
import { Screen } from '@/components/Screen';
import { TransactionTimeline } from '@/components/TransactionTimeline';
import { SkeletonCard } from '@/components/Skeleton';
import { StatusBadge } from '@/components/StatusBadge';
import { formatAmount, formatDate } from '@/lib/format';
import { pickAttachment } from '@/lib/picker';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { Transaction } from '@/types/api';

/** Detalhe / sala de uma transação: montantes, pagamento, comprovativo e acções. */
export default function TransactionDetailScreen() {
  const { ref } = useLocalSearchParams<{ ref: string }>();
  const router = useRouter();
  const queryClient = useQueryClient();
  const [actionError, setActionError] = useState<string | null>(null);

  const { data: tx, isLoading, isError, refetch, isRefetching } = useQuery({
    queryKey: ['transaction', ref],
    queryFn: () => fetchTransaction(ref!),
    enabled: !!ref,
  });

  const onUpdated = (updated: Transaction) => {
    queryClient.setQueryData(['transaction', ref], updated);
    void queryClient.invalidateQueries({ queryKey: ['transactions'] });
  };

  const confirmM = useMutation({
    mutationFn: () => confirmTransaction(ref!),
    onSuccess: onUpdated,
    onError: (e) => setActionError(apiErrorMessage(e)),
  });

  const cancelM = useMutation({
    mutationFn: () => cancelTransaction(ref!),
    onSuccess: onUpdated,
    onError: (e) => setActionError(apiErrorMessage(e)),
  });

  const receiptM = useMutation({
    mutationFn: async () => {
      const file = await pickAttachment();
      if (!file) return null;
      return uploadReceipt(ref!, file);
    },
    onSuccess: (updated) => {
      if (updated) {
        onUpdated(updated);
        Alert.alert('Comprovativo enviado', 'O agente vai validar o teu pagamento.');
      }
    },
    onError: (e) => setActionError(apiErrorMessage(e)),
  });

  const busy = confirmM.isPending || cancelM.isPending || receiptM.isPending;

  const onConfirm = () =>
    Alert.alert('Confirmar recepção', 'Confirmas que recebeste os Kwanzas?', [
      { text: 'Voltar', style: 'cancel' },
      { text: 'Confirmar', onPress: () => { setActionError(null); confirmM.mutate(); } },
    ]);

  const onCancel = () =>
    Alert.alert('Cancelar transação', 'Tens a certeza que queres cancelar esta transação?', [
      { text: 'Voltar', style: 'cancel' },
      {
        text: 'Cancelar transação',
        style: 'destructive',
        onPress: () => { setActionError(null); cancelM.mutate(); },
      },
    ]);

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title={tx?.reference_id ?? 'Transação'} onBack={() => router.back()} />

      {isLoading ? (
        <View style={styles.skeleton}>
          <SkeletonCard />
          <SkeletonCard />
        </View>
      ) : isError || !tx ? (
        <ErrorState
          title="Não foi possível carregar"
          description="Esta transação não carregou. Verifica a ligação e tenta novamente."
          onRetry={() => refetch()}
          retrying={isRefetching}
        />
      ) : (
        <ScrollView
          contentContainerStyle={styles.scroll}
          showsVerticalScrollIndicator={false}
          refreshControl={
            <RefreshControl refreshing={isRefetching} onRefresh={refetch} tintColor={colors.primary} />
          }
        >
          <View style={styles.statusRow}>
            <StatusBadge status={tx.status} label={tx.status_label} />
            <Text style={styles.date}>{formatDate(tx.created_at)}</Text>
          </View>

          <Countdown expiresAt={tx.expires_at} status={tx.status} />

          <Button
            label="Abrir conversa com o agente"
            onPress={() => router.push({ pathname: '/transactions/[ref]/chat', params: { ref: tx.reference_id } })}
          />

          <TransactionTimeline tx={tx} />

          <View style={styles.card}>
            <Row label="Envias" value={`${formatAmount(tx.amount_sent)} ${tx.currency_from}`} />
            <Row label="Taxa aplicada" value={`${formatAmount(tx.rate_applied)} AOA`} />
            {Number(tx.fee_amount) > 0 && (
              <Row label="Comissão" value={`${formatAmount(tx.fee_amount)} ${tx.currency_from}`} />
            )}
            <View style={styles.divider} />
            <Row label="Recebes" value={`${formatAmount(tx.amount_received)} ${tx.currency_to}`} highlight />
          </View>

          {tx.destination && (
            <View style={styles.card}>
              <Text style={styles.cardTitle}>Recebes em</Text>
              <Row label="Destino" value={tx.destination.label} />
              <Row label={tx.destination.type === 'bank' ? 'IBAN' : 'Identificador'} value={tx.destination.identifier} />
              <Row label="Titular" value={tx.destination.holder} />
              {tx.destination.network && <Row label="Rede" value={tx.destination.network} />}
            </View>
          )}

          {tx.payment_account && (
            <View style={styles.card}>
              <Text style={styles.cardTitle}>Onde pagar</Text>
              {tx.payment_account.holder && <Row label="Titular" value={tx.payment_account.holder} />}
              {tx.payment_account.identifier && <Row label="Conta / IBAN" value={tx.payment_account.identifier} />}
              {tx.payment_account.network && <Row label="Rede" value={tx.payment_account.network} />}
              {tx.payment_account.instructions && (
                <Text style={styles.instructions}>{tx.payment_account.instructions}</Text>
              )}
            </View>
          )}

          <RecourseSection reference={tx.reference_id} />

          {!!actionError && <Text style={styles.error}>{actionError}</Text>}

          <View style={styles.actions}>
            {tx.can_upload_receipt && (
              <Button
                label="Enviar comprovativo de pagamento"
                onPress={() => { setActionError(null); receiptM.mutate(); }}
                loading={receiptM.isPending}
                disabled={busy}
              />
            )}
            {tx.can_confirm && (
              <Button label="Confirmar recepção" onPress={onConfirm} loading={confirmM.isPending} disabled={busy} />
            )}
            {tx.can_cancel && (
              <Button label="Cancelar transação" variant="ghost" onPress={onCancel} loading={cancelM.isPending} disabled={busy} />
            )}
          </View>
        </ScrollView>
      )}
    </Screen>
  );
}

function Row({ label, value, highlight }: { label: string; value: string; highlight?: boolean }) {
  return (
    <View style={styles.row}>
      <Text style={styles.rowLabel}>{label}</Text>
      <Text style={[styles.rowValue, highlight && styles.rowValueOk]} numberOfLines={1}>
        {value}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  skeleton: { gap: spacing.md, paddingVertical: spacing.md },
  scroll: { gap: spacing.lg, paddingVertical: spacing.md },
  statusRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  date: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted },
  card: {
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.lg,
    gap: spacing.md,
  },
  cardTitle: { fontFamily: fonts.displaySemi, fontSize: fontSize.md, color: colors.text },
  row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', gap: spacing.md },
  rowLabel: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.textMuted },
  rowValue: { flexShrink: 1, textAlign: 'right', fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.text },
  rowValueOk: { color: colors.primary, fontSize: fontSize.md },
  divider: { height: 1, backgroundColor: colors.border },
  instructions: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.text, lineHeight: 20 },
  actions: { gap: spacing.sm },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger },
});
