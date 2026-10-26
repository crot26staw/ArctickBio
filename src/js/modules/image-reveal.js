import { gsap, ScrollTrigger, prefersReducedMotion } from './motion.js';

/**
 * Появление фото «шейп + картинка» (комментарий дизайнера, референс floema.com/en/sustainability — image-reveal):
 * сначала сверху вниз раскрывается синяя плашка (accent), следом за ней так же раскрывается фото.
 * Раскрытие — clip-path inset снизу, при появлении во вьюпорте, один раз.
 * Разметка (стили — components/_image-reveal.scss):
 *   [data-js-image-reveal].image-reveal > span.image-reveal__bg + .image-reveal__media (img или picture)
 *   [data-image-reveal-delay="0.2"] — задержка, сек; [data-image-reveal-start] — start ScrollTrigger, по умолчанию 'top 60%'.
 * До запуска скрыто (base/_global.scss). Не ставить внутри закреплённых сцен.
 */
// Тайминги — из исходников референса (компонент RevealImage)
const IMAGE_REVEAL = {
  // Плашка раскрывается быстро и тормозит в конце
  bg: { duration: 0.7, ease: 'expo.out' },
  // Фото стартует чуть позже плашки, сначала отстаёт, потом догоняет
  media: { duration: 1, ease: 'expo.inOut', offset: 0.1 },
  start: 'top 60%',
  hidden: 'inset(0% 0% 100% 0%)',
  shown: 'inset(0% 0% 0% 0%)',
};

export function initImageReveal() {
  const items = document.querySelectorAll('[data-js-image-reveal]');
  if (!items.length || prefersReducedMotion()) return;

  items.forEach((item) => {
    const bg = item.querySelector('.image-reveal__bg');
    const media = item.querySelector('.image-reveal__media');
    gsap.set([bg, media], { clipPath: IMAGE_REVEAL.hidden });
    item.classList.add('is-reveal-ready');

    ScrollTrigger.create({
      trigger: item,
      start: item.dataset.imageRevealStart || IMAGE_REVEAL.start,
      once: true,
      onEnter: () => {
        gsap.timeline({ delay: Number(item.dataset.imageRevealDelay) || 0 })
          .to(bg, { clipPath: IMAGE_REVEAL.shown, ...IMAGE_REVEAL.bg })
          .to(media, {
            clipPath: IMAGE_REVEAL.shown,
            duration: IMAGE_REVEAL.media.duration,
            ease: IMAGE_REVEAL.media.ease,
            clearProps: 'clipPath',
          }, IMAGE_REVEAL.media.offset);
      },
    });
  });
}
