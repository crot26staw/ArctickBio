/**
 * Выбор формата в карточке товара: цена выбранного варианта ([data-js-product-format], data-price)
 * подставляется в заголовок и кнопку «В корзину» ([data-js-product-price] в той же форме/секции).
 */
export function initProductFormats() {
  document.querySelectorAll('[data-js-product-format]').forEach((radio) => {
    radio.addEventListener('change', () => {
      if (!radio.checked) return;
      const scope = radio.closest('.product') ?? document;
      scope.querySelectorAll('[data-js-product-price]').forEach((el) => {
        el.textContent = radio.dataset.price;
      });
    });
  });
}
