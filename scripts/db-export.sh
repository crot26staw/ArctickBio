#!/usr/bin/env bash
# Дамп локальной базы для переноса на хостинг (WORDPRESS.md → «Деплой» → «База и загрузки»).
#   ./scripts/db-export.sh [https://домен]   — по умолчанию PROD_URL из .env
# Результат: scripts/out/dump.sql.gz — импортировать в phpMyAdmin хостинга (в пустую базу).
# Локальная база не меняется: она копируется во временную базу (DB_NAME_export), там WP-CLI меняет адреса
# (сериализованные данные — корректно, JSON с \/ — вторым проходом), чистит кеш и сессии и меняет пароль / почту админа
# (admin / admin с локалки на проде оставлять нельзя); затем временная база выгружается и удаляется.
# Переносить базу целиком — только до запуска магазина (потом заказы и клиенты живут на хостинге).
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || { echo "Нет .env (шаблон — .env.example)"; exit 1; }
set -a; . ./.env; set +a

OLD_URL="${WP_URL:?WP_URL не задан в .env}"
NEW_URL="${1:-${PROD_URL:-}}"
NEW_URL="${NEW_URL%/}"
[[ "$NEW_URL" =~ ^https?://[^/]+$ ]] || { echo "Адрес сайта: ./scripts/db-export.sh https://домен (или PROD_URL в .env)"; exit 1; }
PREFIX="${DB_PREFIX:-wp_}"
ADMIN="${WP_ADMIN_USER:-admin}"
TMP_DB="${DB_NAME}_export"
OUT=scripts/out
mkdir -p "$OUT"
rm -f "$OUT/dump.sql" "$OUT/dump.sql.gz" "$OUT/local.sql"

root_sql() { docker compose exec -T db mariadb -uroot -p"$DB_ROOT_PASSWORD" "$@"; }
# WP-CLI во временной базе
twp() { docker compose run --rm -T -e WORDPRESS_DB_NAME="$TMP_DB" wpcli wp "$@"; }
cleanup() { root_sql -e "DROP DATABASE IF EXISTS \`$TMP_DB\`;" 2>/dev/null || true; rm -f "$OUT/local.sql"; }
trap cleanup EXIT

echo "→ Копия локальной базы во временную ($TMP_DB)"
docker compose run --rm -T wpcli wp db export /out/local.sql --default-character-set=utf8mb4 --quiet
root_sql -e "DROP DATABASE IF EXISTS \`$TMP_DB\`; CREATE DATABASE \`$TMP_DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci; GRANT ALL ON \`$TMP_DB\`.* TO '$DB_USER'@'%';"
root_sql "$TMP_DB" < "$OUT/local.sql"

echo "→ Адреса: $OLD_URL → $NEW_URL"
# guid тоже: сайт ещё нигде не публиковался, иначе в RSS и медиатеке остаётся localhost
twp search-replace "$OLD_URL" "$NEW_URL" --all-tables-with-prefix --precise --report-changed-only
twp search-replace "${OLD_URL//\//\\/}" "${NEW_URL//\//\\/}" --all-tables-with-prefix --precise --report-changed-only

echo "→ Кеш, сессии корзин и входа"
twp transient delete --all --quiet
twp transient delete --all --network --quiet
twp db query "TRUNCATE TABLE ${PREFIX}woocommerce_sessions; DELETE FROM ${PREFIX}usermeta WHERE meta_key = 'session_tokens';"

echo "→ Админ ($ADMIN) на хостинге"
# Пароль — из .env или новый случайный (сохраняется в файл, в терминал не выводится)
PASSWORD="${PROD_ADMIN_PASSWORD:-}"
if [ -z "$PASSWORD" ]; then
  PASSWORD="$(openssl rand -base64 18 | tr -d '/+=')"
  printf '%s\n' "$PASSWORD" > "$OUT/admin-password.txt"
  chmod 600 "$OUT/admin-password.txt"
  echo "   пароль сгенерирован: $OUT/admin-password.txt"
fi
docker compose run --rm -T -e WORDPRESS_DB_NAME="$TMP_DB" -e ARCTIK_PASS="$PASSWORD" wpcli wp eval \
  '$u = get_user_by( "login", "'"$ADMIN"'" ); if ( ! $u ) { WP_CLI::error( "нет пользователя" ); } wp_set_password( getenv( "ARCTIK_PASS" ), $u->ID );'
if [ -n "${PROD_ADMIN_EMAIL:-}" ]; then
  twp user update "$ADMIN" --user_email="$PROD_ADMIN_EMAIL" --skip-email --quiet
  for OPTION in admin_email woocommerce_email_from_address woocommerce_stock_email_recipient; do
    twp option update "$OPTION" "$PROD_ADMIN_EMAIL" --quiet
  done
fi

echo "→ Выгрузка"
twp db export /out/dump.sql --default-character-set=utf8mb4 --quiet
LEFT="$( { grep -o "${OLD_URL#*://}" "$OUT/dump.sql" || true; } | wc -l | tr -d ' ')"
gzip -9 "$OUT/dump.sql"
SIZE="$(du -h "$OUT/dump.sql.gz" | cut -f1 | tr -d ' ')"

echo ""
echo "Готово: $OUT/dump.sql.gz (${SIZE}), префикс таблиц — ${PREFIX} (на хостинге в wp-config.php должен быть такой же)"
[ "$LEFT" = "0" ] || echo "Внимание: в базе осталось упоминаний «${OLD_URL#*://}»: ${LEFT}"
[ -n "${PROD_ADMIN_EMAIL:-}" ] || echo "Внимание: PROD_ADMIN_EMAIL не задан — почта админа осталась ${WP_ADMIN_EMAIL:-локальной}, поменять в админке"
echo "Дальше — WORDPRESS.md → «Деплой» → «Первый перенос»."
