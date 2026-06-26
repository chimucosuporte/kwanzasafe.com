/**
 * Identidade visual KwanzaSafe — tema ESCURO (cripto/fintech).
 * Verde de marca #009d44 sobre fundos quase-pretos. Display: Syne. Corpo: DM Sans.
 *
 * Os tokens mantêm as mesmas chaves do tema claro anterior: ao trocar só os
 * valores, todo o app que importa `colors.*` herda o dark automaticamente.
 */

export const colors = {
  // Marca
  primary: '#00b454', // verde KwanzaSafe, ligeiramente mais vivo para legibilidade no escuro
  primaryDark: '#00913f',
  primaryBright: '#2ee27e', // brilhos / realces
  primaryTint: '#0f241a', // superfície verde-escura (chips activos, avatar)
  primaryTintBorder: '#1d4a32',

  black: '#000000',
  white: '#ffffff',

  // Fundos / superfícies (escuro)
  bg: '#0a0d0b', // fundo da app (quase-preto com leve tom verde)
  surface: '#151b18', // superfície base (blocos internos, chips)
  card: '#161c19', // painéis / cartões elevados
  surfaceAlt: '#1b231f', // inputs e blocos mais claros
  border: '#242d28', // contornos subtis
  borderStrong: '#33403a',

  // Texto
  text: '#f3f6f4',
  textMuted: '#8b978f',
  textFaint: '#5c665f',
  textInverse: '#0a0d0b',

  // Semânticos (tom + tint escuro)
  danger: '#ff5a5a',
  dangerTint: '#2a1414',
  success: '#16d06a',
  successTint: '#0f241a',
  warning: '#f0b429',
  warningTint: '#2a2110',
  info: '#4d9bff',
  infoTint: '#0f1d2e',

  // Utilitários
  overlay: 'rgba(0,0,0,0.66)',
  shadow: '#000000',
  scrim: 'rgba(255,255,255,0.06)', // realce translúcido sobre superfícies escuras
} as const;

/**
 * Famílias de fontes — os nomes têm de coincidir com as chaves passadas
 * a `useFonts` (pacotes @expo-google-fonts/*).
 */
export const fonts = {
  display: 'Syne_800ExtraBold',
  displaySemi: 'Syne_700Bold',
  body: 'DMSans_400Regular',
  bodyMedium: 'DMSans_500Medium',
  bodyBold: 'DMSans_700Bold',
} as const;

export const spacing = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
  xxl: 48,
} as const;

export const radius = {
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  pill: 999,
} as const;

export const fontSize = {
  xs: 12,
  sm: 14,
  md: 16,
  lg: 18,
  xl: 22,
  xxl: 28,
  display: 34,
} as const;

/** Sombras/elevação prontas a espalhar em estilos (iOS + Android). */
export const elevation = {
  none: {},
  sm: {
    shadowColor: colors.shadow,
    shadowOpacity: 0.3,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 4 },
    elevation: 4,
  },
  md: {
    shadowColor: colors.shadow,
    shadowOpacity: 0.4,
    shadowRadius: 16,
    shadowOffset: { width: 0, height: 8 },
    elevation: 8,
  },
  glowPrimary: {
    shadowColor: colors.primary,
    shadowOpacity: 0.45,
    shadowRadius: 18,
    shadowOffset: { width: 0, height: 8 },
    elevation: 10,
  },
} as const;
