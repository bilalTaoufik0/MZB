#!/usr/bin/env bash
set -e

if [ ! -f .env ]; then
  echo ".env not found. Copy .env.example to .env and edit it first."
  exit 1
fi

source .env

if [ -z "$1" ] || [ -z "$2" ]; then
  echo "Usage: $0 <db-sql-file> <wp-tar.gz>"
  echo "Example: $0 backups/db_202501011200.sql backups/wp_files_202501011200.tar.gz"
  exit 1
fi

DBFILE="$1"
WPFILE="$2"

if [ ! -f "$DBFILE" ]; then
  echo "DB file not found: $DBFILE"
  exit 1
fi

if [ ! -f "$WPFILE" ]; then
  echo "WP file not found: $WPFILE"
  exit 1
fi

echo "Restoring WP files from $WPFILE..."
export MSYS2_ARG_CONV_EXCL="*"
cat "$WPFILE" | docker compose exec -T wordpress sh -c "tar xzf - -C /var/www/html"


echo "Restoring WP files from $WPFILE..."
cat "$WPFILE" | docker compose exec -T wordpress tar xzf - -C /var/www/html

echo "Restore complete."
