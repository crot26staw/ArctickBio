import { gsap, prefersReducedMotion } from './motion.js';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(SplitText);

/**
 * Поочерёдное появление блоков секции по скроллу (scrub, без закрепления):
 * пока секция въезжает в экран (SEQUENCE.start → SEQUENCE.end), элементы по очереди поднимаются снизу и проявляются,
 * при обратном скролле — прячутся обратно.
 * Разметка: section[data-js-reveal-sequence] с элементами [data-js-reveal-sequence-item] (порядок = порядок в DOM).
 * [data-reveal-sequence-split] у элемента — бить на строки (заголовок: строки по очереди, с размытием, как в title-reveal.js).
 * Такому заголовку НЕ ставить data-js-title-reveal — он анимируется здесь.
 * Сейчас — comparison: заголовок → фото → таблица; usage: заголовок → фото → карточки по одной.
 */
const SEQUENCE = {
  start: 'top 90%',
  end: 'top -20%', // секция уже ушла под верх экрана, таблица ещё в кадре
  scrub: 1,
  y: 60, // фото, таблица
  lineY: 17, // строки заголовка
  blur: 6,
  lineStagger: 0.15,
  gap: 0.5, // следующий элемент стартует, когда предыдущий прошёл половину
  ease: 'power2.out',
};

export function initRevealSequence() {
  const groups = document.querySelectorAll('[data-js-reveal-sequence]');
  if (!groups.length || prefersReducedMotion()) return;

  document.fonts.ready.then(() => {
    groups.forEach((group) => {
      const items = group.querySelectorAll('[data-js-reveal-sequence-item]');
      if (!items.length) return;

      const tl = gsap.timeline({
        defaults: { ease: SEQUENCE.ease, duration: 1 },
        scrollTrigger: {
          trigger: group,
          start: SEQUENCE.start,
          end: SEQUENCE.end,
          scrub: SEQUENCE.scrub,
        },
      });

      items.forEach((item, i) => {
        const position = i === 0 ? 0 : `-=${SEQUENCE.gap}`;

        if (item.hasAttribute('data-reveal-sequence-split')) {
          const { lines } = SplitText.create(item, { type: 'lines', linesClass: 'title-line', aria: 'auto' });
          tl.from(
            lines,
            { opacity: 0, y: SEQUENCE.lineY, filter: `blur(${SEQUENCE.blur}px)`, stagger: SEQUENCE.lineStagger },
            position,
          );
        } else {
          tl.from(item, { opacity: 0, y: SEQUENCE.y }, position);
        }
      });
    });
  });
}
