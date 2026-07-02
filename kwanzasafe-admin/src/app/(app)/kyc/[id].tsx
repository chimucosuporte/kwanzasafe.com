import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Image } from 'expo-image';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, Alert, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';

import { approveKyc, fetchKycDetail, rejectKyc } from '@/api/admin';
import { apiErrorMessage } from '@/api/client';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { formatDate } from '@/lib/format';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';

export default function KycDetailScreen() {
  const { id: idParam } = useLocalSearchParams<{ id: string }>();
  const id = Number(idParam);
  const router = useRouter();
  const qc = useQueryClient();
  const token = useAuthStore((s) => s.token);

  const { data: u, isLoading, isError, refetch } = useQuery({
    queryKey: ['admin-kyc', id],
    queryFn: () => fetchKycDetail(id),
  });

  const [rejecting, setRejecting] = useState(false);
  const [reason, setReason] = useState('');

  const done = () => { void qc.invalidateQueries({ queryKey: ['admin-kyc'] }); router.back(); };

  const approveM = useMutation({
    mutationFn: () => approveKyc(id),
    onSuccess: done,
    onError: (e) => Alert.alert('Erro', apiErrorMessage(e)),
  });
  const rejectM = useMutation({
    mutationFn: () => rejectKyc(id, reason.trim()),
    onSuccess: done,
    onError: (e) => Alert.alert('Erro', apiErrorMessage(e)),
  });

  if (isLoading) return <Screen edges={['top', 'bottom']}><ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} /></Screen>;
  if (isError || !u) return <Screen edges={['top', 'bottom']}><Header title="KYC" onBack={() => router.back()} /><Pressable onPress={() => refetch()}><Text style={styles.err}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable></Screen>;

  const headers = token ? { Authorization: `Bearer ${token}` } : undefined;

  return (
    <Screen edges={['top', 'bottom']}>
      <Header title="Revisão KYC" subtitle={u.full_name ?? u.email} onBack={() => router.back()} />
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: spacing.xxl, gap: spacing.md }}>
        {/* Estado do bot */}
        <View style={styles.botCard}>
          <Text style={styles.botScore}>Score do bot: <Text style={{ color: colors.primaryBright }}>{u.kyc_score ?? 0}/100</Text></Text>
          <Text style={styles.botStatus}>{u.kyc_bot_status ?? '—'}{u.is_verified ? ' · JÁ VERIFICADO' : ''}</Text>
        </View>

        {/* Dados pessoais */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Dados pessoais</Text>
          <Field k="Nome" v={u.full_name} />
          <Field k="Email" v={u.email} />
          <Field k="Nº BI" v={u.bi_number} />
          <Field k="Validade BI" v={u.bi_expiry} />
          <Field k="Nascimento" v={u.birth_date} />
          <Field k="Género" v={u.gender} />
          <Field k="Telefone" v={u.phone_number ? `${u.phone_number}${u.phone_verified ? ' ✓' : ''}` : null} />
          <Field k="Província" v={u.province} />
          <Field k="Município" v={u.municipality} />
          <Field k="Morada" v={u.address} />
          <Field k="Submetido" v={formatDate(u.submitted_at)} />
        </View>

        {/* Documento */}
        {u.document_url && (
          <View style={styles.card}>
            <Text style={styles.cardTitle}>Documento de identidade</Text>
            <Image source={{ uri: u.document_url, headers }} style={styles.docImg} contentFit="contain" />
          </View>
        )}
        {/* Selfie */}
        {u.photo_url && (
          <View style={styles.card}>
            <Text style={styles.cardTitle}>Selfie</Text>
            <Image source={{ uri: u.photo_url, headers }} style={styles.selfie} contentFit="cover" />
          </View>
        )}

        {/* Ações */}
        {!rejecting ? (
          <View style={{ gap: spacing.sm }}>
            <Button label="Aprovar KYC" onPress={() => approveM.mutate()} loading={approveM.isPending} />
            <Button label="Rejeitar" variant="danger" onPress={() => setRejecting(true)} />
          </View>
        ) : (
          <View style={styles.card}>
            <Text style={styles.cardTitle}>Motivo da rejeição</Text>
            <TextInput
              value={reason} onChangeText={setReason} multiline
              placeholder="Ex.: Documento ilegível, reenvie uma foto nítida."
              placeholderTextColor={colors.textFaint} style={styles.reason}
            />
            <View style={{ flexDirection: 'row', gap: spacing.sm, marginTop: spacing.sm }}>
              <Button label="Voltar" variant="ghost" onPress={() => setRejecting(false)} style={{ flex: 1 }} />
              <Button label="Confirmar rejeição" variant="danger" onPress={() => rejectM.mutate()} loading={rejectM.isPending} disabled={reason.trim().length < 5} style={{ flex: 1 }} />
            </View>
          </View>
        )}
      </ScrollView>
    </Screen>
  );
}

function Field({ k, v }: { k: string; v: string | null | undefined }) {
  return (
    <View style={styles.field}>
      <Text style={styles.fieldK}>{k}</Text>
      <Text style={styles.fieldV}>{v || '—'}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginTop: spacing.md },
  botCard: { backgroundColor: colors.primaryTint, borderWidth: 1, borderColor: colors.primaryTintBorder, borderRadius: radius.lg, padding: spacing.md },
  botScore: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  botStatus: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.textMuted, marginTop: 2, textTransform: 'uppercase' },
  card: { backgroundColor: colors.card, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md },
  cardTitle: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted, marginBottom: spacing.sm, textTransform: 'uppercase' },
  field: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 6, borderBottomWidth: 1, borderBottomColor: colors.border, gap: spacing.md },
  fieldK: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  fieldV: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text, flexShrink: 1, textAlign: 'right' },
  docImg: { width: '100%', height: 220, borderRadius: radius.md, backgroundColor: colors.surface },
  selfie: { width: 160, height: 160, borderRadius: radius.md, alignSelf: 'center', backgroundColor: colors.surface },
  reason: { minHeight: 90, backgroundColor: colors.surfaceAlt, borderRadius: radius.md, padding: spacing.md, fontFamily: fonts.body, fontSize: fontSize.md, color: colors.text, textAlignVertical: 'top' },
});
