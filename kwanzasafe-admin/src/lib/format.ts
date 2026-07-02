/** Formata um montante (string/number) no estilo pt: 1.234,56 */
export function formatAmount(value: string | number | null | undefined, decimals = 2): string {
  const n = typeof value === 'string' ? parseFloat(value) : (value ?? 0);
  if (!isFinite(n)) return '0,00';
  const fixed = Math.abs(n).toFixed(decimals);
  const [int, dec] = fixed.split('.');
  const withSep = int.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  return decimals > 0 ? `${withSep},${dec}` : withSep;
}

/** Data curta pt: 02/07/2026 14:30 */
export function formatDate(iso: string | null | undefined): string {
  if (!iso) return '';
  const d = new Date(iso);
  if (isNaN(d.getTime())) return '';
  const p = (n: number) => String(n).padStart(2, '0');
  return `${p(d.getDate())}/${p(d.getMonth() + 1)}/${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}`;
}
