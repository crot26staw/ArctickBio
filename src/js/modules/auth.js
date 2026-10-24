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

/**
 * Попап входа (partials/auth-modal.html, макет 18126:59052): экраны login / register / forgot / sent / reset.
 * Открывают [data-js-auth-open="<экран>"] и адрес #auth-<экран> (ссылка из письма — #auth-reset),
 * переключают [data-js-auth-go="<экран>"], закрывают [data-js-auth-close] (крестик, клик мимо панели) и Escape.
 * Пока открыт: Lenis stop, фокус внутри панели, по закрытии фокус возвращается на кнопку.
 * Проверка полей — field.js; без ошибок форма уходит в onSubmit (до WordPress — демо-переходы).
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
    forgot: (form) => {
      const email = form.elements.user_login.value.trim();
      const sent = views.get('sent');
      sent.querySelector('[data-js-auth-sent-email]').textContent = email;
      sent.querySelector('[data-js-field-input]').value = email;
      show('sent');
    },
    sent: () => {},
    reset: (form) => {
      resetForm(form);
      show('login');
    },
  };

  root.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-js-auth-form]');
    if (!form) return;
    event.preventDefault();
    if (validateForm(form)) onSubmit[form.dataset.jsAuthForm]?.(form);
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
