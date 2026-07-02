import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, Alert, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { KeyboardAvoidingView } from 'react-native-keyboard-controller';

import { closeRecourse, fetchRecourse, replyRecourse } from '@/api/admin';
import { apiErrorMessage } from '@/api/client';
import { Button } from '@/components/Button';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { formatDate } from '@/lib/format';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { RecourseMessage } from '@/types/api';

export default function RecourseDetailScreen() {
  const { id: idParam } = useLocalSearchParams<{ id: string }>();
  const id = Number(idParam);
  const router = useRouter();
  const qc = useQueryClient();

  const { data: r, isLoading, isError, refetch } = useQuery({ queryKey: ['admin-recourse', id], queryFn: () => fetchRecourse(id) });

  const [text, setText] = useState('');
  const [closing, setClosing] = useState<null | 'resolve' | 'reject'>(null);
  const [resolution, setResolution] = useState('');

  const refresh = () => { void qc.invalidateQueries({ queryKey: ['admin-recourse', id] }); void qc.invalidateQueries({ queryKey: ['admin-recourses'] }); };

  const replyM = useMutation({ mutationFn: () => replyRecourse(id, text.trim()), onSuccess: () => { setText(''); refresh(); }, onError: (e) => Alert.alert('Erro', apiErrorMessage(e)) });
  const closeM = useMutation({
    mutationFn: () => closeRecourse(id, closing!, resolution.trim()),
    onSuccess: () => { setClosing(null); setResolution(''); refresh(); router.back(); },
    onError: (e) => Alert.alert('Erro', apiErrorMessage(e)),
  });

  if (isLoading) return <Screen edges={['top', 'bottom']}><ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} /></Screen>;
  if (isError || !r) return <Screen edges={['top', 'bottom']}><Header title="Recurso" onBack={() => router.back()} /><Pressable onPress={() => refetch()}><Text style={styles.err}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable></Screen>;

  const isActive = r.status === 'open' || r.status === 'in_review';

  return (
    <Screen edges={['top', 'bottom']} padded={false}>
      <View style={{ paddingHorizontal: spacing.md }}>
        <Header title={`Recurso #${r.reference_id ?? r.transaction_id}`} subtitle={r.status_label} onBack={() => router.back()} />
      </View>
      <KeyboardAvoidingView behavior="padding" style={{ flex: 1 }} keyboardVerticalOffset={8}>
        <ScrollView style={{ flex: 1 }} contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
          <View style={styles.card}>
            <Text style={styles.client}>{r.client_name}</Text>
            <Text style={styles.email}>{r.client_email}</Text>
            <Text style={styles.reasonLabel}>Motivo do recurso</Text>
            <Text style={styles.reason}>{r.reason ?? '—'}</Text>
            {!!r.resolution && (<><Text style={styles.reasonLabel}>Resolução</Text><Text style={styles.reason}>{r.resolution}</Text></>)}
          </View>

          <Text style={styles.section}>Conversa do recurso</Text>
          {r.messages.map((m) => <Bubble key={m.id} m={m} />)}
          {r.messages.length === 0 && <Text style={styles.noMsg}>Sem mensagens ainda.</Text>}

          {isActive && (
            <View style={styles.closeRow}>
              <Button label="Resolver" onPress={() => setClosing('resolve')} style={{ flex: 1 }} />
              <Button label="Indeferir" variant="danger" onPress={() => setClosing('reject')} style={{ flex: 1 }} />
            </View>
          )}

          {closing && (
            <View style={styles.card}>
              <Text style={styles.section}>{closing === 'resolve' ? 'Resolução' : 'Motivo do indeferimento'}</Text>
              <TextInput value={resolution} onChangeText={setResolution} multiline placeholder="Descreve a decisão para o cliente…" placeholderTextColor={colors.textFaint} style={styles.resInput} />
              <View style={{ flexDirection: 'row', gap: spacing.sm, marginTop: spacing.sm }}>
                <Button label="Cancelar" variant="ghost" onPress={() => setClosing(null)} style={{ flex: 1 }} />
                <Button label="Confirmar" variant={closing === 'reject' ? 'danger' : 'primary'} onPress={() => closeM.mutate()} loading={closeM.isPending} disabled={resolution.trim().length < 3} style={{ flex: 1 }} />
              </View>
            </View>
          )}
        </ScrollView>

        {isActive && !closing && (
          <View style={styles.composer}>
            <TextInput value={text} onChangeText={setText} placeholder="Responder ao cliente…" placeholderTextColor={colors.textFaint} style={styles.input} multiline />
            <Pressable onPress={() => replyM.mutate()} disabled={replyM.isPending || !text.trim()} style={[styles.sendBtn, (replyM.isPending || !text.trim()) && { opacity: 0.4 }]}>
              {replyM.isPending ? <ActivityIndicator color={colors.white} size="small" /> : <Text style={styles.sendText}>Enviar</Text>}
            </Pressable>
          </View>
        )}
      </KeyboardAvoidingView>
    </Screen>
  );
}

function Bubble({ m }: { m: RecourseMessage }) {
  if (m.is_system) return <Text style={styles.sys}>{m.text}</Text>;
  return (
    <View style={[styles.bubble, m.is_mine ? styles.mine : styles.theirs]}>
      <Text style={[styles.bubbleText, m.is_mine && { color: colors.white }]}>{m.text}</Text>
      <Text style={[styles.time, m.is_mine && { color: 'rgba(255,255,255,0.7)' }]}>{formatDate(m.created_at)}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  scroll: { paddingHorizontal: spacing.md, paddingBottom: spacing.md, gap: spacing.sm },
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginTop: spacing.md },
  card: { backgroundColor: colors.card, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md },
  client: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  email: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  reasonLabel: { fontFamily: fonts.bodyBold, fontSize: fontSize.xs, color: colors.textMuted, marginTop: spacing.sm, textTransform: 'uppercase' },
  reason: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.text, marginTop: 2 },
  section: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted, marginTop: spacing.sm },
  noMsg: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textFaint, textAlign: 'center', paddingVertical: spacing.md },
  sys: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, textAlign: 'center', backgroundColor: colors.surface, alignSelf: 'center', paddingVertical: 4, paddingHorizontal: 10, borderRadius: radius.pill, overflow: 'hidden' },
  bubble: { maxWidth: '85%', borderRadius: radius.md, padding: spacing.sm, paddingHorizontal: 12 },
  mine: { alignSelf: 'flex-end', backgroundColor: colors.primary },
  theirs: { alignSelf: 'flex-start', backgroundColor: colors.surfaceAlt },
  bubbleText: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.text },
  time: { fontFamily: fonts.body, fontSize: 10, color: colors.textFaint, marginTop: 2 },
  closeRow: { flexDirection: 'row', gap: spacing.sm, marginTop: spacing.sm },
  resInput: { minHeight: 80, backgroundColor: colors.surfaceAlt, borderRadius: radius.md, padding: spacing.md, fontFamily: fonts.body, fontSize: fontSize.md, color: colors.text, textAlignVertical: 'top', marginTop: spacing.sm },
  composer: { flexDirection: 'row', gap: spacing.sm, borderTopWidth: 1, borderTopColor: colors.border, padding: spacing.sm, backgroundColor: colors.bg, alignItems: 'flex-end' },
  input: { flex: 1, maxHeight: 100, backgroundColor: colors.surfaceAlt, borderRadius: radius.md, paddingHorizontal: spacing.md, paddingVertical: 10, fontFamily: fonts.body, fontSize: fontSize.md, color: colors.text },
  sendBtn: { height: 44, paddingHorizontal: spacing.md, borderRadius: radius.md, backgroundColor: colors.primary, alignItems: 'center', justifyContent: 'center' },
  sendText: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.white },
});
