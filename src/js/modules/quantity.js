/**
 * Счётчик количества товара (partials/quantity.html): кнопки −/+ меняют input.qty в пределах min/max.
 * Событие change на поле — как при ручном вводе, чтобы WooCommerce и другие скрипты его видели.
 */
function initQuantity(root) {
  const input = root.querySelector('[data-js-quantity-input]');
  const minus = root.querySelector('[data-js-quantity-minus]');
  const plus = root.querySelector('[data-js-quantity-plus]');

  const min = Number(input.min) || 1;
  const max = Number(input.max) || Infinity;

  const clamp = (value) => Math.min(max, Math.max(min, Math.round(value) || min));

  const update = () => {
    minus.disabled = Number(input.value) <= min;
    plus.disabled = Number(input.value) >= max;
  };

  const set = (value) => {
    input.value = clamp(value);
    input.dispatchEvent(new Event('change', { bubbles: true }));
  };

  minus.addEventListener('click', () => set(Number(input.value) - 1));
  plus.addEventListener('click', () => set(Number(input.value) + 1));
  input.addEventListener('change', () => {
    input.value = clamp(Number(input.value));
    update();
  });

  update();
}

// scope — область поиска (товары корзины после обновления фрагментами, cart.js)
export function initQuantities(scope = document) {
  scope.querySelectorAll('[data-js-quantity]').forEach(initQuantity);
}
