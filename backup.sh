#!/usr/bin/env bash
set -e

if [ ! -f .env ]; then
  echo ".env not found. Copy .env.example to .env and edit it first."
  exit 1
fi

source .env

TIMESTAMP=$(date +"%Y%m%d%H%M")

DBFILE="backups/db_${TIMESTAMP}.sql"
WPFILE="backups/wp_files_${TIMESTAMP}.tar.gz"

mkdir -p backups

echo "Dumping MySQL to $DBFILE..."
docker compose exec -T db sh -c "mysqldump -u$MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE" > "$DBFILE"

echo "Archiving WordPress files to $WPFILE..."
export MSYS2_ARG_CONV_EXCL="*"
docker compose exec -T wordpress sh -c "tar czf - -C /var/www/html ." > "$WPFILE"

echo "Backup complete."
