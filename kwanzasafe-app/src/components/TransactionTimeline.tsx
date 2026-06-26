import { Ionicons } from '@expo/vector-icons';
import { StyleSheet, Text, View } from 'react-native';

import { formatDate } from '@/lib/format';
import { colors, fonts, fontSize, radius, spacing } from '@/theme';
import type { Transaction, TransactionStatus } from '@/types/api';

type IconName = keyof typeof Ionicons.glyphMap;

/** Rank do estado no caminho normal (para saber até onde a transação avançou). */
const RANK: Record<TransactionStatus, number> = {
  pending: 1,
  negotiating: 1,
  awaiting_payment: 2,
  payment_received: 3,
  processing: 3,
  aoa_sent: 4,
  completed: 5,
  cancelled: 0,
  expired: 0,
};

type Stage = { rank: number; label: string; icon: IconName; timeKey: keyof Transaction | null };

const STAGES: Stage[] = [
  { rank: 1, label: 'Transação criada', icon: 'add-circle', timeKey: 'created_at' },
  { rank: 2, label: 'A aguardar pagamento', icon: 'time', timeKey: null },
  { rank: 3, label: 'Pagamento recebido', icon: 'card', timeKey: 'payment_received_at' },
  { rank: 4, label: 'Kwanzas enviados', icon: 'paper-plane', timeKey: 'aoa_sent_at' },
  { rank: 5, label: 'Concluída', icon: 'checkmark-done', timeKey: 'client_confirmed_at' },
];

/**
 * Linha do tempo do estado de uma transação: marcos concluídos (verde),
 * o estado actual (destacado) e os passos seguintes (a cinza). Estados
 * terminais (cancelada/expirada) terminam com um nó vermelho.
 */
export function TransactionTimeline({ tx }: { tx: Transaction }) {
  const terminal = tx.status === 'cancelled' || tx.status === 'expired';
  const currentRank = RANK[tx.status] ?? 1;

  return (
    <View style={styles.card}>
      <Text style={styles.title}>Estado da transação</Text>

      {STAGES.map((stage, i) => {
        // Em estados terminais, só a criação conta como concluída.
        const reached = terminal ? stage.rank <= 1 : currentRank >= stage.rank;
        const state: 'done' | 'current' | 'todo' = terminal
          ? (stage.rank <= 1 ? 'done' : 'todo')
          : currentRank > stage.rank
            ? 'done'
            : currentRank === stage.rank
              ? 'current'
              : 'todo';

        const time = stage.timeKey ? (tx[stage.timeKey] as string | null) : null;
        const nextReached = terminal ? false : currentRank >= STAGES[i + 1]?.rank;

        return (
          <View key={stage.label} style={styles.row}>
            <View style={styles.rail}>
              <Node state={state} icon={stage.icon} />
              {i < STAGES.length - 1 && <View style={[styles.line, nextReached && styles.lineOn]} />}
            </View>
            <View style={styles.texts}>
              <Text style={[styles.label, state === 'todo' && styles.labelTodo]}>{stage.label}</Text>
              {state === 'current' && !terminal && <Text style={styles.current}>A decorrer…</Text>}
              {reached && !!time && <Text style={styles.time}>{formatDate(time)}</Text>}
            </View>
          </View>
        );
      })}

      {terminal && (
        <View style={styles.row}>
          <View style={styles.rail}>
            <View style={[styles.node, styles.nodeTerminal]}>
              <Ionicons name="close" size={16} color={colors.white} />
            </View>
          </View>
          <View style={styles.texts}>
            <Text style={[styles.label, styles.labelTerminal]}>
              {tx.status === 'cancelled' ? 'Transação cancelada' : 'Transação expirada'}
            </Text>
          </View>
        </View>
      )}
    </View>
  );
}

function Node({ state, icon }: { state: 'done' | 'current' | 'todo'; icon: IconName }) {
  if (state === 'done') {
    return (
      <View style={[styles.node, styles.nodeDone]}>
        <Ionicons name="checkmark" size={16} color={colors.white} />
      </View>
    );
  }
  if (state === 'current') {
    return (
      <View style={[styles.node, styles.nodeCurrent]}>
        <Ionicons name={icon} size={15} color={colors.primaryBright} />
      </View>
    );
  }
  return (
    <View style={[styles.node, styles.nodeTodo]}>
      <Ionicons name={icon} size={15} color={colors.textFaint} />
    </View>
  );
}

const NODE = 30;

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.lg,
    padding: spacing.lg,
    gap: 0,
  },
  title: { fontFamily: fonts.displaySemi, fontSize: fontSize.md, color: colors.text, marginBottom: spacing.md },
  row: { flexDirection: 'row', gap: spacing.md },
  rail: { alignItems: 'center', width: NODE },
  node: { width: NODE, height: NODE, borderRadius: NODE, alignItems: 'center', justifyContent: 'center' },
  nodeDone: { backgroundColor: colors.primary },
  nodeCurrent: { backgroundColor: colors.primaryTint, borderWidth: 1.5, borderColor: colors.primaryBright },
  nodeTodo: { backgroundColor: colors.surface, borderWidth: 1.5, borderColor: colors.border },
  nodeTerminal: { backgroundColor: colors.danger },
  line: { width: 2, flex: 1, minHeight: 18, backgroundColor: colors.border, marginVertical: 2 },
  lineOn: { backgroundColor: colors.primary },
  texts: { flex: 1, paddingBottom: spacing.lg, paddingTop: 4 },
  label: { fontFamily: fonts.bodyBold, fontSize: fontSize.sm, color: colors.text },
  labelTodo: { color: colors.textMuted, fontFamily: fonts.bodyMedium },
  labelTerminal: { color: colors.danger },
  current: { fontFamily: fonts.bodyMedium, fontSize: fontSize.xs, color: colors.primaryBright, marginTop: 2 },
  time: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.textMuted, marginTop: 2 },
});
