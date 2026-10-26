import { gsap, prefersReducedMotion } from './motion.js';

/**
 * Сдвиг ленты по скроллу: секция [data-js-scroll-shift] закрепляется у верха экрана,
 * а лента [data-js-scroll-shift-track] уезжает из начального сдвига (CSS translate) на своё место, затем скролл идёт дальше.
 * Начальный сдвиг задаётся в CSS (translate у ленты), путь скролла = ему, лента движется со скоростью скролла.
 * Двигает CSS-свойство translate, не transform: transform у ленты занят drag-slider.js.
 * Сейчас — сотрудники «О компании» (лента правее заголовка → к левому полю). Только десктоп.
 */
const MEDIA = '(min-width: 1200px)';

export function initScrollShift() {
  if (prefersReducedMotion()) return;

  document.querySelectorAll('[data-js-scroll-shift]').forEach((section) => {
    const track = section.querySelector('[data-js-scroll-shift-track]');
    if (!track) return;

    gsap.matchMedia().add(MEDIA, () => {
      // Сдвиг из CSS в px (rem зависит от ширины окна — читаем при каждом refresh), без инлайна от GSAP
      const distance = () => {
        const inline = track.style.translate;
        track.style.translate = '';
        const value = parseFloat(getComputedStyle(track).translate) || 0;
        track.style.translate = inline;
        return value;
      };

      gsap.fromTo(track, { translate: () => `${distance()}px 0px` }, {
        translate: '0px 0px',
        ease: 'none',
        scrollTrigger: {
          trigger: section,
          start: 'top top',
          end: () => `+=${distance()}`,
          pin: true,
          scrub: true,
          invalidateOnRefresh: true,
        },
      });
    });
  });
}
