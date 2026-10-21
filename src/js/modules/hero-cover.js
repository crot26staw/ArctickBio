import { ScrollTrigger, prefersReducedMotion } from './motion.js';

/**
 * Переход hero → about: hero закреплён у верха экрана (pin без pinSpacing) на 1 экран,
 * about (.sachet-journey, z-index выше hero) наезжает на него снизу и у верха закрепляется сам (sachet-journey.js).
 * Облачная завеса — на переходе about → product-scene (sachet-journey.js, initVeil).
 */
export function initHeroCover() {
  const hero = document.querySelector('[data-js-hero-cover]');
  if (!hero || prefersReducedMotion()) return;

  ScrollTrigger.create({
    trigger: hero,
    start: 'top top',
    end: '+=100%',
    pin: true,
    pinSpacing: false,
  });
}
