# KwanzaSafe — App Mobile

App oficial da KwanzaSafe em **React Native + TypeScript + Expo (SDK 55)**, a consumir a
API REST do backend Laravel (`/api/v1`, autenticação por tokens Sanctum).

> Backend e plano de fases: ver `../kwanzasafe/` e a secção 20 do `../CLAUDE.md`.

## Stack

| Área | Tecnologia |
|---|---|
| Navegação | Expo Router (file-based, `src/app/`) |
| Estado de servidor | TanStack Query |
| Estado de cliente | Zustand (`src/stores/auth.ts`) |
| HTTP | Axios (`src/api/client.ts`) com injecção de token + tratamento de 401 |
| Token | expo-secure-store (Keychain/Keystore) — nunca AsyncStorage |
| Fontes | Syne (display) + DM Sans (corpo) via `@expo-google-fonts/*` |
| Tema | `src/theme/` — verde `#009d44`, preto, branco |

## Estrutura

```
src/
├── app/                      # rotas (Expo Router)
│   ├── _layout.tsx           # providers + fontes + AuthGate (redirecção por sessão)
│   ├── (auth)/login.tsx      # ecrã de login (público)
│   └── (app)/index.tsx       # home autenticada (stub — calculadora chega na FASE 3)
├── api/{client,auth}.ts      # cliente HTTP único + chamadas de auth
├── stores/auth.ts            # sessão (token + user) em Zustand
├── lib/{secureStore,queryClient}.ts
├── components/               # Button, TextField, Screen, Wordmark
├── theme/                    # cores, tipografia, espaçamentos
├── types/api.ts              # tipos alinhados com as API Resources do Laravel
└── config.ts                 # base da API (EXPO_PUBLIC_API_URL)
```

## Configuração

Copia `.env.example` para `.env` e define a base da API:

```bash
cp .env.example .env
```

- **Produção:** `EXPO_PUBLIC_API_URL=https://kwanzasafe.com`
- **Local (WAMP):** usa o **IP da máquina na LAN** (não `localhost`, que no telemóvel
  aponta para o próprio aparelho), e serve o backend com
  `php -S 0.0.0.0:8000 router.local.php` a partir da raiz do repo.

A base `/api/v1` é acrescentada automaticamente em `src/config.ts`.

## Arrancar (Expo Go)

```bash
npm install
npm start          # abre o Metro; lê o QR code com a app Expo Go
```

## Verificação

```bash
npx tsc --noEmit   # type-check estrito
npx expo-doctor    # saúde do projecto
```

## Próximas fases

- **FASE 3:** calculadora (taxas) + criar/listar/detalhe de transações.
- **FASE 4:** sala de transação (chat com polling).
- **FASE 5:** KYC com câmara + EAS development build.
- **FASE 6:** push, biometria, ícone/splash com identidade, polimento.
