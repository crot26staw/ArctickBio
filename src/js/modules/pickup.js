import { closeModal } from './modal.js';
import { cartRequest } from './cart-api.js';
import { initSuggest } from './suggest.js';
import { initSwipeButtons } from './swipe-button.js';
import { loadYmaps } from './ymaps.js';

const MOBILE_QUERY = '(max-width: 767px)';
const ACTIVE_CLASS = 'is-active';
const MARKER_SIZE = 35;
const MARKER_ACTIVE_SIZE = 44;
const POINT_ZOOM = 15;
const SUGGEST_DELAY = 250;

// Демо-города статики (в WordPress — подсказки СДЭК)
const DEMO_CITIES = [
  { name: 'Москва', label: 'Москва, Россия' },
  { name: 'Санкт-Петербург', label: 'Санкт-Петербург, Россия' },
  { name: 'Екатеринбург', label: 'Екатеринбург, Свердловская область' },
  { name: 'Мурманск', label: 'Мурманск, Мурманская область' },
  { name: 'Архангельск', label: 'Архангельск, Архангельская область' },
];

/**
 * Выбор пункта выдачи (partials/checkout/pickup-modal.html, макет 18270:36436).
 * - Службы: метки на карте [data-js-pickup-chip] (включаются по одной) и список «Службы выдачи» [data-js-pickup-select]
 *   (выбор — только эта служба). Ничего не выбрано — видны все пункты.
 * - Пункт выбирается радиокнопкой в списке или меткой на карте (на мобилке — переход на вкладку «Списком»);
 *   у выбранного — «Привезти сюда»: событие pickup:choose на document (checkout.js) и закрытие попапа.
 * - Город: [data-js-pickup-city-open] открывает поле с подсказками [data-js-pickup-city] (макета нет); новый город — событие
 *   pickup:clear (выбранный пункт сбрасывается).
 * - Мобилка: вкладки [data-js-pickup-tab] (data-view у диалога), поиск по адресу [data-js-pickup-search].
 * Статика — демо: карта-картинка с метками [data-js-pickup-marker], пункты в разметке, город меняет только подписи.
 * WordPress (window.arctik.pickup, есть <template data-js-pickup-point-template>): при открытии — пункты города из API СДЭК
 * (wc-ajax arctik_pickup_points), список — из шаблона, метки — на Яндекс Картах [data-js-pickup-ymap];
 * город — подсказки arctik_city_suggest, выбор — arctik_city_set (пересчёт доставки, фрагменты формы — cart-api.js).
 */
export function initPickup() {
  const root = document.querySelector('[data-js-pickup]');
  if (!root) return;

  const dialog = root.querySelector('[data-js-pickup-dialog]');
  const list = root.querySelector('[data-js-pickup-list]');
  const template = root.querySelector('[data-js-pickup-point-template]');
  const empty = root.querySelector('[data-js-pickup-empty]');
  const chips = [...root.querySelectorAll('[data-js-pickup-chip]')];
  const tabs = [...root.querySelectorAll('[data-js-pickup-tab]')];
  const select = root.querySelector('[data-js-pickup-select]');
  const selectBtn = select.querySelector('[data-js-pickup-select-btn]');
  const selectValue = select.querySelector('[data-js-pickup-select-value]');
  const selectList = select.querySelector('[data-js-pickup-select-list]');
  const options = [...selectList.querySelectorAll('[role="option"]')];
  const search = root.querySelector('[data-js-pickup-search]');
  const cityBox = root.querySelector('[data-js-pickup-city]');
  const cityInput = root.querySelector('[data-js-pickup-city-input]');
  const cityLabel = root.querySelector('[data-js-pickup-city-label]');
  const cityName = root.querySelector('[data-js-pickup-city-name]');
  const cityEdit = cityLabel.closest('[data-js-pickup-city-open]');
  const mobile = window.matchMedia(MOBILE_QUERY);
  const config = window.arctik?.pickup;
  const live = Boolean(config && template);

  const getPoints = () => [...list.querySelectorAll('[data-js-pickup-point]')];
  const serviceNames = Object.fromEntries(chips.map((chip) => [chip.dataset.jsPickupChip, chip.textContent.trim()]));
  const services = new Set(chips.filter((chip) => chip.getAttribute('aria-pressed') === 'true').map((chip) => chip.dataset.jsPickupChip));
  const isShown = (service) => !services.size || services.has(service);
  // В списке «Службы выдачи» название показывается, только если служба выбрана в нём (метки на карте его не меняют)
  let chosenInSelect = false;
  let loaded = false;

  const selectPoint = (id) => {
    const point = getPoints().find((item) => item.dataset.jsPickupPoint === id);
    if (!point) return;
    point.querySelector('[data-js-pickup-input]').checked = true;
    map.highlight(id);
    if (mobile.matches) setView('list');
    point.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  };

  // Метки: в статике — кнопки поверх картинки, в WordPress — Яндекс Карта (создаётся при первом открытии)
  const map = live ? createYandexMap(root, chips, config.mapKey, (id) => selectPoint(id)) : createDemoMap(root, selectPoint);

  const apply = () => {
    const query = search?.value.trim().toLowerCase() ?? '';

    getPoints().forEach((point) => {
      const address = point.querySelector('[data-js-pickup-point-address]').textContent.replace(/ /g, ' ').toLowerCase();
      point.hidden = !isShown(point.dataset.service) || (query && !address.includes(query));
    });
    map.filter(isShown);
    chips.forEach((chip) => chip.setAttribute('aria-pressed', String(services.has(chip.dataset.jsPickupChip))));

    const single = chosenInSelect && services.size === 1 ? serviceNames[[...services][0]] : '';
    options.forEach((option) => option.setAttribute('aria-selected', String(Boolean(single) && services.has(option.dataset.value))));
    selectValue.textContent = single || 'Службы выдачи';
    selectBtn.classList.toggle('is-filled', Boolean(single));
  };

  const setView = (view) => {
    dialog.dataset.view = view;
    tabs.forEach((tab) => tab.setAttribute('aria-selected', String(tab.dataset.jsPickupTab === view)));
    if (view === 'map') map.resize();
  };

  // ---------- Пункты из API (WordPress) ----------

  const renderPoints = ({ points, price, date }) => {
    list.replaceChildren(
      ...points.map((point) => {
        const node = template.content.firstElementChild.cloneNode(true);
        node.dataset.service = point.service;
        node.dataset.jsPickupPoint = point.id;
        node.querySelector('[data-js-pickup-input]').value = point.id;
        node.querySelector('[data-js-pickup-point-title]').textContent = point.title;
        node.querySelector('[data-js-pickup-point-address]').textContent = point.address;
        node.querySelector('[data-js-pickup-point-price]').textContent = price;
        node.querySelector('[data-js-pickup-point-date]').textContent = date;
        return node;
      }),
    );
    empty.hidden = points.length > 0;
    initSwipeButtons();
  };

  const setCity = (city) => {
    cityLabel.textContent = city.label;
    cityName.textContent = city.name;
  };

  const applyData = (data) => {
    setCity(data.city);
    renderPoints(data);
    map.setPoints(data.points);
    apply();
  };

  const load = async () => {
    root.setAttribute('aria-busy', 'true');
    const json = await cartRequest('arctik_pickup_points');
    root.removeAttribute('aria-busy');
    if (!json.success) return;
    loaded = true;
    applyData(json.data);
  };

  // ---------- Город ----------

  const closeCity = () => {
    cityBox.hidden = true;
    cityEdit.hidden = false;
  };

  const openCity = () => {
    if (mobile.matches) setView('list');
    cityEdit.hidden = true;
    cityBox.hidden = false;
    cityInput.value = '';
    cityInput.focus();
  };

  const chooseCity = async (city) => {
    closeCity();
    cityEdit.focus();
    document.dispatchEvent(new CustomEvent('pickup:clear'));

    if (!live) {
      setCity(city);
      return;
    }

    root.setAttribute('aria-busy', 'true');
    const json = await cartRequest('arctik_city_set', { code: city.code });
    root.removeAttribute('aria-busy');
    if (json.success) applyData(json.data);
  };

  initSuggest({
    input: cityInput,
    list: root.querySelector('[data-js-pickup-city-list]'),
    delay: live ? SUGGEST_DELAY : 0,
    source: live
      ? async (query) => (await cartRequest('arctik_city_suggest', { query })).data?.cities.map((city) => ({ ...city, label: city.full })) ?? []
      : (query) => DEMO_CITIES.filter((city) => city.name.toLowerCase().startsWith(query.toLowerCase())),
    onPick: chooseCity,
  });

  root.querySelectorAll('[data-js-pickup-city-open]').forEach((button) => button.addEventListener('click', openCity));

  // Ушли из поля, не выбрав город, — снова строка города
  cityInput.addEventListener('blur', () => setTimeout(() => !cityBox.contains(document.activeElement) && closeCity()));
  cityInput.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    event.stopPropagation();
    closeCity();
    cityEdit.focus();
  });

  // ---------- Службы, вкладки, выбор ----------

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

  root.addEventListener('change', (event) => {
    const input = event.target.closest('[data-js-pickup-input]');
    if (!input) return;
    map.highlight(input.value);
    map.focus(input.value);
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
          address: point.querySelector('[data-js-pickup-point-address]').textContent.replace(/ /g, ' ').trim(),
        },
      }),
    );
    closeModal();
  });

  // Мобилка открывается на карте (макет 18263:87991); пункты из API — при первом открытии
  root.addEventListener('modal:open', () => {
    setView('map');
    map.resize();
    if (live && !loaded) load();
  });

  apply();
}

/**
 * Демо-карта статики: метки [data-js-pickup-marker] поверх картинки.
 */
function createDemoMap(root, onSelect) {
  const markers = [...root.querySelectorAll('[data-js-pickup-marker]')];
  markers.forEach((marker) => marker.addEventListener('click', () => onSelect(marker.dataset.jsPickupMarker)));

  return {
    filter: (isShown) =>
      markers.forEach((marker) => {
        marker.hidden = !isShown(marker.dataset.service);
      }),
    highlight: (id) => markers.forEach((marker) => marker.classList.toggle(ACTIVE_CLASS, marker.dataset.jsPickupMarker === id)),
    focus: () => {},
    setPoints: () => {},
    resize: () => {},
  };
}

/**
 * Яндекс Карта в [data-js-pickup-ymap]: метки пунктов (ObjectManager с кластерами), иконка — data-pickup-marker у метки службы.
 * Создаётся при первом обращении (контейнер должен быть виден); вызовы до загрузки ждут её.
 */
function createYandexMap(root, chips, apiKey, onSelect) {
  const container = root.querySelector('[data-js-pickup-ymap]');
  const icons = Object.fromEntries(chips.map((chip) => [chip.dataset.jsPickupChip, chip.dataset.pickupMarker]));
  let ready = null;
  let activeId = null;

  const iconOptions = (service, active) => {
    const size = active ? MARKER_ACTIVE_SIZE : MARKER_SIZE;
    return {
      iconLayout: 'default#image',
      iconImageHref: icons[service],
      iconImageSize: [size, size],
      iconImageOffset: [-size / 2, -size / 2],
      zIndex: active ? 1000 : 0,
    };
  };

  const init = () => {
    ready ??= loadYmaps(apiKey).then((ymaps) => {
      const map = new ymaps.Map(container, { center: [55.75, 37.62], zoom: 10, controls: [] }, { suppressMapOpenBlock: true });
      // Зум справа: слева сверху — метки служб
      map.controls.add('zoomControl', { size: 'small', position: { right: 16, top: 16 } });
      const manager = new ymaps.ObjectManager({ clusterize: true, gridSize: 64 });
      manager.clusters.options.set('preset', 'islands#darkBlueClusterIcons');
      manager.objects.events.add('click', (event) => onSelect(event.get('objectId')));
      map.geoObjects.add(manager);
      return { map, manager };
    });
    ready.catch(() => {
      ready = null;
    });
    return ready;
  };

  // Метки, фильтр и центр — только у уже созданной карты (скрипт карт не грузится, пока попап не открывали)
  const withMap = (callback) => init().then(callback, () => {});
  const ifReady = (callback) => ready?.then(callback, () => {});

  const setActive = (manager, id, active) => {
    const object = manager.objects.getById(id);
    if (object) manager.objects.setObjectOptions(id, iconOptions(object.properties.service, active));
  };

  return {
    filter: (isShown) => ifReady(({ manager }) => manager.setFilter((object) => isShown(object.properties.service))),
    highlight: (id) =>
      ifReady(({ manager }) => {
        if (activeId) setActive(manager, activeId, false);
        activeId = id;
        setActive(manager, id, true);
      }),
    focus: (id) =>
      ifReady(({ map, manager }) => {
        const object = manager.objects.getById(id);
        if (object) map.setCenter(object.geometry.coordinates, Math.max(map.getZoom(), POINT_ZOOM), { duration: 300 });
      }),
    setPoints: (points) =>
      withMap(({ map, manager }) => {
        activeId = null;
        manager.removeAll();
        manager.add({
          type: 'FeatureCollection',
          features: points.map((point) => ({
            type: 'Feature',
            id: point.id,
            geometry: { type: 'Point', coordinates: [point.lat, point.lon] },
            properties: { service: point.service, hintContent: point.address },
            options: iconOptions(point.service, false),
          })),
        });
        // Один пункт — не приближать до предела
        if (points.length) {
          map
            .setBounds(manager.getBounds(), { checkZoomRange: true, zoomMargin: 40 })
            .then(() => map.getZoom() > POINT_ZOOM && map.setZoom(POINT_ZOOM));
        }
      }),
    resize: () => withMap(({ map }) => map.container.fitToViewport()),
  };
}
