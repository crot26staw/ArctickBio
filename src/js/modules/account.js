import { validateForm, resetForm, setFieldError } from './field.js';
import { initSwipeButtons } from './swipe-button.js';

const api = () => window.arctik?.account;

/**
 * Запрос в WordPress (window.arctik.account — inc/account.php, обработчики inc/ajax/account.php).
 * Возвращает данные успешного ответа или null; ошибки { errors: { имя поля: текст } } показываются у полей формы.
 */
async function accountRequest(action, body, form = null) {
  const { endpoint, nonce } = api();
  body.append('security', nonce);

  form?.setAttribute('aria-busy', 'true');
  const json = await fetch(endpoint.replace('%%endpoint%%', action), { method: 'POST', body, credentials: 'same-origin' })
    .then((response) => response.json())
    .catch(() => ({ success: false }));
  form?.removeAttribute('aria-busy');

  if (json.success) return json.data || {};
  if (!form) return null;

  const errors = json.data?.errors || {};
  const inputs = [...form.querySelectorAll('[data-js-field-input]')];
  // Поле без имени в ответе (или нет ответа) — ошибка у первого поля формы
  if (!Object.keys(errors).length) errors[inputs[0].name] = 'Не\u00a0удалось сохранить, попробуйте ещё раз';

  let first = null;
  Object.entries(errors).forEach(([name, message]) => {
    const input = inputs.find((el) => el.name === name) || inputs[0];
    setFieldError(input, message);
    first ??= input;
  });
  first?.focus();
  return null;
}

/** Вкладки «Активные» / «Завершенные» в «Моих заказах» (role="tab" + aria-controls) */
function initTabs(root) {
  const tabs = [...root.querySelectorAll('[data-js-account-tab]')];

  const select = (tab) => {
    tabs.forEach((item) => {
      const selected = item === tab;
      item.setAttribute('aria-selected', String(selected));
      item.tabIndex = selected ? 0 : -1;
      document.getElementById(item.getAttribute('aria-controls')).hidden = !selected;
    });
  };

  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => select(tab));
    tab.addEventListener('keydown', (event) => {
      if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') return;
      const next = tabs[(index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length];
      select(next);
      next.focus();
    });
  });

  select(tabs.find((tab) => tab.getAttribute('aria-selected') === 'true') || tabs[0]);
}

/**
 * Формы «Аккаунта»: кнопка активна, только когда есть что сохранять —
 * данные отличаются от сохранённых (details) или заполнены все поля пароля (password).
 * В WordPress — arctik_account_details / arctik_account_password (после смены пароля nonce новый), в статике — демо-сохранение.
 */
function initAccountForm(form) {
  const submit = form.querySelector('[data-js-account-submit]');
  const inputs = [...form.querySelectorAll('[data-js-field-input]')];
  const isPassword = form.dataset.jsAccountForm === 'password';
  let saved = inputs.map((input) => input.value);

  const update = () => {
    submit.disabled = isPassword
      ? inputs.some((input) => !input.value)
      : inputs.every((input, index) => input.value.trim() === saved[index]);
  };

  form.addEventListener('input', update);
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!validateForm(form) || form.hasAttribute('aria-busy')) return;

    let data = {};
    if (api()) {
      data = await accountRequest(isPassword ? 'arctik_account_password' : 'arctik_account_details', new FormData(form), form);
      if (!data) return;
    }

    if (isPassword) {
      if (data.nonce) api().nonce = data.nonce;
      resetForm(form);
    } else {
      inputs.forEach((input) => (input.value = input.value.trim()));
      saved = inputs.map((input) => input.value);
      const name = document.querySelector('[data-js-account-name]');
      if (name) name.textContent = data.name || form.elements.account_display_name.value;
    }
    update();
  });

  update();
}

// Поля формы адреса ← data-address-* карточки (WordPress; в статике — из текста карточки)
const ADDRESS_FIELDS = {
  shipping_full_name: 'addressName',
  shipping_phone: 'addressPhone',
  shipping_address_1: 'addressAddress1',
  shipping_address_2: 'addressAddress2',
  shipping_country: 'addressCountry',
  shipping_city: 'addressCity',
  shipping_postcode: 'addressPostcode',
};

/**
 * «Мои адреса»: «Добавить адрес» / «Изменить» открывают форму вместо списка, «Назад» возвращает.
 * В WordPress сохранение, удаление и выбор основного (радиокнопка) — запросы arctik_address_save / _remove / _default,
 * ответ — новый список карточек. В статике: сохранение добавляет/обновляет карточку из <template>, корзина удаляет карточку.
 */
function initAddresses(root) {
  const list = root.querySelector('[data-js-addresses-list]');
  const items = root.querySelector('[data-js-addresses-items]');
  const template = root.querySelector('[data-js-addresses-template]');
  const form = root.querySelector('[data-js-addresses-form]');
  const submit = form.querySelector('[data-js-addresses-submit]');
  let editing = null;

  const setSubmitText = (text) => {
    // У кнопки swipe-эффект: текст в двух копиях
    const labels = submit.querySelectorAll('.swipe__label');
    if (labels.length) labels.forEach((label) => (label.textContent = text));
    else submit.textContent = text;
  };

  const showForm = (card = null) => {
    editing = card;
    resetForm(form);
    form.elements.address_id.value = card?.querySelector('input[type="radio"]').value || '';
    if (card?.dataset.addressName !== undefined) {
      Object.entries(ADDRESS_FIELDS).forEach(([name, key]) => (form.elements[name].value = card.dataset[key] || ''));
    } else if (card) {
      form.elements.shipping_full_name.value = card.querySelector('[data-js-address-card-name]').textContent.replace(/\u00a0/g, ' ');
      form.elements.shipping_address_1.value = card.querySelector('[data-js-address-card-address]').textContent.replace(/\u00a0/g, ' ');
    }
    setSubmitText(card ? 'Сохранить адрес' : 'Добавить адрес');
    list.hidden = true;
    form.hidden = false;
    form.querySelector('[data-js-field-input]').focus();
  };

  const showList = () => {
    form.hidden = true;
    list.hidden = false;
    editing = null;
  };

  root.querySelector('[data-js-addresses-add]').addEventListener('click', () => showForm());
  form.querySelector('[data-js-addresses-back]').addEventListener('click', showList);

  const renderList = (html) => {
    items.innerHTML = html;
    initSwipeButtons();
  };

  // Запрос по карточке (удаление, основной адрес): адрес — значение её радиокнопки
  const cardRequest = async (action, card) => {
    const body = new FormData();
    body.append('address_id', card.querySelector('input[type="radio"]').value);
    card.setAttribute('aria-busy', 'true');
    const data = await accountRequest(action, body);
    card.removeAttribute('aria-busy');
    if (data) renderList(data.html);
  };

  items.addEventListener('click', (event) => {
    const card = event.target.closest('[data-js-address-card]');
    if (!card) return;
    if (event.target.closest('[data-js-address-edit]')) showForm(card);
    if (event.target.closest('[data-js-address-remove]')) {
      if (api()) cardRequest('arctik_address_remove', card);
      else card.remove();
    }
  });

  items.addEventListener('change', (event) => {
    const card = event.target.closest('[data-js-address-card]');
    if (card && api()) cardRequest('arctik_address_default', card);
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!validateForm(form) || form.hasAttribute('aria-busy')) return;

    if (api()) {
      const data = await accountRequest('arctik_address_save', new FormData(form), form);
      if (!data) return;
      renderList(data.html);
      showList();
      return;
    }

    const { shipping_full_name: name, shipping_address_1: street, shipping_address_2: flat, shipping_city: city, shipping_postcode: postcode } = form.elements;
    const address = [postcode.value, city.value, street.value, flat.value].map((value) => value.trim()).filter(Boolean).join(', ');

    let card = editing;
    if (!card) {
      card = template.content.firstElementChild.cloneNode(true);
      const input = card.querySelector('input[type="radio"]');
      input.value = String(Date.now());
      input.checked = !items.children.length;
      items.append(card);
      initSwipeButtons();
    }
    card.querySelector('[data-js-address-card-name]').textContent = name.value.trim();
    card.querySelector('[data-js-address-card-address]').textContent = address;
    showList();
  });
}

/** «Ваш заказ» на странице статуса: «2 товара» сворачивает список */
function initOrderToggle(toggle) {
  const list = document.getElementById(toggle.getAttribute('aria-controls'));
  toggle.addEventListener('click', () => {
    const expanded = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', String(expanded));
    list.hidden = !expanded;
  });
}

/** Личный кабинет (partials/account/*) и статус заказа (partials/order/status.html) */
export function initAccount() {
  document.querySelectorAll('[data-js-account-tabs]').forEach(initTabs);
  document.querySelectorAll('[data-js-account-form]').forEach(initAccountForm);
  document.querySelectorAll('[data-js-addresses]').forEach(initAddresses);
  document.querySelectorAll('[data-js-order-toggle]').forEach(initOrderToggle);
}
