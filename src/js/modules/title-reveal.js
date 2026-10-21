import { gsap, ScrollTrigger, prefersReducedMotion } from './motion.js';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(SplitText);

/**
 * Появление заголовков и описаний «строка из-под маски» (референс дизайнера: kononenkogroup.com):
 * текст бьётся на строки, каждая строка в своей маске (overflow: clip) и по очереди выезжает снизу.
 * Заголовки первого экрана анимируются сразу, остальные — при появлении во вьюпорте (один раз).
 * Разметка: [data-js-title-reveal] — заголовки секций и описания под ними.
 * [data-title-reveal-start] — свой момент запуска (start ScrollTrigger), по умолчанию 'top 85%'.
 *   В about — 'top 20%': about наезжает на hero, строки выезжают, когда он почти у верха экрана.
 * Запас маски по вертикали (под выносные элементы букв) — .title-line-mask в base/_global.scss.
 */
export const LINE_REVEAL = {
  duration: 1.1,
  stagger: 0.1,
  // Строка уезжает за маску с запасом: маска выше строки на padding (_global.scss)
  yPercent: 130,
  ease: 'cubic-bezier(0.17, 0.84, 0.44, 1)',
};

export function splitLines(el, options = {}) {
  // SplitText ломает строку на любом <br>, даже скрытом (переносы только для мобилки: about__text-br) —
  // невидимые на этой ширине убираем до разбивки. Смена брейкпоинта без перезагрузки их не вернёт
  el.querySelectorAll('br').forEach((br) => {
    if (getComputedStyle(br).display === 'none') br.remove();
  });

  return SplitText.create(el, {
    type: 'lines',
    mask: 'lines',
    linesClass: 'title-line',
    aria: 'auto',
    ...options,
  });
}

export function initTitleReveal() {
  const titles = document.querySelectorAll('[data-js-title-reveal]');
  if (!titles.length || prefersReducedMotion()) return;

  document.fonts.ready.then(() => {
    titles.forEach((title) => {
      splitLines(title, {
        autoSplit: true,
        onSplit(self) {
          title.classList.add('is-split');

          // При пересплите (resize) анимацию не повторяем
          if (title.dataset.revealed) return undefined;

          gsap.set(self.lines, { yPercent: LINE_REVEAL.yPercent });

          return ScrollTrigger.create({
            trigger: title,
            start: title.dataset.titleRevealStart || 'top 85%',
            once: true,
            onEnter: () => {
              title.dataset.revealed = 'true';
              gsap.to(self.lines, {
                yPercent: 0,
                duration: LINE_REVEAL.duration,
                stagger: LINE_REVEAL.stagger,
                ease: LINE_REVEAL.ease,
              });
            },
          });
        },
      });
    });
  });
}
