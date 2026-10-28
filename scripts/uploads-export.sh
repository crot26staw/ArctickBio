#!/usr/bin/env bash
# Загрузки WordPress (медиатека) для переноса на хостинг (WORDPRESS.md → «Деплой» → «База и загрузки»).
#   ./scripts/uploads-export.sh            — копия wp-content/uploads из Docker в scripts/out/uploads (без логов WC)
#   ./scripts/uploads-export.sh --upload   — копия + выгрузка по FTP в <FTP_SITE_PATH>/wp-content/uploads/ (доступы — .env)
#   ./scripts/uploads-export.sh --check    — только проверить вход по FTP и что папка сайта на месте
# FTP — через curl (есть в macOS), FTPS при FTP_PROTOCOL=ftps. Файлы перезаписываются, лишние на сервере не удаляются.
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || { echo "Нет .env (шаблон — .env.example)"; exit 1; }
set -a; . ./.env; set +a

MODE="${1:-}"
OUT=scripts/out/uploads

ftp_base() {
  : "${FTP_HOST:?FTP_HOST не задан в .env}" "${FTP_USER:?FTP_USER не задан}" "${FTP_PASSWORD:?FTP_PASSWORD не задан}" "${FTP_SITE_PATH:?FTP_SITE_PATH не задан}"
  CURL=(curl --silent --show-error --fail --user "$FTP_USER:$FTP_PASSWORD")
  [ "${FTP_PROTOCOL:-ftps}" = "ftps" ] && CURL+=(--ssl-reqd)
  BASE="ftp://$FTP_HOST/${FTP_SITE_PATH#/}"
  BASE="${BASE%/}"
}

if [ "$MODE" = "--check" ]; then
  ftp_base
  echo "→ Вход по FTP: $FTP_HOST (${FTP_PROTOCOL:-ftps}), папка $FTP_SITE_PATH"
  LIST="$("${CURL[@]}" --list-only "$BASE/")"
  echo "$LIST" | sed 's/^/   /'
  echo "$LIST" | grep -qx 'wp-config.php' && echo "OK: WordPress на месте" || echo "wp-config.php нет — WordPress в этой папке ещё не установлен (или путь другой)"
  exit 0
fi

echo "→ Копия загрузок из Docker"
rm -rf "$OUT"
mkdir -p "$(dirname "$OUT")"
docker compose cp wordpress:/var/www/html/wp-content/uploads "$OUT"
rm -rf "$OUT/wc-logs"
find "$OUT" -name '.DS_Store' -delete
echo "   $(find "$OUT" -type f | wc -l | tr -d ' ') файлов, $(du -sh "$OUT" | cut -f1) — $OUT"

[ "$MODE" = "--upload" ] || { echo "Выгрузить на хостинг: ./scripts/uploads-export.sh --upload (или папку $OUT — любым FTP-клиентом в wp-content/uploads/)"; exit 0; }

ftp_base
echo "→ Выгрузка в $FTP_SITE_PATH/wp-content/uploads/"
# Одна команда curl на пачку файлов — соединение переиспользуется
ARGS=()
COUNT=0
flush() {
  [ ${#ARGS[@]} -eq 0 ] && return
  "${CURL[@]}" --ftp-create-dirs "${ARGS[@]}"
  ARGS=()
}
while IFS= read -r -d '' FILE; do
  REL="${FILE#"$OUT"/}"
  ARGS+=(-T "$FILE" "$BASE/wp-content/uploads/$REL")
  COUNT=$((COUNT + 1))
  if [ $((COUNT % 50)) -eq 0 ]; then flush; echo "   ${COUNT}…"; fi
done < <(find "$OUT" -type f -print0)
flush
echo "Готово: выгружено файлов — $COUNT"
