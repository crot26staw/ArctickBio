import { getLenis } from './smooth-scroll.js';

const OPEN_CLASS = 'is-open';
const LOCK_CLASS = 'is-scroll-locked';
const HEADER_CLASS = 'is-cart-open';
const BLOCKED_CLASS = 'is-blocked';
const SOLD_CLASS = 'cart-item--sold';
const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled])';

const formatPrice = (value) => `${value}₽`;

/**
 * Корзина-попап (partials/cart.html). Открывает кнопка корзины в шапке [data-js-cart-open] — повторное нажатие
 * закрывает (на мобилке в ней крестик), закрывают [data-js-cart-close] (крестик, клик мимо панели) и Escape.
 * Пока открыта: Lenis stop, фокус внутри панели, у шапки is-cart-open.
 *
 * Пересчёт до WordPress — демо (там фрагмент мини-корзины обновляет WooCommerce): счётчик −/+ (quantity.js, событие change)
 * меняет сумму строки, итог и число товаров в заголовке и бейдже шапки; «Удалить» убирает товар сразу (заметка в макете —
 * при количестве 1 минус неактивен, удалить можно только кнопкой). Нет товаров — экран «Корзина пуста».
 * Остались только разобранные — оформить нельзя: is-blocked (макет 18269:34349), кнопка неактивна.
 */
export function initCart() {
  const root = document.querySelector('[data-js-cart]');
  if (!root) return;

  const dialog = root.querySelector('[data-js-cart-dialog]');
  const list = root.querySelector('[data-js-cart-list]');
  const empty = root.querySelector('[data-js-cart-empty]');
  const content = root.querySelector('[data-js-cart-content]');
  const titleCount = root.querySelector('[data-js-cart-title-count]');
  const total = root.querySelector('[data-js-cart-total]');
  const discount = Number(root.querySelector('[data-js-cart-discount]')?.dataset.value) || 0;
  const checkout = root.querySelector('[data-js-cart-checkout]');
  const header = document.querySelector('[data-js-header]');
  let opener = null;

  const isOpen = () => root.classList.contains(OPEN_CLASS);

  const update = () => {
    const items = [...list.querySelectorAll('[data-js-cart-item]')];
    const available = items.filter((item) => !item.classList.contains(SOLD_CLASS));
    let count = 0;
    let sum = 0;

    available.forEach((item) => {
      const qty = Number(item.querySelector('[data-js-quantity-input]').value) || 1;
      const line = Number(item.dataset.price) * qty;
      item.querySelector('[data-js-cart-item-price]').textContent = formatPrice(line);
      count += qty;
      sum += line;
    });

    empty.hidden = items.length > 0;
    content.hidden = items.length === 0;
    titleCount.textContent = items.length ? ` (${count})` : '';
    total.textContent = formatPrice(Math.max(sum - discount, 0));

    const blocked = items.length > 0 && available.length === 0;
    root.classList.toggle(BLOCKED_CLASS, blocked);
    checkout.classList.toggle('is-disabled', blocked);
    checkout.setAttribute('aria-disabled', String(blocked));

    document.querySelectorAll('[data-js-cart-count]').forEach((badge) => {
      badge.textContent = count;
    });
  };

  const open = (trigger = null) => {
    opener = trigger;
    root.inert = false;
    root.classList.add(OPEN_CLASS);
    header?.classList.add(HEADER_CLASS);
    document.documentElement.classList.add(LOCK_CLASS);
    getLenis()?.stop();
    // На мобилке крестик закрытия — в шапке, фокус остаётся на нём
    if (getComputedStyle(dialog.querySelector('[data-js-cart-close]')).display !== 'none') {
      dialog.querySelector(FOCUSABLE)?.focus({ preventScroll: true });
    }
  };

  const close = () => {
    if (!isOpen()) return;
    root.classList.remove(OPEN_CLASS);
    root.inert = true;
    header?.classList.remove(HEADER_CLASS);
    document.documentElement.classList.remove(LOCK_CLASS);
    getLenis()?.start();
    opener?.focus({ preventScroll: true });
    opener = null;
  };

  list.addEventListener('change', (event) => {
    if (event.target.closest('[data-js-quantity-input]')) update();
  });

  root.addEventListener('click', (event) => {
    const remove = event.target.closest('[data-js-cart-remove]');
    if (remove) {
      event.preventDefault();
      remove.closest('[data-js-cart-item]').remove();
      update();
      return;
    }

    if (event.target.closest('[data-js-cart-close]')) close();
  });

  document.addEventListener('click', (event) => {
    const openBtn = event.target.closest('[data-js-cart-open]');
    if (!openBtn) return;
    event.preventDefault();
    if (isOpen()) close();
    else open(openBtn);
  });

  document.addEventListener('keydown', (event) => {
    if (!isOpen()) return;

    if (event.key === 'Escape') {
      close();
      return;
    }

    // Фокус не уходит из панели (на мобилке — ещё и кнопка-крестик в шапке)
    if (event.key === 'Tab') {
      const items = [...dialog.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
      if (opener && !root.contains(opener) && getComputedStyle(dialog.querySelector('[data-js-cart-close]')).display === 'none') {
        items.unshift(opener);
      }
      const first = items[0];
      const last = items[items.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  });

  update();
}
