import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { useMemo, useState } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';

import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

const SUGGESTIONS = [
  { icon: 'swap-horizontal' as const, label: 'As minhas transações', route: '/transactions' },
  { icon: 'wallet' as const, label: 'Beneficiários e carteiras', route: '/beneficiaries' },
  { icon: 'shield-checkmark' as const, label: 'Verificação de identidade', route: '/kyc' },
  { icon: 'help-buoy' as const, label: 'Ajuda e suporte', route: '/help' },
];

/**
 * Pesquisa global (UI). Por agora sugere atalhos; a ligação à pesquisa real
 * (transações, beneficiários, referências) entra numa fase seguinte.
 */
export default function SearchScreen() {
  const router = useRouter();
  const [q, setQ] = useState('');

  const results = useMemo(() => {
    const t = q.trim().toLowerCase();
    if (!t) return SUGGESTIONS;
    return SUGGESTIONS.filter((s) => s.label.toLowerCase().includes(t));
  }, [q]);

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Pesquisar" onBack={() => router.back()} />

      <View style={styles.searchBox}>
        <Ionicons name="search" size={18} color={colors.textMuted} />
        <TextInput
          value={q}
          onChangeText={setQ}
          placeholder="Procurar transações, referências, ajuda…"
          placeholderTextColor={colors.textMuted}
          style={styles.input}
          autoFocus
          returnKeyType="search"
        />
        {q.length > 0 && (
          <Pressable onPress={() => setQ('')} hitSlop={8}>
            <Ionicons name="close-circle" size={18} color={colors.textMuted} />
          </Pressable>
        )}
      </View>

      <ScrollView showsVerticalScrollIndicator={false} keyboardShouldPersistTaps="handled">
        <Text style={styles.sectionTitle}>{q.trim() ? 'Resultados' : 'Sugestões'}</Text>
        {results.length === 0 ? (
          <Text style={styles.empty}>Sem resultados para “{q.trim()}”.</Text>
        ) : (
          <View style={styles.list}>
            {results.map((s) => (
              <Pressable
                key={s.label}
                style={({ pressed }) => [styles.row, pressed && styles.pressed]}
                onPress={() => router.push(s.route as never)}
              >
                <View style={styles.rowIcon}>
                  <Ionicons name={s.icon} size={18} color={colors.primaryBright} />
                </View>
                <Text style={styles.rowLabel}>{s.label}</Text>
                <Ionicons name="chevron-forward" size={18} color={colors.textFaint} />
              </Pressable>
            ))}
          </View>
        )}
      </ScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  searchBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    backgroundColor: colors.surfaceAlt,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.md,
    paddingHorizontal: spacing.md,
    height: 50,
    marginBottom: spacing.lg,
  },
  input: { flex: 1, fontFamily: fonts.body, fontSize: fontSize.md, color: colors.text, height: '100%' },
  sectionTitle: {
    fontFamily: fonts.bodyMedium,
    fontSize: fontSize.sm,
    color: colors.textMuted,
    marginBottom: spacing.sm,
  },
  list: { gap: spacing.sm },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.md,
  },
  pressed: { opacity: 0.7 },
  rowIcon: {
    width: 38,
    height: 38,
    borderRadius: radius.pill,
    backgroundColor: colors.primaryTint,
    alignItems: 'center',
    justifyContent: 'center',
  },
  rowLabel: { flex: 1, fontFamily: fonts.bodyMedium, fontSize: fontSize.md, color: colors.text },
  empty: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
});
