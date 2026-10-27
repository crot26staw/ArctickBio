import { getLenis } from './smooth-scroll.js';
import { validateForm, resetForm, setFieldError } from './field.js';
import { initSwipeButtons } from './swipe-button.js';

const OPEN_CLASS = 'is-open';
const LOCK_CLASS = 'is-scroll-locked';
const HASH_PREFIX = '#auth-';
// Куда ведёт «Назад»; у экранов без записи кнопки нет (как в макете)
const BACK = {
  forgot: 'login',
  sent: 'forgot',
  reset: 'login',
};
const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled])';
// Форма попапа → обработчик wc-ajax (inc/ajax/auth.php)
const ENDPOINTS = {
  login: 'arctik_login',
  register: 'arctik_register',
  forgot: 'arctik_forgot',
  sent: 'arctik_forgot',
  reset: 'arctik_reset',
};

const api = () => window.arctik?.auth;

/** Отправка формы в WordPress. Возвращает данные успешного ответа или null (ошибки уже показаны у полей). */
async function authRequest(form) {
  const { endpoint, nonce } = api();
  const body = new FormData(form);
  body.append('security', nonce);

  form.setAttribute('aria-busy', 'true');
  const json = await fetch(endpoint.replace('%%endpoint%%', ENDPOINTS[form.dataset.jsAuthForm]), {
    method: 'POST',
    body,
    credentials: 'same-origin',
  })
    .then((response) => response.json())
    .catch(() => ({ success: false }));
  form.removeAttribute('aria-busy');

  if (json.success) return json.data || {};

  const errors = json.data?.errors || {};
  const inputs = [...form.querySelectorAll('[data-js-field-input]')];
  // Поле без имени в ответе (или нет ответа) — ошибка у первого поля формы
  const fallback = inputs.find((input) => input.type !== 'checkbox');
  if (!Object.keys(errors).length) errors[fallback.name] = 'Не\u00a0удалось отправить, попробуйте ещё раз';

  let first = null;
  Object.entries(errors).forEach(([name, message]) => {
    const input = inputs.find((el) => el.name === name) || fallback;
    setFieldError(input, message);
    first ??= input;
  });
  first?.focus();
  return null;
}

// После входа — та же страница без #auth-…
const reloadPage = () => {
  if (location.hash.startsWith(HASH_PREFIX)) history.replaceState(null, '', location.pathname + location.search);
  location.reload();
};

/**
 * Попап входа (partials/auth-modal.html, макет 18126:59052): экраны login / register / forgot / sent / reset.
 * Открывают [data-js-auth-open="<экран>"] и адрес #auth-<экран> (ссылка из письма — #auth-reset),
 * переключают [data-js-auth-go="<экран>"], закрывают [data-js-auth-close] (крестик, клик мимо панели) и Escape.
 * Пока открыт: Lenis stop, фокус внутри панели, по закрытии фокус возвращается на кнопку.
 * Проверка полей — field.js; без ошибок форма уходит в onSubmit. В WordPress (window.arctik.auth — inc/auth.php)
 * формы отправляются в wc-ajax обработчики inc/ajax/auth.php, ошибки { errors: { имя поля: текст } } показываются у полей;
 * после входа и регистрации страница перезагружается (шапка с буквой имени — из PHP). В статике — демо-переходы.
 */
export function initAuth() {
  const root = document.querySelector('[data-js-auth]');
  if (!root) return;

  const dialog = root.querySelector('[data-js-auth-dialog]');
  const backBtn = root.querySelector('[data-js-auth-back]');
  const views = new Map([...root.querySelectorAll('[data-js-auth-view]')].map((el) => [el.dataset.jsAuthView, el]));
  let current = 'login';
  let opener = null;

  const isOpen = () => root.classList.contains(OPEN_CLASS);

  const focusFirst = () => {
    const view = views.get(current);
    const target = view.querySelector('[data-js-field-input]') || view.querySelector(FOCUSABLE);
    target?.focus({ preventScroll: true });
  };

  const show = (name) => {
    if (!views.has(name)) return;
    views.forEach((view, key) => {
      view.hidden = key !== name;
    });
    current = name;
    backBtn.hidden = !BACK[name];
    dialog.setAttribute('aria-labelledby', views.get(name).getAttribute('aria-labelledby'));
    dialog.scrollTop = 0;
    if (isOpen()) focusFirst();
  };

  const open = (name = 'login', trigger = null) => {
    opener = trigger;
    show(name);
    root.inert = false;
    root.classList.add(OPEN_CLASS);
    document.documentElement.classList.add(LOCK_CLASS);
    getLenis()?.stop();
    focusFirst();
  };

  const close = () => {
    if (!isOpen()) return;
    root.classList.remove(OPEN_CLASS);
    root.inert = true;
    document.documentElement.classList.remove(LOCK_CLASS);
    getLenis()?.start();
    if (location.hash.startsWith(HASH_PREFIX)) history.replaceState(null, '', location.pathname + location.search);
    opener?.focus({ preventScroll: true });
    opener = null;
  };

  // Демо до WordPress: «вход» — в шапке вместо иконки первая буква (макет 18182:43263)
  const showUser = (letter) => {
    document.querySelectorAll('[data-js-auth-open]').forEach((btn) => {
      if (!btn.closest('[data-js-header]')) return;
      const link = document.createElement('a');
      link.className = 'header__icon-btn header__user';
      link.href = '#';
      link.setAttribute('aria-label', 'Личный кабинет');
      link.dataset.jsSwipe = '';
      link.textContent = letter || 'А';
      btn.replaceWith(link);
    });
    initSwipeButtons();
  };

  const showSent = (email) => {
    const sent = views.get('sent');
    sent.querySelector('[data-js-auth-sent-email]').textContent = email;
    sent.querySelector('[data-js-field-input]').value = email;
    show('sent');
  };

  const onSubmitApi = {
    login: reloadPage,
    register: reloadPage,
    forgot: (form) => showSent(form.elements.user_login.value.trim()),
    sent: () => {},
    reset: (form, data) => {
      resetForm(form);
      show('login');
      const login = views.get('login').querySelector('[name="username"]');
      if (data.login) login.value = data.login;
    },
  };

  const onSubmit = {
    login: (form) => {
      showUser(form.elements.username.value.trim()[0]);
      close();
      resetForm(form);
    },
    register: (form) => {
      showUser(form.elements.first_name.value.trim()[0]);
      close();
      resetForm(form);
    },
    forgot: (form) => showSent(form.elements.user_login.value.trim()),
    sent: () => {},
    reset: (form) => {
      resetForm(form);
      show('login');
    },
  };

  root.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-js-auth-form]');
    if (!form) return;
    event.preventDefault();
    if (form.hasAttribute('aria-busy') || !validateForm(form)) return;

    const name = form.dataset.jsAuthForm;
    if (!api()) {
      onSubmit[name]?.(form);
      return;
    }

    const data = await authRequest(form);
    if (data) onSubmitApi[name]?.(form, data);
  });

  document.addEventListener('click', (event) => {
    const openBtn = event.target.closest('[data-js-auth-open]');
    if (openBtn) {
      event.preventDefault();
      open(openBtn.dataset.jsAuthOpen || 'login', openBtn);
      return;
    }

    const goBtn = event.target.closest('[data-js-auth-go]');
    if (goBtn && root.contains(goBtn)) {
      show(goBtn.dataset.jsAuthGo);
      return;
    }

    if (event.target.closest('[data-js-auth-close]') && root.contains(event.target)) close();
  });

  backBtn.addEventListener('click', () => show(BACK[current]));

  document.addEventListener('keydown', (event) => {
    if (!isOpen()) return;

    if (event.key === 'Escape') {
      close();
      return;
    }

    // Фокус не уходит из панели
    if (event.key === 'Tab') {
      const items = [...dialog.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
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

  const openFromHash = () => {
    if (!location.hash.startsWith(HASH_PREFIX)) return;
    const name = location.hash.slice(HASH_PREFIX.length);
    if (views.has(name)) open(name);
  };

  openFromHash();
  window.addEventListener('hashchange', openFromHash);

  // Серверная ошибка в форме (поле уже с is-error) — попап открывается на этом экране
  const serverError = root.querySelector('[data-js-field].is-error [data-js-field-input]');
  if (serverError) {
    open(serverError.closest('[data-js-auth-view]').dataset.jsAuthView);
    setFieldError(serverError, serverError.closest('[data-js-field]').querySelector('[data-js-field-hint]').textContent);
  }
}
