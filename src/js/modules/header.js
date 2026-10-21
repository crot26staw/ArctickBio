import { ScrollTrigger } from './motion.js';

const SCROLLED_CLASS = 'is-scrolled';
const SCROLL_OFFSET = 10;

/**
 * Фиксированная шапка:
 * - класс is-scrolled, как только страница прокручена;
 * - data-theme = значение data-header-theme ("light" | "dark") секции, над которой сейчас шапка.
 */
export function initHeader() {
  const header = document.querySelector('[data-js-header]');
  if (!header) return;

  const sections = [...document.querySelectorAll('[data-header-theme]')];
  let ticking = false;

  const update = () => {
    ticking = false;
    header.classList.toggle(SCROLLED_CLASS, window.scrollY > SCROLL_OFFSET);

    // Точка проверки — середина шапки
    const probe = header.offsetHeight / 2;
    // findLast: элемент ниже по DOM лежит поверх (облачная завеса и about — над hero)
    const current = sections.findLast((section) => {
      const { top, bottom } = section.getBoundingClientRect();
      return top <= probe && bottom > probe;
    });

    header.dataset.theme = current?.dataset.headerTheme ?? 'dark';
  };

  const requestUpdate = () => {
    if (!ticking) {
      ticking = true;
      requestAnimationFrame(update);
    }
  };

  update();
  window.addEventListener('scroll', requestUpdate, { passive: true });
  window.addEventListener('resize', requestUpdate);
  // Пины и анимации GSAP (облачная завеса, hero) меняют позиции секций без события scroll
  ScrollTrigger.addEventListener('refresh', requestUpdate);
}
