#!/usr/bin/env bash
#
# Deploy locale: dipendenze e comandi Artisan eseguiti nel container PHP Docker;
# download del backup di produzione via SSH da geobox; reimport nel Postgres
# locale (container) con psql, senza usare `php artisan db:restore`.
#
# Flusso DB: il dump è un SQL completo (schema + dati). Si ricrea il database
# vuoto, si importa il dump, poi si lancia `migrate` per allineare eventuali
# migrazioni più recenti del backup. Eseguire migrate prima dell'import
# verrebbe annullato dallo schema presente nel dump.
#
# Prerequisiti: `geobox` in ~/.ssh/config (o host risolvibile), docker compose
# avviato (php81_${APP_NAME}, postgres_${APP_NAME}), .env in root progetto.
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$ROOT"

if [[ ! -f .env ]]; then
  echo "Errore: file .env assente in ${ROOT}" >&2
  exit 1
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

: "${APP_NAME:?Definire APP_NAME in .env}"
: "${DB_DATABASE:?Definire DB_DATABASE in .env}"
: "${DB_USERNAME:?Definire DB_USERNAME in .env}"
: "${DB_PASSWORD:?Definire DB_PASSWORD in .env}"

PHP_CONTAINER="php81_${APP_NAME}"
PG_CONTAINER="postgres_${APP_NAME}"
# Origine backup su server Geobox (stesso path usato da prod_new_backup.sh)
REMOTE_DUMP="geobox:/root/html/sisteco/storage/app/database/last-dump.sql.gz"
LOCAL_DB_DIR="${ROOT}/storage/app/database"
LOCAL_DUMP="${LOCAL_DB_DIR}/last-dump.sql.gz"

echo "=== Deploy locale con dump produzione (Docker) ==="

if ! docker exec "$PHP_CONTAINER" true 2>/dev/null; then
  echo "Errore: container ${PHP_CONTAINER} non raggiungibile. Avviare docker compose." >&2
  exit 1
fi
if ! docker exec "$PG_CONTAINER" true 2>/dev/null; then
  echo "Errore: container ${PG_CONTAINER} non raggiungibile. Avviare docker compose." >&2
  exit 1
fi

mkdir -p "$LOCAL_DB_DIR"

echo ">>> Scarico ${REMOTE_DUMP} -> ${LOCAL_DUMP}"
scp "$REMOTE_DUMP" "$LOCAL_DUMP"

echo ">>> composer install (${PHP_CONTAINER})"
docker exec "$PHP_CONTAINER" composer install --no-interaction

echo ">>> composer dump-autoload"
docker exec "$PHP_CONTAINER" composer dump-autoload --no-interaction

# Clear and cache config (stesso ordine dello script precedente)
echo ">>> php artisan config:cache / config:clear / clear-compiled"
docker exec "$PHP_CONTAINER" php artisan config:cache
docker exec "$PHP_CONTAINER" php artisan config:clear
docker exec "$PHP_CONTAINER" php artisan clear-compiled

# TODO: Uncomment when api.favorite issue will be resolved
# docker exec "$PHP_CONTAINER" php artisan optimize

echo ">>> Ricreo il database ${DB_DATABASE} e importo il dump (psql nel container)"
docker exec -i "$PG_CONTAINER" env PGPASSWORD="${DB_PASSWORD}" psql -U "${DB_USERNAME}" -d postgres -v ON_ERROR_STOP=1 <<SQL
SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = '${DB_DATABASE}' AND pid <> pg_backend_pid();
DROP DATABASE IF EXISTS "${DB_DATABASE}";
CREATE DATABASE "${DB_DATABASE}" OWNER "${DB_USERNAME}";
SQL

echo ">>> gunzip | psql -> ${PG_CONTAINER}"
gunzip -c "$LOCAL_DUMP" | docker exec -i "$PG_CONTAINER" env PGPASSWORD="${DB_PASSWORD}" psql -U "${DB_USERNAME}" -d "${DB_DATABASE}" -v ON_ERROR_STOP=1

echo ">>> php artisan migrate --force (${PHP_CONTAINER})"
docker exec "$PHP_CONTAINER" php artisan migrate --force

echo "=== Deploy completato ==="
