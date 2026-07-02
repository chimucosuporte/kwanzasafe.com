import { useState } from 'react';
import { Alert, StyleSheet, Text, View } from 'react-native';

import { logout } from '@/api/auth';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

export default function AccountScreen() {
  const user = useAuthStore((s) => s.user);
  const clearSession = useAuthStore((s) => s.clearSession);
  const [busy, setBusy] = useState(false);

  const onLogout = () => {
    Alert.alert('Terminar sessão', 'Queres sair da conta de administração?', [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Sair', style: 'destructive',
        onPress: async () => {
          setBusy(true);
          await logout();
          await clearSession();
        },
      },
    ]);
  };

  return (
    <Screen>
      <Header title="Conta" />
      <View style={styles.card}>
        <Text style={styles.name}>{user?.full_name ?? '—'}</Text>
        <Text style={styles.email}>{user?.email}</Text>
        {!!user?.role_label && <Text style={styles.role}>{user.role_label}</Text>}
      </View>
      <Button label="Terminar sessão" variant="danger" onPress={onLogout} loading={busy} />
    </Screen>
  );
}

const styles = StyleSheet.create({
  card: { backgroundColor: colors.card, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.lg, marginBottom: spacing.lg },
  name: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  email: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, marginTop: 2 },
  role: { fontFamily: fonts.bodyBold, fontSize: fontSize.xs, color: colors.primaryBright, marginTop: spacing.sm },
});
