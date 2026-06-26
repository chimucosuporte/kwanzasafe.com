# Deploy Sprint N — Instruções

**Bundle:** `_DOCS/deploy_sprint_n/sprint_n.tar.gz` (48KB, 26 ficheiros)
**Script:** `_DOCS/deploy_sprint_n/deploy.sh`
**Servidor:** `u763057780@195.35.41.218` porta `65002`

---

## Passo 1 — Upload do bundle e script (PowerShell local)

Cola no terminal (vai pedir a password SSH):

```powershell
& "C:\Program Files\PuTTY\pscp.exe" -P 65002 `
  "C:\wamp64\www\kwanzasa.com\_DOCS\deploy_sprint_n\sprint_n.tar.gz" `
  "C:\wamp64\www\kwanzasa.com\_DOCS\deploy_sprint_n\deploy.sh" `
  u763057780@195.35.41.218:~/
```

## Passo 2 — Executar o deploy no servidor

```powershell
& "C:\Program Files\PuTTY\plink.exe" -P 65002 u763057780@195.35.41.218 `
  "dos2unix ~/deploy.sh 2>/dev/null; bash ~/deploy.sh"
```

(`dos2unix` corrige line endings se o ficheiro foi criado em Windows; ignora se já está limpo.)

## Passo 3 — Rollback (apenas se algo correr mal)

```powershell
& "C:\Program Files\PuTTY\plink.exe" -P 65002 u763057780@195.35.41.218
```

Dentro do SSH:
```bash
cd ~/domains/kwanzasafe.com/kwanzasafe
php artisan down

# Restaurar ficheiros (substitui TIMESTAMP pelo real)
cd ~/domains/kwanzasafe.com
tar -xzf ~/backup_files_pre_sprint_n_TIMESTAMP.tar.gz

# Restaurar BD
mysql -u u763057780_kwansafe -p u763057780_kwansafe_db < ~/backup_db_pre_sprint_n_TIMESTAMP.sql

php artisan up
```

---

## O que o deploy.sh faz (resumo)

1. mysqldump (backup BD)
2. tar.gz dos ficheiros actuais (backup ficheiros)
3. `php artisan down` (modo manutenção)
4. Extrai `sprint_n.tar.gz` (sobrescreve 26 ficheiros)
5. Copia `public/css/app.css` para `public_html/css/`
6. `php artisan migrate --force` (3 migrações novas)
7. `php artisan optimize` + clear caches
8. `php artisan up`

Os 2 backups ficam em `~/` (home do utilizador SSH).
