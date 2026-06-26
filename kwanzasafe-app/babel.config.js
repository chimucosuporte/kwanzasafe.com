module.exports = function (api) {
  api.cache(true);
  return {
    // babel-preset-expo (SDK 54) inclui automaticamente o plugin de worklets do
    // react-native-reanimated — necessário para o auto-scroll do
    // react-native-keyboard-controller (KeyboardAwareScrollView).
    presets: ['babel-preset-expo'],
  };
};
