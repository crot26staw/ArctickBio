/**
 * Поля форм (partials/form-field.html, form-field-password.html; стили components/_field.scss).
 *
 * Показ пароля: [data-js-password-toggle] переключает type у поля из aria-controls.
 * Проверка: у поля [data-js-field-input] атрибут data-validate:
 *   email    — пустое / не похоже на email;
 *   required — пустое, текст ошибки в data-message;
 *   repeat   — пустое / не совпадает с полем из data-repeat (id).
 * Ошибка — класс is-error у [data-js-field] и текст в [data-js-field-hint]. Сервер может отдать поле
 * уже с is-error и текстом подсказки («Неверный логин или пароль») — ошибка снимется при вводе.
 */

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const MESSAGES = {
  emailEmpty: 'Введите email',
  emailFormat: 'Введите email в формате name@example.com',
  required: 'Заполните поле',
  repeatEmpty: 'Повторите пароль',
  repeatMismatch: 'Пароли не совпадают',
};

function getError(input) {
  const value = input.value.trim();

  switch (input.dataset.validate) {
    case 'email':
      if (!value) return MESSAGES.emailEmpty;
      return EMAIL_RE.test(value) ? '' : MESSAGES.emailFormat;
    case 'required':
      return value ? '' : input.dataset.message || MESSAGES.required;
    case 'repeat': {
      if (!input.value) return MESSAGES.repeatEmpty;
      const original = document.getElementById(input.dataset.repeat);
      return original && original.value !== input.value ? MESSAGES.repeatMismatch : '';
    }
    default:
      return '';
  }
}

export function setFieldError(input, message) {
  const field = input.closest('[data-js-field]');
  const hint = field?.querySelector('[data-js-field-hint]');
  if (!field) return;

  field.classList.toggle('is-error', Boolean(message));
  input.setAttribute('aria-invalid', String(Boolean(message)));
  if (hint) hint.textContent = message;
}

/** Проверяет все поля формы, показывает ошибки, ставит фокус в первое неверное. Возвращает true, если всё верно. */
export function validateForm(form) {
  form.dataset.validated = '';
  let firstInvalid = null;

  form.querySelectorAll('[data-js-field-input][data-validate]').forEach((input) => {
    const error = getError(input);
    setFieldError(input, error);
    if (error && !firstInvalid) firstInvalid = input;
  });

  firstInvalid?.focus();
  return !firstInvalid;
}

export function resetForm(form) {
  form.reset();
  delete form.dataset.validated;
  form.querySelectorAll('[data-js-field-input]').forEach((input) => setFieldError(input, ''));
}

export function initFields() {
  document.querySelectorAll('[data-js-password-toggle]').forEach((toggle) => {
    const input = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!input) return;

    toggle.addEventListener('click', () => {
      const shown = input.type === 'password';
      input.type = shown ? 'text' : 'password';
      toggle.setAttribute('aria-pressed', String(shown));
      toggle.setAttribute('aria-label', shown ? 'Скрыть пароль' : 'Показать пароль');
    });
  });

  // После первой попытки отправки ошибки пересчитываются при вводе; серверная ошибка снимается при вводе
  document.addEventListener('input', (event) => {
    const input = event.target.closest?.('[data-js-field-input]');
    if (!input) return;

    const form = input.form;
    if (form && 'validated' in form.dataset && input.dataset.validate) {
      setFieldError(input, getError(input));
      // Повтор пароля зависит от основного поля
      form.querySelectorAll(`[data-repeat="${input.id}"]`).forEach((repeat) => {
        if (repeat.value) setFieldError(repeat, getError(repeat));
      });
    } else {
      setFieldError(input, '');
    }
  });
}
