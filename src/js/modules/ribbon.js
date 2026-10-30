import { gsap, prefersReducedMotion } from './motion.js';
import { loadOnTouch } from './lazy-media.js';

/**
 * Горизонтальная лента, которую проматывает обычный скролл страницы:
 * секция [data-js-ribbon] закрепляется у верха экрана, а лента [data-js-ribbon-track]
 * уезжает влево на свою лишнюю ширину. Путь скролла = этой ширине, поэтому лента
 * движется со скоростью скролла. На мобилке и при prefers-reduced-motion ленту листают свайпом (нативный скролл, CSS).
 */
const MEDIA = '(min-width: 768px)';

export function initRibbons() {
  // Мобилка / reduced motion — лента листается свайпом: картинки шагов — при первом касании (lazy-media.js)
  document.querySelectorAll('[data-js-ribbon-track]').forEach(loadOnTouch);
  if (prefersReducedMotion()) return;

  document.querySelectorAll('[data-js-ribbon]').forEach((section) => {
    const track = section.querySelector('[data-js-ribbon-track]');
    if (!track) return;

    // offsetWidth, не scrollWidth: в scrollWidth попадают скрытые попапы шагов, выступающие за ленту
    const distance = () => Math.max(0, track.offsetWidth - section.clientWidth);

    gsap.matchMedia().add(MEDIA, () => {
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
  });
}
