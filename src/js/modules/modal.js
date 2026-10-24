import { getLenis } from './smooth-scroll.js';

const OPEN_CLASS = 'is-open';
const LOCK_CLASS = 'is-scroll-locked';
const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), [tabindex="0"]';

const isVisible = (el) => el.offsetParent !== null;
let current = null;
let opener = null;

export function openModal(name, trigger = null) {
  const root = document.querySelector(`[data-js-modal="${name}"]`);
  if (!root || root === current) return;
  if (current) closeModal();

  current = root;
  opener = trigger;
  root.inert = false;
  root.classList.add(OPEN_CLASS);
  document.documentElement.classList.add(LOCK_CLASS);
  getLenis()?.stop();
  root.dispatchEvent(new CustomEvent('modal:open'));

  const dialog = root.querySelector('[data-js-modal-dialog]');
  [...dialog.querySelectorAll(FOCUSABLE)].find(isVisible)?.focus({ preventScroll: true });
}

export function closeModal() {
  if (!current) return;
  const root = current;
  current = null;
  root.classList.remove(OPEN_CLASS);
  root.inert = true;
  document.documentElement.classList.remove(LOCK_CLASS);
  getLenis()?.start();
  root.dispatchEvent(new CustomEvent('modal:close'));
  opener?.focus({ preventScroll: true });
  opener = null;
}

/**
 * Попапы с картой на оформлении заказа (partials/checkout/pickup-modal.html, address-modal.html; стили blocks/_map-modal.scss).
 * [data-js-modal="<имя>"] — попап, [data-js-modal-open="<имя>"] — кнопка открытия, [data-js-modal-close] — закрыть
 * (крестик, «Назад», клик мимо панели), Escape тоже закрывает. Открыт один попап; пока открыт — Lenis stop, фокус внутри.
 * openModal / closeModal — для других модулей (pickup.js закрывает попап после выбора пункта).
 */
export function initModals() {
  if (!document.querySelector('[data-js-modal]')) return;

  document.addEventListener('click', (event) => {
    const openBtn = event.target.closest('[data-js-modal-open]');
    if (openBtn) {
      event.preventDefault();
      openModal(openBtn.dataset.jsModalOpen, openBtn);
      return;
    }

    if (current && event.target.closest('[data-js-modal-close]') && current.contains(event.target)) closeModal();
  });

  document.addEventListener('keydown', (event) => {
    if (!current) return;

    if (event.key === 'Escape') {
      closeModal();
      return;
    }

    if (event.key === 'Tab') {
      const items = [...current.querySelectorAll(`[data-js-modal-dialog] :is(${FOCUSABLE})`)].filter(isVisible);
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
}
