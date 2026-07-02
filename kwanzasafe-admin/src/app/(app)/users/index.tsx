import { Ionicons } from '@expo/vector-icons';
import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';

import { fetchUsers } from '@/api/admin';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { AdminUserRow } from '@/types/api';

const FILTERS = [
  { key: 'all', label: 'Todos' },
  { key: 'approved', label: 'Verificados' },
  { key: 'pending', label: 'KYC pendente' },
  { key: 'none', label: 'Sem KYC' },
];

export default function UsersScreen() {
  const router = useRouter();
  const [kyc, setKyc] = useState('all');
  const [q, setQ] = useState('');

  const { data, isLoading, isError, refetch, isRefetching } = useQuery({
    queryKey: ['admin-users', kyc, q],
    queryFn: () => fetchUsers({ kyc, q }),
  });

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Utilizadores" subtitle={data ? `${data.meta.total} no total` : undefined} onBack={() => router.back()} />

      <View style={styles.search}>
        <Ionicons name="search" size={18} color={colors.textMuted} />
        <TextInput value={q} onChangeText={setQ} placeholder="Nome, email ou BI…" placeholderTextColor={colors.textFaint} style={styles.searchInput} autoCapitalize="none" />
        {q.length > 0 && <Ionicons name="close-circle" size={18} color={colors.textMuted} onPress={() => setQ('')} />}
      </View>

      <View style={styles.filters}>
        {FILTERS.map((f) => (
          <Pressable key={f.key} onPress={() => setKyc(f.key)} style={[styles.pill, kyc === f.key && styles.pillOn]}>
            <Text style={[styles.pillText, kyc === f.key && styles.pillTextOn]}>{f.label}</Text>
          </Pressable>
        ))}
      </View>

      {isLoading ? (
        <ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} />
      ) : isError ? (
        <Pressable onPress={() => refetch()}><Text style={styles.err}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable>
      ) : (
        <FlatList
          data={data?.data ?? []}
          keyExtractor={(u) => String(u.id)}
          onRefresh={refetch}
          refreshing={isRefetching}
          contentContainerStyle={{ paddingBottom: spacing.xl, gap: spacing.sm }}
          ListEmptyComponent={<Text style={styles.empty}>Sem utilizadores.</Text>}
          renderItem={({ item }) => <Row u={item} onPress={() => router.push(`/users/${item.id}`)} />}
        />
      )}
    </Screen>
  );
}

function Row({ u, onPress }: { u: AdminUserRow; onPress: () => void }) {
  return (
    <Pressable onPress={onPress} style={styles.row}>
      <View style={styles.avatar}><Text style={styles.avatarText}>{(u.full_name ?? u.email).charAt(0).toUpperCase()}</Text></View>
      <View style={{ flex: 1 }}>
        <Text style={styles.name} numberOfLines={1}>{u.full_name ?? '—'}</Text>
        <Text style={styles.email} numberOfLines={1}>{u.email}</Text>
      </View>
      {u.is_admin && <View style={styles.tag}><Text style={styles.tagText}>staff</Text></View>}
      {u.is_verified ? <Ionicons name="shield-checkmark" size={18} color={colors.success} /> : u.kyc_pending ? <Ionicons name="time" size={18} color={colors.warning} /> : null}
      <Ionicons name="chevron-forward" size={18} color={colors.textFaint} />
    </Pressable>
  );
}

const styles = StyleSheet.create({
  search: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, backgroundColor: colors.surfaceAlt, borderWidth: 1, borderColor: colors.border, borderRadius: radius.md, paddingHorizontal: spacing.md, height: 46, marginBottom: spacing.sm },
  searchInput: { flex: 1, fontFamily: fonts.body, fontSize: fontSize.md, color: colors.text },
  filters: { flexDirection: 'row', gap: spacing.xs, marginBottom: spacing.sm, flexWrap: 'wrap' },
  pill: { paddingVertical: 5, paddingHorizontal: 12, borderRadius: radius.pill, borderWidth: 1, borderColor: colors.border },
  pillOn: { backgroundColor: colors.primaryTint, borderColor: colors.primary },
  pillText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.xs, color: colors.textMuted },
  pillTextOn: { color: colors.primaryBright },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md },
  avatar: { width: 38, height: 38, borderRadius: radius.pill, backgroundColor: colors.primaryTint, alignItems: 'center', justifyContent: 'center' },
  avatarText: { fontFamily: fonts.displaySemi, fontSize: fontSize.md, color: colors.primaryBright },
  name: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  email: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  tag: { backgroundColor: colors.infoTint, paddingHorizontal: 8, paddingVertical: 2, borderRadius: radius.pill },
  tagText: { fontFamily: fonts.bodyBold, fontSize: 10, color: colors.info },
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginTop: spacing.md },
  empty: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', marginTop: spacing.xl },
});
