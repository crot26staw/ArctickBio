import { validateForm, setFieldError } from './field.js';
import { initSwipeButtons } from './swipe-button.js';

const MOBILE_QUERY = '(max-width: 767px)';
const ERROR_CLASS = 'is-error';
const STORAGE_NOTE = 'Срок хранения заказа — 14 дней';
const FAIL_MESSAGE = 'Не удалось оформить заказ, попробуйте ещё раз';

/**
 * Оформление заказа (partials/checkout/main.html).
 * - Способ получения [data-js-checkout-shipping="pickup|courier"]: показывает блок [data-js-checkout-panel] — пункт выдачи или курьер.
 * - «Оформляем 2 товара» / мобильная строка «Итого» [data-js-checkout-toggle] сворачивает список товаров;
 *   на мобилке список изначально свёрнут (макет 18270:36258).
 * - Выбор пункта выдачи / адреса приходит из попапов событиями pickup:choose / address:choose (pickup.js, address.js).
 * - Отправка: поля (field.js), выбран пункт выдачи / указан адрес курьера, согласие с офертой. В статике — переход на «Заказ оформлен».
 *   В WordPress (window.arctik.checkout — inc/checkout.php) — POST в ?wc-ajax=checkout: успех — переход по redirect,
 *   ошибки WC — у полей по data-id, у согласия — рамка, прочие (товар закончился) — под кнопкой оплаты [data-js-checkout-error="general"].
 *   Итоги [data-js-checkout-summary] после промокода обновляются фрагментами (cart-api.js) — кнопке оплаты заново ховер.
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
  const pickupAddress = form.querySelector('[data-js-checkout-pickup-address]');
  const pickupService = form.querySelector('[data-js-checkout-pickup-service]');
  const pickupError = form.querySelector('[data-js-checkout-error="pickup"]');
  const pickupIcon = form.querySelector('[data-js-checkout-pickup-icon] img');
  const pickupTitle = form.querySelector('[data-js-checkout-pickup-title]');
  const pickupText = form.querySelector('[data-js-checkout-pickup-text]');
  const addressValue = form.querySelector('[data-js-checkout-address-value]');
  const addressText = form.querySelector('[data-js-checkout-address-text]');
  const courierError = form.querySelector('[data-js-checkout-error="courier"]');
  const api = window.arctik?.checkout;
  let busy = false;

  // Ошибка под кнопкой оплаты: в WordPress блок итогов заменяется фрагментами — ищется каждый раз
  const generalError = () => form.querySelector('[data-js-checkout-error="general"]');

  const showGeneralError = (message) => {
    const error = generalError();
    error.textContent = message;
    error.hidden = !message;
  };

  const getShipping = () => form.querySelector('[data-js-checkout-shipping]:checked')?.dataset.jsCheckoutShipping;

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
    pickupAddress.value = `${detail.serviceName}, ${detail.address}`;
    pickupService.value = detail.service;
    pickupTitle.textContent = `Пункт ${detail.serviceName}`;
    pickupText.textContent = `${detail.address}\n${STORAGE_NOTE}`;
    pickupText.hidden = false;
    if (detail.logo) pickupIcon.src = detail.logo;
    pickupError.hidden = true;
  });

  document.addEventListener('address:choose', ({ detail }) => {
    addressText.textContent = detail.address;
    addressText.hidden = false;
    addressValue.value = detail.address;
    courierError.hidden = true;
  });

  // Ошибки WC: <li data-id="billing_phone">…</li> — у поля, data-id="terms" — согласие, остальные — под кнопкой оплаты
  const showServerErrors = (html) => {
    const doc = new DOMParser().parseFromString(html || '', 'text/html');
    const general = [];
    let first = null;

    doc.querySelectorAll('li').forEach((item) => {
      const message = item.textContent.replace(/\s+/g, ' ').trim();
      const input = item.dataset.id && form.elements[item.dataset.id];

      if (input === termsInput) {
        terms.classList.add(ERROR_CLASS);
        first ??= termsInput;
      } else if (input?.closest?.('[data-js-field]')) {
        setFieldError(input, message);
        first ??= input;
      } else if (message) {
        general.push(message);
      }
    });

    if (!first && !general.length) general.push(FAIL_MESSAGE);
    showGeneralError(general.join(' '));
    first?.focus();
  };

  const placeOrder = async () => {
    busy = true;
    form.setAttribute('aria-busy', 'true');
    showGeneralError('');

    const json = await fetch(api.endpoint, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
      .then((response) => response.json())
      .catch(() => ({}));

    if (json.result === 'success' && json.redirect) {
      window.location.assign(json.redirect);
      return;
    }
    if (json.reload) {
      window.location.reload();
      return;
    }

    busy = false;
    form.removeAttribute('aria-busy');
    showServerErrors(json.messages);
  };

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    if (busy) return;
    let valid = validateForm(form);

    if (getShipping() === 'pickup' && !pickupValue.value) {
      pickupError.hidden = false;
      if (valid) form.querySelector('[data-js-checkout-panel="pickup"] button').focus();
      valid = false;
    }

    if (getShipping() === 'courier' && !addressValue.value) {
      courierError.hidden = false;
      if (valid) form.querySelector('[data-js-checkout-panel="courier"] button').focus();
      valid = false;
    }

    if (!termsInput.checked) {
      terms.classList.add(ERROR_CLASS);
      if (valid) termsInput.focus();
      valid = false;
    }

    if (!valid) return;

    // В статике заказ «оформлен» — страница подтверждения
    if (api) placeOrder();
    else window.location.assign(form.getAttribute('action'));
  });

  if (api) document.addEventListener('cart:updated', initSwipeButtons);

  showPanel();
  setExpanded(!window.matchMedia(MOBILE_QUERY).matches);
}
