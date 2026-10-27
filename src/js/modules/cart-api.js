/**
 * Запросы к корзине WooCommerce (только WordPress: window.arctik.cart — inc/cart.php; в статике модулей-демо это не касается).
 * POST на ?wc-ajax=<endpoint>: стандартные add_to_cart / remove_from_cart и свои arctik_cart_qty, arctik_coupon_apply,
 * arctik_coupon_remove (inc/ajax/cart.php). В ответе — фрагменты WC { селектор: разметка }: элементы заменяются целиком,
 * затем событие cart:updated на document (cart.js заново подключает счётчики и ховеры новых товаров).
 */
const config = () => window.arctik?.cart;

export const hasCartApi = () => Boolean(config());

const applyFragments = (fragments) => {
  Object.entries(fragments).forEach(([selector, html]) => {
    document.querySelectorAll(selector).forEach((node) => {
      node.outerHTML = html;
    });
  });
  document.dispatchEvent(new CustomEvent('cart:updated'));
};

/**
 * Запрос к wc-ajax. Возвращает ответ JSON (у своих обработчиков — { success, data }, у WC — { fragments } или { error }).
 */
export async function cartRequest(endpoint, data = {}) {
  const { endpoint: url, nonce } = config();
  const body = new FormData();
  body.append('security', nonce);
  Object.entries(data).forEach(([key, value]) => body.append(key, value));

  const response = await fetch(url.replace('%%endpoint%%', endpoint), {
    method: 'POST',
    body,
    credentials: 'same-origin',
  });
  const json = await response.json().catch(() => ({ success: false }));

  if (json.fragments) applyFragments(json.fragments);
  return json;
}
