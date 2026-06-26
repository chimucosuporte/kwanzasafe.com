import { Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Modal,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { KeyboardAvoidingView } from 'react-native-keyboard-controller';
import { SafeAreaView } from 'react-native-safe-area-context';

import { apiErrorMessage } from '@/api/client';
import { fetchMessages, sendMessage } from '@/api/chat';
import { Screen } from '@/components/Screen';
import { dayKey, formatDayLabel } from '@/lib/format';
import { capturePhoto } from '@/lib/imagePicker';
import { pickAttachment } from '@/lib/picker';
import { useAuthStore } from '@/stores/auth';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { ChatMessage, PickedFile } from '@/types/api';

const POLL_MS = 4000;

type ChatItem =
  | { kind: 'day'; id: string; label: string }
  | { kind: 'msg'; id: string; msg: ChatMessage };

/** Junta mensagens novas evitando duplicados, ordenadas por id. */
function mergeMessages(prev: ChatMessage[], incoming: ChatMessage[]): ChatMessage[] {
  if (!incoming.length) return prev;
  const map = new Map(prev.map((m) => [m.id, m]));
  for (const m of incoming) map.set(m.id, m);
  return Array.from(map.values()).sort((a, b) => a.id - b.id);
}

/** Intercala separadores de dia entre as mensagens. */
function withDaySeparators(messages: ChatMessage[]): ChatItem[] {
  const items: ChatItem[] = [];
  let lastDay = '';
  for (const m of messages) {
    const dk = dayKey(m.created_at);
    if (dk && dk !== lastDay) {
      lastDay = dk;
      items.push({ kind: 'day', id: `day-${dk}`, label: formatDayLabel(m.created_at) });
    }
    items.push({ kind: 'msg', id: `m-${m.id}`, msg: m });
  }
  return items;
}

/** Sala de conversa cliente ↔ agente — nível profissional (polling a 4s). */
export default function ChatScreen() {
  const { ref } = useLocalSearchParams<{ ref: string }>();
  const router = useRouter();
  const token = useAuthStore((s) => s.token);

  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [draft, setDraft] = useState('');
  const [attachment, setAttachment] = useState<PickedFile | null>(null);
  const [sending, setSending] = useState(false);
  const [lightbox, setLightbox] = useState<string | null>(null);

  const lastIdRef = useRef(0);
  const listRef = useRef<FlatList<ChatItem>>(null);

  const ingest = useCallback((incoming: ChatMessage[]) => {
    if (!incoming.length) return;
    lastIdRef.current = incoming.reduce((m, x) => Math.max(m, x.id), lastIdRef.current);
    setMessages((prev) => mergeMessages(prev, incoming));
  }, []);

  useEffect(() => {
    if (!ref) return;
    let active = true;

    (async () => {
      setLoading(true);
      try {
        const init = await fetchMessages(ref, 0);
        if (!active) return;
        lastIdRef.current = init.reduce((m, x) => Math.max(m, x.id), 0);
        setMessages(init);
      } catch (e) {
        if (active) setError(apiErrorMessage(e));
      } finally {
        if (active) setLoading(false);
      }
    })();

    const timer = setInterval(async () => {
      try {
        const news = await fetchMessages(ref, lastIdRef.current);
        if (active) ingest(news);
      } catch {
        /* erros transitórios no polling são ignorados */
      }
    }, POLL_MS);

    return () => {
      active = false;
      clearInterval(timer);
    };
  }, [ref, ingest]);

  const items = useMemo(() => withDaySeparators(messages), [messages]);

  const send = async () => {
    const text = draft.trim();
    if ((!text && !attachment) || sending) return;
    setError(null);
    setSending(true);
    try {
      const msg = await sendMessage(ref!, { text: text || undefined, file: attachment });
      ingest([msg]);
      setDraft('');
      setAttachment(null);
    } catch (e) {
      setError(apiErrorMessage(e));
    } finally {
      setSending(false);
    }
  };

  const attach = async () => {
    try {
      const file = await pickAttachment();
      if (file) setAttachment(file);
    } catch (e) {
      setError(apiErrorMessage(e));
    }
  };

  const camera = async () => {
    try {
      const file = await capturePhoto();
      if (file) setAttachment(file);
    } catch (e) {
      setError(apiErrorMessage(e));
    }
  };

  const canSend = (!!draft.trim() || !!attachment) && !sending;
  const attachIsImage = attachment?.mimeType.startsWith('image/');

  return (
    <Screen edges={['top', 'bottom']} style={styles.screen}>
      {/* Header rico */}
      <View style={styles.header}>
        <Pressable onPress={() => router.back()} hitSlop={10} style={styles.back}>
          <Ionicons name="chevron-back" size={24} color={colors.text} />
        </Pressable>
        <View style={styles.agentAvatar}>
          <Ionicons name="headset" size={18} color={colors.primaryBright} />
          <View style={styles.onlineDot} />
        </View>
        <View style={styles.flex}>
          <Text style={styles.agentName}>Apoio KwanzaSafe</Text>
          <Text style={styles.agentSub}>Transação {ref} · responde em minutos</Text>
        </View>
      </View>

      <KeyboardAvoidingView style={styles.flex} behavior="padding" keyboardVerticalOffset={8}>
        {loading ? (
          <View style={styles.center}>
            <ActivityIndicator color={colors.primary} />
          </View>
        ) : (
          <FlatList
            ref={listRef}
            data={items}
            keyExtractor={(it) => it.id}
            renderItem={({ item }) =>
              item.kind === 'day' ? (
                <View style={styles.dayWrap}>
                  <Text style={styles.dayText}>{item.label}</Text>
                </View>
              ) : (
                <Bubble msg={item.msg} token={token} onOpenImage={setLightbox} />
              )
            }
            contentContainerStyle={styles.list}
            ListEmptyComponent={
              <View style={styles.emptyWrap}>
                <View style={styles.emptyIcon}>
                  <Ionicons name="chatbubbles-outline" size={28} color={colors.primaryBright} />
                </View>
                <Text style={styles.emptyTitle}>Começa a conversa</Text>
                <Text style={styles.emptyText}>Diz olá ao agente — respondemos o mais rápido possível. 👋</Text>
              </View>
            }
            onContentSizeChange={() => listRef.current?.scrollToEnd({ animated: true })}
            showsVerticalScrollIndicator={false}
          />
        )}

        {!!error && <Text style={styles.error}>{error}</Text>}

        {/* Pré-visualização do anexo */}
        {attachment && (
          <View style={styles.attachChip}>
            {attachIsImage ? (
              <Image source={{ uri: attachment.uri }} style={styles.attachThumb} contentFit="cover" />
            ) : (
              <View style={styles.attachThumbDoc}>
                <Ionicons name="document-text" size={18} color={colors.primaryBright} />
              </View>
            )}
            <Text style={styles.attachName} numberOfLines={1}>
              {attachment.name}
            </Text>
            <Pressable onPress={() => setAttachment(null)} hitSlop={8}>
              <Ionicons name="close-circle" size={20} color={colors.textMuted} />
            </Pressable>
          </View>
        )}

        {/* Composer */}
        <View style={styles.inputBar}>
          <Pressable onPress={attach} hitSlop={6} style={styles.iconBtn}>
            <Ionicons name="add" size={24} color={colors.textMuted} />
          </Pressable>
          <Pressable onPress={camera} hitSlop={6} style={styles.iconBtn}>
            <Ionicons name="camera-outline" size={22} color={colors.textMuted} />
          </Pressable>
          <TextInput
            style={styles.input}
            value={draft}
            onChangeText={setDraft}
            placeholder="Mensagem…"
            placeholderTextColor={colors.textMuted}
            multiline
          />
          <Pressable onPress={send} disabled={!canSend} style={[styles.sendBtn, !canSend && styles.sendBtnOff]}>
            {sending ? (
              <ActivityIndicator color={colors.white} size="small" />
            ) : (
              <Ionicons name="arrow-up" size={22} color={colors.white} />
            )}
          </Pressable>
        </View>
      </KeyboardAvoidingView>

      {/* Lightbox de imagem */}
      <Modal visible={!!lightbox} transparent animationType="fade" onRequestClose={() => setLightbox(null)}>
        <SafeAreaView style={styles.lightbox}>
          <Pressable style={styles.lightboxClose} onPress={() => setLightbox(null)} hitSlop={12}>
            <Ionicons name="close" size={28} color={colors.white} />
          </Pressable>
          {lightbox && (
            <Image
              source={{ uri: lightbox, headers: token ? { Authorization: `Bearer ${token}` } : undefined }}
              style={styles.lightboxImg}
              contentFit="contain"
            />
          )}
        </SafeAreaView>
      </Modal>
    </Screen>
  );
}

function Bubble({
  msg,
  token,
  onOpenImage,
}: {
  msg: ChatMessage;
  token: string | null;
  onOpenImage: (uri: string) => void;
}) {
  if (msg.is_system) {
    return (
      <View style={styles.systemWrap}>
        <Text style={styles.systemText}>{msg.text}</Text>
      </View>
    );
  }

  const mine = msg.is_mine;
  return (
    <View style={[styles.bubbleRow, mine ? styles.rowMine : styles.rowTheirs]}>
      <View style={[styles.bubble, mine ? styles.bubbleMine : styles.bubbleTheirs]}>
        {msg.is_image && msg.file_url && (
          <Pressable onPress={() => onOpenImage(msg.file_url!)}>
            <Image
              source={{ uri: msg.file_url, headers: token ? { Authorization: `Bearer ${token}` } : undefined }}
              style={styles.bubbleImage}
              contentFit="cover"
              transition={150}
            />
          </Pressable>
        )}
        {msg.is_pdf && msg.file_url && (
          <View style={styles.pdfRow}>
            <Ionicons name="document-text" size={18} color={mine ? colors.white : colors.primaryBright} />
            <Text style={[styles.pdfText, mine ? styles.textMine : styles.textTheirs]}>Documento PDF</Text>
          </View>
        )}
        {!!msg.text && (
          <Text style={[styles.bubbleText, mine ? styles.textMine : styles.textTheirs]}>{msg.text}</Text>
        )}
        <View style={styles.metaRow}>
          <Text style={[styles.bubbleTime, mine ? styles.timeMine : styles.timeTheirs]}>{msg.time}</Text>
          {mine && (
            <Ionicons
              name={msg.is_read ? 'checkmark-done' : 'checkmark'}
              size={14}
              color={msg.is_read ? '#bff5d6' : 'rgba(255,255,255,0.7)'}
            />
          )}
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { paddingHorizontal: 0 },
  flex: { flex: 1 },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },

  header: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    paddingHorizontal: spacing.md,
    paddingBottom: spacing.sm,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  back: { padding: 2 },
  agentAvatar: {
    width: 40,
    height: 40,
    borderRadius: radius.pill,
    backgroundColor: colors.primaryTint,
    borderWidth: 1,
    borderColor: colors.primaryTintBorder,
    alignItems: 'center',
    justifyContent: 'center',
  },
  onlineDot: {
    position: 'absolute',
    right: 1,
    bottom: 1,
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: colors.primaryBright,
    borderWidth: 2,
    borderColor: colors.bg,
  },
  agentName: { fontFamily: fonts.displaySemi, fontSize: fontSize.md, color: colors.text },
  agentSub: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted },

  list: { padding: spacing.md, gap: spacing.sm, flexGrow: 1 },

  dayWrap: { alignItems: 'center', paddingVertical: spacing.sm },
  dayText: {
    fontFamily: fonts.bodyMedium,
    fontSize: fontSize.xs,
    color: colors.textMuted,
    backgroundColor: colors.surface,
    borderRadius: radius.pill,
    paddingVertical: 4,
    paddingHorizontal: spacing.md,
    overflow: 'hidden',
  },

  emptyWrap: { alignItems: 'center', justifyContent: 'center', gap: spacing.sm, paddingTop: spacing.xxl },
  emptyIcon: {
    width: 64,
    height: 64,
    borderRadius: radius.pill,
    backgroundColor: colors.primaryTint,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.xs,
  },
  emptyTitle: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  emptyText: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, textAlign: 'center', paddingHorizontal: spacing.lg, lineHeight: 20 },

  systemWrap: { alignItems: 'center', paddingHorizontal: spacing.lg },
  systemText: {
    fontFamily: fonts.body,
    fontSize: fontSize.xs,
    color: colors.textMuted,
    textAlign: 'center',
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    paddingVertical: spacing.xs,
    paddingHorizontal: spacing.md,
    overflow: 'hidden',
  },

  bubbleRow: { flexDirection: 'row' },
  rowMine: { justifyContent: 'flex-end' },
  rowTheirs: { justifyContent: 'flex-start' },
  bubble: { maxWidth: '82%', borderRadius: radius.lg, paddingVertical: spacing.sm, paddingHorizontal: spacing.md, gap: 6 },
  bubbleMine: { backgroundColor: colors.primary, borderBottomRightRadius: radius.sm },
  bubbleTheirs: { backgroundColor: colors.card, borderWidth: 1, borderColor: colors.border, borderBottomLeftRadius: radius.sm },
  bubbleImage: { width: 210, height: 210, borderRadius: radius.md, backgroundColor: colors.surfaceAlt },
  pdfRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
  pdfText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm },
  bubbleText: { fontFamily: fonts.body, fontSize: fontSize.md, lineHeight: 21 },
  textMine: { color: colors.white },
  textTheirs: { color: colors.text },
  metaRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'flex-end', gap: 4 },
  bubbleTime: { fontFamily: fonts.body, fontSize: 10 },
  timeMine: { color: 'rgba(255,255,255,0.8)' },
  timeTheirs: { color: colors.textMuted },

  attachChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.md,
    padding: spacing.sm,
    marginHorizontal: spacing.md,
    marginBottom: spacing.xs,
  },
  attachThumb: { width: 36, height: 36, borderRadius: radius.sm },
  attachThumbDoc: {
    width: 36,
    height: 36,
    borderRadius: radius.sm,
    backgroundColor: colors.primaryTint,
    alignItems: 'center',
    justifyContent: 'center',
  },
  attachName: { flex: 1, fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text },

  inputBar: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    gap: spacing.xs,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  iconBtn: { width: 40, height: 44, alignItems: 'center', justifyContent: 'center' },
  input: {
    flex: 1,
    minHeight: 44,
    maxHeight: 120,
    borderRadius: radius.xl,
    borderWidth: 1.5,
    borderColor: colors.border,
    backgroundColor: colors.surfaceAlt,
    paddingHorizontal: spacing.md,
    paddingTop: spacing.sm,
    paddingBottom: spacing.sm,
    fontFamily: fonts.body,
    fontSize: fontSize.md,
    color: colors.text,
  },
  sendBtn: {
    width: 44,
    height: 44,
    borderRadius: radius.pill,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.primary,
  },
  sendBtnOff: { backgroundColor: colors.surfaceAlt },
  error: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.danger, paddingHorizontal: spacing.md, paddingBottom: spacing.xs },

  lightbox: { flex: 1, backgroundColor: 'rgba(0,0,0,0.95)', alignItems: 'center', justifyContent: 'center' },
  lightboxClose: { position: 'absolute', top: 50, right: 20, zIndex: 2, padding: spacing.sm },
  lightboxImg: { width: '100%', height: '80%' },
});
