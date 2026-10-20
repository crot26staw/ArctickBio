import { gsap, ScrollTrigger, prefersReducedMotion } from './motion.js';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(SplitText);

/**
 * Анимация заголовков «mask reveal up» (референс: animata.design/docs/text/mask-reveal-up):
 * заголовок бьётся на строки, строки по очереди поднимаются снизу, проявляются и теряют размытие.
 * Заголовки первого экрана анимируются сразу, остальные — при появлении во вьюпорте (один раз).
 * Разметка: [data-js-title-reveal].
 */
const ENTER = {
  duration: 0.55,
  stagger: 0.065,
  y: 17,
  blur: 6,
  ease: 'cubic-bezier(0.22, 1, 0.36, 1)',
};

function animate(lines) {
  return gsap.fromTo(
    lines,
    { opacity: 0, y: ENTER.y, filter: `blur(${ENTER.blur}px)` },
    {
      opacity: 1,
      y: 0,
      filter: 'blur(0px)',
      duration: ENTER.duration,
      stagger: ENTER.stagger,
      ease: ENTER.ease,
      clearProps: 'filter,willChange',
    },
  );
}

export function initTitleReveal() {
  const titles = document.querySelectorAll('[data-js-title-reveal]');
  if (!titles.length || prefersReducedMotion()) return;

  document.fonts.ready.then(() => {
    titles.forEach((title) => {
      SplitText.create(title, {
        type: 'lines',
        linesClass: 'title-line',
        autoSplit: true,
        aria: 'auto',
        onSplit(self) {
          gsap.set(self.lines, { opacity: 0, willChange: 'transform, opacity, filter' });
          title.classList.add('is-split');

          // При пересплите (resize) анимацию не повторяем
          if (title.dataset.revealed) {
            gsap.set(self.lines, { opacity: 1, willChange: 'auto' });
            return undefined;
          }

          return ScrollTrigger.create({
            trigger: title,
            start: 'top 85%',
            once: true,
            onEnter: () => {
              title.dataset.revealed = 'true';
              animate(self.lines);
            },
          });
        },
      });
    });
  });
}
