#!/usr/bin/env bash
# Секреты GitHub для deploy-theme.yml из .env (FTP_HOST, FTP_USER, FTP_PASSWORD, FTP_SITE_PATH, FTP_PROTOCOL).
#   ./scripts/gh-secrets.sh   — нужен gh с доступом к репозиторию (gh auth status)
# Значения уходят только в GitHub (gh secret set читает их из stdin), в терминал не выводятся.
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || { echo "Нет .env (шаблон — .env.example)"; exit 1; }
set -a; . ./.env; set +a

: "${FTP_HOST:?FTP_HOST не задан в .env}" "${FTP_USER:?FTP_USER не задан}" "${FTP_PASSWORD:?FTP_PASSWORD не задан}" "${FTP_SITE_PATH:?FTP_SITE_PATH не задан}"

SITE="${FTP_SITE_PATH#/}"
SITE="${SITE%/}"

printf '%s' "$FTP_HOST" | gh secret set FTP_HOST
printf '%s' "$FTP_USER" | gh secret set FTP_USER
printf '%s' "$FTP_PASSWORD" | gh secret set FTP_PASSWORD
printf '%s' "$SITE/wp-content/themes/arctik-bio/" | gh secret set FTP_THEME_PATH
gh variable set FTP_PROTOCOL --body "${FTP_PROTOCOL:-ftps}"

echo "Готово: секреты FTP_HOST, FTP_USER, FTP_PASSWORD, FTP_THEME_PATH ($SITE/wp-content/themes/arctik-bio/), переменная FTP_PROTOCOL"
gh secret list
