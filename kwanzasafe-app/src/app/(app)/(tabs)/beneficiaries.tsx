import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { Alert, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';

import { fetchBeneficiaries, removeBeneficiary } from '@/api/beneficiaries';
import { apiErrorMessage } from '@/api/client';
import { fetchWallets, removeWallet, setDefaultWallet } from '@/api/wallets';
import { EmptyState } from '@/components/EmptyState';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { SkeletonCard } from '@/components/Skeleton';
import { TourGuide, type TourStep } from '@/components/TourGuide';
import { WALLET_META } from '@/lib/wallets';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { Beneficiary, PaymentWallet } from '@/types/api';

/**
 * Destinos de pagamento: contas bancárias (IBAN) + carteiras
 * Bybit/Binance/RedotPay. O beneficiário escolhe onde recebe os Kwanzas.
 */
export default function DestinationsScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();

  const { data: banks, isLoading: loadingBanks } = useQuery({ queryKey: ['beneficiaries'], queryFn: fetchBeneficiaries });
  const { data: wallets, isLoading: loadingWallets } = useQuery({ queryKey: ['wallets'], queryFn: fetchWallets });

  const removeBankM = useMutation({
    mutationFn: (id: number) => removeBeneficiary(id),
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['beneficiaries'] }),
    onError: (e) => Alert.alert('Erro', apiErrorMessage(e)),
  });

  const removeWalletM = useMutation({
    mutationFn: (id: number) => removeWallet(id),
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['wallets'] }),
    onError: (e) => Alert.alert('Erro', apiErrorMessage(e)),
  });

  const defaultWalletM = useMutation({
    mutationFn: (id: number) => setDefaultWallet(id),
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['wallets'] }),
    onError: (e) => Alert.alert('Erro', apiErrorMessage(e)),
  });

  const confirmRemoveBank = (b: Beneficiary) =>
    Alert.alert('Remover conta', `Remover ${b.bank_name}?`, [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Remover', style: 'destructive', onPress: () => removeBankM.mutate(b.id) },
    ]);

  const confirmRemoveWallet = (w: PaymentWallet) =>
    Alert.alert('Remover carteira', `Remover ${WALLET_META[w.provider].label}?`, [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Remover', style: 'destructive', onPress: () => removeWalletM.mutate(w.id) },
    ]);

  const isLoading = loadingBanks || loadingWallets;
  const isEmpty = !isLoading && !banks?.length && !wallets?.length;

  return (
    <Screen edges={['top']}>
      <Header title="Destinos de pagamento" />
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
        <Text style={styles.intro}>Escolhe onde queres receber os teus Kwanzas: conta bancária ou carteira.</Text>

        {isLoading ? (
          <View style={styles.list}>
            <SkeletonCard />
            <SkeletonCard />
          </View>
        ) : isEmpty ? (
          <EmptyState
            icon="wallet-outline"
            title="Sem destinos"
            description="Adiciona uma conta bancária ou carteira para receberes os valores."
          />
        ) : (
          <View style={styles.list}>
            {/* Contas bancárias */}
            {banks?.map((b) => (
              <View key={`bank-${b.id}`} style={styles.item}>
                <View style={[styles.badge, { backgroundColor: colors.primaryTint }]}>
                  <Ionicons name="business" size={20} color={colors.primaryBright} />
                </View>
                <View style={styles.itemInfo}>
                  <Text style={styles.itemTitle}>{b.bank_name}</Text>
                  <Text style={styles.itemId}>{b.iban}</Text>
                  <Text style={styles.itemHolder}>{b.holder_name}</Text>
                </View>
                <Pressable onPress={() => confirmRemoveBank(b)} hitSlop={8}>
                  <Ionicons name="trash-outline" size={18} color={colors.textMuted} />
                </Pressable>
              </View>
            ))}

            {/* Carteiras */}
            {wallets?.map((w) => {
              const meta = WALLET_META[w.provider];
              return (
                <View key={`w-${w.id}`} style={styles.item}>
                  <View style={[styles.badge, { backgroundColor: `${meta.color}22` }]}>
                    <Text style={[styles.badgeLetter, { color: meta.color }]}>{meta.label[0]}</Text>
                  </View>
                  <View style={styles.itemInfo}>
                    <View style={styles.titleRow}>
                      <Text style={styles.itemTitle}>{meta.label}</Text>
                      {w.is_default && (
                        <View style={styles.defaultTag}>
                          <Ionicons name="star" size={10} color={colors.primaryBright} />
                          <Text style={styles.defaultText}>Predefinida</Text>
                        </View>
                      )}
                      {!!w.network && <Text style={styles.network}>{w.network}</Text>}
                    </View>
                    <Text style={styles.itemId}>{w.identifier}</Text>
                    <Text style={styles.itemHolder}>{w.holder_name}</Text>
                  </View>
                  <View style={styles.walletActions}>
                    {!w.is_default && (
                      <Pressable onPress={() => defaultWalletM.mutate(w.id)} hitSlop={6}>
                        <Ionicons name="star-outline" size={18} color={colors.textMuted} />
                      </Pressable>
                    )}
                    <Pressable onPress={() => confirmRemoveWallet(w)} hitSlop={6}>
                      <Ionicons name="trash-outline" size={18} color={colors.textMuted} />
                    </Pressable>
                  </View>
                </View>
              );
            })}
          </View>
        )}

        <Pressable
          style={({ pressed }) => [styles.addBtn, pressed && styles.pressed]}
          onPress={() => router.push('/add-destination')}
        >
          <Ionicons name="add-circle" size={22} color={colors.primaryBright} />
          <Text style={styles.addText}>Adicionar destino</Text>
        </Pressable>

        <Text style={styles.note}>
          As contas e carteiras têm de estar em teu nome (igual ao KYC). Destinos de terceiros são recusados.
        </Text>
      </ScrollView>

      <TourGuide tourKey="destinations-v1" steps={DEST_TOUR} />
    </Screen>
  );
}

const DEST_TOUR: TourStep[] = [
  { icon: 'wallet', title: 'Onde recebes', body: 'Adiciona contas bancárias (IBAN) ou carteiras (Bybit, Binance, RedotPay) para receberes os teus Kwanzas.' },
  { icon: 'shield-checkmark', title: 'Em teu nome', body: 'Por segurança, os destinos têm de estar no teu nome (igual ao KYC). Destinos de terceiros são recusados.' },
];

const styles = StyleSheet.create({
  scroll: { gap: spacing.md, paddingVertical: spacing.lg },
  intro: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, lineHeight: 20 },
  list: { gap: spacing.sm },
  item: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.md,
  },
  badge: { width: 42, height: 42, borderRadius: radius.md, alignItems: 'center', justifyContent: 'center' },
  badgeLetter: { fontFamily: fonts.display, fontSize: fontSize.lg },
  itemInfo: { flex: 1, gap: 2 },
  titleRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
  itemTitle: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  itemId: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.text },
  itemHolder: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted },
  network: { fontFamily: fonts.bodyMedium, fontSize: 10, color: colors.textMuted },
  defaultTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
    backgroundColor: colors.primaryTint,
    borderRadius: radius.pill,
    paddingVertical: 2,
    paddingHorizontal: 6,
  },
  defaultText: { fontFamily: fonts.bodyMedium, fontSize: 10, color: colors.primaryBright },
  walletActions: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
  addBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    borderWidth: 1.5,
    borderColor: colors.primaryTintBorder,
    borderStyle: 'dashed',
    backgroundColor: colors.primaryTint,
    marginTop: spacing.xs,
  },
  pressed: { opacity: 0.7 },
  addText: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.primaryBright },
  note: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, lineHeight: 18 },
});
