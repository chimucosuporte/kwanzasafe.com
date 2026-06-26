/**
 * Copiar para a área de transferência.
 * Import LAZY de expo-clipboard (módulo nativo) — degrada para false se o
 * módulo não existir na build, em vez de crashar.
 */
export async function copyToClipboard(text: string): Promise<boolean> {
  try {
    const Clipboard = await import('expo-clipboard');
    await Clipboard.setStringAsync(text);
    return true;
  } catch {
    return false;
  }
}
