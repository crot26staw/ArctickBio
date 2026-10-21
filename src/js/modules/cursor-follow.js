import { gsap, prefersReducedMotion } from './motion.js';

const FOLLOW = {
  duration: 0.6, // запаздывание за курсором, с
  ease: 'power3.out',
};

const GAP = 5; // элемент слева сверху от курсора: его правый нижний угол на GAP px левее и выше курсора

/**
 * Элемент [data-js-cursor-follow] плавно следует за курсором (слева сверху от него, на GAP px) в пределах области
 * (ближайший [data-js-cursor-follow-area], иначе родитель). Курсор ушёл — элемент возвращается на место.
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

    const x = pointer.x - rect.left - GAP - halfW;
    const y = pointer.y - rect.top - GAP - halfH;

    xTo(clamp(x, halfW, rect.width - halfW) - homeX);
    yTo(clamp(y, halfH, rect.height - halfH) - homeY);
  };

  area.addEventListener('pointermove', (event) => {
    if (event.pointerType !== 'mouse') return;
    pointer = { x: event.clientX, y: event.clientY };
    follow();
  });

  area.addEventListener('pointerleave', () => {
    pointer = null;
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
