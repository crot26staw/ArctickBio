import { closeModal } from './modal.js';

const MOBILE_QUERY = '(max-width: 767px)';
const ACTIVE_CLASS = 'is-active';

/**
 * Выбор пункта выдачи (partials/checkout/pickup-modal.html, макет 18270:36436).
 * - Службы: метки на карте [data-js-pickup-chip] (включаются по одной) и список «Службы выдачи» [data-js-pickup-select]
 *   (выбор — только эта служба). Ничего не выбрано — видны все пункты.
 * - Пункт выбирается радиокнопкой в списке или меткой на карте [data-js-pickup-marker] (на мобилке — переход на вкладку «Списком»);
 *   у выбранного — «Привезти сюда»: событие pickup:choose на document (checkout.js) и закрытие попапа.
 * - Мобилка: вкладки [data-js-pickup-tab] (data-view у диалога), поиск по адресу [data-js-pickup-search].
 * В WordPress пункты и метки придут из API службы доставки; метки — на Яндекс Картах.
 */
export function initPickup() {
  const root = document.querySelector('[data-js-pickup]');
  if (!root) return;

  const dialog = root.querySelector('[data-js-pickup-dialog]');
  const points = [...root.querySelectorAll('[data-js-pickup-point]')];
  const markers = [...root.querySelectorAll('[data-js-pickup-marker]')];
  const chips = [...root.querySelectorAll('[data-js-pickup-chip]')];
  const tabs = [...root.querySelectorAll('[data-js-pickup-tab]')];
  const select = root.querySelector('[data-js-pickup-select]');
  const selectBtn = select.querySelector('[data-js-pickup-select-btn]');
  const selectValue = select.querySelector('[data-js-pickup-select-value]');
  const selectList = select.querySelector('[data-js-pickup-select-list]');
  const options = [...selectList.querySelectorAll('[role="option"]')];
  const search = root.querySelector('[data-js-pickup-search]');
  const mobile = window.matchMedia(MOBILE_QUERY);

  const serviceNames = Object.fromEntries(chips.map((chip) => [chip.dataset.jsPickupChip, chip.textContent.trim()]));
  const services = new Set(chips.filter((chip) => chip.getAttribute('aria-pressed') === 'true').map((chip) => chip.dataset.jsPickupChip));
  // В списке «Службы выдачи» название показывается, только если служба выбрана в нём (метки на карте его не меняют)
  let chosenInSelect = false;

  const apply = () => {
    const query = search?.value.trim().toLowerCase() ?? '';
    const isShown = (service) => !services.size || services.has(service);

    points.forEach((point) => {
      const address = point.querySelector('[data-js-pickup-point-address]').textContent.replace(/\u00a0/g, ' ').toLowerCase();
      point.hidden = !isShown(point.dataset.service) || (query && !address.includes(query));
    });
    markers.forEach((marker) => {
      marker.hidden = !isShown(marker.dataset.service);
    });
    chips.forEach((chip) => chip.setAttribute('aria-pressed', String(services.has(chip.dataset.jsPickupChip))));

    const single = chosenInSelect && services.size === 1 ? serviceNames[[...services][0]] : '';
    options.forEach((option) => option.setAttribute('aria-selected', String(Boolean(single) && services.has(option.dataset.value))));
    selectValue.textContent = single || 'Службы выдачи';
    selectBtn.classList.toggle('is-filled', Boolean(single));
  };

  const setView = (view) => {
    dialog.dataset.view = view;
    tabs.forEach((tab) => tab.setAttribute('aria-selected', String(tab.dataset.jsPickupTab === view)));
  };

  const highlight = (id) => {
    markers.forEach((marker) => marker.classList.toggle(ACTIVE_CLASS, marker.dataset.jsPickupMarker === id));
  };

  const toggleSelect = (open = selectList.hidden) => {
    selectList.hidden = !open;
    selectBtn.setAttribute('aria-expanded', String(open));
    if (open) (options.find((option) => option.getAttribute('aria-selected') === 'true') || options[0]).focus();
  };

  chips.forEach((chip) =>
    chip.addEventListener('click', () => {
      const service = chip.dataset.jsPickupChip;
      if (services.has(service)) services.delete(service);
      else services.add(service);
      chosenInSelect = false;
      apply();
    }),
  );

  selectBtn.addEventListener('click', () => toggleSelect());

  const choose = (option) => {
    services.clear();
    services.add(option.dataset.value);
    chosenInSelect = true;
    apply();
    toggleSelect(false);
    selectBtn.focus();
  };

  selectList.addEventListener('click', (event) => {
    const option = event.target.closest('[role="option"]');
    if (option) choose(option);
  });

  selectList.addEventListener('keydown', (event) => {
    const index = options.indexOf(document.activeElement);
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault();
      const next = (index + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length;
      options[next].focus();
    } else if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      if (index >= 0) choose(options[index]);
    } else if (event.key === 'Escape') {
      // Escape закрывает только список, не попап
      event.stopPropagation();
      toggleSelect(false);
      selectBtn.focus();
    }
  });

  document.addEventListener('click', (event) => {
    if (!selectList.hidden && !select.contains(event.target)) toggleSelect(false);
  });

  tabs.forEach((tab) => tab.addEventListener('click', () => setView(tab.dataset.jsPickupTab)));

  markers.forEach((marker) =>
    marker.addEventListener('click', () => {
      const point = points.find((item) => item.dataset.jsPickupPoint === marker.dataset.jsPickupMarker);
      if (!point) return;
      point.querySelector('[data-js-pickup-input]').checked = true;
      highlight(marker.dataset.jsPickupMarker);
      if (mobile.matches) setView('list');
      point.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }),
  );

  root.addEventListener('change', (event) => {
    const input = event.target.closest('[data-js-pickup-input]');
    if (input) highlight(input.value);
  });

  search?.addEventListener('input', apply);

  root.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-js-pickup-choose]');
    if (!btn) return;
    const point = btn.closest('[data-js-pickup-point]');
    document.dispatchEvent(
      new CustomEvent('pickup:choose', {
        detail: {
          id: point.dataset.jsPickupPoint,
          service: point.dataset.service,
          serviceName: serviceNames[point.dataset.service],
          logo: chips.find((chip) => chip.dataset.jsPickupChip === point.dataset.service)?.querySelector('img')?.getAttribute('src'),
          address: point.querySelector('[data-js-pickup-point-address]').textContent.replace(/\u00a0/g, ' ').trim(),
        },
      }),
    );
    closeModal();
  });

  // Мобилка открывается на карте (макет 18263:87991)
  root.addEventListener('modal:open', () => setView('map'));

  apply();
}
