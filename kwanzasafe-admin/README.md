# KwanzaSafe Admin (app)

App Expo (React Native + TypeScript, **SDK 54**) para a administração da KwanzaSafe.
Consome a **API admin** `/api/v1/admin/*` (tokens Sanctum Bearer). Acesso reservado a
staff (`is_admin`). Irmã da app do cliente (`kwanzasafe-app`) e do backend (`kwanzasafe`).

## Fase 1 (núcleo)
- Login de staff (rejeita contas não-admin)
- Dashboard com estatísticas vivas (transações, KYC, volume, utilizadores)
- Transações: lista com filtros + detalhe com transições de estado
  (solicitar pagamento → confirmar pagamento → AOA enviados → aprovar / cancelar / assumir)
  e **chat** (cliente + notas internas) com polling e anexos
- Revisão de **KYC**: lista de pendentes, detalhe (dados + documento + selfie) e aprovar/rejeitar

## Correr localmente
1. `npm install --legacy-peer-deps`
2. Criar `.env` a partir de `.env.example` com o IP LAN do PC:
   `EXPO_PUBLIC_API_URL=http://<IP-LAN>:8000` (emulador Android: `http://10.0.2.2:8000`)
3. Backend a servir em `0.0.0.0:8000` (`php -S 0.0.0.0:8000 router.local.php` da raiz do repo)
4. `npx expo start -c` (reiniciar sempre que mudar o `.env`)

## Build (EAS)
- `eas build --profile production --platform android` gera `.aab` para o Play (package `com.kwanzasafe.admin`).
- Definir `EXPO_PUBLIC_API_URL=https://kwanzasafe.com` no perfil de release do `eas.json`.

## Notas
- `node_modules/` fora do git. Token em `expo-secure-store` (chave `ks_admin_token`).
- Estado: TanStack Query (servidor) + Zustand (sessão). HTTP: axios (`src/api/client.ts`).
