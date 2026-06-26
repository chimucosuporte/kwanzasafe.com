import { Ionicons } from '@expo/vector-icons';
import { useQuery } from '@tanstack/react-query';
import { Image } from 'expo-image';
import { useRouter } from 'expo-router';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';

import { fetchMe, logout as logoutApi } from '@/api/auth';
import { Header } from '@/components/Header';
import { MenuRow } from '@/components/MenuRow';
import { Screen } from '@/components/Screen';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

/**
 * Hub da conta: cartão de perfil + secções (Segurança, Conta, App).
 * Os formulários ficam em ecrãs dedicados (dados pessoais, palavra-passe, 2FA).
 */
export default function ProfileScreen() {
  const router = useRouter();
  const storeUser = useAuthStore((s) => s.user);
  const clearSession = useAuthStore((s) => s.clearSession);
  const token = useAuthStore((s) => s.token);

  const { data: me } = useQuery({ queryKey: ['me'], queryFn: async () => (await fetchMe()).user });
  const user = me ?? storeUser;
  const twoFa = !!user?.two_factor_enabled;

  const signOut = async () => {
    try {
      await logoutApi();
    } catch {
      /* ignora */
    } finally {
      await clearSession();
    }
  };

  const initial = (user?.full_name?.trim()?.[0] ?? 'K').toUpperCase();
  const verified = !!user?.is_fully_verified;

  return (
    <Screen edges={['top']}>
      <Header title="A minha conta" />
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
        {/* Cartão de perfil */}
        <Pressable
          style={({ pressed }) => [styles.hero, pressed && styles.pressed]}
          onPress={() => router.push('/account')}
        >
          <View style={styles.avatar}>
            {user?.avatar_url ? (
              <Image
                source={{ uri: user.avatar_url, headers: token ? { Authorization: `Bearer ${token}` } : undefined }}
                style={styles.avatarImg}
                contentFit="cover"
              />
            ) : (
              <Text style={styles.avatarText}>{initial}</Text>
            )}
            <View style={styles.avatarEdit}>
              <Ionicons name="camera" size={12} color={colors.white} />
            </View>
          </View>
          <View style={styles.heroTexts}>
            <Text style={styles.heroName} numberOfLines={1}>
              {user?.full_name ?? 'A minha conta'}
            </Text>
            <Text style={styles.heroEmail} numberOfLines={1}>
              {user?.email}
            </Text>
            <View style={[styles.pill, verified ? styles.pillOk : styles.pillWarn]}>
              <Ionicons
                name={verified ? 'shield-checkmark' : 'shield-half-outline'}
                size={12}
                color={verified ? colors.primaryBright : colors.warning}
              />
              <Text style={[styles.pillText, { color: verified ? colors.primaryBright : colors.warning }]}>
                {verified ? 'Conta verificada' : 'Verificação pendente'}
              </Text>
            </View>
          </View>
          <Ionicons name="chevron-forward" size={20} color={colors.textMuted} />
        </Pressable>

        <Text style={styles.section}>Segurança</Text>
        <View style={styles.group}>
          <MenuRow
            icon="shield-checkmark-outline"
            label="Autenticação 2FA"
            description="Google Authenticator"
            right={
              <View style={[styles.tag, twoFa ? styles.tagOn : styles.tagOff]}>
                <Text style={[styles.tagText, { color: twoFa ? colors.primaryBright : colors.textMuted }]}>
                  {twoFa ? 'Ativa' : 'Desativada'}
                </Text>
              </View>
            }
            onPress={() => router.push('/two-factor')}
          />
          <MenuRow
            icon="key-outline"
            label="Alterar palavra-passe"
            description="Atualiza a tua palavra-passe"
            onPress={() => router.push('/change-password')}
          />
          <MenuRow
            icon="finger-print"
            label="Biometria"
            description="Desbloqueio por impressão/rosto"
            onPress={() => router.push('/settings')}
          />
        </View>

        <Text style={styles.section}>Conta</Text>
        <View style={styles.group}>
          <MenuRow
            icon="person-outline"
            label="Dados pessoais"
            description="Nome, email e foto"
            onPress={() => router.push('/account')}
          />
          <MenuRow
            icon="card-outline"
            label="Verificação de identidade"
            description={verified ? 'Identidade verificada' : 'Conclui o KYC'}
            onPress={() => router.push('/kyc')}
          />
          <MenuRow
            icon="wallet-outline"
            label="Beneficiários e carteiras"
            description="Onde recebes os Kwanzas"
            onPress={() => router.push('/beneficiaries')}
          />
        </View>

        <Text style={styles.section}>Aplicação</Text>
        <View style={styles.group}>
          <MenuRow icon="settings-outline" label="Definições" onPress={() => router.push('/settings')} />
          <MenuRow icon="notifications-outline" label="Notificações" onPress={() => router.push('/notifications')} />
          <MenuRow icon="help-circle-outline" label="Ajuda e suporte" onPress={() => router.push('/help')} />
          <MenuRow icon="gift-outline" label="Convidar amigos" onPress={() => router.push('/invite')} />
        </View>

        <Pressable style={({ pressed }) => [styles.logout, pressed && styles.pressed]} onPress={signOut}>
          <Ionicons name="log-out-outline" size={20} color={colors.danger} />
          <Text style={styles.logoutText}>Terminar sessão</Text>
        </Pressable>
      </ScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  scroll: { gap: spacing.sm, paddingTop: spacing.md, paddingBottom: spacing.xxl },
  pressed: { opacity: 0.7 },
  hero: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.xl,
    padding: spacing.md,
    marginBottom: spacing.md,
  },
  avatar: {
    width: 60,
    height: 60,
    borderRadius: radius.pill,
    backgroundColor: colors.primaryTint,
    borderWidth: 1,
    borderColor: colors.primaryTintBorder,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: { fontFamily: fonts.display, fontSize: fontSize.xl, color: colors.primaryBright },
  avatarImg: { width: 60, height: 60, borderRadius: radius.pill },
  avatarEdit: {
    position: 'absolute',
    right: -2,
    bottom: -2,
    width: 22,
    height: 22,
    borderRadius: radius.pill,
    backgroundColor: colors.primary,
    borderWidth: 2,
    borderColor: colors.card,
    alignItems: 'center',
    justifyContent: 'center',
  },
  heroTexts: { flex: 1, gap: 3 },
  heroName: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  heroEmail: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  pill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    alignSelf: 'flex-start',
    borderRadius: radius.pill,
    paddingVertical: 3,
    paddingHorizontal: spacing.sm,
    marginTop: 2,
  },
  pillOk: { backgroundColor: colors.primaryTint },
  pillWarn: { backgroundColor: colors.warningTint },
  pillText: { fontFamily: fonts.bodyMedium, fontSize: 11 },
  section: {
    fontFamily: fonts.bodyBold,
    fontSize: fontSize.xs,
    color: colors.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginTop: spacing.md,
    marginBottom: spacing.xs,
  },
  group: { gap: spacing.sm },
  tag: { borderRadius: radius.pill, paddingVertical: 4, paddingHorizontal: spacing.sm },
  tagOn: { backgroundColor: colors.primaryTint },
  tagOff: { backgroundColor: colors.surface },
  tagText: { fontFamily: fonts.bodyMedium, fontSize: 11 },
  logout: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
    marginTop: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.border,
    backgroundColor: colors.card,
  },
  logoutText: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.danger },
});
