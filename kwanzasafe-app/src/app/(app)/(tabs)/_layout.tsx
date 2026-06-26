import { Ionicons } from '@expo/vector-icons';
import { Tabs } from 'expo-router';
import { StyleSheet } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

import { colors, fonts } from '@/theme';

type IconName = keyof typeof Ionicons.glyphMap;

/** Ícone de tab com variante preenchida quando activo. */
function tabIcon(base: IconName) {
  const Icon = ({ color, size, focused }: { color: string; size: number; focused: boolean }) => (
    <Ionicons name={focused ? base : (`${base}-outline` as IconName)} size={size} color={color} />
  );
  Icon.displayName = `TabIcon(${base})`;
  return Icon;
}

/**
 * Barra de separadores fixa (Início · Transações · Beneficiários · Conta).
 * Os ecrãs de detalhe/fluxo NÃO estão aqui — vivem no Stack pai ((app)/_layout),
 * para que "voltar" siga a hierarquia e o estado das tabs persista.
 */
export default function TabsLayout() {
  const insets = useSafeAreaInsets();

  return (
    <Tabs
      backBehavior="history"
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: colors.textMuted,
        tabBarLabelStyle: styles.label,
        tabBarHideOnKeyboard: true,
        tabBarStyle: [styles.bar, { height: 58 + insets.bottom, paddingBottom: insets.bottom + 6 }],
        tabBarItemStyle: styles.item,
      }}
    >
      <Tabs.Screen name="index" options={{ title: 'Início', tabBarIcon: tabIcon('home') }} />
      <Tabs.Screen name="transactions/index" options={{ title: 'Transações', tabBarIcon: tabIcon('swap-horizontal') }} />
      <Tabs.Screen name="beneficiaries" options={{ title: 'Beneficiários', tabBarIcon: tabIcon('wallet') }} />
      <Tabs.Screen name="profile" options={{ title: 'Conta', tabBarIcon: tabIcon('person') }} />
    </Tabs>
  );
}

const styles = StyleSheet.create({
  bar: {
    backgroundColor: colors.card,
    borderTopColor: colors.border,
    borderTopWidth: 1,
    paddingTop: 8,
    elevation: 0,
  },
  item: { paddingTop: 2 },
  label: { fontFamily: fonts.bodyMedium, fontSize: 11, marginTop: 2 },
});
