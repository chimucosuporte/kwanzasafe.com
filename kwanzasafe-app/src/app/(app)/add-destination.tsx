import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Switch, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { fetchMe } from '@/api/auth';
import { addBeneficiary } from '@/api/beneficiaries';
import { apiErrorMessage } from '@/api/client';
import { addWallet } from '@/api/wallets';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { WALLET_META } from '@/lib/wallets';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { WalletProvider } from '@/types/api';

type DestType = 'bank' | WalletProvider;

const TYPES: { key: DestType; label: string; color: string; icon?: 'business' }[] = [
  { key: 'bank', label: 'Banco', color: colors.primary, icon: 'business' },
  { key: 'bybit', label: 'Bybit', color: WALLET_META.bybit.color },
  { key: 'binance', label: 'Binance', color: WALLET_META.binance.color },
  { key: 'redotpay', label: 'RedotPay', color: WALLET_META.redotpay.color },
];

/** Adicionar destino de pagamento: conta bancária (API) ou carteira (local). */
export default function AddDestinationScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const { data: me } = useQuery({ queryKey: ['me'], queryFn: async () => (await fetchMe()).user });

  const [type, setType] = useState<DestType>('bank');
  const [bank, setBank] = useState('');
  const [iban, setIban] = useState('');
  const [identifier, setIdentifier] = useState('');
  const [network, setNetwork] = useState('');
  const [holder, setHolder] = useState('');
  const [isDefault, setIsDefault] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Pré-preenche o titular com o nome do KYC (tem de coincidir).
  useEffect(() => {
    if (me?.full_name && !holder) setHolder(me.full_name);
  }, [me?.full_name]); // eslint-disable-line react-hooks/exhaustive-deps

  const bankM = useMutation({
    mutationFn: () => addBeneficiary({ bank_name: bank.trim(), iban: iban.trim(), holder_name: holder.trim() }),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['beneficiaries'] });
      router.back();
    },
    onError: (e) => setError(apiErrorMessage(e)),
  });

  const walletM = useMutation({
    mutationFn: () =>
      addWallet({
        provider: type as WalletProvider,
        identifier: identifier.trim(),
        holder_name: holder.trim(),
        network: network.trim() || undefined,
        is_default: isDefault,
      }),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['wallets'] });
      router.back();
    },
    onError: (e) => setError(apiErrorMessage(e)),
  });

  const submit = async () => {
    setError(null);
    if (!holder.trim()) {
      setError('Indica o titular (igual ao teu KYC).');
      return;
    }
    if (type === 'bank') {
      if (!bank.trim() || !iban.trim()) {
        setError('Preenche o banco e o IBAN.');
        return;
      }
      bankM.mutate();
      return;
    }
    // Carteira (via API)
    if (!identifier.trim()) {
      setError(`Preenche o campo ${WALLET_META[type].idLabel}.`);
      return;
    }
    walletM.mutate();
  };

  const meta = type !== 'bank' ? WALLET_META[type] : null;

  return (
    <Screen edges={['top']}>
      <Header title="Adicionar destino" onBack={() => router.back()} />
      <KeyboardAwareScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false} bottomOffset={24}>
          <Text style={styles.label}>Tipo de destino</Text>
          <View style={styles.types}>
            {TYPES.map((t) => {
              const active = type === t.key;
              return (
                <Pressable
                  key={t.key}
                  onPress={() => {
                    setType(t.key);
                    setError(null);
                  }}
                  style={[styles.typeCard, active && { borderColor: t.color, backgroundColor: `${t.color}1a` }]}
                >
                  <View style={[styles.typeDot, { backgroundColor: t.color }]}>
                    {t.icon ? (
                      <Ionicons name={t.icon} size={14} color={colors.white} />
                    ) : (
                      <Text style={styles.typeDotText}>{t.label[0]}</Text>
                    )}
                  </View>
                  <Text style={[styles.typeLabel, active && { color: colors.text }]}>{t.label}</Text>
                </Pressable>
              );
            })}
          </View>

          <View style={styles.card}>
            {type === 'bank' ? (
              <>
                <TextField label="Banco" value={bank} onChangeText={setBank} placeholder="Ex.: BAI, BFA, Atlântico" />
                <TextField
                  label="IBAN"
                  value={iban}
                  onChangeText={setIban}
                  autoCapitalize="characters"
                  placeholder="AO06 0000 0000 0000 0000 0000 0"
                />
              </>
            ) : (
              <>
                <TextField
                  label={meta!.idLabel}
                  value={identifier}
                  onChangeText={setIdentifier}
                  autoCapitalize="none"
                  placeholder={meta!.idPlaceholder}
                />
                {meta!.hasNetwork && (
                  <TextField
                    label="Rede (opcional)"
                    value={network}
                    onChangeText={setNetwork}
                    autoCapitalize="characters"
                    placeholder="Ex.: USDT TRC20, BEP20"
                  />
                )}
              </>
            )}

            <TextField label="Titular" value={holder} onChangeText={setHolder} placeholder="Nome do titular (igual ao KYC)" />

            {type !== 'bank' && (
              <View style={styles.switchRow}>
                <View style={styles.flex}>
                  <Text style={styles.switchLabel}>Destino predefinido</Text>
                  <Text style={styles.switchHint}>Receber aqui por defeito.</Text>
                </View>
                <Switch
                  value={isDefault}
                  onValueChange={setIsDefault}
                  trackColor={{ true: colors.primary, false: colors.border }}
                  thumbColor={colors.white}
                />
              </View>
            )}

            {!!error && <Text style={styles.error}>{error}</Text>}
            <Button label="Guardar destino" onPress={submit} loading={bankM.isPending || walletM.isPending} />
          </View>

          {type !== 'bank' && (
            <Text style={styles.note}>
              A carteira é guardada na tua conta com o titular do teu KYC. Destinos de terceiros são recusados.
            </Text>
          )}
      </KeyboardAwareScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  scroll: { gap: spacing.md, paddingVertical: spacing.lg },
  label: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.textMuted },
  types: { flexDirection: 'row', gap: spacing.sm },
  typeCard: {
    flex: 1,
    alignItems: 'center',
    gap: 6,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    borderWidth: 1.5,
    borderColor: colors.border,
    backgroundColor: colors.card,
  },
  typeDot: { width: 28, height: 28, borderRadius: radius.pill, alignItems: 'center', justifyContent: 'center' },
  typeDotText: { fontFamily: fonts.display, fontSize: fontSize.sm, color: colors.white },
  typeLabel: { fontFamily: fonts.bodyMedium, fontSize: 11, color: colors.textMuted },
  card: {
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.lg,
    gap: spacing.md,
  },
  switchRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
  switchLabel: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.text },
  switchHint: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger },
  note: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, lineHeight: 18 },
});
