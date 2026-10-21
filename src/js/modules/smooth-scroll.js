import Lenis from 'lenis';
import { gsap, ScrollTrigger, prefersReducedMotion } from './motion.js';

let lenis = null;

// Плавный скролл Lenis, синхронизированный с GSAP: один тикер, ScrollTrigger обновляется на каждом кадре скролла.
// При prefers-reduced-motion не включается — остаётся нативный скролл.
export function initSmoothScroll() {
  if (prefersReducedMotion()) return null;

  lenis = new Lenis({
    lerp: 0.1,
    anchors: true, // ссылки вида href="#id" скроллятся плавно
  });

  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add((time) => lenis.raf(time * 1000));
  gsap.ticker.lagSmoothing(0);

  return lenis;
}

// Доступ к экземпляру из других модулей: getLenis()?.scrollTo(target), getLenis()?.stop() / start() (модалки)
export const getLenis = () => lenis;
