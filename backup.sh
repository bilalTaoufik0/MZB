#!/usr/bin/env bash
set -e

if [ ! -f .env ]; then
  echo ".env not found. Copy .env.example to .env and edit it first."
  exit 1
fi

source .env
mkdir -p backups
TS=$(date +%Y%m%d%H%M)

echo "Dumping MySQL to backups/db_$TS.sql..."
docker compose exec -T db sh -c "exec mysqldump -u$MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE" > backups/db_$TS.sql

echo "Archiving WordPress files to backups/wp_files_$TS.tar.gz..."
docker compose exec -T wordpress tar czf - -C /var/www/html . > backups/wp_files_$TS.tar.gz

echo "Backup saved in backups/"
