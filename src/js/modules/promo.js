import { cartRequest, hasCartApi } from './cart-api.js';

/**
 * Промокод (partials/promo.html): is-filled, пока в поле есть текст (крестик очистки, синяя кнопка);
 * «Применить» или Enter — демо-проверка: кодов в статике нет, поэтому всегда «Промокод не найден» (is-error).
 * Ошибка скрывается при вводе.
 *
 * В WordPress — купоны WC (cart-api.js, inc/ajax/cart.php): текст ошибки отдаёт сервер, применённый код остаётся в поле
 * (его же подставляет шаблон после перезагрузки), крестик снимает его с корзины. Промокод в корзине один.
 */
const FILLED_CLASS = 'is-filled';
const ERROR_CLASS = 'is-error';

function initPromo(root) {
  const input = root.querySelector('[data-js-promo-input]');
  const error = root.querySelector('[data-js-promo-error]');
  const api = hasCartApi();
  let applied = input.value.trim();
  let busy = false;

  const update = () => root.classList.toggle(FILLED_CLASS, input.value.trim() !== '');

  const request = async (endpoint, code) => {
    busy = true;
    root.setAttribute('aria-busy', 'true');
    const json = await cartRequest(endpoint, { coupon_code: code });
    busy = false;
    root.removeAttribute('aria-busy');
    return json;
  };

  const apply = async () => {
    const code = input.value.trim();
    if (!code) {
      input.focus();
      return;
    }
    if (!api) {
      root.classList.add(ERROR_CLASS);
      return;
    }
    if (busy || code === applied) return;

    const json = await request('arctik_coupon_apply', code);
    if (json.fragments) {
      applied = code;
      return;
    }
    error.textContent = json.data?.errors?.coupon_code || 'Промокод не\u00a0найден';
    root.classList.add(ERROR_CLASS);
  };

  input.addEventListener('input', () => {
    root.classList.remove(ERROR_CLASS);
    update();
  });

  // Enter внутри формы оформления отправил бы весь заказ
  input.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') return;
    event.preventDefault();
    apply();
  });

  root.querySelector('[data-js-promo-apply]').addEventListener('click', apply);

  root.querySelector('[data-js-promo-clear]').addEventListener('click', () => {
    if (api && applied && !busy) {
      request('arctik_coupon_remove', applied);
      applied = '';
    }
    input.value = '';
    root.classList.remove(ERROR_CLASS);
    update();
    input.focus();
  });

  update();
}

export function initPromos() {
  document.querySelectorAll('[data-js-promo]').forEach(initPromo);
}
