#!/usr/bin/env bash
set -e

if [ ! -f .env ]; then
  echo ".env not found. Copy .env.example to .env and edit it first."
  exit 1
fi

# Charge les variables MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE, etc.
source .env

mkdir -p backups
TS=$(date +%Y%m%d%H%M)

echo "Dumping MySQL to backups/db_$TS.sql..."
# --no-tablespaces pour éviter l'erreur de privilèges avec MySQL 8
docker compose exec -T db sh -c \
  "exec mysqldump --no-tablespaces -u\"\$MYSQL_USER\" -p\"\$MYSQL_PASSWORD\" \"\$MYSQL_DATABASE\"" \
  > "backups/db_$TS.sql"

echo "Archiving WordPress files to backups/wp_files_$TS.tar.gz..."
docker compose exec -T wordpress tar czf - -C /var/www/html . > "backups/wp_files_$TS.tar.gz"

echo "✅ Backup saved in backups/"
echo " - backups/db_$TS.sql"
echo " - backups/wp_files_$TS.tar.gz"
