/**
 * «Где нас купить?» ([data-js-stores]): у каждого адреса своя Яндекс Карта. Нажали адрес [data-js-stores-address] —
 * в iframe [data-js-stores-map] его карта (data-map; нет — карта по умолчанию data-default-map у iframe),
 * у адреса is-active + aria-pressed. Открыли город (аккордеон, accordion.js) — выбран его первый адрес.
 */
const ACTIVE_CLASS = 'is-active';

export function initStores() {
  document.querySelectorAll('[data-js-stores]').forEach((section) => {
    const frame = section.querySelector('[data-js-stores-map]');
    const addresses = [...section.querySelectorAll('[data-js-stores-address]')];
    if (!frame || !addresses.length) return;

    const select = (address) => {
      addresses.forEach((item) => {
        const isActive = item === address;
        item.classList.toggle(ACTIVE_CLASS, isActive);
        item.setAttribute('aria-pressed', String(isActive));
      });

      const url = address.dataset.map || frame.dataset.defaultMap;
      if (url && frame.getAttribute('src') !== url) frame.setAttribute('src', url);
    };

    addresses.forEach((address) => address.addEventListener('click', () => select(address)));

    section.querySelectorAll('[data-js-accordion]').forEach((city) => {
      const toggle = city.querySelector('[data-js-accordion-toggle]');
      const first = city.querySelector('[data-js-stores-address]');
      if (!toggle || !first) return;

      // accordion.js обновляет aria-expanded в своём обработчике — он подключён раньше
      toggle.addEventListener('click', () => {
        if (toggle.getAttribute('aria-expanded') === 'true') select(first);
      });
    });
  });
}
