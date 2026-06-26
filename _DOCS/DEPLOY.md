# 🚀 Deploy KwanzaSafe — Produção (Hostinger)

> Servidor: `u763057780@195.35.41.218 -p 65002`
> Laravel: `~/domains/kwanzasafe.com/kwanzasafe/` · Público: `~/domains/kwanzasafe.com/public_html/`
> Esta versão entrega: **site público corporativo novo**, **API mobile `/api/v1`**, **banner "Continua na app"** no dashboard, e **12 migrações novas**.

---

## ⚠️ Antes de tudo — BACKUP

```bash
# no servidor, antes de qualquer mudança
cd ~/domains/kwanzasafe.com/kwanzasafe
php artisan down --message="Manutenção em curso" --retry=60

# backup da base de dados
mysqldump -u u763057780_... -p u763057780_kwansafe_db > ~/backup_$(date +%F_%H%M).sql

# backup rápido do código atual (caso precises reverter)
cd ~/domains/kwanzasafe.com
cp -r kwanzasafe kwanzasafe_bak_$(date +%F)
```

---

## Opção A — Deploy por ZIP (recomendado nesta entrega)

> Usa esta via porque as alterações **não foram commitadas/enviadas** para o Git — o `git pull` não as traria.

Tens 2 arquivos (gerados localmente em `_DOCS/deploy/`):
- `kwanzasafe-deploy.tar.gz` → conteúdo da pasta `kwanzasafe/`
- `public_html-deploy.tar.gz` → conteúdo da pasta `public_html/`

Os arquivos **NÃO incluem**: `vendor/`, `node_modules/`, `.git/`, `.env`, `storage/logs`, caches. Por isso **descompacta POR CIMA** da app existente (preserva o teu `.env` de produção, o `vendor/` e os uploads em `storage/`).

```bash
# 1) enviar os arquivos para o servidor (a partir do teu PC)
scp -P 65002 _DOCS/deploy/kwanzasafe-deploy.tar.gz   u763057780@195.35.41.218:~/domains/kwanzasafe.com/
scp -P 65002 _DOCS/deploy/public_html-deploy.tar.gz  u763057780@195.35.41.218:~/domains/kwanzasafe.com/

# 2) no servidor: extrair POR CIMA das pastas existentes
cd ~/domains/kwanzasafe.com
tar xzf kwanzasafe-deploy.tar.gz   -C kwanzasafe/      # extrai o conteúdo dentro de kwanzasafe/
tar xzf public_html-deploy.tar.gz  -C public_html/     # extrai o conteúdo dentro de public_html/
```

> Se o `composer.lock` mudou (normalmente não muda nesta entrega): `composer install --no-dev --optimize-autoloader`.

Salta para **"3. Migrações + cache"** abaixo.

---

## Opção B — Deploy por Git

Só funciona se tiveres **feito commit + push** das alterações primeiro (no teu PC):
```bash
# no teu PC
git add -A && git commit -m "Site público corporativo + API mobile + banner app"
git push origin main
```
Depois, no servidor:
```bash
cd ~/domains/kwanzasafe.com/kwanzasafe
git pull origin main
composer install --no-dev --optimize-autoloader   # só se o composer.lock mudou
```

---

## 3. Migrações + cache (ambas as opções)

```bash
cd ~/domains/kwanzasafe.com/kwanzasafe

# aplicar as 12 migrações novas
php artisan migrate --force

# storage link (se ainda não existir — para ficheiros públicos)
php artisan storage:link

# recompilar caches de produção
php artisan optimize        # config + route + view cache
php artisan up              # tirar de manutenção
```

### Migrações desta entrega (aplicadas por `migrate --force`)
```
2026_06_04_100001  add_support_roles_to_users_table
2026_06_04_100002  add_channel_to_chat_messages
2026_06_04_100003  create_recourses_table
2026_06_04_100004  create_staff_messages_table
2026_06_04_100005  create_payment_accounts_table
2026_06_21_100001  create_payment_wallets_table
2026_06_21_100002  add_two_factor_to_users_table
2026_06_22_100001  add_expo_push_token_to_users_table
2026_06_23_100001  make_chat_messages_sender_id_nullable
2026_06_23_100002  add_destination_to_transactions
2026_06_24_100001  add_cancel_and_expiry_to_recourses
2026_06_24_100002  create_user_notifications_table
```

---

## 4. Confirmar o `.env` de produção (no servidor)

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kwanzasafe.com
# SANCTUM (API mobile por bearer token — sem cookies)
SANCTUM_TOKEN_EXPIRATION=          # vazio = não expira (ou define minutos)
# SMTP Hostinger (já configurado)
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=geral@kwanzasafe.com
```

> ⚠️ Nunca enviar o `.env` local para o servidor — o de produção tem os segredos corretos.

---

## 5. Verificações pós-deploy

```bash
# API mobile a responder (essencial para a app)
curl -s https://kwanzasafe.com/api/v1/rates | head -c 200      # → JSON de taxas

# páginas públicas
curl -s -o /dev/null -w "%{http_code}\n" https://kwanzasafe.com/            # 200
curl -s -o /dev/null -w "%{http_code}\n" https://kwanzasafe.com/termos      # 200
curl -s -o /dev/null -w "%{http_code}\n" https://kwanzasafe.com/sitemap.xml # 200
```

No browser:
- [ ] `https://kwanzasafe.com` — carrossel do hero a deslizar, calculadora, bandeiras, footer.
- [ ] `/login` e `/register` — layout 2 colunas com painel de marca.
- [ ] `/termos` e `/privacidade` — índice lateral sticky.
- [ ] Login no dashboard → banner **"Continua no telemóvel"** (botão Google Play; "Baixar APK" aparece depois de subires o `.apk`).
- [ ] Favicon KwanzaSafe no separador do dashboard e do admin.

---

## 6. App mobile

Depois do servidor estar no ar (API a responder), seguir **`_DOCS/PUBLICAR-APP.md`** para gerar o `.aab` (loja) e o `.apk` (→ `public_html/downloads/kwanzasafe.apk`).

---

## Reverter (se algo correr mal)
```bash
cd ~/domains/kwanzasafe.com
rm -rf kwanzasafe && mv kwanzasafe_bak_AAAA-MM-DD kwanzasafe
mysql -u u763057780_... -p u763057780_kwansafe_db < ~/backup_AAAA-MM-DD_HHMM.sql
cd kwanzasafe && php artisan optimize && php artisan up
```
