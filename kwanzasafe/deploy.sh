#!/usr/bin/env bash
#
# KwanzaSafe — Deploy (lado servidor)
# ------------------------------------------------------------------
# Corre ESTE script NO SERVIDOR, depois de:
#   1) Teres transferido o código (rsync, a partir do teu PC).
#   2) Teres editado o .env de produção:  APP_DEBUG=false  +  novas
#      passwords (DB e MAIL) já rotacionadas no painel Hostinger.
#
# Uso normal:        bash deploy.sh
# Rotar APP_KEY:     ROTATE_KEY=1 bash deploy.sh   (uma vez; faz logout geral)
#
# Em caso de erro o script PÁRA e deixa o site em manutenção (não serve
# código partido). Ver rollback no runbook.
# ------------------------------------------------------------------
set -euo pipefail

APP_DIR="${APP_DIR:-$HOME/domains/kwanzasafe.com/kwanzasafe}"
cd "$APP_DIR"

trap 'echo ""; echo "❌ Deploy FALHOU. O site ficou em manutenção propositadamente."; echo "   Corrige o erro acima e volta a correr, ou faz rollback (ver runbook)."; exit 1' ERR

# Lê um valor do .env sem o imprimir (remove aspas envolventes)
get_env() { grep -E "^$1=" .env | head -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'\$//"; }

TS=$(date +%F_%H%M%S)

echo "→ 1/8  Manutenção ON"
php artisan down --message="Manutenção em curso" --retry=30 || true

echo "→ 2/8  Backup da BD + .env  (~/backup_db_${TS}.sql)"
DB_DATABASE=$(get_env DB_DATABASE)
DB_USERNAME=$(get_env DB_USERNAME)
DB_PASSWORD=$(get_env DB_PASSWORD)
mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$HOME/backup_db_${TS}.sql"
cp .env "$HOME/.env.backup_${TS}"

echo "→ 3/8  Mover ficheiros sensíveis para o disco privado (storage/app)"
(
  cd storage/app
  [ -d public/kyc ]              && mv public/kyc ./              || true
  [ -d public/receipts ]         && mv public/receipts ./         || true
  [ -d public/chat_attachments ] && mv public/chat_attachments ./ || true
)

echo "→ 4/8  Dependências de produção (sem dev → ignition fora)"
composer install --no-dev --optimize-autoloader --no-interaction

if [ "${ROTATE_KEY:-0}" = "1" ]; then
  echo "→ 4b   Rotação de APP_KEY (faz logout de todas as sessões)"
  php artisan key:generate --force
fi

echo "→ 5/8  Migrações"
php artisan migrate --force

echo "→ 6/8  storage:link"
php artisan storage:link || true

echo "→ 7/8  Recompilar caches"
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "→ 8/8  Manutenção OFF"
php artisan up

echo ""
echo "✅ Deploy concluído (${TS})"
echo "------------------- Verificação -------------------"
php artisan migrate:status | tail -8 || true
php artisan about 2>/dev/null | grep -iE 'Environment|Debug' || true
echo "---------------------------------------------------"
echo "Lembra-te: criar as Contas de Recepção reais em /admin/payment-accounts"
