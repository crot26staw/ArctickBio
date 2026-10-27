#!/usr/bin/env bash
# Первый запуск локального WordPress: установка, язык, плагины, базовые настройки.
# Повторный запуск безопасен — уже сделанное пропускается.
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || cp .env.example .env
set -a; . ./.env; set +a

wp() { docker compose run --rm -T wpcli wp "$@" 2> >(grep -v "Container arctik-bio" >&2); }

docker compose up -d

echo '→ Ждём WordPress и базу…'
until wp db check >/dev/null 2>&1 && wp core version >/dev/null 2>&1; do sleep 2; done

if ! wp core is-installed >/dev/null 2>&1; then
  wp core install --url="$WP_URL" --title="$WP_TITLE" \
    --admin_user="$WP_ADMIN_USER" --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" --skip-email
fi

echo '→ Русский язык'
wp language core install ru_RU --activate
wp option update timezone_string 'Europe/Moscow'
wp option update date_format 'd.m.Y'
wp option update time_format 'H:i'

echo '→ Постоянные ссылки'
wp rewrite structure "/%postname%/"

echo '→ Плагины'
wp plugin install woocommerce wp-mail-smtp --activate
wp plugin delete akismet hello >/dev/null 2>&1 || true
wp language plugin install --all ru_RU

echo '→ WooCommerce'
wp option update woocommerce_default_country 'RU'
wp option update woocommerce_currency 'RUB'
wp option update woocommerce_price_num_decimals 0
wp option update woocommerce_price_thousand_sep ' '
wp option update woocommerce_currency_pos 'right'
wp option update woocommerce_coming_soon 'no'
wp option update woocommerce_onboarding_profile '{"skipped":true}' --format=json
wp wc tool run install_pages --user="$WP_ADMIN_USER" >/dev/null

echo '→ Страницы: русские названия, без демо-контента'
page() { # page <опция с ID страницы> <заголовок> <ярлык>
  local id; id=$(wp option get "$1" | tr -d '\r')
  [ -n "$id" ] && wp post update "$id" --post_title="$2" --post_name="$3" >/dev/null
}
page woocommerce_shop_page_id 'Каталог' catalog
page woocommerce_cart_page_id 'Корзина' cart
page woocommerce_checkout_page_id 'Оформление заказа' checkout
page woocommerce_myaccount_page_id 'Личный кабинет' my-account
page woocommerce_refund_returns_page_id 'Возврат и обмен' refund-returns
page wp_page_for_privacy_policy 'Политика конфиденциальности' privacy-policy
wp post delete $(wp post list --post_type=post,page --name=hello-world --format=ids) --force >/dev/null 2>&1 || true
wp post delete $(wp post list --post_type=page --name=sample-page --format=ids) --force >/dev/null 2>&1 || true
wp comment delete 1 --force >/dev/null 2>&1 || true

echo '→ WP Mail SMTP → Mailpit (локально)'
wp option update wp_mail_smtp "{\"mail\":{\"mailer\":\"smtp\",\"from_email\":\"$WP_ADMIN_EMAIL\",\"from_name\":\"$WP_TITLE\",\"from_email_force\":true,\"from_name_force\":true},\"smtp\":{\"host\":\"mailpit\",\"port\":1025,\"encryption\":\"none\",\"autotls\":false,\"auth\":false}}" --format=json

echo
echo "Готово: $WP_URL  (админка — $WP_URL/wp-admin, $WP_ADMIN_USER / $WP_ADMIN_PASSWORD)"
echo "Почта — http://localhost:$MAIL_PORT, phpMyAdmin — http://localhost:$PMA_PORT"
