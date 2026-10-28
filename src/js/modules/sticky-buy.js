/**
 * Нижняя панель покупки на мобилке ([data-js-sticky-buy]): появляется (is-visible), когда блок с кнопкой
 * «В корзину» в форме ([data-js-sticky-buy-source]) ушёл с экрана. На ≥768 панель скрыта в CSS.
 * Количество в панели и в форме синхронизировано; кнопка панели отправляет форму ([data-js-sticky-buy-form]).
 * Секция лежит в .overlap-stack, первая секция там sticky — это свой контекст наложения, и z-index панели
 * не поднимал её над следующими секциями (видео закрывало кнопку). Поэтому панель переносится сразу за .overlap-stack.
 */
const VISIBLE_CLASS = 'is-visible';

function syncInputs(a, b) {
  a.addEventListener('change', () => {
    if (b.value === a.value) return;
    b.value = a.value;
    b.dispatchEvent(new Event('change', { bubbles: true }));
  });
}

export function initStickyBuy() {
  document.querySelectorAll('[data-js-sticky-buy]').forEach((bar) => {
    const scope = bar.closest('.product') ?? document;
    const form = scope.querySelector('[data-js-sticky-buy-form]');
    const source = scope.querySelector('[data-js-sticky-buy-source]');
    if (!form || !source) return;
    bar.closest('.overlap-stack')?.after(bar);

    const formInput = source.querySelector('[data-js-quantity-input]');
    const barInput = bar.querySelector('[data-js-quantity-input]');
    if (formInput && barInput) {
      syncInputs(formInput, barInput);
      syncInputs(barInput, formInput);
    }

    bar.querySelector('[data-js-sticky-buy-submit]')?.addEventListener('click', () => form.requestSubmit());

    new IntersectionObserver(([entry]) => {
      const visible = !entry.isIntersecting;
      bar.classList.toggle(VISIBLE_CLASS, visible);
      bar.inert = !visible;
      bar.setAttribute('aria-hidden', String(!visible));
    }).observe(source);
  });
}
