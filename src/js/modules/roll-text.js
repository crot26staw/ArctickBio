import { prefersReducedMotion } from './motion.js';

/**
 * Ховер текстовых ссылок «roll text» (референс: animata.design/docs/text/roll-text, stagger: character):
 * у каждой буквы две копии друг над другом, при наведении/фокусе стопка по очереди уезжает вверх на одну строку.
 * Анимация доигрывает до конца, даже если курсор ушёл; повторный запуск — только после окончания.
 * В конце копии совпадают, поэтому стопка просто сбрасывается на место (без «открытого» состояния).
 *
 * Разметка: [data-js-roll] — ссылка с эффектом; [data-js-roll-group] — все ссылки внутри (меню wp_nav_menu).
 * Внутри ссылки — только текст. Буквы собираются здесь, в разметке/PHP — обычный текст. Стили — components/_roll.scss.
 */

const ROLL = {
  duration: 450, // мс, на одну букву
  stagger: 20, // мс между буквами
  easing: 'cubic-bezier(0.77, 0, 0.175, 1)',
};

function createUnit(char) {
  const unit = document.createElement('span');
  unit.className = 'roll__unit';

  const sizer = document.createElement('span');
  sizer.className = 'roll__sizer';
  sizer.textContent = char;

  const stack = document.createElement('span');
  stack.className = 'roll__stack';
  for (let i = 0; i < 2; i += 1) {
    const line = document.createElement('span');
    line.className = 'roll__line';
    line.textContent = char;
    stack.append(line);
  }

  unit.append(sizer, stack);
  return { unit, stack };
}

function setup(link) {
  if (link.classList.contains('roll')) return;

  const text = link.textContent.trim().replace(/\s+/g, ' ');
  if (!text) return;

  const label = document.createElement('span');
  label.className = 'visually-hidden';
  label.textContent = text;

  // Слова — неразрывные группы букв, пробелы между ними обычным текстом: длинная ссылка переносится по словам
  const track = document.createElement('span');
  track.className = 'roll__track';
  track.setAttribute('aria-hidden', 'true');

  const stacks = [];
  text.split(' ').forEach((word, i) => {
    if (i > 0) track.append(' ');
    const wordEl = document.createElement('span');
    wordEl.className = 'roll__word';
    [...word].forEach((char) => {
      const { unit, stack } = createUnit(char);
      wordEl.append(unit);
      stacks.push(stack);
    });
    track.append(wordEl);
  });

  link.replaceChildren(label, track);
  link.classList.add('roll');

  let playing = false;
  const play = () => {
    if (playing) return;
    playing = true;

    const animations = stacks.map((stack, i) =>
      stack.animate([{ transform: 'translateY(0)' }, { transform: 'translateY(-50%)' }], {
        duration: ROLL.duration,
        delay: i * ROLL.stagger,
        easing: ROLL.easing,
      }),
    );
    animations.at(-1).finished.finally(() => {
      playing = false;
    });
  };

  link.addEventListener('mouseenter', play);
  link.addEventListener('focusin', play);
}

export function initRollText() {
  if (prefersReducedMotion()) return;
  document.querySelectorAll('[data-js-roll], [data-js-roll-group] a').forEach(setup);
}
