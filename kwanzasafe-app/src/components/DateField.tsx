import { Ionicons } from '@expo/vector-icons';
import { useState } from 'react';
import { FlatList, Modal, Pressable, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

import { colors, fonts, fontSize, radius, spacing } from '@/theme';

const MONTHS = [
  'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
];

type Part = 'y' | 'm' | 'd';

/** Nº de dias do mês (1-12), respeitando anos bissextos. */
function daysInMonth(year: number, month: number): number {
  if (!year || !month) return 31;
  return new Date(year, month, 0).getDate();
}

function parse(value?: string): { y: number; m: number; d: number } {
  const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value ?? '');
  if (!match) return { y: 0, m: 0, d: 0 };
  return { y: +match[1], m: +match[2], d: +match[3] };
}

function pad(n: number): string {
  return String(n).padStart(2, '0');
}

/**
 * Campo de data em 3 seletores (ano/mês/dia) que funcionam em conjunto.
 * Os dias disponíveis dependem do mês/ano escolhidos (abril → 30, fev → 28/29).
 * Emite uma data completa AAAA-MM-DD via onChange (ou '' se incompleta).
 */
export function DateField({
  label,
  value,
  onChange,
  error,
  minYear,
  maxYear,
  yearOrder = 'desc',
}: {
  label: string;
  value?: string;
  onChange: (value: string) => void;
  error?: string | null;
  minYear: number;
  maxYear: number;
  yearOrder?: 'asc' | 'desc';
}) {
  const insets = useSafeAreaInsets();
  const [open, setOpen] = useState<Part | null>(null);
  const { y, m, d } = parse(value);

  const years: number[] = [];
  for (let yr = minYear; yr <= maxYear; yr++) years.push(yr);
  if (yearOrder === 'desc') years.reverse();

  const maxDay = daysInMonth(y, m);

  const setPart = (part: Part, val: number) => {
    let ny = y, nm = m, nd = d;
    if (part === 'y') ny = val;
    if (part === 'm') nm = val;
    if (part === 'd') nd = val;
    // Ajusta o dia se passou a ser inválido (ex.: 31 → abril).
    const md = daysInMonth(ny, nm);
    if (nd > md) nd = md;
    onChange(ny && nm && nd ? `${ny}-${pad(nm)}-${pad(nd)}` : '');
    setOpen(null);
  };

  const options =
    open === 'y' ? years.map((v) => ({ v, label: String(v) }))
    : open === 'm' ? MONTHS.map((name, i) => ({ v: i + 1, label: name }))
    : open === 'd' ? Array.from({ length: maxDay }, (_, i) => ({ v: i + 1, label: String(i + 1) }))
    : [];

  const selectedValue = open === 'y' ? y : open === 'm' ? m : open === 'd' ? d : 0;

  return (
    <View style={styles.wrapper}>
      <Text style={styles.label}>{label}</Text>
      <View style={styles.row}>
        <Selector flex={1.1} placeholder="Ano" value={y ? String(y) : ''} onPress={() => setOpen('y')} error={!!error} />
        <Selector flex={1.4} placeholder="Mês" value={m ? MONTHS[m - 1] : ''} onPress={() => setOpen('m')} error={!!error} />
        <Selector flex={0.9} placeholder="Dia" value={d ? String(d) : ''} onPress={() => setOpen('d')} error={!!error} />
      </View>
      {!!error && <Text style={styles.error}>{error}</Text>}

      <Modal visible={open !== null} transparent animationType="slide" onRequestClose={() => setOpen(null)}>
        <Pressable style={styles.backdrop} onPress={() => setOpen(null)} />
        <View style={[styles.sheet, { paddingBottom: insets.bottom + spacing.md }]}>
          <View style={styles.sheetHead}>
            <Text style={styles.sheetTitle}>
              {open === 'y' ? 'Ano' : open === 'm' ? 'Mês' : 'Dia'}
            </Text>
            <Pressable onPress={() => setOpen(null)} hitSlop={10}>
              <Ionicons name="close" size={22} color={colors.textMuted} />
            </Pressable>
          </View>
          <FlatList
            data={options}
            keyExtractor={(o) => String(o.v)}
            initialNumToRender={20}
            style={styles.list}
            renderItem={({ item }) => {
              const active = item.v === selectedValue;
              return (
                <Pressable
                  style={[styles.option, active && styles.optionActive]}
                  onPress={() => open && setPart(open, item.v)}
                >
                  <Text style={[styles.optionText, active && styles.optionTextActive]}>{item.label}</Text>
                  {active && <Ionicons name="checkmark" size={18} color={colors.primaryBright} />}
                </Pressable>
              );
            }}
          />
        </View>
      </Modal>
    </View>
  );
}

function Selector({
  placeholder,
  value,
  onPress,
  error,
  flex,
}: {
  placeholder: string;
  value: string;
  onPress: () => void;
  error?: boolean;
  flex: number;
}) {
  return (
    <Pressable style={[styles.selector, { flex }, error && styles.selectorError]} onPress={onPress}>
      <Text style={[styles.selectorText, !value && styles.selectorPlaceholder]} numberOfLines={1}>
        {value || placeholder}
      </Text>
      <Ionicons name="chevron-down" size={14} color={colors.textMuted} />
    </Pressable>
  );
}

const styles = StyleSheet.create({
  wrapper: { gap: spacing.xs },
  label: { fontFamily: fonts.bodyMedium, fontSize: fontSize.sm, color: colors.text },
  row: { flexDirection: 'row', gap: spacing.sm },
  selector: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 4,
    height: 54,
    borderRadius: radius.md,
    borderWidth: 1.5,
    borderColor: colors.border,
    backgroundColor: colors.surfaceAlt,
    paddingHorizontal: spacing.md,
  },
  selectorError: { borderColor: colors.danger },
  selectorText: { flex: 1, fontFamily: fonts.body, fontSize: fontSize.md, color: colors.text },
  selectorPlaceholder: { color: colors.textMuted },
  error: { fontFamily: fonts.body, fontSize: fontSize.xs, color: colors.danger },

  backdrop: { ...StyleSheet.absoluteFillObject, backgroundColor: colors.overlay },
  sheet: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    maxHeight: '70%',
    backgroundColor: colors.card,
    borderTopLeftRadius: radius.xl,
    borderTopRightRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.border,
    paddingTop: spacing.md,
  },
  sheetHead: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.sm,
  },
  sheetTitle: { fontFamily: fonts.displaySemi, fontSize: fontSize.lg, color: colors.text },
  list: { paddingHorizontal: spacing.md },
  option: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.md,
    borderRadius: radius.md,
  },
  optionActive: { backgroundColor: colors.primaryTint },
  optionText: { fontFamily: fonts.bodyMedium, fontSize: fontSize.md, color: colors.text },
  optionTextActive: { color: colors.primaryBright },
});
