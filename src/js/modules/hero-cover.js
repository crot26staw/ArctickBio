import { gsap, ScrollTrigger, prefersReducedMotion } from './motion.js';

// Параллакс контента hero (референс unitedcarriers.com/about): пока about наезжает, текст и слайдер уходят вверх медленнее него и гаснут
const PARALLAX = {
  y: -40, // vh за весь наезд (about проходит 100vh)
  opacity: 0.2,
  shade: 0.8, // доля наезда, к которой hero затемнён полностью (about закрыл ~80% экрана)
};

/**
 * Переход hero → about: hero закреплён у верха экрана (pin без pinSpacing) на 1 экран,
 * about (.sachet-journey, z-index выше hero) наезжает на него снизу и у верха закрепляется сам (sachet-journey.js).
 * Контент hero [data-js-hero-cover-parallax] на это время — параллакс (PARALLAX),
 * слой [data-js-hero-cover-shade] затемняет hero целиком к PARALLAX.shade наезда.
 * Облачная завеса — на переходе about → product-scene (sachet-journey.js, initVeil).
 */
export function initHeroCover() {
  const hero = document.querySelector('[data-js-hero-cover]');
  if (!hero || prefersReducedMotion()) return;

  // Твин — в том же триггере, что и пин: у отдельного триггера на закреплённом hero старт сдвигается на длину пина
  const content = hero.querySelector('[data-js-hero-cover-parallax]');
  const shade = hero.querySelector('[data-js-hero-cover-shade]');
  const animation = gsap.timeline({ defaults: { ease: 'none' } });

  if (content) {
    animation.to(content, {
      y: () => (window.innerHeight * PARALLAX.y) / 100,
      opacity: PARALLAX.opacity,
      duration: 1,
    }, 0);
  }
  if (shade) animation.to(shade, { opacity: 1, duration: PARALLAX.shade }, 0);

  ScrollTrigger.create({
    trigger: hero,
    start: 'top top',
    end: '+=100%',
    pin: true,
    pinSpacing: false,
    animation,
    scrub: true,
    invalidateOnRefresh: true,
  });
}
