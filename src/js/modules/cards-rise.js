import { gsap, prefersReducedMotion } from './motion.js';

/**
 * Карточки выезжают снизу лесенкой с проявлением — по скроллу (scrub), как карточки в about (sachet-journey.js).
 * Разметка: контейнер [data-js-cards-rise] с карточками [data-js-cards-rise-item].
 * Старт — когда ряд карточек показывается снизу экрана, финиш — когда секция доходит до верха экрана.
 */

const RISE = {
  y: 120,
  stagger: 0.1,
  ease: 'power2.out',
};

export function initCardsRise() {
  if (prefersReducedMotion()) return;

  document.querySelectorAll('[data-js-cards-rise]').forEach((list) => {
    const cards = list.querySelectorAll('[data-js-cards-rise-item]');
    const section = list.closest('section') ?? list;
    if (!cards.length) return;

    gsap.from(cards, {
      y: RISE.y,
      opacity: 0,
      stagger: RISE.stagger,
      ease: RISE.ease,
      scrollTrigger: {
        trigger: list,
        start: 'top bottom',
        endTrigger: section,
        end: 'top top',
        scrub: 0.6,
      },
    });
  });
}
