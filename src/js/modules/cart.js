import { getLenis } from './smooth-scroll.js';
import { cartRequest, hasCartApi } from './cart-api.js';
import { initQuantities } from './quantity.js';
import { initRollText } from './roll-text.js';

const OPEN_CLASS = 'is-open';
const LOCK_CLASS = 'is-scroll-locked';
const HEADER_CLASS = 'is-cart-open';
const BLOCKED_CLASS = 'is-blocked';
const SOLD_CLASS = 'cart-item--sold';
const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled])';
const QTY_DELAY = 400;

// «1 440 ₽» — как цены WooCommerce (неразрывные пробелы в разрядах и перед ₽)
const formatPrice = (value) => `${String(value).replace(/\B(?=(\d{3})+(?!\d))/g, '\u00a0')}\u00a0₽`;

/**
 * Корзина-попап (partials/cart.html). Открывает кнопка корзины в шапке [data-js-cart-open] — повторное нажатие
 * закрывает (на мобилке в ней крестик), закрывают [data-js-cart-close] (крестик, клик мимо панели) и Escape.
 * Пока открыта: Lenis stop, фокус внутри панели, у шапки is-cart-open.
 *
 * В статике пересчёт — демо: счётчик −/+ (quantity.js, событие change) меняет сумму строки, итог и число товаров
 * в заголовке и бейдже шапки; «Удалить» убирает товар сразу (заметка в макете — при количестве 1 минус неактивен,
 * удалить можно только кнопкой). Нет товаров — экран «Корзина пуста».
 * Остались только разобранные — оформить нельзя: is-blocked (макет 18269:34349), кнопка неактивна.
 *
 * В WordPress (cart-api.js) суммы и счётчики считает сервер: количество (с задержкой QTY_DELAY), «Удалить» и «+» у карточек
 * товаров (.add_to_cart_button[data-product_id]), форма покупки на странице товара ([data-js-cart-form]) — запросы,
 * ответ — фрагменты WC. Списки и итоги после ответа заменяются,
 * поэтому элементы ищутся при каждом обновлении. После добавления открывается попап с обновлённой корзиной
 * (в статике — сразу, с демо-товарами).
 */
export function initCart() {
  const root = document.querySelector('[data-js-cart]');
  if (!root) return;

  const api = hasCartApi();
  const dialog = root.querySelector('[data-js-cart-dialog]');
  const empty = root.querySelector('[data-js-cart-empty]');
  const content = root.querySelector('[data-js-cart-content]');
  const checkout = root.querySelector('[data-js-cart-checkout]');
  const header = document.querySelector('[data-js-header]');
  const qtyTimers = new Map();
  let opener = null;

  const isOpen = () => root.classList.contains(OPEN_CLASS);

  // Демо-пересчёт статики: суммы строк, итог, число товаров
  const recount = (available, hasItems) => {
    const discount = Number(root.querySelector('[data-js-cart-discount]')?.dataset.value) || 0;
    let count = 0;
    let sum = 0;

    available.forEach((item) => {
      const qty = Number(item.querySelector('[data-js-quantity-input]').value) || 1;
      const line = Number(item.dataset.price) * qty;
      item.querySelector('[data-js-cart-item-price]').textContent = formatPrice(line);
      count += qty;
      sum += line;
    });

    root.querySelector('[data-js-cart-title-count]').textContent = hasItems ? ` (${count})` : '';
    root.querySelector('[data-js-cart-total]').textContent = formatPrice(Math.max(sum - discount, 0));
    document.querySelectorAll('[data-js-cart-count]').forEach((badge) => {
      badge.textContent = count;
    });
  };

  const update = () => {
    const items = [...root.querySelectorAll('[data-js-cart-item]')];
    const available = items.filter((item) => !item.classList.contains(SOLD_CLASS));

    if (!api) recount(available, items.length > 0);

    empty.hidden = items.length > 0;
    content.hidden = items.length === 0;

    const blocked = items.length > 0 && available.length === 0;
    root.classList.toggle(BLOCKED_CLASS, blocked);
    checkout.classList.toggle('is-disabled', blocked);
    checkout.setAttribute('aria-disabled', String(blocked));
  };

  // Новые товары из фрагментов: счётчики и ховер «Удалить»
  const refresh = () => {
    const list = root.querySelector('[data-js-cart-list]');
    if (list) {
      initQuantities(list);
      initRollText(list);
    }
    update();
  };

  const setQuantity = (input) => {
    const item = input.closest('[data-js-cart-item]');
    const key = item.dataset.cart_item_key;
    clearTimeout(qtyTimers.get(key));
    qtyTimers.set(key, setTimeout(() => {
      qtyTimers.delete(key);
      cartRequest('arctik_cart_qty', { cart_item_key: key, quantity: input.value });
    }, QTY_DELAY));
  };

  const removeItem = (link) => {
    const item = link.closest('[data-js-cart-item]');
    const key = item.dataset.cart_item_key;
    item.remove();
    update();
    if (api) {
      clearTimeout(qtyTimers.get(key));
      cartRequest('remove_from_cart', { cart_item_key: key });
    }
  };

  // Товар добавлен — открыть попап (уже открыт — «Попробуйте также» — остаётся открытым)
  const showAdded = (trigger) => {
    if (!isOpen()) open(trigger);
  };

  // «+» у карточек товаров (каталог, мини-карточки, «Попробуйте также»); не вышло (нет в наличии) — на страницу товара, как в WC
  const addToCart = async (button) => {
    if (!api) {
      showAdded(button);
      return;
    }
    if (button.getAttribute('aria-busy') === 'true') return;
    button.setAttribute('aria-busy', 'true');
    const json = await cartRequest('add_to_cart', { product_id: button.dataset.product_id, quantity: 1 });
    button.removeAttribute('aria-busy');
    if (json.error && json.product_url) window.location.href = json.product_url;
    else if (json.fragments) showAdded(button);
  };

  // Форма покупки на странице товара: выбранная вариация (радио формата, variation_id) или сам товар (add-to-cart).
  // Ошибку (не хватило остатка) WC пишет в уведомления — на странице товара их нет, кнопка просто снова активна
  const submitProductForm = async (form, trigger) => {
    if (!api) {
      showAdded(trigger);
      return;
    }
    if (form.getAttribute('aria-busy') === 'true') return;
    const productId = form.querySelector('[data-js-product-format]:checked')?.value
      || form.elements['add-to-cart']?.value;
    if (!productId) return;
    form.setAttribute('aria-busy', 'true');
    const json = await cartRequest('add_to_cart', { product_id: productId, quantity: form.elements.quantity?.value || 1 });
    form.removeAttribute('aria-busy');
    if (json.fragments && !json.error) showAdded(trigger);
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

  root.addEventListener('change', (event) => {
    const input = event.target.closest('[data-js-cart-list] [data-js-quantity-input]');
    if (!input) return;
    if (api) setQuantity(input);
    else update();
  });

  root.addEventListener('click', (event) => {
    const remove = event.target.closest('[data-js-cart-remove]');
    if (remove) {
      event.preventDefault();
      removeItem(remove);
      return;
    }

    if (event.target.closest('[data-js-cart-close]')) close();
  });

  if (api) document.addEventListener('cart:updated', refresh);

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.add_to_cart_button[data-product_id]');
    if (!button) return;
    event.preventDefault();
    addToCart(button);
  });

  // В статике форма никуда не отправляется (action="#"). Нижняя панель мобилки отправляет форму через requestSubmit()
  // без submitter — фокус тогда вернётся на её кнопку (activeElement)
  document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-js-cart-form]');
    if (!form) return;
    event.preventDefault();
    submitProductForm(form, event.submitter || document.activeElement);
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
