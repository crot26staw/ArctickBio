import { validateForm, resetForm } from './field.js';
import { initSwipeButtons } from './swipe-button.js';

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
 * данные отличаются от сохранённых (details) или заполнены все поля пароля (password). До WordPress — демо-сохранение.
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
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!validateForm(form)) return;

    if (isPassword) {
      resetForm(form);
    } else {
      saved = inputs.map((input) => input.value.trim());
      const name = document.querySelector('[data-js-account-name]');
      if (name) name.textContent = form.elements.account_display_name.value.trim();
    }
    update();
  });

  update();
}

/**
 * «Мои адреса»: «Добавить адрес» / «Изменить» открывают форму вместо списка, «Назад» возвращает.
 * До WordPress: сохранение добавляет/обновляет карточку из <template>, корзина удаляет карточку.
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
    if (card) {
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

  items.addEventListener('click', (event) => {
    const card = event.target.closest('[data-js-address-card]');
    if (!card) return;
    if (event.target.closest('[data-js-address-edit]')) showForm(card);
    if (event.target.closest('[data-js-address-remove]')) card.remove();
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!validateForm(form)) return;

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
