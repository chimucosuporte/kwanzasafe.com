/**
 * Identidade visual KwanzaSafe Admin — tema ESCURO (fintech).
 * Verde de marca sobre fundos quase-pretos. Display: Syne. Corpo: DM Sans.
 */

export const colors = {
  primary: '#00b454',
  primaryDark: '#00913f',
  primaryBright: '#2ee27e',
  primaryTint: '#0f241a',
  primaryTintBorder: '#1d4a32',

  black: '#000000',
  white: '#ffffff',

  bg: '#0a0d0b',
  surface: '#151b18',
  card: '#161c19',
  surfaceAlt: '#1b231f',
  border: '#242d28',
  borderStrong: '#33403a',

  text: '#f3f6f4',
  textMuted: '#8b978f',
  textFaint: '#5c665f',
  textInverse: '#0a0d0b',

  danger: '#ff5a5a',
  dangerTint: '#2a1414',
  success: '#16d06a',
  successTint: '#0f241a',
  warning: '#f0b429',
  warningTint: '#2a2110',
  info: '#4d9bff',
  infoTint: '#0f1d2e',

  overlay: 'rgba(0,0,0,0.66)',
  shadow: '#000000',
} as const;

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
