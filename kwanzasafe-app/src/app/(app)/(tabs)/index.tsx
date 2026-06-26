import { useQuery } from '@tanstack/react-query';
import { useRouter } from 'expo-router';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { KeyboardAwareScrollView } from 'react-native-keyboard-controller';

import { fetchMe } from '@/api/auth';
import { fetchUnreadCount } from '@/api/notifications';
import { fetchTransactions } from '@/api/transactions';
import { AppHeader } from '@/components/AppHeader';
import { BalanceCard } from '@/components/BalanceCard';
import { Calculator } from '@/components/Calculator';
import { ErrorState } from '@/components/ErrorState';
import { Screen } from '@/components/Screen';
import { SkeletonCard } from '@/components/Skeleton';
import { TourGuide, type TourStep } from '@/components/TourGuide';
import { TransactionRow } from '@/components/TransactionRow';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

/**
 * Dashboard do cliente (FASE 3): saudação + estado KYC, calculadora de
 * conversão e as transações mais recentes.
 */
export default function HomeScreen() {
  const router = useRouter();
  const user = useAuthStore((s) => s.user);
  const setUser = useAuthStore((s) => s.setUser);

  const { data: me } = useQuery({
    queryKey: ['me'],
    queryFn: async () => {
      const res = await fetchMe();
      setUser(res.user);
      return res.user;
    },
  });
  const profile = me ?? user;
  const canTransact = !!profile?.is_fully_verified;
  const { data: unread } = useQuery({ queryKey: ['notifications-unread'], queryFn: fetchUnreadCount });
  const hasAlerts = (!!profile && (!profile.email_verified || !profile.is_fully_verified)) || (unread ?? 0) > 0;

  const {
    data: transactions,
    isLoading: loadingTx,
    isError: txError,
    refetch: refetchTx,
    isRefetching: refetchingTx,
  } = useQuery({
    queryKey: ['transactions'],
    queryFn: fetchTransactions,
  });
  const recent = transactions?.slice(0, 3) ?? [];

  return (
    <Screen>
      <AppHeader user={profile} alerts={hasAlerts} />

      <KeyboardAwareScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false} keyboardShouldPersistTaps="handled" bottomOffset={24}>
        <Text style={styles.greeting}>
          Olá, {profile?.full_name?.split(' ')[0] ?? 'cliente'} 👋
        </Text>

        <BalanceCard user={profile} />

        {profile && !profile.email_verified ? (
          <Pressable style={styles.kycBanner} onPress={() => router.push('/verify-email')}>
            <Text style={styles.kycTitle}>Confirma o teu email</Text>
            <Text style={styles.kycText}>
              Toca aqui para introduzir o código que enviámos para {profile.email} e ativar a tua conta.
            </Text>
          </Pressable>
        ) : (
          !canTransact && (
            <Pressable style={styles.kycBanner} onPress={() => router.push('/kyc')}>
              <Text style={styles.kycTitle}>Verificação pendente</Text>
              <Text style={styles.kycText}>
                Toca para concluíres a verificação de identidade (KYC) e poderes iniciar transações.
              </Text>
            </Pressable>
          )
        )}

        <Calculator canTransact={canTransact} />

        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>As minhas transações</Text>
          {recent.length > 0 && (
            <Pressable onPress={() => router.push('/transactions')} hitSlop={10}>
              <Text style={styles.link}>Ver todas</Text>
            </Pressable>
          )}
        </View>

        {loadingTx ? (
          <View style={styles.list}>
            <SkeletonCard />
            <SkeletonCard />
          </View>
        ) : txError ? (
          <ErrorState
            title="Não foi possível carregar"
            description="As tuas transações não carregaram. Tenta novamente."
            onRetry={() => refetchTx()}
            retrying={refetchingTx}
          />
        ) : recent.length === 0 ? (
          <Text style={styles.empty}>
            Ainda não tens transações. Usa a calculadora acima para começar.
          </Text>
        ) : (
          <View style={styles.list}>
            {recent.map((tx) => (
              <TransactionRow
                key={tx.id}
                tx={tx}
                onPress={() =>
                  router.push({ pathname: '/transactions/[ref]', params: { ref: tx.reference_id } })
                }
              />
            ))}
          </View>
        )}
      </KeyboardAwareScrollView>

      <TourGuide tourKey="home-v1" steps={HOME_TOUR} />
    </Screen>
  );
}

const HOME_TOUR: TourStep[] = [
  { icon: 'sparkles', title: 'Bem-vindo à KwanzaSafe', body: 'Converte Euros, Reais e USDC para Kwanzas com segurança. Aqui vês o teu saldo e as transações recentes.' },
  { icon: 'calculator', title: 'Calculadora', body: 'Escolhe a moeda, o valor e onde queres receber. A taxa é ao vivo — toca em "Iniciar transação" para começar.' },
  { icon: 'grid', title: 'Navegação', body: 'No topo: pesquisa, leitor de QR, notificações e a tua conta. Em baixo: Início, Transações, Beneficiários e Conta.' },
];

const styles = StyleSheet.create({
  scroll: { gap: spacing.lg, paddingTop: spacing.sm, paddingBottom: spacing.xl },
  greeting: { fontFamily: fonts.display, fontSize: fontSize.xxl, color: colors.text },
  kycBanner: {
    backgroundColor: colors.warningTint,
    borderWidth: 1,
    borderColor: 'rgba(240,180,41,0.35)',
    borderRadius: radius.lg,
    padding: spacing.md,
    gap: spacing.xs,
  },
  kycTitle: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.warning },
  kycText: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.text, lineHeight: 20 },
  sectionHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  sectionTitle: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  link: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.primary },
  empty: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, lineHeight: 20 },
  list: { gap: spacing.sm },
});
