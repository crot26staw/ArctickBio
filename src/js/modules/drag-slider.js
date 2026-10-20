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

  centerTrack();

  const [draggable] = Draggable.create(track, {
    type: 'x',
    bounds: getBounds(),
    edgeResistance: 0.85,
    inertia: !prefersReducedMotion(),
    dragClickables: true,
    zIndexBoost: false,
  });

  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      draggable.applyBounds(getBounds());
      centerTrack();
      draggable.update();
    }, 150);
  });
}

export function initDragSliders() {
  document.querySelectorAll('[data-js-drag-slider]').forEach(initSlider);
}
