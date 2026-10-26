/**
 * Промокод (partials/promo.html): is-filled, пока в поле есть текст (крестик очистки, синяя кнопка);
 * «Применить» или Enter — демо-проверка: кодов в статике нет, поэтому всегда «Промокод не найден» (is-error).
 * В WordPress проверяет сервер. Ошибка скрывается при вводе.
 */
const FILLED_CLASS = 'is-filled';
const ERROR_CLASS = 'is-error';

function initPromo(root) {
  const input = root.querySelector('[data-js-promo-input]');

  const update = () => root.classList.toggle(FILLED_CLASS, input.value.trim() !== '');

  const apply = () => {
    if (!input.value.trim()) {
      input.focus();
      return;
    }
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
