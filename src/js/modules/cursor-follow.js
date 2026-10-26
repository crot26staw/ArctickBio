import { gsap, prefersReducedMotion } from './motion.js';

const FOLLOW = {
  duration: 0.6, // запаздывание за курсором, с
  ease: 'power3.out',
};

const ACTIVE_CLASS = 'is-cursor-follow'; // у области, пока элемент следует за мышью: в CSS курсор скрыт (cursor: none)

/**
 * Элемент [data-js-cursor-follow] плавно следует за курсором (центр элемента — под курсором) в пределах области
 * (ближайший [data-js-cursor-follow-area], иначе родитель). Курсор ушёл — элемент возвращается на место.
 * Пока мышь в области, у неё класс is-cursor-follow — сам курсор скрыт, его роль играет элемент (кнопка «Смотреть»).
 * Позиция в покое задаётся в CSS, центрирование — через `translate: -50% -50%`
 * (не transform: его занимает GSAP). Только для мыши; при prefers-reduced-motion не двигается.
 */
function setup(el) {
  const area = el.closest('[data-js-cursor-follow-area]') ?? el.parentElement;
  const xTo = gsap.quickTo(el, 'x', FOLLOW);
  const yTo = gsap.quickTo(el, 'y', FOLLOW);
  let pointer = null;

  const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

  const follow = () => {
    if (!pointer) return;

    const rect = area.getBoundingClientRect();
    // Центр элемента в покое (offset* не учитывают transform/translate)
    const homeX = el.offsetLeft;
    const homeY = el.offsetTop;
    const halfW = el.offsetWidth / 2;
    const halfH = el.offsetHeight / 2;

    const x = pointer.x - rect.left;
    const y = pointer.y - rect.top;

    xTo(clamp(x, halfW, rect.width - halfW) - homeX);
    yTo(clamp(y, halfH, rect.height - halfH) - homeY);
  };

  area.addEventListener('pointermove', (event) => {
    if (event.pointerType !== 'mouse') return;
    pointer = { x: event.clientX, y: event.clientY };
    area.classList.add(ACTIVE_CLASS);
    follow();
  });

  area.addEventListener('pointerleave', () => {
    pointer = null;
    area.classList.remove(ACTIVE_CLASS);
    xTo(0);
    yTo(0);
  });

  // Страница скроллится под неподвижным курсором — догоняем его
  window.addEventListener('scroll', follow, { passive: true });
}

export function initCursorFollow() {
  if (prefersReducedMotion() || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

  document.querySelectorAll('[data-js-cursor-follow]').forEach(setup);
}
