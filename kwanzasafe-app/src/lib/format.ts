/**
 * Helpers de formatação (dinheiro e datas) — estilo pt-AO/pt-PT.
 *
 * Os montantes chegam da API como string (precisão decimal). Convertemos para
 * número apenas para apresentação; nunca para cálculos financeiros autoritativos
 * (esses são sempre do servidor).
 */

/** Converte uma string/number da API para número seguro (0 em caso de falha). */
export function toNumber(value: string | number | null | undefined): number {
  if (value == null) return 0;
  const n = typeof value === 'number' ? value : parseFloat(value);
  return Number.isFinite(n) ? n : 0;
}

/**
 * Formata um montante no estilo português: milhares com `.` e decimais com `,`.
 * Ex.: 187500 → "187.500,00".
 */
export function formatAmount(value: string | number | null | undefined, decimals = 2): string {
  const fixed = toNumber(value).toFixed(decimals);
  const [intPart, decPart] = fixed.split('.');
  const grouped = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  return decimals > 0 ? `${grouped},${decPart}` : grouped;
}

/** Montante com código da moeda. Ex.: "187.500,00 AOA". */
export function formatMoney(
  value: string | number | null | undefined,
  currency: string,
  decimals = 2,
): string {
  return `${formatAmount(value, decimals)} ${currency}`;
}

/** Data/hora curta a partir de ISO8601. Ex.: "15/06/2026 13:45". */
export function formatDate(iso: string | null | undefined): string {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  const pad = (x: number) => String(x).padStart(2, '0');
  return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

/** Chave do dia (AAAA-MM-DD) para agrupar mensagens; '' se inválida. */
export function dayKey(iso: string | null | undefined): string {
  if (!iso) return '';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '';
  const pad = (x: number) => String(x).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

/** Rótulo amigável do dia: "Hoje", "Ontem" ou "15 de junho de 2026". */
export function formatDayLabel(iso: string | null | undefined): string {
  if (!iso) return '';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '';
  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);
  if (dayKey(iso) === dayKey(today.toISOString())) return 'Hoje';
  if (dayKey(iso) === dayKey(yesterday.toISOString())) return 'Ontem';
  const meses = [
    'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
    'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro',
  ];
  return `${d.getDate()} de ${meses[d.getMonth()]} de ${d.getFullYear()}`;
}
