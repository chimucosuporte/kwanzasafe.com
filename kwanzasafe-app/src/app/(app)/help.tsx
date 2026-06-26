import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { useState } from 'react';
import { Linking, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';

import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

const WHATSAPP = '5511933579009';
const EMAIL = 'geral@kwanzasafe.com';

const FAQ: { q: string; a: string }[] = [
  {
    q: 'Como funciona uma transferência?',
    a: 'Usa a calculadora para simular a conversão, inicia a transação e fala com o nosso agente na sala da transação. Depois de pagares, envias o comprovativo; assim que confirmarmos, enviamos os Kwanzas.',
  },
  {
    q: 'Quanto tempo demora?',
    a: 'Após a confirmação do teu pagamento, o envio dos Kwanzas é normalmente processado no mesmo dia útil.',
  },
  {
    q: 'Que moedas posso converter?',
    a: 'Aceitamos Euros (EUR), Reais (BRL) e USDC, com conversão para Kwanzas (AOA).',
  },
  {
    q: 'É seguro?',
    a: 'Sim. Usamos verificação de identidade (KYC), registo de auditoria e contas de receção verificadas. Nunca partilhes a tua palavra-passe.',
  },
  {
    q: 'Preciso de verificar a minha identidade?',
    a: 'Sim, a verificação (KYC) é obrigatória antes de iniciares transações, por exigências de segurança e conformidade.',
  },
];

export default function HelpScreen() {
  const router = useRouter();
  const [open, setOpen] = useState<number | null>(0);

  const openWhatsApp = () => {
    const text = encodeURIComponent('Olá, preciso de ajuda com o KwanzaSafe.');
    Linking.openURL(`https://wa.me/${WHATSAPP}?text=${text}`).catch(() => {});
  };
  const openEmail = () => Linking.openURL(`mailto:${EMAIL}`).catch(() => {});

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Ajuda e suporte" onBack={() => router.back()} />
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
        <View style={styles.hero}>
          <Text style={styles.heroTitle}>Estamos aqui para ajudar</Text>
          <Text style={styles.heroText}>
            Fala connosco diretamente pelo WhatsApp — resposta rápida — ou por email.
          </Text>
          <Button label="Falar no WhatsApp" onPress={openWhatsApp} style={styles.waBtn} />
          <Pressable onPress={openEmail} hitSlop={8} style={styles.emailRow}>
            <Ionicons name="mail-outline" size={16} color={colors.primaryDark} />
            <Text style={styles.emailText}>{EMAIL}</Text>
          </Pressable>
        </View>

        <Text style={styles.section}>Perguntas frequentes</Text>
        <View style={styles.faqList}>
          {FAQ.map((item, i) => {
            const isOpen = open === i;
            return (
              <Pressable key={i} style={styles.faqItem} onPress={() => setOpen(isOpen ? null : i)}>
                <View style={styles.faqHeader}>
                  <Text style={styles.faqQ}>{item.q}</Text>
                  <Ionicons
                    name={isOpen ? 'chevron-up' : 'chevron-down'}
                    size={18}
                    color={colors.textMuted}
                  />
                </View>
                {isOpen && <Text style={styles.faqA}>{item.a}</Text>}
              </Pressable>
            );
          })}
        </View>
      </ScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  scroll: { gap: spacing.lg, paddingVertical: spacing.lg },
  hero: {
    backgroundColor: colors.primaryTint,
    borderRadius: radius.lg,
    padding: spacing.lg,
    gap: spacing.sm,
  },
  heroTitle: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  heroText: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.text, lineHeight: 20, marginBottom: spacing.sm },
  waBtn: { backgroundColor: '#25d366' },
  emailRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: spacing.xs, marginTop: spacing.sm },
  emailText: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.primaryDark },
  section: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  faqList: { gap: spacing.sm },
  faqItem: {
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.md,
    gap: spacing.sm,
  },
  faqHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: spacing.md },
  faqQ: { flex: 1, fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.text },
  faqA: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, lineHeight: 20 },
});
