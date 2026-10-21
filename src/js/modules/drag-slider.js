import { gsap, prefersReducedMotion } from './motion.js';
import { Draggable } from 'gsap/Draggable';
import { InertiaPlugin } from 'gsap/InertiaPlugin';

gsap.registerPlugin(Draggable, InertiaPlugin);

/**
 * Горизонтальная лента, которая листается зажатием и свайпом (мышь и тач).
 * Разметка: [data-js-drag-slider] (видимая область) > [data-js-drag-slider-track] (лента).
 * Если лента шире области — стартует по центру, как в макете.
 */
function initSlider(viewport) {
  const track = viewport.querySelector('[data-js-drag-slider-track]');
  if (!track) return;

  const getBounds = () => {
    const overflow = track.scrollWidth - viewport.clientWidth;
    return overflow > 0
      ? { minX: -overflow, maxX: 0 }
      : { minX: -overflow / 2, maxX: -overflow / 2 };
  };

  const centerTrack = () => {
    const { minX, maxX } = getBounds();
    gsap.set(track, { x: (minX + maxX) / 2 });
  };

  // Сообщить о сдвиге ленты (fake-glass.js выравнивает по нему подложку карточек)
  const notify = () => viewport.dispatchEvent(new CustomEvent('drag-slider:update', { bubbles: true }));

  centerTrack();

  const [draggable] = Draggable.create(track, {
    type: 'x',
    bounds: getBounds(),
    edgeResistance: 0.85,
    inertia: !prefersReducedMotion(),
    dragClickables: true,
    zIndexBoost: false,
    onDrag: notify,
    onThrowUpdate: notify,
  });

  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      draggable.applyBounds(getBounds());
      centerTrack();
      draggable.update();
      notify();
    }, 150);
  });
}

export function initDragSliders() {
  document.querySelectorAll('[data-js-drag-slider]').forEach(initSlider);
}
