/**
 * Swipe-эффект кнопок (референс animata.design/docs/button/swipe-button):
 * при наведении содержимое уезжает вверх, снизу въезжает копия на фоне цвета ховера (--swipe-bg).
 *
 * Разметка:
 *   [data-js-swipe]               — кнопка с эффектом;
 *   [data-js-swipe-group]         — все ссылки внутри получают эффект (меню wp_nav_menu);
 *   [data-swipe-trigger]          — предок, наведение на который запускает эффект вложенной кнопки;
 *   [data-swipe-static]           — дочерний элемент, который не двигается (бейдж корзины).
 * Копия создаётся здесь, чтобы не дублировать содержимое в разметке/PHP. Стили — components/_swipe.scss.
 */

function setup(el) {
  if (el.classList.contains('swipe')) return;

  const inner = document.createElement('span');
  inner.className = 'swipe__inner';

  const label = document.createElement('span');
  label.className = 'swipe__label';
  [...el.childNodes].forEach((node) => {
    if (node.nodeType === Node.ELEMENT_NODE && node.hasAttribute('data-swipe-static')) return;
    label.append(node);
  });

  const clone = label.cloneNode(true);
  clone.classList.add('swipe__label--clone');
  clone.setAttribute('aria-hidden', 'true');
  clone.querySelectorAll('[id]').forEach((node) => node.removeAttribute('id'));

  inner.append(label, clone);
  el.prepend(inner);
  el.classList.add('swipe');

  // Сдвиг слоёв = высота кнопки, чтобы текст полностью уходил за край
  const setShift = () => el.style.setProperty('--swipe-shift', `${el.clientHeight}px`);
  setShift();
  new ResizeObserver(setShift).observe(el);
}

export function initSwipeButtons() {
  document.querySelectorAll('[data-js-swipe], [data-js-swipe-group] a').forEach(setup);
}
