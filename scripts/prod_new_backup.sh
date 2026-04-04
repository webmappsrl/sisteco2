#!/usr/bin/env bash
#
# Backup del database PostgreSQL di produzione (container Docker) in un dump
# compresso sotto storage/app/database, con symlink last-dump.sql.gz all'ultimo file.
# Eseguire dalla macchina host dove è deployata l'app (path e docker come in prod).

# Directory del progetto Laravel sul server
cd /root/html/sisteco

# Carica variabili da .env nel processo corrente (APP_NAME, DB_*, ecc.)
set -a && . ./.env && set +a

# Timestamp univoco per il nome file del backup
TS=$(date +%Y%m%d%H%M%S)

# Percorso relativo al progetto: dump gzip nominato con data/ora
OUT="storage/app/database/${TS}_sisteco_backup.sql.gz"

# Dump dal container Postgres (nome container = postgres_${APP_NAME}), poi compressione su stdout verso file locale
docker exec "postgres_${APP_NAME}" env PGPASSWORD="$DB_PASSWORD" \
  pg_dump -U "$DB_USERNAME" -d "$DB_DATABASE" --no-owner --no-acl | gzip -c > "$OUT"

# Aggiorna il symlink "ultimo dump" nella stessa cartella dei backup
cd storage/app/database
rm -f last-dump.sql.gz
ln -s "${TS}_sisteco_backup.sql.gz" last-dump.sql.gz
