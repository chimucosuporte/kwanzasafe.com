#!/bin/bash
# Deploy Sprint N - KwanzaSafe
# Servidor: u763057780@195.35.41.218:65002
# Path Laravel: ~/domains/kwanzasafe.com/kwanzasafe/
# Path public:  ~/domains/kwanzasafe.com/public_html/
#
# Pré-requisitos no servidor:
#   - sprint_n.tar.gz uploaded para ~/sprint_n.tar.gz
#
# Uso:
#   bash deploy.sh
#
# Sair em qualquer erro
set -e

cd ~/domains/kwanzasafe.com
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "==> [1/8] Backup base de dados (credenciais lidas do .env)"
DB_NAME=$(grep "^DB_DATABASE=" kwanzasafe/.env | cut -d= -f2- | tr -d '"' | tr -d "'" | tr -d ' ')
DB_USER=$(grep "^DB_USERNAME=" kwanzasafe/.env | cut -d= -f2- | tr -d '"' | tr -d "'" | tr -d ' ')
DB_PASS=$(grep "^DB_PASSWORD=" kwanzasafe/.env | cut -d= -f2- | tr -d '"' | tr -d "'")
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > ~/backup_db_pre_sprint_n_${TIMESTAMP}.sql
echo "    Backup BD: ~/backup_db_pre_sprint_n_${TIMESTAMP}.sql ($(wc -c < ~/backup_db_pre_sprint_n_${TIMESTAMP}.sql) bytes)"

echo "==> [2/8] Backup ficheiros Laravel"
tar -czf ~/backup_files_pre_sprint_n_${TIMESTAMP}.tar.gz kwanzasafe/app kwanzasafe/database/migrations kwanzasafe/resources/views kwanzasafe/routes kwanzasafe/public/css 2>/dev/null || true
echo "    Backup ficheiros: ~/backup_files_pre_sprint_n_${TIMESTAMP}.tar.gz"

echo "==> [3/8] Modo manutenção"
cd kwanzasafe
php artisan down --message="Manutenção programada — Sprint N. Voltamos em 5 minutos." --retry=60 || true

echo "==> [4/8] Extrair Sprint N"
cd ~/domains/kwanzasafe.com
tar -xzf ~/sprint_n.tar.gz
echo "    26 ficheiros aplicados"

echo "==> [5/8] Sincronizar CSS para public_html"
mkdir -p public_html/css
cp -f kwanzasafe/public/css/app.css public_html/css/app.css
echo "    CSS copiado para public_html/css/app.css"

echo "==> [6/8] Migrações"
cd kwanzasafe
php artisan migrate --force

echo "==> [7/8] Optimize caches"
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan optimize

echo "==> [8/8] Voltar a online"
php artisan up

echo ""
echo "✓ Deploy Sprint N concluído"
echo ""
echo "Verificações manuais sugeridas:"
echo "  - https://kwanzasafe.com (landing)"
echo "  - https://kwanzasafe.com/login"
echo "  - https://kwanzasafe.com/dashboard (após login)"
echo "  - https://kwanzasafe.com/admin/transactions"
echo "  - php artisan migrate:status | tail -5"
echo "  - tail -50 storage/logs/laravel.log"
