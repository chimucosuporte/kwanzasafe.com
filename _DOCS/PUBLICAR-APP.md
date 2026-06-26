# 📱 Publicar a app KwanzaSafe (Android) — `.aab` + `.apk`

> App: **Expo SDK 54** · pacote `com.kwanzasafe.app` · conta Expo `edsonchimuco` · projectId `801f2c1f-1b28-438d-9494-b16a66a71ef6`.
> Os builds correm na **cloud da Expo** (EAS). Precisas de estar autenticado: `npx eas-cli@latest login`.

---

## 0. Pré-requisitos (uma vez)

1. **A API tem de estar em produção primeiro.** Os builds estão configurados (`eas.json`) para falar com **`https://kwanzasafe.com`** (ver `env.EXPO_PUBLIC_API_URL` nos perfis `preview` e `production`). Faz primeiro o **deploy do servidor** (ver `_DOCS/DEPLOY.md`) e confirma que `https://kwanzasafe.com/api/v1/rates` responde **200 JSON**.
2. Login Expo: `cd kwanzasafe-app && npx eas-cli@latest login`.
3. Confirma a config: `npx expo-doctor` (deve passar) e `npx eas-cli build:list` (vê builds anteriores).

> ⚠️ Tudo o que se segue corre **dentro de `kwanzasafe-app/`**.

---

## 1. `.aab` para a Google Play Store (perfil `production`)

O `.aab` (Android App Bundle) é o formato **obrigatório** para publicar na Play Store.

```bash
cd kwanzasafe-app
npx eas-cli build --profile production --platform android
```

- Usa `buildType: app-bundle` → gera **`.aab`**.
- `autoIncrement: true` → o **versionCode** sobe sozinho a cada build (1.ª vez fica 3, já que o `app.json` está em `versionCode: 2`).
- Demora ~10–20 min. No fim, a EAS dá um link para **descarregar o `.aab`**.

### Submeter à Play Store
Depois de teres a conta Google Play Console (taxa única de 25 USD) e a ficha da app criada:

```bash
npx eas-cli submit --profile production --platform android
```
…ou faz upload manual do `.aab` em **Play Console → Produção → Criar nova versão**.

---

## 2. `.apk` para instalação direta (perfil `preview`)

O `.apk` serve para **instalar fora da Play Store** (testes, link direto no dashboard).

```bash
cd kwanzasafe-app
npx eas-cli build --profile preview --platform android
```

- Usa `buildType: apk` → gera **`.apk`** instalável.
- No fim, **descarrega o `.apk`** do link da EAS.

### Disponibilizar no dashboard ("Baixar APK")
1. Renomeia o ficheiro para **`kwanzasafe.apk`**.
2. Coloca-o em **`public_html/downloads/kwanzasafe.apk`** no servidor.
3. O botão **"Baixar APK"** aparece automaticamente no dashboard do utilizador (o banner "Continua no telemóvel"). Enquanto o ficheiro não existir, só aparece o botão **Google Play**.

> O `.apk` da Play Store **não** se descarrega diretamente; por isso mantemos este `.apk` próprio para o download no site.

---

## 3. Notificações push em produção (FCM)

A app já tem `google-services.json` e `expo-notifications`. Para o push **funcionar nos builds de loja**:

1. Confirma que as credenciais FCM V1 estão na EAS: `npx eas-cli credentials` → Android → *Push Notifications (FCM)*.
2. Se faltar, faz upload da **chave de conta de serviço** do Firebase (Project Settings → Service accounts → Generate new private key) quando a EAS pedir.

O backend já guarda o token (`users.expo_push_token`) e envia via Expo — não muda nada do lado do servidor.

---

## 4. Atualizações futuras (sem nova app na loja)

Para correções rápidas de JS/assets (sem rebuild nativo), usa **EAS Update** (canal `production` já configurado):

```bash
cd kwanzasafe-app
npx eas-cli update --branch production --message "descrição da correção"
```
Mudanças nativas (novas permissões, libs nativas, ícone, versão) **exigem novo build** (.aab) + nova versão na loja.

---

## ✅ Checklist de release

- [ ] API em produção a responder (`/api/v1/rates` → 200).
- [ ] `eas.json` aponta para `https://kwanzasafe.com` (✓ já configurado).
- [ ] `eas build --profile production --platform android` → `.aab` para a loja.
- [ ] `eas build --profile preview --platform android` → `.apk` → `public_html/downloads/kwanzasafe.apk`.
- [ ] Credenciais FCM na EAS (push).
- [ ] Ficha da app na Play Console (ícone, screenshots, descrição PT, política de privacidade = `https://kwanzasafe.com/privacidade`).
- [ ] Submeter `.aab` (revisão Google ~1–3 dias).
