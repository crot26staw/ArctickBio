const ACTIVE_CLASS = 'is-active';

/**
 * Поле с подсказками (combobox): input[role="combobox"] + список [role="listbox"] (стили .map-modal__suggest-list).
 * Стрелки, Enter, клик; Escape закрывает только подсказки. Сейчас — адрес курьера (address.js) и город пункта выдачи (pickup.js).
 * source(query) → массив или Promise массива { label, … } — подсказки; запросы с задержкой delay, устаревший ответ отбрасывается.
 * onPick(item) — выбрана подсказка.
 */
export function initSuggest({ input, list, source, onPick, minChars = 2, delay = 0 }) {
  let results = [];
  let items = [];
  let active = -1;
  let timer = 0;
  let request = 0;

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

  const render = (found) => {
    results = found;
    list.innerHTML = '';
    items = found.map((result, index) => {
      const item = document.createElement('li');
      item.className = 'map-modal__suggest-item';
      item.id = `${list.id}-${index}`;
      item.setAttribute('role', 'option');
      item.textContent = result.label;
      list.append(item);
      return item;
    });

    if (!items.length || document.activeElement !== input) {
      hide();
      return;
    }
    list.hidden = false;
    input.setAttribute('aria-expanded', 'true');
    active = -1;
  };

  const update = () => {
    clearTimeout(timer);
    const query = input.value.trim();
    const id = ++request;

    if (query.length < minChars) {
      render([]);
      return;
    }

    timer = setTimeout(async () => {
      const found = await Promise.resolve(source(query)).catch(() => []);
      if (id === request) render(found ?? []);
    }, delay);
  };

  const pick = (index) => {
    const result = results[index];
    if (!result) return;
    hide();
    onPick(result);
  };

  input.addEventListener('input', update);
  input.addEventListener('focus', update);
  input.addEventListener('blur', hide);

  input.addEventListener('keydown', (event) => {
    if (list.hidden) return;
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault();
      const step = event.key === 'ArrowDown' ? 1 : -1;
      setActive((active + step + items.length) % items.length);
    } else if (event.key === 'Enter' && items[active]) {
      event.preventDefault();
      pick(active);
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
    pick(items.indexOf(item));
  });

  return { hide };
}
