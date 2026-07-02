import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useEffect, useState } from 'react';
import { ActivityIndicator, StyleSheet, Switch, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { fetchRates, updateRate } from '@/api/admin';
import { apiErrorMessage } from '@/api/client';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

export default function RateEditScreen() {
  const { id: idParam } = useLocalSearchParams<{ id: string }>();
  const id = Number(idParam);
  const router = useRouter();
  const qc = useQueryClient();

  const { data: rates, isLoading } = useQuery({ queryKey: ['admin-rates'], queryFn: fetchRates });
  const rate = rates?.find((r) => r.id === id);

  const [value, setValue] = useState('');
  const [active, setActive] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (rate) { setValue(String(rate.rate)); setActive(rate.is_active); }
  }, [rate?.id]); // eslint-disable-line react-hooks/exhaustive-deps

  const m = useMutation({
    mutationFn: () => updateRate(id, { rate: parseFloat(value.replace(',', '.')), is_active: active }),
    onSuccess: () => { void qc.invalidateQueries({ queryKey: ['admin-rates'] }); router.back(); },
    onError: (e) => setError(apiErrorMessage(e)),
  });

  if (isLoading || !rate) return <Screen edges={['top', 'bottom']}><ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} /></Screen>;

  const num = parseFloat(value.replace(',', '.'));
  const valid = isFinite(num) && num > 0;

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title={`Taxa ${rate.currency_from}/${rate.currency_to}`} onBack={() => router.back()} />
      <KeyboardAwareScrollView contentContainerStyle={{ paddingVertical: spacing.md }} bottomOffset={24}>
        {!!error && <Text style={styles.err}>{error}</Text>}
        <TextField
          label={`Taxa (1 ${rate.currency_from} = ? Kz)`}
          value={value}
          onChangeText={(t) => { setValue(t); setError(null); }}
          keyboardType="decimal-pad"
          placeholder="0,0000"
        />
        <View style={styles.switchRow}>
          <View style={{ flex: 1 }}>
            <Text style={styles.switchLabel}>Taxa ativa</Text>
            <Text style={styles.switchSub}>Inativa não aparece na calculadora do cliente.</Text>
          </View>
          <Switch value={active} onValueChange={setActive} trackColor={{ true: colors.primary, false: colors.border }} thumbColor={colors.white} />
        </View>
        <Button label="Guardar taxa" onPress={() => m.mutate()} loading={m.isPending} disabled={!valid} />
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
