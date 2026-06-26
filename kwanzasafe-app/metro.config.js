// Configuração Metro do Expo.
// `unstable_enablePackageExports = false`: usa resolução clássica (campos
// main/react-native/source + índice de diretório). Necessário para o
// react-native-keyboard-controller, cujo `source` (src/index.ts) importa
// `./types` (pasta) — com package exports activo o Metro não resolvia.
const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);

config.resolver.unstable_enablePackageExports = false;

module.exports = config;
