import { gsap, ScrollTrigger, prefersReducedMotion } from './motion.js';

// Всё — в экранах скролла (1 = высота окна)
const VEIL = {
  gap: 0.5, // задержка about: сколько экранов hero стоит один, прежде чем снизу пойдёт about
  speed: 0.7, // насколько завеса обгоняет скролл (поднимается в 1.7 раза быстрее страницы)
  hideHero: 1, // завеса полностью закрыла экран — прячем hero
  fadeStart: 1, // начинаем рассеивать завесу, под ней фон страницы = фон about
  fadeEnd: 1.45, // к моменту, когда about у верха экрана (1 + gap), завеса рассеялась
};

/**
 * Переход hero → about (референс — white-desert.com):
 * - hero закреплён у верха экрана (pin без pinSpacing), about отодвинут вниз на VEIL.gap экрана;
 * - снизу поднимается облачная завеса [data-js-cloud-veil] и к концу первого экрана скролла закрывает hero;
 * - about поднимается под завесой, hero скрывается, завеса рассеивается — about «выходит из облаков».
 * Весь переход — 1 + VEIL.gap экрана. Пока край about в кадре, его закрывает плотная нижняя часть облаков.
 * Слои — z-index в CSS (_hero.scss, _sachet-journey.scss, _cloud-veil.scss).
 */
export function initHeroCover() {
  const hero = document.querySelector('[data-js-hero-cover]');
  const veil = document.querySelector('[data-js-cloud-veil]');
  if (!hero || !veil || prefersReducedMotion()) return;

  // Отступ только при анимации: без неё (reduced motion) about идёт сразу за hero
  gsap.set(veil.parentElement, { marginTop: `${VEIL.gap * 100}svh` });

  ScrollTrigger.create({
    trigger: hero,
    start: 'top top',
    end: '+=100%',
    pin: true,
    pinSpacing: false,
  });

  gsap.set(veil, { opacity: 1 });

  const length = 1 + VEIL.gap;
  // Сдвиг завесы в экранах → yPercent (высота завесы — 3 экрана)
  const toPercent = (screens) => (screens * 100) / 3;
  const startY = 2 - VEIL.gap;

  gsap.timeline({
    scrollTrigger: {
      // Числами, а не через trigger: у закреплённого hero ScrollTrigger сдвигает 'top top'
      start: () => hero.parentElement.offsetTop,
      end: () => hero.parentElement.offsetTop + window.innerHeight * length,
      scrub: 0.6,
      invalidateOnRefresh: true,
    },
  })
    .fromTo(
      veil,
      { yPercent: toPercent(startY) },
      { yPercent: toPercent(startY - VEIL.speed * length), duration: length, ease: 'none' },
      0,
    )
    .set(hero, { autoAlpha: 0 }, VEIL.hideHero)
    .to(veil, { opacity: 0, duration: VEIL.fadeEnd - VEIL.fadeStart, ease: 'power1.in' }, VEIL.fadeStart);
}
