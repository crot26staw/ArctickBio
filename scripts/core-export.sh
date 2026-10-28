#!/usr/bin/env bash
# Ядро WordPress, плагины и база на хостинг без SSH (WORDPRESS.md → «Деплой» → «Первый перенос», шаги 2, 3, 6).
#   ./scripts/core-export.sh             — собрать в scripts/out/core: wp-core.zip (ядро, плагины, переводы из Docker),
#                                          wp-config.php прода (PROD_DB_*, новые соли), одноразовый установщик с токеном
#   ./scripts/core-export.sh --probe     — выгрузить установщик и проверить хостинг: PHP, ZipArchive, вход в базу прода
#   ./scripts/core-export.sh --core      — выгрузить zip и wp-config.php, распаковать на сервере, убрать заглушку Reg.ru index.html
#   ./scripts/core-export.sh --db        — импорт scripts/out/dump.sql.gz (сначала ./scripts/db-export.sh) в ПУСТУЮ базу прода
#   ./scripts/core-export.sh --theme     — тема без GitHub Actions: сборка как в deploy-theme.yml, zip, распаковка установщиком
# Установщик удаляет себя (и zip / дамп) после каждого запуска; токен передаётся POST-запросом.
# Тема — workflow deploy-theme.yml (должна быть на сервере до импорта базы), загрузки — uploads-export.sh --upload.
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || { echo "Нет .env (шаблон — .env.example)"; exit 1; }
set -a; . ./.env; set +a

MODE="${1:-}"
OUT=scripts/out/core

ftp_base() {
  : "${FTP_HOST:?FTP_HOST не задан в .env}" "${FTP_USER:?FTP_USER не задан}" "${FTP_PASSWORD:?FTP_PASSWORD не задан}" "${FTP_SITE_PATH:?FTP_SITE_PATH не задан}"
  : "${PROD_URL:?PROD_URL не задан}"
  CURL=(curl --silent --show-error --fail --user "$FTP_USER:$FTP_PASSWORD")
  [ "${FTP_PROTOCOL:-ftps}" = "ftps" ] && CURL+=(--ssl-reqd)
  BASE="ftp://$FTP_HOST/${FTP_SITE_PATH#/}"
  BASE="${BASE%/}"
}

# Выгрузить файлы (локальный путь → имя в папке сайта), вызвать установщик с действием, установщик удаляет себя сам
run_installer() {
  local ACTION="$1"; shift
  [ -f "$OUT/installer.php" ] || { echo "Нет $OUT/installer.php — сначала ./scripts/core-export.sh"; exit 1; }
  ftp_base
  local NAME TOKEN
  NAME="$(cat "$OUT/installer-name")"
  TOKEN="$(cat "$OUT/installer-token")"
  local ARGS=(-T "$OUT/installer.php" "$BASE/$NAME")
  while [ $# -gt 0 ]; do ARGS+=(-T "$1" "$BASE/$2"); shift 2; done
  echo "→ Выгрузка по FTP в $FTP_SITE_PATH"
  "${CURL[@]}" "${ARGS[@]}"
  echo "→ Установщик: $ACTION"
  # Сразу после выгрузки сервер иногда ещё отдаёт 404 — повтор через пару секунд
  local TRY CODE
  for TRY in 1 2 3; do
    CODE="$(curl --silent --show-error --max-time 900 -o "$OUT/installer-response" -w '%{http_code}' \
      --data-urlencode "t=$TOKEN" --data-urlencode "do=$ACTION" "${PROD_URL%/}/$NAME" || true)"
    [ "$CODE" = "404" ] || break
    sleep 3
  done
  cat "$OUT/installer-response" 2>/dev/null; rm -f "$OUT/installer-response"
  [ "$CODE" = "200" ] || echo "   ответ сервера: HTTP $CODE"
  echo ""
  # Подстраховка: установщик удаляет себя сам, но если запрос оборвался — удалить по FTP
  "${CURL[@]}" "ftp://$FTP_HOST/" -Q "DELE ${FTP_SITE_PATH%/}/$NAME" -o /dev/null 2>/dev/null && echo "   (установщик удалён по FTP)" || true
}

case "$MODE" in
  --probe) run_installer probe "$OUT/wp-config.php" wp-config.php; exit 0 ;;
  --core)
    [ -f "$OUT/wp-core.zip" ] || { echo "Нет $OUT/wp-core.zip — сначала ./scripts/core-export.sh"; exit 1; }
    run_installer core "$OUT/wp-core.zip" wp-core.zip "$OUT/wp-config.php" wp-config.php
    exit 0 ;;
  --theme)
    # Без GitHub Actions (нет секретов) — сборка как в deploy-theme.yml, распаковка тем же установщиком; лишние файлы на сервере не удаляются
    echo "→ Сборка темы (build:wp, composer --no-dev)"
    npm run build:wp --silent >/dev/null
    rm -rf "$OUT/theme" "$OUT/theme.zip"
    mkdir -p "$OUT/theme/wp-content/themes"
    rsync -a --exclude node_modules --exclude '.git*' --exclude .DS_Store --exclude hot --exclude vendor \
      wp-theme/arctik-bio/ "$OUT/theme/wp-content/themes/arctik-bio/"
    composer install --no-dev --optimize-autoloader --no-interaction --no-progress --quiet --working-dir="$OUT/theme/wp-content/themes/arctik-bio"
    [ -f "$OUT/theme/wp-content/themes/arctik-bio/dist/.vite/manifest.json" ] || { echo "Нет dist/.vite/manifest.json"; exit 1; }
    (cd "$OUT/theme" && zip -qr -X ../theme.zip .)
    echo "   $(find "$OUT/theme" -type f | wc -l | tr -d ' ') файлов, zip — $(du -h "$OUT/theme.zip" | cut -f1 | tr -d ' ')"
    rm -rf "$OUT/theme"
    run_installer core "$OUT/theme.zip" wp-core.zip
    exit 0 ;;
  --db)
    [ -f scripts/out/dump.sql.gz ] || { echo "Нет scripts/out/dump.sql.gz — сначала ./scripts/db-export.sh"; exit 1; }
    run_installer db scripts/out/dump.sql.gz arctik-dump.sql.gz
    exit 0 ;;
  "") ;;
  *) echo "Режимы: без аргумента / --probe / --core / --db"; exit 1 ;;
esac

: "${PROD_DB_NAME:?PROD_DB_NAME не задан в .env}" "${PROD_DB_USER:?PROD_DB_USER не задан}" "${PROD_DB_PASSWORD:?PROD_DB_PASSWORD не задан}"
mkdir -p "$OUT"
rm -rf "$OUT/src" "$OUT/wp-core.zip"

echo "→ Ядро, плагины и переводы из Docker"
# Без загрузок, кеша, логов, локального wp-config и нашей темы (её выгружает workflow); из стандартных тем — одна запасная
mkdir -p "$OUT/src"
docker compose exec -T wordpress tar -C /var/www/html -cf - \
  --exclude=./wp-config.php --exclude=./wp-config-docker.php --exclude=./.htaccess \
  --exclude=./wp-content/uploads --exclude=./wp-content/cache --exclude=./wp-content/upgrade \
  --exclude=./wp-content/debug.log --exclude=./wp-content/themes/arctik-bio \
  --exclude=./wp-content/themes/twentytwentythree --exclude=./wp-content/themes/twentytwentyfour \
  . | tar -C "$OUT/src" -xf -
find "$OUT/src" -name '.DS_Store' -delete
(cd "$OUT/src" && zip -qr -X ../wp-core.zip .)
echo "   $(find "$OUT/src" -type f | wc -l | tr -d ' ') файлов, zip — $(du -h "$OUT/wp-core.zip" | cut -f1 | tr -d ' ')"
rm -rf "$OUT/src"

echo "→ wp-config.php прода"
php_str() { printf "'%s'" "$(printf '%s' "$1" | sed -e "s/\\\\/\\\\\\\\/g" -e "s/'/\\\\'/g")"; }
salt() { openssl rand -base64 48 | tr -d '\n=' | tr '/+' '_-'; }
{
  cat <<'EOF'
<?php
// wp-config.php прода — собран scripts/core-export.sh из .env (PROD_DB_*). В git не хранится.
EOF
  echo "define( 'DB_NAME', $(php_str "$PROD_DB_NAME") );"
  echo "define( 'DB_USER', $(php_str "$PROD_DB_USER") );"
  echo "define( 'DB_PASSWORD', $(php_str "$PROD_DB_PASSWORD") );"
  echo "define( 'DB_HOST', $(php_str "${PROD_DB_HOST:-localhost}") );"
  echo "define( 'DB_CHARSET', 'utf8mb4' );"
  echo "define( 'DB_COLLATE', '' );"
  echo ""
  for KEY in AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT; do
    echo "define( '$KEY', '$(salt)' );"
  done
  cat <<EOF

\$table_prefix = '${DB_PREFIX:-wp_}';

// За nginx хостинга: https приходит заголовком
if ( isset( \$_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === \$_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
	\$_SERVER['HTTPS'] = 'on';
}

define( 'WP_ENVIRONMENT_TYPE', 'production' );
define( 'WP_DEBUG', false );
define( 'DISALLOW_FILE_EDIT', true );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
EOF
} > "$OUT/wp-config.php"
chmod 600 "$OUT/wp-config.php"

echo "→ Установщик"
TOKEN="$(openssl rand -hex 24)"
NAME="arctik-install-$(openssl rand -hex 8).php"
printf '%s' "$TOKEN" > "$OUT/installer-token"
printf '%s' "$NAME" > "$OUT/installer-name"
chmod 600 "$OUT/installer-token"
sed "s/__TOKEN__/$TOKEN/" scripts/core-installer.php > "$OUT/installer.php"

echo ""
echo "Готово: $OUT (wp-core.zip, wp-config.php, установщик). Дальше: --probe → --core → тема (тег) → db-export.sh → --db"
