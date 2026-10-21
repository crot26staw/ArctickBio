import { ScrollTrigger, prefersReducedMotion } from './motion.js';
import { playNumberTickers, resetNumberTickers } from './number-ticker.js';

/**
 * Блок «Исследования»: список, где раскрыт один пункт (по умолчанию первый). Анимация раскрытия — в CSS (_research.scss), секция — 100svh.
 * Пункты сменяются по скроллу: секция закрепляется (pin), путь делится поровну между пунктами,
 * на каждый — STEP экрана. Фокус с клавиатуры тоже раскрывает пункт.
 * При prefers-reduced-motion пина нет — пункт раскрывается при наведении/фокусе/тапе.
 * Цифры раскрытого пункта отсчитываются от 0 (number-ticker.js): у первого — при появлении блока, у остальных — при раскрытии.
 */

const ACTIVE_CLASS = 'is-active';
const STEP = 0.7; // путь скролла на один пункт, в экранах

function initScrollSteps(section, items, activate) {
  ScrollTrigger.create({
    trigger: section,
    // Секция ниже экрана — закрепляем по центру, выше — у верха
    start: () => (section.offsetHeight < window.innerHeight ? 'center center' : 'top top'),
    end: `+=${items.length * STEP * 100}%`,
    pin: true,
    invalidateOnRefresh: true,
    onUpdate: (self) => {
      const index = Math.min(items.length - 1, Math.floor(self.progress * items.length));
      activate(items[index]);
    },
  });
}

export function initResearch() {
  document.querySelectorAll('[data-js-research-list]').forEach((list) => {
    const items = [...list.querySelectorAll('[data-js-research-item]')];
    const section = list.closest('section');
    const byScroll = !prefersReducedMotion() && section;
    let isSeen = false;

    const activate = (item) => {
      if (item.classList.contains(ACTIVE_CLASS)) return;
      items.forEach((other) => other.classList.toggle(ACTIVE_CLASS, other === item));
      if (isSeen) playNumberTickers(item);
    };

    items.forEach((item) => {
      item.addEventListener('focusin', () => activate(item));
      if (byScroll) return;
      if (window.matchMedia('(hover: hover)').matches) item.addEventListener('mouseenter', () => activate(item));
      item.addEventListener('click', () => activate(item));
    });

    if (byScroll) initScrollSteps(section, items, activate);

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
