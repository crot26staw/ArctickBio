/**
 * Выбор формата в карточке товара: цена выбранного варианта ([data-js-product-format], data-price)
 * подставляется в заголовок и кнопку «В корзину» ([data-js-product-price] в той же форме/секции).
 * В WordPress у варианта ещё data-max — сколько можно купить (остаток): это max у счётчиков количества секции.
 */
export function initProductFormats() {
  document.querySelectorAll('[data-js-product-format]').forEach((radio) => {
    radio.addEventListener('change', () => {
      if (!radio.checked) return;
      const scope = radio.closest('.product') ?? document;
      scope.querySelectorAll('[data-js-product-price]').forEach((el) => {
        el.textContent = radio.dataset.price;
      });

      if (!radio.dataset.max) return;
      scope.querySelectorAll('[data-js-quantity-input]').forEach((input) => {
        input.max = radio.dataset.max;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
    });
  });
}
