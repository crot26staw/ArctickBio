import { gsap, prefersReducedMotion } from './motion.js';

/**
 * Появление карточек по скроллу — как в about (sachet-journey.js, фаза 1):
 * секция доходит до верха экрана и закрепляется (pin на RISE.pin экрана),
 * карточки по одной выезжают снизу из прозрачности, в конце — короткая пауза. Всё по скроллу (scrub).
 * Разметка: контейнер [data-js-cards-rise] с карточками [data-js-cards-rise-item]; закрепляется ближайшая section.
 */

const RISE = {
  pin: 2.5, // длина закрепления, в экранах скролла
  y: 300,
  duration: 0.3,
  stagger: 0.2,
  pause: 0.15,
  ease: 'power2.out',
};

export function initCardsRise() {
  if (prefersReducedMotion()) return;

  document.querySelectorAll('[data-js-cards-rise]').forEach((list) => {
    const cards = list.querySelectorAll('[data-js-cards-rise-item]');
    const section = list.closest('section') ?? list;
    if (!cards.length) return;

    gsap
      .timeline({
        scrollTrigger: {
          trigger: section,
          start: 'top top',
          end: `+=${RISE.pin * 100}%`,
          pin: true,
          scrub: 1,
        },
      })
      .from(cards, {
        y: RISE.y,
        opacity: 0,
        duration: RISE.duration,
        stagger: RISE.stagger,
        ease: RISE.ease,
      })
      .to({}, { duration: RISE.pause });
  });
}
