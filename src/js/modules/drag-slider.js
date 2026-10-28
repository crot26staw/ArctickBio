import { gsap, prefersReducedMotion } from './motion.js';
import { Draggable } from 'gsap/Draggable';
import { InertiaPlugin } from 'gsap/InertiaPlugin';

gsap.registerPlugin(Draggable, InertiaPlugin);

/**
 * Горизонтальная лента, которая листается зажатием и свайпом (мышь и тач).
 * Разметка: [data-js-drag-slider] (видимая область) > [data-js-drag-slider-track] (лента).
 * Положение ленты в покое: по центру, как отзывы в макете, или [data-drag-slider-align="start"] — у левого края;
 * [data-drag-slider-align-mobile="start"] — у левого края только на мобилке (отзывы).
 * Листается, только если лента шире области (например, каталог — на планшете, а на десктопе помещается);
 * тогда у области класс is-draggable.
 */
function initSlider(viewport) {
  const track = viewport.querySelector('[data-js-drag-slider-track]');
  if (!track || viewport.dataset.dragSliderReady !== undefined) return;
  viewport.dataset.dragSliderReady = '';

  const mobileQuery = window.matchMedia('(max-width: 767px)');
  const isAlignStart = () =>
    viewport.dataset.dragSliderAlign === 'start' ||
    (viewport.dataset.dragSliderAlignMobile === 'start' && mobileQuery.matches);
  const getOverflow = () => track.scrollWidth - viewport.clientWidth;

  const getBounds = () => {
    const overflow = getOverflow();
    return overflow > 0
      ? { minX: -overflow, maxX: 0 }
      : { minX: -overflow / 2, maxX: -overflow / 2 };
  };

  const resetPosition = () => {
    const { minX, maxX } = getBounds();
    gsap.set(track, { x: isAlignStart() ? maxX : (minX + maxX) / 2 });
  };

  // Сообщить о сдвиге ленты (fake-glass.js выравнивает по нему подложку карточек)
  const notify = () => viewport.dispatchEvent(new CustomEvent('drag-slider:update', { bubbles: true }));

  resetPosition();

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

  const setScrollable = () => {
    const scrollable = getOverflow() > 0;
    viewport.classList.toggle('is-draggable', scrollable);
    if (scrollable) draggable.enable();
    else draggable.disable();
  };

  setScrollable();

  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      draggable.applyBounds(getBounds());
      resetPosition();
      draggable.update();
      setScrollable();
      notify();
    }, 150);
  });
}

// scope — область поиска (лента «Попробуйте также» после обновления корзины фрагментами, cart.js); готовые ленты пропускаются
export function initDragSliders(scope = document) {
  scope.querySelectorAll('[data-js-drag-slider]').forEach(initSlider);
}
