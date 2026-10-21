import { gsap, ScrollTrigger, prefersReducedMotion } from './motion.js';

/**
 * Появление элементов «из прозрачности снизу» (референс дизайнера: drinksom.eu):
 * элемент поднимается на FADE_UP.y и проявляется, при появлении во вьюпорте, один раз.
 * Разметка:
 *   [data-js-fade-up] — сам элемент (кнопка, фото, карточка);
 *   [data-js-fade-up-group] — его дети по очереди (список карточек), шаг FADE_UP.stagger.
 *   [data-fade-up-delay="0.4"] — задержка, сек (например, после заголовка на первом экране).
 *   [data-fade-up-start] — свой момент запуска (start ScrollTrigger), по умолчанию 'top 85%'.
 * Не ставить внутри закреплённых сцен (about, «Процесс», «Исследования», benefits) — у них своя анимация по скроллу.
 * Сдвиг — CSS-свойством translate, не transform: у элементов бывает свой transform (витамины: translateY(-50%)).
 * До запуска элементы скрыты (base/_global.scss), без мигания.
 */
const FADE_UP = {
  y: 32,
  duration: 0.8,
  stagger: 0.15,
  ease: 'cubic-bezier(0.16, 1, 0.3, 1)',
  start: 'top 85%',
};

export function initFadeUp() {
  const items = document.querySelectorAll('[data-js-fade-up], [data-js-fade-up-group]');
  if (!items.length || prefersReducedMotion()) return;

  items.forEach((item) => {
    const targets = item.hasAttribute('data-js-fade-up-group') ? [...item.children] : [item];

    gsap.set(targets, { opacity: 0, translate: `0px ${FADE_UP.y}px` });
    item.classList.add('is-fade-ready');

    ScrollTrigger.create({
      trigger: item,
      start: item.dataset.fadeUpStart || FADE_UP.start,
      once: true,
      onEnter: () => {
        gsap.to(targets, {
          opacity: 1,
          translate: '0px 0px',
          duration: FADE_UP.duration,
          stagger: FADE_UP.stagger,
          delay: Number(item.dataset.fadeUpDelay) || 0,
          ease: FADE_UP.ease,
          clearProps: 'opacity,translate',
        });
      },
    });
  });
}
