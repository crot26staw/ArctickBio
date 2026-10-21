import { gsap, prefersReducedMotion } from './motion.js';

/**
 * Горизонтальная лента, которую проматывает обычный скролл страницы:
 * секция [data-js-ribbon] закрепляется у верха экрана, а лента [data-js-ribbon-track]
 * уезжает влево на свою лишнюю ширину. Путь скролла = этой ширине, поэтому лента
 * движется со скоростью скролла. При prefers-reduced-motion ленту листают вручную (CSS).
 */
export function initRibbons() {
  if (prefersReducedMotion()) return;

  document.querySelectorAll('[data-js-ribbon]').forEach((section) => {
    const track = section.querySelector('[data-js-ribbon-track]');
    if (!track) return;

    const distance = () => Math.max(0, track.scrollWidth - section.clientWidth);

    gsap.to(track, {
      x: () => -distance(),
      ease: 'none',
      scrollTrigger: {
        trigger: section,
        start: 'top top',
        end: () => `+=${distance()}`,
        pin: true,
        scrub: true, // без собственной задержки: скролл и так сглаживает Lenis, иначе лента не успевает доехать до конца пина
        invalidateOnRefresh: true,
      },
    });
  });
}
