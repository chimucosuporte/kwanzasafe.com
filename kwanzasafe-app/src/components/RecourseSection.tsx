import { Ionicons } from '@expo/vector-icons';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useState } from 'react';
import { Alert, Modal, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';

import { apiErrorMessage } from '@/api/client';
import { cancelRecourse, fetchRecourse, openRecourse } from '@/api/recourse';
import { Button } from '@/components/Button';
import { Countdown } from '@/components/Countdown';
import { formatDate } from '@/lib/format';
import { toast } from '@/stores/toast';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { RecourseStatus } from '@/types/api';

const TONE: Record<RecourseStatus, string> = {
  open: colors.warning,
  in_review: colors.info,
  resolved: colors.primaryBright,
  rejected: colors.danger,
  cancelled: colors.textMuted,
};

/**
 * Secção de recurso (appeal) de uma transação: estado actual, abrir (com
 * motivo), cancelar. A conversa em si decorre no chat (canal 'recourse').
 */
export function RecourseSection({ reference }: { reference: string }) {
  const queryClient = useQueryClient();
  const [open, setOpen] = useState(false);
  const [reason, setReason] = useState('');

  const { data: recourse, isLoading } = useQuery({
    queryKey: ['recourse', reference],
    queryFn: () => fetchRecourse(reference),
  });

  const refresh = () => queryClient.invalidateQueries({ queryKey: ['recourse', reference] });

  const openM = useMutation({
    mutationFn: () => openRecourse(reference, reason.trim()),
    onSuccess: () => {
      setOpen(false);
      setReason('');
      void refresh();
      toast.success('Recurso submetido. Um super-administrador vai analisar o teu caso.');
    },
    onError: (e) => toast.error(apiErrorMessage(e)),
  });

  const cancelM = useMutation({
    mutationFn: () => cancelRecourse(reference),
    onSuccess: () => {
      void refresh();
      toast.success('Recurso cancelado.');
    },
    onError: (e) => toast.error(apiErrorMessage(e)),
  });

  if (isLoading) return null;

  const active = recourse?.is_active;

  const submit = () => {
    if (reason.trim().length < 10) {
      toast.error('Descreve o motivo com pelo menos 10 caracteres.');
      return;
    }
    openM.mutate();
  };

  const confirmCancel = () =>
    Alert.alert('Cancelar recurso', 'Tens a certeza de que queres cancelar este recurso?', [
      { text: 'Voltar', style: 'cancel' },
      { text: 'Cancelar recurso', style: 'destructive', onPress: () => cancelM.mutate() },
    ]);

  return (
    <View style={styles.card}>
      <View style={styles.head}>
        <Ionicons name="shield-half-outline" size={18} color={colors.text} />
        <Text style={styles.title}>Recurso</Text>
        {recourse && (
          <View style={[styles.badge, { backgroundColor: `${TONE[recourse.status]}22` }]}>
            <Text style={[styles.badgeText, { color: TONE[recourse.status] }]}>{recourse.status_label}</Text>
          </View>
        )}
      </View>

      {!recourse || !active ? (
        <>
          {recourse && (
            <Text style={styles.muted}>
              Último recurso: {recourse.status_label}
              {recourse.resolution ? ` — ${recourse.resolution}` : ''} ({formatDate(recourse.created_at)})
            </Text>
          )}
          <Text style={styles.muted}>
            Sentes que houve um erro nesta transação? Abre um recurso e um super-administrador analisa o teu caso.
          </Text>
          <Button label="Solicitar recurso" variant="ghost" onPress={() => setOpen(true)} />
        </>
      ) : (
        <>
          <Text style={styles.reason}>{recourse.reason}</Text>
          <Countdown expiresAt={recourse.expires_at} status="open" />
          <Text style={styles.muted}>Acompanha as respostas do super-administrador na conversa desta transação.</Text>
          <Button label="Cancelar recurso" variant="ghost" onPress={confirmCancel} loading={cancelM.isPending} />
        </>
      )}

      {/* Modal: motivo do recurso */}
      <Modal visible={open} transparent animationType="slide" onRequestClose={() => setOpen(false)}>
        <Pressable style={styles.backdrop} onPress={() => setOpen(false)} />
        <View style={styles.sheet}>
          <View style={styles.sheetHead}>
            <Text style={styles.sheetTitle}>Solicitar recurso</Text>
            <Pressable onPress={() => setOpen(false)} hitSlop={10}>
              <Ionicons name="close" size={22} color={colors.textMuted} />
            </Pressable>
          </View>
          <Text style={styles.muted}>Explica o que aconteceu. Sê claro — isto ajuda a resolver o teu caso.</Text>
          <TextInput
            value={reason}
            onChangeText={setReason}
            placeholder="Descreve o motivo do recurso…"
            placeholderTextColor={colors.textMuted}
            style={styles.input}
            multiline
            maxLength={2000}
          />
          <Text style={styles.counter}>{reason.length}/2000</Text>
          <Button label="Submeter recurso" onPress={submit} loading={openM.isPending} />
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.lg,
    gap: spacing.sm,
  },
  head: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
  title: { flex: 1, fontFamily: fonts.displaySemi, fontSize: fontSize.md, color: colors.text },
  badge: { borderRadius: radius.pill, paddingVertical: 3, paddingHorizontal: spacing.sm },
  badgeText: { fontFamily: fonts.bodyBold, fontSize: fontSize.xs },
  muted: { fontFamily: fonts.body, fontSize: fontSize.sm, color: colors.textMuted, lineHeight: 20 },
  reason: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text, lineHeight: 20 },

  backdrop: { ...StyleSheet.absoluteFillObject, backgroundColor: colors.overlay },
  sheet: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: colors.card,
    borderTopLeftRadius: radius.xl,
    borderTopRightRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border,
    padding: spacing.lg,
    paddingBottom: spacing.xl,
    gap: spacing.sm,
  },
  sheetHead: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  sheetTitle: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  input: {
    minHeight: 120,
    borderRadius: radius.md,
    borderWidth: 1.5,
    borderColor: colors.border,
    backgroundColor: colors.surfaceAlt,
    padding: spacing.md,
    fontFamily: fonts.body,
    fontSize: fontSize.md,
    color: colors.text,
    textAlignVertical: 'top',
  },
  counter: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textFaint, textAlign: 'right' },
});
