#!/usr/bin/env bash
# restore-linux.sh — Levanta DB, importa dump, levanta WP y aplica fixes básicos (Linux)
set -euo pipefail

DUMP_FILE="wordpress.sql.gz"
SITE_URL="http://localhost:8080"
CLEAN=0
DB_SERVICE="db"
WP_SERVICE="wordpress"

usage() {
  cat <<'USAGE'
Uso: ./restore-linux.sh [-f dump.sql[.gz]] [-u http://localhost:8080] [--clean] [--db db] [--wp wordpress]

  -f, --dump-file   Ruta al dump SQL (.sql o .sql.gz). Por defecto: wordpress.sql.gz
  -u, --site-url    URL local del sitio. Por defecto: http://localhost:8080
      --clean       Hace 'docker compose down -v' (borra volúmenes / BD)
      --db          Nombre del servicio de BD en docker-compose.yml (default: db)
      --wp          Nombre del servicio de WordPress en docker-compose.yml (default: wordpress)

Requisitos: docker + docker compose, .env (o .env.sample) en esta carpeta.
USAGE
}

# ---- parse args ----
while [[ $# -gt 0 ]]; do
  case "$1" in
    -f|--dump-file) DUMP_FILE="$2"; shift 2;;
    -u|--site-url)  SITE_URL="$2"; shift 2;;
    --clean)        CLEAN=1; shift;;
    --db)           DB_SERVICE="$2"; shift 2;;
    --wp)           WP_SERVICE="$2"; shift 2;;
    -h|--help)      usage; exit 0;;
    *) echo "Opción desconocida: $1"; usage; exit 1;;
  esac
done

# ---- choose docker compose command ----
if docker compose version >/dev/null 2>&1; then
  COMPOSE="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE="docker-compose"
else
  echo "ERROR: No encuentro 'docker compose' ni 'docker-compose'." >&2
  exit 1
fi

# ---- load env (.env or .env.sample) ----
if [[ ! -f ".env" && -f ".env.sample" ]]; then
  cp .env.sample .env
fi
if [[ ! -f ".env" ]]; then
  echo "ERROR: falta .env (puedes copiar .env.sample) y definir MYSQL_ROOT_PASSWORD" >&2
  exit 1
fi

# Exporta variables del .env (formato KEY=VALUE)
set -a
# shellcheck disable=SC1091
. ./.env
set +a

: "${MYSQL_DATABASE:=wordpress}"

if [[ -z "${MYSQL_ROOT_PASSWORD:-}" || "${MYSQL_ROOT_PASSWORD}" == "cambia_esto" ]]; then
  echo "ADVERTENCIA: Define MYSQL_ROOT_PASSWORD en .env" >&2
fi

# ---- ensure content dir ----
if [[ ! -d "./_data" ]]; then
  mkdir -p "./_data"
fi
if [[ ! -d "./_data/wp-admin" ]]; then
  echo "Aviso: ./_data no contiene wp-admin/wp-content. La imagen copiará WordPress limpio al primer arranque."
fi

# ---- bring stack down ----
if [[ "$CLEAN" -eq 1 ]]; then
  echo "Bajando stack y borrando volúmenes..."
  $COMPOSE down -v || true
else
  echo "Bajando stack (sin borrar volúmenes)..."
  $COMPOSE down || true
fi

# ---- up db and wait ----
echo "Levantando DB (${DB_SERVICE})..."
$COMPOSE up -d "${DB_SERVICE}"

echo "Esperando a MySQL..."
for i in {1..60}; do
  if $COMPOSE exec -T "${DB_SERVICE}" sh -lc 'mysqladmin ping -h 127.0.0.1 -p"$MYSQL_ROOT_PASSWORD" --silent' >/dev/null 2>&1; then
    break
  fi
  sleep 2
  if [[ $i -eq 60 ]]; then
    echo "ERROR: MySQL no respondió a tiempo." >&2
    exit 1
  fi
done

# ---- import dump ----
if [[ ! -f "${DUMP_FILE}" ]]; then
  echo "ERROR: No encuentro el dump: ${DUMP_FILE}" >&2
  exit 1
fi

CID="$($COMPOSE ps -q "${DB_SERVICE}")"
if [[ -z "${CID}" ]]; then
  echo "ERROR: No encuentro el contenedor del servicio ${DB_SERVICE}" >&2
  exit 1
fi

echo "Copiando dump dentro del contenedor..."
if [[ "${DUMP_FILE}" == *.gz ]]; then
  docker cp "${DUMP_FILE}" "${CID}:/dump.sql.gz"
  IMPORT_CMD='gzip -dc /dump.sql.gz | sed "/^mysqldump:/d" 2>/dev/null | mysql -u root -p"$MYSQL_ROOT_PASSWORD" "${MYSQL_DATABASE:-wordpress}" || gzip -dc /dump.sql.gz | grep -v "^mysqldump:" | mysql -u root -p"$MYSQL_ROOT_PASSWORD" "${MYSQL_DATABASE:-wordpress}"'
else
  docker cp "${DUMP_FILE}" "${CID}:/dump.sql"
  IMPORT_CMD='sed "/^mysqldump:/d" /dump.sql 2>/dev/null | mysql -u root -p"$MYSQL_ROOT_PASSWORD" "${MYSQL_DATABASE:-wordpress}" || grep -v "^mysqldump:" /dump.sql | mysql -u root -p"$MYSQL_ROOT_PASSWORD" "${MYSQL_DATABASE:-wordpress}"'
fi

echo "Importando BD ${MYSQL_DATABASE}..."
$COMPOSE exec -T "${DB_SERVICE}" sh -lc "${IMPORT_CMD}"

# ---- up wordpress ----
echo "Levantando WordPress (${WP_SERVICE})..."
$COMPOSE up -d "${WP_SERVICE}"

# ---- post-fix ----
echo "Fijando siteurl/home -> ${SITE_URL}"
$COMPOSE run --rm wpcli option update siteurl "${SITE_URL}" --allow-root >/dev/null
$COMPOSE run --rm wpcli option update home    "${SITE_URL}" --allow-root >/dev/null

echo "Desactivando todos los plugins..."
$COMPOSE run --rm wpcli plugin deactivate --all --allow-root || true

echo "Activando tema twentytwentyfour (fallback)..."
if ! $COMPOSE run --rm wpcli theme activate twentytwentyfour --allow-root; then
  $COMPOSE run --rm wpcli theme install twentytwentyfour --activate --allow-root
fi

echo "Flush de permalinks..."
$COMPOSE run --rm wpcli rewrite flush --hard --allow-root >/dev/null || true

echo "Habilitando WP_DEBUG (log en wp-content/debug.log)..."
$COMPOSE run --rm wpcli config set WP_DEBUG true --raw --type=constant --allow-root >/dev/null || true
$COMPOSE run --rm wpcli config set WP_DEBUG_LOG true --raw --type=constant --allow-root >/dev/null || true
$COMPOSE run --rm wpcli config set WP_DEBUG_DISPLAY false --raw --type=constant --allow-root >/dev/null || true

echo
echo "✅ Todo listo. Abre: ${SITE_URL}"
echo "👉 Logs recientes de wordpress:"
$COMPOSE logs --tail=60 "${WP_SERVICE}" || true
