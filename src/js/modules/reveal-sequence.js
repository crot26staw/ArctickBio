import { gsap, prefersReducedMotion } from './motion.js';
import { LINE_REVEAL, splitLines } from './title-reveal.js';

/**
 * Поочерёдное появление блоков секции по скроллу (scrub, без закрепления):
 * пока секция въезжает в экран (SEQUENCE.start → SEQUENCE.end), элементы по очереди поднимаются снизу и проявляются,
 * при обратном скролле — прячутся обратно.
 * Разметка: section[data-js-reveal-sequence] с элементами [data-js-reveal-sequence-item] (порядок = порядок в DOM).
 * [data-reveal-sequence-split] у элемента — бить на строки (заголовок: строки по очереди выезжают из-под маски, как в title-reveal.js).
 * Такому заголовку НЕ ставить data-js-title-reveal — он анимируется здесь.
 * [data-reveal-sequence-children] — анимировать не сам элемент, а его детей по очереди (список карточек).
 *   ="visible" — только детей, видных в окне по горизонтали (лента шире экрана: drag-slider), остальные не трогаем.
 * [data-reveal-sequence-end="top 40%"] у секции — закончить раньше SEQUENCE.end (всё должно успеть появиться, пока секция в кадре).
 * [data-reveal-sequence-gap="1.5"] — начать раньше: насколько наложиться на предыдущий элемент (по умолчанию SEQUENCE.gap).
 * Сейчас — comparison: заголовок → фото → таблица; usage: заголовок → фото → карточки по одной;
 * catalog: заголовок → карточки по одной (заканчивается раньше — data-reveal-sequence-end); reviews: заголовок → кнопка → видимые карточки;
 * faq: заголовок → описание → кнопка → аккордеоны по одному → «Нужна помощь» с соцсетями.
 */
const SEQUENCE = {
  start: 'top 90%',
  end: 'top -20%', // секция уже ушла под верх экрана, таблица ещё в кадре
  scrub: 1,
  y: 60, // фото, таблица
  childStagger: 0.3, // дети [data-reveal-sequence-children]
  lineStagger: 0.15,
  gap: 0.5, // следующий элемент стартует, когда предыдущий прошёл половину
  ease: 'power2.out',
};

function getTargets(item) {
  const mode = item.dataset.revealSequenceChildren;
  if (mode === undefined) return item;
  if (mode !== 'visible') return item.children;

  return [...item.children].filter((child) => {
    const { left, right } = child.getBoundingClientRect();
    return right > 0 && left < window.innerWidth;
  });
}

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
          end: group.dataset.revealSequenceEnd ?? SEQUENCE.end,
          scrub: SEQUENCE.scrub,
        },
      });

      items.forEach((item, i) => {
        const position = i === 0 ? 0 : `-=${item.dataset.revealSequenceGap ?? SEQUENCE.gap}`;

        if (item.hasAttribute('data-reveal-sequence-split')) {
          const { lines } = splitLines(item);
          tl.from(lines, { yPercent: LINE_REVEAL.yPercent, stagger: SEQUENCE.lineStagger }, position);
        } else {
          const vars = { opacity: 0, y: SEQUENCE.y, stagger: SEQUENCE.childStagger };
          let targets = getTargets(item);
          let tween = tl.from(targets, vars, position).recent();

          // Лента листается (drag-slider) — на экране уже другие карточки. Без пересборки обратный скролл
          // прятал бы ушедшие за край карточки, а видимые оставались бы на месте
          if (item.dataset.revealSequenceChildren === 'visible') {
            let timer;
            group.addEventListener('drag-slider:update', () => {
              clearTimeout(timer);
              timer = setTimeout(() => {
                const start = tween.startTime();
                tl.remove(tween);
                gsap.set(targets, { clearProps: 'opacity,transform' });

                targets = getTargets(item);
                // lazy: false — твин сразу запоминает значения (иначе при обратном скролле карточки не прячутся)
                tween = gsap.from(targets, { ...vars, duration: 1, ease: SEQUENCE.ease, lazy: false });
                tl.add(tween, start);
                // Состояние новых карточек — по текущему положению скролла
                tween.totalTime(Math.max(0, tl.time() - start));
              }, 200);
            });
          }
        }
      });
    });
  });
}
