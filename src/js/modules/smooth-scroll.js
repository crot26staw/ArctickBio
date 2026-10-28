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

// Переход по ссылке с якорем с другой страницы (/#reviews): браузер прыгает к разделу до того, как пины GSAP
// добавят высоту, и раздел оказывается ниже. После загрузки (ScrollTrigger к этому времени пересчитан) — к разделу ещё раз.
// #auth-… — экраны попапа входа (auth.js), не разделы.
function scrollToHash() {
  const id = decodeURIComponent(location.hash.slice(1));
  if (!id || id.startsWith('auth-')) return;
  const target = document.getElementById(id);
  if (!target) return;

  requestAnimationFrame(() => {
    target.scrollIntoView({ block: 'start' });
    lenis?.resize();
  });
}

if (document.readyState === 'complete') scrollToHash();
else window.addEventListener('load', scrollToHash, { once: true });

// Доступ к экземпляру из других модулей: getLenis()?.scrollTo(target), getLenis()?.stop() / start() (модалки)
export const getLenis = () => lenis;
