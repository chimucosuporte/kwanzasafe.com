import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useMemo, useState } from 'react';
import { ActivityIndicator, Modal, Pressable, StyleSheet, Text, View } from 'react-native';

import { fetchBeneficiaries } from '@/api/beneficiaries';
import { apiErrorMessage } from '@/api/client';
import { fetchRates } from '@/api/rates';
import { createTransaction } from '@/api/transactions';
import { fetchWallets } from '@/api/wallets';
import { Button } from '@/components/Button';
import { TextField } from '@/components/TextField';
import { formatAmount, toNumber } from '@/lib/format';
import { WALLET_META } from '@/lib/wallets';
import { colors, elevation, fonts, fontSize, radius, spacing } from '@/theme';
import type { WalletProvider } from '@/types/api';

type Dest = { type: 'bank' | WalletProvider; id: number; title: string; subtitle: string; isDefault: boolean };

/** Limites espelham CreateTransactionRequest (backend). */
const MIN = 10;
const MAX = 50000;

/**
 * Calculadora de conversão + criação de transação.
 *
 * Busca as taxas activas, deixa escolher a moeda e o valor, mostra os AOA a
 * receber e cria a transação (`POST /transactions`), navegando para o detalhe.
 */
export function Calculator({ canTransact }: { canTransact: boolean }) {
  const router = useRouter();
  const queryClient = useQueryClient();
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [amount, setAmount] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [destKey, setDestKey] = useState<string | null>(null);
  const [destOpen, setDestOpen] = useState(false);

  const { data: rates, isLoading, isError, refetch } = useQuery({
    queryKey: ['rates'],
    queryFn: fetchRates,
  });
  const { data: banks } = useQuery({ queryKey: ['beneficiaries'], queryFn: fetchBeneficiaries });
  const { data: wallets } = useQuery({ queryKey: ['wallets'], queryFn: fetchWallets });

  // Moeda activa: a seleccionada, ou a primeira disponível por defeito.
  const selected = useMemo(() => {
    if (!rates?.length) return null;
    return rates.find((r) => r.id === selectedId) ?? rates[0];
  }, [rates, selectedId]);

  // Destinos de recepção combinados (contas bancárias + carteiras).
  const destinations = useMemo<Dest[]>(() => {
    const out: Dest[] = [];
    for (const b of banks ?? []) out.push({ type: 'bank', id: b.id, title: b.bank_name, subtitle: b.iban, isDefault: false });
    for (const w of wallets ?? []) out.push({ type: w.provider, id: w.id, title: WALLET_META[w.provider].label, subtitle: w.identifier, isDefault: w.is_default });
    return out;
  }, [banks, wallets]);

  const selectedDest = useMemo<Dest | null>(() => {
    if (!destinations.length) return null;
    return destinations.find((d) => `${d.type}:${d.id}` === destKey) ?? destinations.find((d) => d.isDefault) ?? destinations[0];
  }, [destinations, destKey]);

  const amountNum = toNumber(amount.replace(',', '.'));
  const received = selected ? amountNum * toNumber(selected.rate) : 0;

  const mutation = useMutation({
    mutationFn: () =>
      createTransaction({ moeda: selected!.id, valor_enviar: amountNum, destino_tipo: selectedDest!.type, destino_id: selectedDest!.id }),
    onSuccess: async (tx) => {
      setAmount('');
      await queryClient.invalidateQueries({ queryKey: ['transactions'] });
      router.push({ pathname: '/transactions/[ref]', params: { ref: tx.reference_id } });
    },
    onError: (err) => setError(apiErrorMessage(err)),
  });

  const submit = () => {
    setError(null);
    if (!selected) return;
    if (!Number.isFinite(amountNum) || amountNum <= 0) {
      setError('Introduz um valor válido.');
      return;
    }
    if (amountNum < MIN) {
      setError(`O valor mínimo é ${MIN} ${selected.currency_from}.`);
      return;
    }
    if (amountNum > MAX) {
      setError(`O valor máximo é ${formatAmount(MAX, 0)} ${selected.currency_from}.`);
      return;
    }
    if (!selectedDest) {
      setError('Escolhe onde queres receber os Kwanzas.');
      return;
    }
    mutation.mutate();
  };

  if (isLoading) {
    return (
      <View style={[styles.card, styles.centered]}>
        <ActivityIndicator color={colors.primary} />
      </View>
    );
  }

  if (isError || !rates?.length) {
    return (
      <View style={styles.card}>
        <Text style={styles.muted}>Não foi possível carregar as taxas.</Text>
        <Button label="Tentar de novo" variant="ghost" onPress={() => refetch()} />
      </View>
    );
  }

  return (
    <View style={styles.card}>
      <View style={styles.titleRow}>
        <Text style={styles.cardTitle}>Calculadora</Text>
        <View style={styles.livePill}>
          <View style={styles.liveDot} />
          <Text style={styles.liveText}>Taxa ao vivo</Text>
        </View>
      </View>

      <Text style={styles.fieldLabel}>Moeda de envio</Text>
      <View style={styles.chips}>
        {rates.map((rate) => {
          const active = selected?.id === rate.id;
          return (
            <Pressable
              key={rate.id}
              onPress={() => setSelectedId(rate.id)}
              style={[styles.chip, active && styles.chipActive]}
            >
              <Text style={[styles.chipText, active && styles.chipTextActive]}>
                {rate.currency_from}
              </Text>
            </Pressable>
          );
        })}
      </View>

      <TextField
        label={`Valor a enviar (${selected?.currency_from ?? ''})`}
        value={amount}
        onChangeText={(t) => setAmount(t.replace(/[^0-9.,]/g, ''))}
        keyboardType="decimal-pad"
        inputMode="decimal"
        placeholder="0"
        returnKeyType="done"
        onSubmitEditing={submit}
      />

      <View style={styles.result}>
        <View style={styles.resultGlow} />
        <Text style={styles.resultLabel}>Recebes (aprox.)</Text>
        <View style={styles.resultAmountRow}>
          <Text style={styles.resultValue}>{formatAmount(received)}</Text>
          <Text style={styles.resultCurrency}>AOA</Text>
        </View>
        {selected && (
          <View style={styles.rateRow}>
            <Ionicons name="trending-up" size={13} color={colors.textMuted} />
            <Text style={styles.rateHint}>
              1 {selected.currency_from} = {formatAmount(selected.rate)} AOA
            </Text>
          </View>
        )}
      </View>

      {/* Destino de recepção */}
      {canTransact && (
        destinations.length === 0 ? (
          <Pressable style={styles.destCta} onPress={() => router.push('/add-destination')}>
            <Ionicons name="add-circle" size={22} color={colors.primaryBright} />
            <View style={styles.flex}>
              <Text style={styles.destCtaTitle}>Adiciona onde queres receber</Text>
              <Text style={styles.destCtaText}>Precisas de um IBAN ou carteira para receberes os Kwanzas.</Text>
            </View>
            <Ionicons name="chevron-forward" size={18} color={colors.textMuted} />
          </Pressable>
        ) : (
          <>
            <Text style={styles.fieldLabel}>Receber em</Text>
            <Pressable style={styles.destSelector} onPress={() => setDestOpen(true)}>
              <View style={styles.destBadge}>
                <Ionicons name={selectedDest?.type === 'bank' ? 'business' : 'wallet'} size={18} color={colors.primaryBright} />
              </View>
              <View style={styles.flex}>
                <Text style={styles.destTitle}>{selectedDest?.title}</Text>
                <Text style={styles.destSub} numberOfLines={1}>{selectedDest?.subtitle}</Text>
              </View>
              <Ionicons name="chevron-down" size={16} color={colors.textMuted} />
            </Pressable>
          </>
        )
      )}

      {!!error && <Text style={styles.error}>{error}</Text>}

      {canTransact ? (
        <Button
          label="Iniciar transação"
          onPress={submit}
          loading={mutation.isPending}
          disabled={destinations.length === 0}
        />
      ) : (
        <Text style={styles.gated}>
          Conclui a verificação de identidade para poderes iniciar transações.
        </Text>
      )}

      {/* Modal de escolha de destino */}
      <Modal visible={destOpen} transparent animationType="slide" onRequestClose={() => setDestOpen(false)}>
        <Pressable style={styles.backdrop} onPress={() => setDestOpen(false)} />
        <View style={styles.sheet}>
          <View style={styles.sheetHead}>
            <Text style={styles.sheetTitle}>Onde queres receber?</Text>
            <Pressable onPress={() => setDestOpen(false)} hitSlop={10}>
              <Ionicons name="close" size={22} color={colors.textMuted} />
            </Pressable>
          </View>
          {destinations.map((d) => {
            const key = `${d.type}:${d.id}`;
            const active = selectedDest && `${selectedDest.type}:${selectedDest.id}` === key;
            return (
              <Pressable
                key={key}
                style={[styles.destOption, active && styles.destOptionActive]}
                onPress={() => { setDestKey(key); setDestOpen(false); setError(null); }}
              >
                <View style={styles.destBadge}>
                  <Ionicons name={d.type === 'bank' ? 'business' : 'wallet'} size={18} color={colors.primaryBright} />
                </View>
                <View style={styles.flex}>
                  <Text style={styles.destTitle}>{d.title}</Text>
                  <Text style={styles.destSub} numberOfLines={1}>{d.subtitle}</Text>
                </View>
                {active && <Ionicons name="checkmark-circle" size={20} color={colors.primaryBright} />}
              </Pressable>
            );
          })}
          <Pressable style={styles.destAdd} onPress={() => { setDestOpen(false); router.push('/add-destination'); }}>
            <Ionicons name="add" size={18} color={colors.primaryBright} />
            <Text style={styles.destAddText}>Adicionar outro destino</Text>
          </Pressable>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.xl,
    padding: spacing.lg,
    gap: spacing.md,
    ...elevation.sm,
  },
  centered: { alignItems: 'center', justifyContent: 'center', minHeight: 160 },
  titleRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  cardTitle: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  livePill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: colors.primaryTint,
    borderRadius: radius.pill,
    paddingVertical: 4,
    paddingHorizontal: spacing.sm,
  },
  liveDot: { width: 7, height: 7, borderRadius: 4, backgroundColor: colors.primaryBright },
  liveText: { fontFamily: fonts.bodyMedium, fontSize: 11, color: colors.primaryBright },
  fieldLabel: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.textMuted },
  chips: { flexDirection: 'row', gap: spacing.sm, flexWrap: 'wrap' },
  chip: {
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.pill,
    borderWidth: 1.5,
    borderColor: colors.border,
    backgroundColor: colors.surface,
  },
  chipActive: { borderColor: colors.primary, backgroundColor: colors.primaryTint },
  chipText: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted },
  chipTextActive: { color: colors.primaryBright },
  result: {
    backgroundColor: colors.primaryTint,
    borderWidth: 1,
    borderColor: colors.primaryTintBorder,
    borderRadius: radius.lg,
    padding: spacing.md,
    gap: spacing.xs,
    overflow: 'hidden',
  },
  resultGlow: {
    position: 'absolute',
    top: -40,
    right: -30,
    width: 120,
    height: 120,
    borderRadius: radius.pill,
    backgroundColor: colors.primary,
    opacity: 0.18,
  },
  resultLabel: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.textMuted },
  resultAmountRow: { flexDirection: 'row', alignItems: 'flex-end', gap: spacing.sm },
  resultValue: { fontFamily: fonts.display, fontSize: fontSize.xxl, color: colors.primaryBright, letterSpacing: -0.5 },
  resultCurrency: { fontFamily: fonts.displaySemi, fontSize: fontSize.md, color: colors.primaryBright, marginBottom: 4, opacity: 0.85 },
  rateRow: { flexDirection: 'row', alignItems: 'center', gap: 5 },
  rateHint: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted },
  muted: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger },
  gated: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.warning, textAlign: 'center' },

  flex: { flex: 1 },
  destCta: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    backgroundColor: colors.primaryTint,
    borderRadius: radius.md,
    borderWidth: 1.5,
    borderColor: colors.primaryTintBorder,
    borderStyle: 'dashed',
    padding: spacing.md,
  },
  destCtaTitle: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.text },
  destCtaText: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, lineHeight: 16 },
  destSelector: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    backgroundColor: colors.surfaceAlt,
    borderRadius: radius.md,
    borderWidth: 1.5,
    borderColor: colors.border,
    padding: spacing.sm,
  },
  destBadge: { width: 38, height: 38, borderRadius: radius.md, backgroundColor: colors.primaryTint, alignItems: 'center', justifyContent: 'center' },
  destTitle: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.text },
  destSub: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted },

  backdrop: { ...StyleSheet.absoluteFillObject, backgroundColor: colors.overlay },
  sheet: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: colors.card,
    borderTopLeftRadius: radius.xl,
    borderTopRightRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border,
    padding: spacing.md,
    paddingBottom: spacing.xl,
    gap: spacing.xs,
  },
  sheetHead: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: spacing.sm, paddingVertical: spacing.sm },
  sheetTitle: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  destOption: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    padding: spacing.sm,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: 'transparent',
  },
  destOptionActive: { borderColor: colors.primaryTintBorder, backgroundColor: colors.primaryTint },
  destAdd: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs, paddingVertical: spacing.md, marginTop: spacing.xs },
  destAddText: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.primaryBright },
});
