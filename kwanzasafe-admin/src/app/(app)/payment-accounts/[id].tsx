import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useEffect, useState } from 'react';
import { ActivityIndicator, Alert, StyleSheet, Switch, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { deletePaymentAccount, fetchPaymentAccounts, savePaymentAccount } from '@/api/admin';
import { apiErrorMessage, apiFieldErrors } from '@/api/client';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

export default function PaymentAccountFormScreen() {
  const { id: idParam } = useLocalSearchParams<{ id: string }>();
  const isNew = idParam === 'new';
  const id = isNew ? null : Number(idParam);
  const router = useRouter();
  const qc = useQueryClient();

  const { data, isLoading } = useQuery({ queryKey: ['admin-payment-accounts'], queryFn: fetchPaymentAccounts });
  const account = !isNew ? data?.data.find((a) => a.id === id) : undefined;

  const [currency, setCurrency] = useState('');
  const [holder, setHolder] = useState('');
  const [identifier, setIdentifier] = useState('');
  const [network, setNetwork] = useState('');
  const [instructions, setInstructions] = useState('');
  const [active, setActive] = useState(true);
  const [fe, setFe] = useState<Record<string, string>>({});
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (account) {
      setCurrency(account.currency); setHolder(account.holder); setIdentifier(account.identifier);
      setNetwork(account.network ?? ''); setInstructions(account.instructions ?? ''); setActive(account.is_active);
    }
  }, [account?.id]); // eslint-disable-line react-hooks/exhaustive-deps

  const saveM = useMutation({
    mutationFn: () => savePaymentAccount(id, {
      currency: currency.trim().toUpperCase(), holder: holder.trim(), identifier: identifier.trim(),
      network: network.trim() || undefined, instructions: instructions.trim() || undefined, is_active: active,
    }),
    onSuccess: () => { void qc.invalidateQueries({ queryKey: ['admin-payment-accounts'] }); router.back(); },
    onError: (e) => { setFe(apiFieldErrors(e)); setError(Object.keys(apiFieldErrors(e)).length ? null : apiErrorMessage(e)); },
  });

  const delM = useMutation({
    mutationFn: () => deletePaymentAccount(id!),
    onSuccess: () => { void qc.invalidateQueries({ queryKey: ['admin-payment-accounts'] }); router.back(); },
    onError: (e) => Alert.alert('Erro', apiErrorMessage(e)),
  });

  const onDelete = () => Alert.alert('Eliminar conta', `Eliminar a conta de ${account?.currency}?`, [
    { text: 'Cancelar', style: 'cancel' },
    { text: 'Eliminar', style: 'destructive', onPress: () => delM.mutate() },
  ]);

  if (!isNew && isLoading) return <Screen edges={['top', 'bottom']}><ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} /></Screen>;

  const valid = currency.trim() && holder.trim() && identifier.trim();

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title={isNew ? 'Nova conta de recepção' : `Conta ${account?.currency ?? ''}`} onBack={() => router.back()} />
      <KeyboardAwareScrollView contentContainerStyle={{ paddingVertical: spacing.md }} bottomOffset={24}>
        {!!error && <Text style={styles.err}>{error}</Text>}
        <TextField label="Moeda (ex.: EUR, BRL, USDT)" value={currency} onChangeText={(t) => { setCurrency(t); setFe({}); }} autoCapitalize="characters" placeholder="EUR" error={fe.currency} />
        <TextField label="Titular" value={holder} onChangeText={setHolder} placeholder="Nome do titular / conta" error={fe.holder} />
        <TextField label="Identificador (IBAN / carteira / chave)" value={identifier} onChangeText={setIdentifier} placeholder="PT50 ... ou endereço/UID" error={fe.identifier} />
        <TextField label="Rede (opcional)" value={network} onChangeText={setNetwork} placeholder="Ex.: TRC20" error={fe.network} />
        <TextField label="Instruções (opcional)" value={instructions} onChangeText={setInstructions} placeholder="Notas para o cliente" multiline error={fe.instructions} />
        <View style={styles.switchRow}>
          <View style={{ flex: 1 }}>
            <Text style={styles.switchLabel}>Conta ativa</Text>
            <Text style={styles.switchSub}>Só uma conta ativa por moeda é usada nas transações.</Text>
          </View>
          <Switch value={active} onValueChange={setActive} trackColor={{ true: colors.primary, false: colors.border }} thumbColor={colors.white} />
        </View>
        <Button label={isNew ? 'Criar conta' : 'Guardar alterações'} onPress={() => saveM.mutate()} loading={saveM.isPending} disabled={!valid} />
        {!isNew && <Button label="Eliminar conta" variant="danger" onPress={onDelete} loading={delM.isPending} style={{ marginTop: spacing.sm }} />}
      </KeyboardAwareScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginBottom: spacing.sm },
  switchRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.md, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md, marginBottom: spacing.md },
  switchLabel: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  switchSub: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, marginTop: 2 },
});
