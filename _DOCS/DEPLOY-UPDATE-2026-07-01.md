# 🔄 Atualização de Produção — 2026-07-01 (paridade web + fix KYC)

> Servidor: `u763057780@195.35.41.218 -p 65002`
> Laravel: `~/domains/kwanzasafe.com/kwanzasafe/` · Público: `~/domains/kwanzasafe.com/public_html/`

## O que esta atualização entrega
- **Web (novo):** central de **notificações**, **carteiras cripto** (Bybit/Binance/RedotPay), **alterar email com dupla OTP**, **timeline** da transação, **cartão de saldo**, **pesquisa** de transações, **Definições** (`/settings`), **Convidar** (`/convidar`) + KYC web completo + restyle sóbrio.
- **Mobile:** correção dos campos de data do KYC (não faz parte deste deploy web — vai por APK/EAS).

## Natureza do deploy
- ✅ **Só código** (controllers + views + rotas).
- ✅ **SEM migrações novas** (usa tabelas já existentes: `user_notifications`, `payment_wallets`, `otp_codes`).
- ✅ **SEM alterações a assets públicos** → **NÃO** é preciso enviar `public_html-deploy.tar.gz`.
- ⚠️ O servidor **não é um clone git** → tem de ser por **tar.gz** (o `git pull` não funciona lá).

Pacote a enviar: **`_DOCS/deploy/kwanzasafe-deploy.tar.gz`** (325 KB, já gerado; exclui `vendor/`, `.env*`, `storage/`, `public/`, caches).

---

## 1. Backup (NO SERVIDOR)
```bash
cd ~/domains/kwanzasafe.com/kwanzasafe
php artisan down --retry=60          # NOTA: nesta versão do Laravel NÃO existe --message

# Descobrir as credenciais REAIS da BD (usa estes valores no mysqldump abaixo):
grep -E "DB_DATABASE|DB_USERNAME|DB_PASSWORD" .env

# BD (opcional nesta atualização — NÃO há migrações, a BD não muda).
# Usa o DB_USERNAME e DB_DATABASE do .env; a password pedida é a DB_PASSWORD do .env.
mysqldump -u DB_USERNAME_DO_ENV -p DB_DATABASE_DO_ENV > ~/backup_$(date +%F_%H%M).sql

# código atual (para reverter) — este é o backup importante
cd ~/domains/kwanzasafe.com
cp -r kwanzasafe kwanzasafe_bak_$(date +%F)
```

## 2. Enviar o código
> ⚠️ O `scp` corre **NO TEU PC** (Git Bash/PowerShell na pasta do projeto), **NÃO** dentro da sessão SSH. A password pedida aqui é a **password de SSH** (a mesma com que entraste no servidor).
```bash
# NO TEU PC, em C:\wamp64\www\kwanzasa.com
scp -P 65002 _DOCS/deploy/kwanzasafe-deploy.tar.gz u763057780@195.35.41.218:~/domains/kwanzasafe.com/
```
Extrair (NO SERVIDOR):
```bash
cd ~/domains/kwanzasafe.com
tar xzf kwanzasafe-deploy.tar.gz -C kwanzasafe/   # extrai POR CIMA (preserva .env, vendor, storage)
```

## 3. Migrações + cache (no servidor)
```bash
cd ~/domains/kwanzasafe.com/kwanzasafe

php artisan migrate --force      # idempotente — não há migrações novas, mas confirma que estão aplicadas

# ⚠️ LIMPAR as caches antigas é ESSENCIAL (senão as rotas novas /settings, /convidar,
#    /notifications, /wallet, /profile/email/* dão 404 por causa da route cache antiga)
php artisan optimize:clear

# recompilar SÓ config + views (perf). NÃO usar route:cache/optimize:
#   o web.php tem rotas closure ('/', '/termos', '/settings', '/convidar', ...) e o
#   route:cache REBENTA com "Unable to prepare route ... Uses Closure".
php artisan config:cache
php artisan view:cache

php artisan up
```

## 4. Verificações pós-deploy
```bash
curl -s -o /dev/null -w "%{http_code}\n" https://kwanzasafe.com/            # 200
curl -s https://kwanzasafe.com/api/v1/rates | head -c 120                   # JSON de taxas
```
No browser (login como cliente):
- [ ] `/dashboard` — **cartão de saldo** (com olho para esconder), **sino de notificações** com badge.
- [ ] Aba **Histórico** — caixa de **pesquisa** a filtrar; aba **IBAN** — secção **Carteiras (cripto)** + modal.
- [ ] `/notifications` — feed; `/settings` — Definições; `/convidar` — partilha.
- [ ] `/profile` — cartão **"Alterar email"** (envia código); email do topo a só-leitura.
- [ ] Abrir uma transação — **timeline** do estado no topo.

## 5. Reverter (se algo correr mal)
```bash
cd ~/domains/kwanzasafe.com
rm -rf kwanzasafe && mv kwanzasafe_bak_AAAA-MM-DD kwanzasafe
mysql -u u763057780_kwansafe -p u763057780_kwansafe_db < ~/backup_AAAA-MM-DD_HHMM.sql
cd kwanzasafe && php artisan optimize:clear && php artisan config:cache && php artisan view:cache && php artisan up
```
