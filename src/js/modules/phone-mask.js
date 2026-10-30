/**
 * Маска телефона +7 (999)-000-00-00 (просьба пользователя): поле [data-js-phone-mask].
 * Первая цифра 8 или 7 в пустом поле — код страны, превращается в «+7 (»; 9 и другие — уже номер.
 * Вставка и автозаполнение (+7 912…, 8 912…, 912…) приводятся к маске. Стёрли все цифры — поле пустое (подпись опускается).
 * Стирание Backspace перед скобкой/дефисом удаляет цифру, а не знак маски. Проверка — field.js (isPhone), на сервере arctik_is_phone().
 */

const PREFIX = '+7 (';

// Цифры номера без кода страны: «+7…» — код уже есть; иначе первая 7 / 8 — код страны
function nationalDigits(value) {
  const hasCode = value.trimStart().startsWith('+7');
  const digits = value.replace(hasCode ? /^\s*\+7|\D/g : /\D/g, '');
  return (hasCode ? digits : digits.replace(/^[78]/, '')).slice(0, 10);
}

function format(digits) {
  let out = PREFIX + digits.slice(0, 3);
  if (digits.length > 3) out += `)-${digits.slice(3, 6)}`;
  if (digits.length > 6) out += `-${digits.slice(6, 8)}`;
  if (digits.length > 8) out += `-${digits.slice(8, 10)}`;
  return out;
}

// Позиция курсора после n-й цифры номера (в маске цифры начинаются после «+7 (»)
function caretAfter(value, n) {
  if (!n) return PREFIX.length;
  let count = 0;
  for (let i = PREFIX.length; i < value.length; i += 1) {
    if (/\d/.test(value[i]) && (count += 1) === n) return i + 1;
  }
  return value.length;
}

function initMask(input) {
  let prev = nationalDigits(input.value);
  if (input.value) input.value = prev ? format(prev) : '';

  input.addEventListener('input', (event) => {
    const caret = input.selectionStart ?? input.value.length;
    let digits = nationalDigits(input.value);
    let before = Math.min(nationalDigits(input.value.slice(0, caret)).length, digits.length);

    // Backspace стёр знак маски — убрать цифру перед ним
    if (event.inputType === 'deleteContentBackward' && digits === prev && before > 0) {
      digits = digits.slice(0, before - 1) + digits.slice(before);
      before -= 1;
    }

    const typed = event.inputType?.startsWith('insert');
    input.value = digits || typed ? format(digits) : '';
    prev = digits;

    if (document.activeElement === input && input.value) {
      const pos = caretAfter(input.value, before);
      input.setSelectionRange(pos, pos);
    }
  });
}

export function initPhoneMasks(scope = document) {
  scope.querySelectorAll('[data-js-phone-mask]').forEach((input) => {
    if ('phoneMaskReady' in input.dataset) return;
    input.dataset.phoneMaskReady = '';
    initMask(input);
  });
}

/** Значение, записанное скриптом (форма адреса ЛК), — привести к маске */
export function formatPhone(value) {
  const digits = nationalDigits(value);
  return digits ? format(digits) : '';
}
