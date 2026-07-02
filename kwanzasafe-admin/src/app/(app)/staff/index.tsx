import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, Alert, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';

import { createStaff, deleteStaff, fetchStaff, toggleStaffActive } from '@/api/admin';
import { apiErrorMessage, apiFieldErrors } from '@/api/client';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { TextField } from '@/components/TextField';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { AdminStaff } from '@/types/api';

export default function StaffScreen() {
  const router = useRouter();
  const qc = useQueryClient();
  const { data, isLoading, isError, refetch } = useQuery({ queryKey: ['admin-staff'], queryFn: fetchStaff });

  const [adding, setAdding] = useState(false);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [fe, setFe] = useState<Record<string, string>>({});
  const [error, setError] = useState<string | null>(null);

  const refresh = () => qc.invalidateQueries({ queryKey: ['admin-staff'] });

  const createM = useMutation({
    mutationFn: () => createStaff({ full_name: name.trim(), email: email.trim().toLowerCase(), password, password_confirmation: password }),
    onSuccess: () => { setAdding(false); setName(''); setEmail(''); setPassword(''); setFe({}); setError(null); void refresh(); },
    onError: (e) => { setFe(apiFieldErrors(e)); setError(Object.keys(apiFieldErrors(e)).length ? null : apiErrorMessage(e)); },
  });
  const toggleM = useMutation({ mutationFn: (id: number) => toggleStaffActive(id), onSuccess: () => void refresh(), onError: (e) => Alert.alert('Erro', apiErrorMessage(e)) });
  const delM = useMutation({ mutationFn: (id: number) => deleteStaff(id), onSuccess: () => void refresh(), onError: (e) => Alert.alert('Erro', apiErrorMessage(e)) });

  const onDelete = (s: AdminStaff) => Alert.alert('Eliminar funcionário', `Eliminar a conta de ${s.full_name}? Os tíquetes abertos voltam à fila.`, [
    { text: 'Cancelar', style: 'cancel' },
    { text: 'Eliminar', style: 'destructive', onPress: () => delM.mutate(s.id) },
  ]);

  return (
    <Screen edges={['top', 'bottom']}>
      <Header
        title="Funcionários"
        subtitle="Contas de suporte"
        onBack={() => router.back()}
        right={<Pressable onPress={() => setAdding((v) => !v)} hitSlop={8}><Ionicons name={adding ? 'close-circle' : 'add-circle'} size={28} color={colors.primaryBright} /></Pressable>}
      />
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: spacing.xxl, gap: spacing.sm }}>
        {adding && (
          <View style={styles.form}>
            <Text style={styles.formTitle}>Novo funcionário de suporte</Text>
            {!!error && <Text style={styles.err}>{error}</Text>}
            <TextField label="Nome completo" value={name} onChangeText={setName} error={fe.full_name} />
            <TextField label="Email" value={email} onChangeText={setEmail} keyboardType="email-address" autoCapitalize="none" error={fe.email} />
            <TextField label="Palavra-passe (mín. 8)" value={password} onChangeText={setPassword} secureTextEntry error={fe.password} />
            <Button label="Criar conta" onPress={() => createM.mutate()} loading={createM.isPending} disabled={!name.trim() || !email.trim() || password.length < 8} />
          </View>
        )}

        {isLoading ? (
          <ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} />
        ) : isError ? (
          <Pressable onPress={() => refetch()}><Text style={styles.err}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable>
        ) : (
          <>
            {(data?.staff ?? []).length === 0 && <Text style={styles.empty}>Sem funcionários de suporte.</Text>}
            {(data?.staff ?? []).map((s) => (
              <View key={s.id} style={styles.row}>
                <View style={{ flex: 1 }}>
                  <Text style={styles.name}>{s.full_name}</Text>
                  <Text style={styles.email}>{s.email}</Text>
                  <Text style={styles.meta}>{s.open_tickets} tíquete(s) aberto(s) · {s.is_active ? 'ativa' : 'desativada'}</Text>
                </View>
                <Pressable onPress={() => toggleM.mutate(s.id)} hitSlop={6} style={styles.iconBtn}>
                  <Ionicons name={s.is_active ? 'pause-circle-outline' : 'play-circle-outline'} size={24} color={s.is_active ? colors.warning : colors.success} />
                </Pressable>
                <Pressable onPress={() => onDelete(s)} hitSlop={6} style={styles.iconBtn}>
                  <Ionicons name="trash-outline" size={22} color={colors.danger} />
                </Pressable>
              </View>
            ))}

            {(data?.super_admins ?? []).length > 0 && (
              <>
                <Text style={styles.section}>Super-admins</Text>
                {data!.super_admins.map((sa) => (
                  <View key={sa.id} style={[styles.row, { opacity: 0.8 }]}>
                    <View style={{ flex: 1 }}>
                      <Text style={styles.name}>{sa.full_name}</Text>
                      <Text style={styles.email}>{sa.email}</Text>
                    </View>
                    <View style={styles.saTag}><Text style={styles.saTagText}>super</Text></View>
                  </View>
                ))}
              </>
            )}
          </>
        )}
      </ScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  form: { backgroundColor: colors.card, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md, marginBottom: spacing.sm },
  formTitle: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted, marginBottom: spacing.sm, textTransform: 'uppercase' },
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginBottom: spacing.sm },
  empty: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', marginTop: spacing.lg },
  section: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted, marginTop: spacing.md, marginBottom: 2 },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md },
  name: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  email: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  meta: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textFaint, marginTop: 2 },
  iconBtn: { padding: 4 },
  saTag: { backgroundColor: colors.infoTint, paddingHorizontal: 8, paddingVertical: 2, borderRadius: radius.pill },
  saTagText: { fontFamily: fonts.bodyBold, fontSize: 10, color: colors.info },
});
