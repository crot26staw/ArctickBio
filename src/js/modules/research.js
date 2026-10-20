import { playNumberTickers, resetNumberTickers } from './number-ticker.js';

/**
 * Блок «Исследования»: список, где раскрыт один пункт (по умолчанию первый).
 * Пункт раскрывается при наведении (устройства с hover), по фокусу с клавиатуры и по тапу.
 * После ухода курсора остаётся раскрытым последний пункт. Анимация — в CSS (_research.scss).
 * Цифры раскрытого пункта отсчитываются от 0 (number-ticker.js): у первого — при появлении блока, у остальных — при раскрытии.
 */

const ACTIVE_CLASS = 'is-active';

export function initResearch() {
  document.querySelectorAll('[data-js-research-list]').forEach((list) => {
    const items = [...list.querySelectorAll('[data-js-research-item]')];
    const canHover = window.matchMedia('(hover: hover)').matches;
    let isSeen = false;

    const activate = (item) => {
      if (item.classList.contains(ACTIVE_CLASS)) return;
      items.forEach((other) => other.classList.toggle(ACTIVE_CLASS, other === item));
      if (isSeen) playNumberTickers(item);
    };

    items.forEach((item) => {
      if (canHover) item.addEventListener('mouseenter', () => activate(item));
      item.addEventListener('focusin', () => activate(item));
      item.addEventListener('click', () => activate(item));
    });

    resetNumberTickers(list);

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return;
        isSeen = true;
        observer.disconnect();
        const active = items.find((item) => item.classList.contains(ACTIVE_CLASS));
        if (active) playNumberTickers(active);
      },
      { threshold: 0.4 },
    );
    observer.observe(list);
  });
}
