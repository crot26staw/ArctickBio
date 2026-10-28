import { closeModal } from './modal.js';
import { validateForm, setFieldError } from './field.js';
import { initSuggest } from './suggest.js';

// Демо-подсказки до WordPress (там — ymaps.suggest). Текст из макета 18271:40002
const DEMO_ADDRESSES = [
  'Московская область, Балашиха, улица Свердлова, 30',
  'Московская область, Балашиха, улица Свердлова, 32',
  'Московская область, Мытищи, Олимпийский проспект, 10',
  'Москва, улица Хамовнический Вал, 24',
  'Екатеринбург, Уральская улица, 70',
];

/**
 * Адрес доставки курьером (partials/checkout/address-modal.html, макет 18271:39931 / 18271:40002).
 * Поле адреса — combobox с подсказками [data-js-address-suggest] (suggest.js); «Привезти сюда» проверяет адрес
 * и отдаёт его событием address:choose на document (checkout.js), попап закрывается.
 */
export function initAddress() {
  const root = document.querySelector('[data-js-address]');
  if (!root) return;

  const form = root.querySelector('[data-js-address-form]');
  const input = root.querySelector('[data-js-address-input]');

  initSuggest({
    input,
    list: root.querySelector('[data-js-address-suggest]'),
    source: (query) =>
      DEMO_ADDRESSES.filter((address) => address.toLowerCase().includes(query.toLowerCase())).map((address) => ({ label: address })),
    onPick: ({ label }) => {
      input.value = label.replace(/\u00a0/g, ' ');
      setFieldError(input, '');
    },
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!validateForm(form)) return;

    const extra = [
      ['shipping_flat', 'кв.'],
      ['shipping_entrance', 'подъезд'],
      ['shipping_floor', 'этаж'],
    ]
      .map(([name, label]) => (form.elements[name].value.trim() ? `${label} ${form.elements[name].value.trim()}` : ''))
      .filter(Boolean);

    document.dispatchEvent(
      new CustomEvent('address:choose', {
        detail: { address: [input.value.trim(), ...extra].join(', ') },
      }),
    );
    closeModal();
  });
}
