import { Ionicons } from '@expo/vector-icons';
import { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';

import { colors, fonts, fontSize, radius, spacing } from '@/theme';

/** Estados em que a transação ainda está "viva" e o prazo conta. */
const ACTIVE = ['pending', 'negotiating', 'awaiting_payment'];

function format(ms: number): string {
  if (ms <= 0) return 'Expirada';
  const s = Math.floor(ms / 1000);
  const d = Math.floor(s / 86400);
  const h = Math.floor((s % 86400) / 3600);
  const m = Math.floor((s % 3600) / 60);
  const sec = s % 60;
  if (d > 0) return `${d}d ${h}h`;
  if (h > 0) return `${h}h ${m}m`;
  if (m > 0) return `${m}m ${sec}s`;
  return `${sec}s`;
}

/**
 * Contador de validade da transação. Só aparece em estados activos com prazo.
 * Fica em tom de urgência quando falta menos de 1 hora.
 */
export function Countdown({ expiresAt, status }: { expiresAt?: string | null; status: string }) {
  const [now, setNow] = useState(() => Date.now());

  useEffect(() => {
    const t = setInterval(() => setNow(Date.now()), 1000);
    return () => clearInterval(t);
  }, []);

  if (!expiresAt || !ACTIVE.includes(status)) return null;

  const target = new Date(expiresAt).getTime();
  if (Number.isNaN(target)) return null;

  const remaining = target - now;
  const expired = remaining <= 0;
  const urgent = !expired && remaining < 3600 * 1000; // < 1h

  const tone = expired ? colors.danger : urgent ? colors.warning : colors.textMuted;
  const bg = expired ? colors.dangerTint : urgent ? colors.warningTint : colors.surface;

  return (
    <View style={[styles.pill, { backgroundColor: bg }]}>
      <Ionicons name={expired ? 'alert-circle' : 'time-outline'} size={14} color={tone} />
      <Text style={[styles.text, { color: tone }]}>
        {expired ? 'Prazo terminado' : `Expira em ${format(remaining)}`}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  pill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    alignSelf: 'flex-start',
    borderRadius: radius.pill,
    paddingVertical: 4,
    paddingHorizontal: spacing.sm,
  },
  text: { fontFamily: fonts.bodyMedium, fontSize: fontSize.xs },
});
