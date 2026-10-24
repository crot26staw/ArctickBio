import { validateForm } from './field.js';

const MOBILE_QUERY = '(max-width: 767px)';
const ERROR_CLASS = 'is-error';
const STORAGE_NOTE = 'Срок хранения заказа — 14 дней';

/**
 * Оформление заказа (partials/checkout/main.html).
 * - Способ получения [data-js-checkout-shipping]: показывает блок [data-js-checkout-panel] — пункт выдачи или курьер.
 * - «Оформляем 2 товара» / мобильная строка «Итого» [data-js-checkout-toggle] сворачивает список товаров;
 *   на мобилке список изначально свёрнут (макет 18270:36258).
 * - Выбор пункта выдачи / адреса приходит из попапов событиями pickup:choose / address:choose (pickup.js, address.js).
 * - Отправка: поля (field.js), выбран пункт выдачи (для самовывоза), согласие с офертой. До WordPress — переход на «Заказ оформлен».
 */
export function initCheckout() {
  const form = document.querySelector('[data-js-checkout]');
  if (!form) return;

  const panels = [...form.querySelectorAll('[data-js-checkout-panel]')];
  const toggle = form.querySelector('[data-js-checkout-toggle]');
  const list = form.querySelector('[data-js-checkout-list]');
  const terms = form.querySelector('[data-js-checkout-terms]');
  const termsInput = terms.querySelector('input');
  const pickupValue = form.querySelector('[data-js-checkout-pickup-value]');
  const pickupError = form.querySelector('[data-js-checkout-error="pickup"]');
  const pickupIcon = form.querySelector('[data-js-checkout-pickup-icon] img');
  const pickupTitle = form.querySelector('[data-js-checkout-pickup-title]');
  const pickupText = form.querySelector('[data-js-checkout-pickup-text]');

  const getShipping = () => form.querySelector('[data-js-checkout-shipping]:checked')?.value;

  const showPanel = () => {
    const shipping = getShipping();
    panels.forEach((panel) => {
      panel.hidden = panel.dataset.jsCheckoutPanel !== shipping;
    });
  };

  const setExpanded = (expanded) => {
    toggle.setAttribute('aria-expanded', String(expanded));
    list.hidden = !expanded;
  };

  form.addEventListener('change', (event) => {
    if (event.target.matches('[data-js-checkout-shipping]')) showPanel();
    if (event.target === termsInput) terms.classList.remove(ERROR_CLASS);
  });

  toggle.addEventListener('click', () => setExpanded(toggle.getAttribute('aria-expanded') !== 'true'));

  document.addEventListener('pickup:choose', ({ detail }) => {
    pickupValue.value = detail.id;
    pickupTitle.textContent = `Пункт ${detail.serviceName}`;
    pickupText.textContent = `${detail.address}\n${STORAGE_NOTE}`;
    pickupText.hidden = false;
    if (detail.logo) pickupIcon.src = detail.logo;
    pickupError.hidden = true;
  });

  document.addEventListener('address:choose', ({ detail }) => {
    form.querySelector('[data-js-checkout-address-text]').textContent = detail.address;
    form.querySelector('[data-js-checkout-address-value]').value = detail.address;
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    let valid = validateForm(form);

    if (getShipping() === 'pickup' && !pickupValue.value) {
      pickupError.hidden = false;
      if (valid) form.querySelector('[data-js-checkout-panel="pickup"] button').focus();
      valid = false;
    }

    if (!termsInput.checked) {
      terms.classList.add(ERROR_CLASS);
      if (valid) termsInput.focus();
      valid = false;
    }

    // До WordPress: заказ «оформлен» — страница подтверждения
    if (valid) window.location.assign(form.getAttribute('action'));
  });

  showPanel();
  setExpanded(!window.matchMedia(MOBILE_QUERY).matches);
}
