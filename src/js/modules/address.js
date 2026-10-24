import { closeModal } from './modal.js';
import { validateForm, setFieldError } from './field.js';

// Демо-подсказки до WordPress (там — ymaps.suggest). Текст из макета 18271:40002
const DEMO_ADDRESSES = [
  'Московская область, Балашиха, улица Свердлова, 30',
  'Московская область, Балашиха, улица Свердлова, 32',
  'Московская область, Мытищи, Олимпийский проспект, 10',
  'Москва, улица Хамовнический Вал, 24',
  'Екатеринбург, Уральская улица, 70',
];
const MIN_CHARS = 2;
const ACTIVE_CLASS = 'is-active';

/**
 * Адрес доставки курьером (partials/checkout/address-modal.html, макет 18271:39931 / 18271:40002).
 * Поле адреса — combobox с подсказками [data-js-address-suggest] (стрелки, Enter, клик); «Привезти сюда» проверяет адрес
 * и отдаёт его событием address:choose на document (checkout.js), попап закрывается.
 */
export function initAddress() {
  const root = document.querySelector('[data-js-address]');
  if (!root) return;

  const form = root.querySelector('[data-js-address-form]');
  const input = root.querySelector('[data-js-address-input]');
  const list = root.querySelector('[data-js-address-suggest]');
  let items = [];
  let active = -1;

  const hide = () => {
    list.hidden = true;
    input.setAttribute('aria-expanded', 'false');
    input.removeAttribute('aria-activedescendant');
    active = -1;
  };

  const setActive = (index) => {
    active = index;
    items.forEach((item, i) => item.classList.toggle(ACTIVE_CLASS, i === index));
    if (items[index]) {
      input.setAttribute('aria-activedescendant', items[index].id);
      items[index].scrollIntoView({ block: 'nearest' });
    }
  };

  const render = () => {
    const query = input.value.trim().toLowerCase();
    const matches = query.length < MIN_CHARS ? [] : DEMO_ADDRESSES.filter((address) => address.toLowerCase().includes(query));

    list.innerHTML = '';
    items = matches.map((address, index) => {
      const item = document.createElement('li');
      item.className = 'map-modal__suggest-item';
      item.id = `address-suggest-${index}`;
      item.setAttribute('role', 'option');
      item.textContent = address;
      list.append(item);
      return item;
    });

    if (!items.length) {
      hide();
      return;
    }
    list.hidden = false;
    input.setAttribute('aria-expanded', 'true');
    active = -1;
  };

  const pick = (item) => {
    input.value = item.textContent;
    setFieldError(input, '');
    hide();
  };

  input.addEventListener('input', render);
  input.addEventListener('focus', render);
  input.addEventListener('blur', hide);

  input.addEventListener('keydown', (event) => {
    if (list.hidden) return;
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault();
      const step = event.key === 'ArrowDown' ? 1 : -1;
      setActive((active + step + items.length) % items.length);
    } else if (event.key === 'Enter' && items[active]) {
      event.preventDefault();
      pick(items[active]);
    } else if (event.key === 'Escape') {
      // Escape закрывает только подсказки, не попап
      event.stopPropagation();
      hide();
    }
  });

  // mousedown, а не click: иначе поле теряет фокус (blur) и список прячется раньше клика
  list.addEventListener('mousedown', (event) => {
    const item = event.target.closest('[role="option"]');
    if (!item) return;
    event.preventDefault();
    pick(item);
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
