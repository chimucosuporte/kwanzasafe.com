import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Image } from 'expo-image';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useCallback, useEffect, useRef, useState } from 'react';
import { ActivityIndicator, Alert, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { KeyboardAvoidingView } from 'react-native-keyboard-controller';

import { actOnTransaction, fetchMessages, fetchTransaction, sendMessage, type TxAction } from '@/api/admin';
import { apiErrorMessage } from '@/api/client';
import { Header } from '@/components/Header';
import { Screen } from '@/components/Screen';
import { StatusBadge } from '@/components/StatusBadge';
import { formatAmount, formatDate } from '@/lib/format';
import { pickAttachment } from '@/lib/picker';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { AdminChatMessage, PickedFile } from '@/types/api';

export default function TransactionDetailScreen() {
  const { id: idParam } = useLocalSearchParams<{ id: string }>();
  const id = Number(idParam);
  const router = useRouter();
  const qc = useQueryClient();
  const token = useAuthStore((s) => s.token);

  const { data: tx, isLoading, isError, refetch } = useQuery({
    queryKey: ['admin-tx', id],
    queryFn: () => fetchTransaction(id),
  });

  const [messages, setMessages] = useState<AdminChatMessage[]>([]);
  const [text, setText] = useState('');
  const [channel, setChannel] = useState<'client' | 'internal'>('client');
  const [file, setFile] = useState<PickedFile | null>(null);
  const [sending, setSending] = useState(false);
  const scrollRef = useRef<ScrollView>(null);
  const lastId = messages.length ? messages[messages.length - 1].id : 0;

  const poll = useCallback(async () => {
    try {
      const after = messages.length ? messages[messages.length - 1].id : 0;
      const fresh = await fetchMessages(id, after);
      if (fresh.length) {
        setMessages((prev) => {
          const seen = new Set(prev.map((m) => m.id));
          return [...prev, ...fresh.filter((m) => !seen.has(m.id))];
        });
      }
    } catch {
      /* silencioso */
    }
  }, [id, messages]);

  useEffect(() => {
    void poll();
    const t = setInterval(poll, 4000);
    return () => clearInterval(t);
  }, [poll]);

  useEffect(() => {
    const t = setTimeout(() => scrollRef.current?.scrollToEnd({ animated: true }), 100);
    return () => clearTimeout(t);
  }, [messages.length]);

  const actM = useMutation({
    mutationFn: (action: TxAction) => actOnTransaction(id, action),
    onSuccess: (updated) => qc.setQueryData(['admin-tx', id], updated),
    onError: (e) => Alert.alert('Erro', apiErrorMessage(e)),
  });

  const confirmAct = (action: TxAction, title: string) => {
    Alert.alert(title, 'Confirmas esta ação?', [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Confirmar', onPress: () => actM.mutate(action) },
    ]);
  };

  const onSend = async () => {
    if (!text.trim() && !file) return;
    setSending(true);
    try {
      const msg = await sendMessage(id, { text: text.trim() || undefined, channel, file });
      setMessages((prev) => [...prev, msg]);
      setText('');
      setFile(null);
    } catch (e) {
      Alert.alert('Erro', apiErrorMessage(e));
    } finally {
      setSending(false);
    }
  };

  if (isLoading) return <Screen edges={['top', 'bottom']}><ActivityIndicator color={colors.primary} style={{ marginTop: spacing.xl }} /></Screen>;
  if (isError || !tx) return <Screen edges={['top', 'bottom']}><Header title="Transação" onBack={() => router.back()} /><Pressable onPress={() => refetch()}><Text style={styles.err}>Erro ao carregar. Toca para tentar de novo.</Text></Pressable></Screen>;

  const a = tx.actions;

  return (
    <Screen edges={['top', 'bottom']} padded={false}>
      <View style={{ paddingHorizontal: spacing.md }}>
        <Header title={`#${tx.reference_id}`} onBack={() => router.back()} right={<StatusBadge status={tx.status} label={tx.status_label} />} />
      </View>

      <KeyboardAvoidingView behavior="padding" style={{ flex: 1 }} keyboardVerticalOffset={8}>
        <ScrollView ref={scrollRef} style={{ flex: 1 }} contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
          {/* Resumo */}
          <View style={styles.card}>
            <Text style={styles.client}>{tx.client_name}</Text>
            <Text style={styles.email}>{tx.client_email}{tx.client_phone ? ` · ${tx.client_phone}` : ''}</Text>
            <View style={styles.amounts}>
              <View style={styles.amtBox}><Text style={styles.amtLabel}>Envia</Text><Text style={styles.amtValue}>{formatAmount(tx.amount_sent)} {tx.currency_from}</Text></View>
              <View style={styles.amtBox}><Text style={styles.amtLabel}>Recebe</Text><Text style={[styles.amtValue, { color: colors.primaryBright }]}>{formatAmount(tx.amount_received, 0)} AOA</Text></View>
            </View>
            <Text style={styles.rate}>Taxa: 1 {tx.currency_from} = {formatAmount(tx.rate_applied)} · {formatDate(tx.created_at)}</Text>
            {!!tx.agent_name && <Text style={styles.rate}>Agente: {tx.agent_name}</Text>}
          </View>

          {/* Ações */}
          {a && (
            <View style={styles.actions}>
              {a.can_request_payment && <ActBtn icon="cash-outline" label="Solicitar pagamento" onPress={() => actM.mutate('request-payment')} />}
              {a.can_payment_received && <ActBtn icon="checkmark-done-outline" label="Confirmar pagamento" onPress={() => actM.mutate('payment-received')} />}
              {a.can_aoa_sent && <ActBtn icon="send-outline" label="Marcar AOA enviados" onPress={() => actM.mutate('aoa-sent')} />}
              {a.can_approve && <ActBtn icon="shield-checkmark-outline" label="Aprovar / concluir" tone={colors.success} onPress={() => confirmAct('approve', 'Aprovar transação')} />}
              {a.can_assign && <ActBtn icon="person-add-outline" label="Assumir tíquete" onPress={() => actM.mutate('assign')} />}
              {a.can_cancel && <ActBtn icon="close-circle-outline" label="Cancelar" tone={colors.danger} onPress={() => confirmAct('cancel', 'Cancelar transação')} />}
            </View>
          )}

          {/* Conversa */}
          <Text style={styles.section}>Conversa</Text>
          {messages.map((m) => <Bubble key={m.id} m={m} token={token} />)}
          {messages.length === 0 && <Text style={styles.noMsg}>Sem mensagens ainda.</Text>}
        </ScrollView>

        {/* Composer */}
        <View style={styles.composer}>
          <View style={styles.channelRow}>
            {(['client', 'internal'] as const).map((c) => (
              <Pressable key={c} onPress={() => setChannel(c)} style={[styles.chTab, channel === c && styles.chTabOn]}>
                <Text style={[styles.chText, channel === c && styles.chTextOn]}>{c === 'client' ? 'Cliente' : 'Nota interna'}</Text>
              </Pressable>
            ))}
          </View>
          {!!file && <Text style={styles.fileName} numberOfLines={1}>📎 {file.name} <Text onPress={() => setFile(null)} style={{ color: colors.danger }}>✕</Text></Text>}
          <View style={styles.inputRow}>
            <Pressable onPress={async () => setFile(await pickAttachment())} hitSlop={8} style={styles.attach}>
              <Ionicons name="attach" size={22} color={colors.textMuted} />
            </Pressable>
            <TextInput
              value={text} onChangeText={setText}
              placeholder={channel === 'internal' ? 'Nota interna (staff)…' : 'Mensagem ao cliente…'}
              placeholderTextColor={colors.textFaint} style={styles.input} multiline
            />
            <Pressable onPress={onSend} disabled={sending || (!text.trim() && !file)} style={[styles.sendBtn, (sending || (!text.trim() && !file)) && { opacity: 0.4 }]}>
              {sending ? <ActivityIndicator color={colors.white} size="small" /> : <Ionicons name="send" size={18} color={colors.white} />}
            </Pressable>
          </View>
        </View>
      </KeyboardAvoidingView>
    </Screen>
  );
}

function ActBtn({ icon, label, onPress, tone }: { icon: any; label: string; onPress: () => void; tone?: string }) {
  return (
    <Pressable onPress={onPress} style={styles.actBtn}>
      <Ionicons name={icon} size={18} color={tone ?? colors.primaryBright} />
      <Text style={[styles.actLabel, tone ? { color: tone } : null]}>{label}</Text>
    </Pressable>
  );
}

function Bubble({ m, token }: { m: AdminChatMessage; token: string | null }) {
  if (m.is_system) return <Text style={styles.sys}>{m.text}</Text>;
  const mine = m.is_mine;
  return (
    <View style={[styles.bubble, mine ? styles.mine : styles.theirs, m.channel === 'internal' && styles.internal]}>
      {m.is_image && m.file_url ? (
        <Image source={{ uri: m.file_url, headers: token ? { Authorization: `Bearer ${token}` } : undefined }} style={styles.img} contentFit="cover" />
      ) : m.file_url ? (
        <Text style={styles.fileMsg}>📎 {m.text}</Text>
      ) : (
        <Text style={[styles.bubbleText, mine && { color: colors.white }]}>{m.text}</Text>
      )}
      {m.channel === 'internal' && <Text style={styles.internalTag}>nota interna</Text>}
    </View>
  );
}

const styles = StyleSheet.create({
  scroll: { paddingHorizontal: spacing.md, paddingBottom: spacing.md, gap: spacing.sm },
  err: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, marginTop: spacing.md },

  card: { backgroundColor: colors.card, borderWidth: 1, borderColor: colors.border, borderRadius: radius.lg, padding: spacing.md, gap: 4 },
  client: { fontFamily: fonts.bodyBold, fontSize: fontSize.md, color: colors.text },
  email: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted },
  amounts: { flexDirection: 'row', gap: spacing.sm, marginTop: spacing.sm },
  amtBox: { flex: 1, backgroundColor: colors.surface, borderRadius: radius.md, padding: spacing.sm },
  amtLabel: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted },
  amtValue: { fontFamily: fonts.displaySemi, fontSize: fontSize.md, color: colors.text, marginTop: 2 },
  rate: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textFaint, marginTop: 4 },

  actions: { gap: spacing.sm },
  actBtn: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm, backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: radius.md, paddingVertical: 12, paddingHorizontal: spacing.md },
  actLabel: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.primaryBright },

  section: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.textMuted, marginTop: spacing.md },
  noMsg: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textFaint, textAlign: 'center', paddingVertical: spacing.md },
  sys: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, textAlign: 'center', backgroundColor: colors.surface, alignSelf: 'center', paddingVertical: 4, paddingHorizontal: 10, borderRadius: radius.pill, overflow: 'hidden' },
  bubble: { maxWidth: '82%', borderRadius: radius.md, padding: spacing.sm, paddingHorizontal: 12 },
  mine: { alignSelf: 'flex-end', backgroundColor: colors.primary },
  theirs: { alignSelf: 'flex-start', backgroundColor: colors.surfaceAlt },
  internal: { backgroundColor: colors.warningTint, borderWidth: 1, borderColor: colors.warning },
  bubbleText: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.text },
  fileMsg: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text },
  img: { width: 200, height: 200, borderRadius: radius.sm },
  internalTag: { fontFamily: fonts.body, fontSize: 9, color: colors.warning, marginTop: 2 },

  composer: { borderTopWidth: 1, borderTopColor: colors.border, padding: spacing.sm, backgroundColor: colors.bg, gap: spacing.xs },
  channelRow: { flexDirection: 'row', gap: spacing.xs },
  chTab: { paddingVertical: 4, paddingHorizontal: 12, borderRadius: radius.pill, borderWidth: 1, borderColor: colors.border },
  chTabOn: { backgroundColor: colors.primaryTint, borderColor: colors.primary },
  chText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.xs, color: colors.textMuted },
  chTextOn: { color: colors.primaryBright },
  fileName: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, paddingHorizontal: 4 },
  inputRow: { flexDirection: 'row', alignItems: 'flex-end', gap: spacing.xs },
  attach: { padding: 8 },
  input: { flex: 1, maxHeight: 100, backgroundColor: colors.surfaceAlt, borderRadius: radius.md, paddingHorizontal: spacing.md, paddingVertical: 10, fontFamily: fonts.body, fontSize: fontSize.md, color: colors.text },
  sendBtn: { width: 42, height: 42, borderRadius: radius.md, backgroundColor: colors.primary, alignItems: 'center', justifyContent: 'center' },
});
