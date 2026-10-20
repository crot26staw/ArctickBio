import { gsap, prefersReducedMotion } from './motion.js';

/**
 * Сцена блоков about → product-scene, вся анимация привязана к скроллу (scrub).
 *
 * Фаза 1 — появление (комментарии в Figma к блоку about):
 *   пока about поднимается к верху экрана, стик проявляется снизу из прозрачности,
 *   затем карточки по очереди выезжают снизу с проявлением и наслаиваются.
 * Фаза 2 — перелёт (ноды 18126:51425, 18126:51426, 18126:51464):
 *   от «about у верха экрана» до «product-scene у верха экрана» стик опускается,
 *   уменьшается до размера саше в пачке и встаёт на место [data-js-sachet-journey-target].
 *
 * Фаза 1 двигает картинку стика, фаза 2 — его обёртку, поэтому они не конфликтуют.
 */

const APPEAR = {
  sachetY: 160,
  cardY: 120,
  ease: 'power2.out',
};

// Позиция элемента относительно корня по offset* (не зависит от transform)
function offsetWithin(el, root) {
  let x = 0;
  let y = 0;
  let node = el;

  while (node && node !== root) {
    x += node.offsetLeft;
    y += node.offsetTop;
    node = node.offsetParent;
  }

  return { x, y, width: el.offsetWidth, height: el.offsetHeight };
}

function initAppear(root, startSection, sachetImg) {
  const cards = root.querySelectorAll('[data-js-sachet-journey-card]');

  const tl = gsap.timeline({
    scrollTrigger: {
      trigger: startSection,
      start: 'top 80%',
      end: 'top top',
      scrub: 0.6,
    },
  });

  // Шкала 0–1 = путь скролла: стик — первая половина, карточки — вторая (когда они уже в кадре)
  tl.from(sachetImg, { y: APPEAR.sachetY, opacity: 0, duration: 0.4, ease: APPEAR.ease }, 0.1)
    .from(cards, { y: APPEAR.cardY, opacity: 0, duration: 0.3, stagger: 0.1, ease: APPEAR.ease }, 0.5);
}

function initFlight(root, item, target, endSection) {
  // Смещение и масштаб, чтобы стик совпал с целью (transform-origin — верх по центру)
  const getEndState = () => {
    const from = offsetWithin(item, root);
    const to = offsetWithin(target, root);

    return {
      x: to.x + to.width / 2 - (from.x + from.width / 2),
      y: to.y - from.y,
      scaleX: to.width / from.width,
      scaleY: to.height / from.height,
    };
  };

  gsap.set(item, { transformOrigin: '50% 0' });

  if (prefersReducedMotion()) {
    gsap.set(item, getEndState());
    return;
  }

  gsap.to(item, {
    x: () => getEndState().x,
    y: () => getEndState().y,
    scaleX: () => getEndState().scaleX,
    scaleY: () => getEndState().scaleY,
    ease: 'power1.inOut',
    scrollTrigger: {
      trigger: root,
      start: 'top top',
      endTrigger: endSection,
      end: 'top top',
      scrub: 0.6,
      invalidateOnRefresh: true,
    },
  });
}

export function initSachetJourney() {
  const root = document.querySelector('[data-js-sachet-journey]');
  if (!root) return;

  const item = root.querySelector('[data-js-sachet-journey-item]');
  const sachetImg = root.querySelector('[data-js-sachet-journey-img]');
  const target = root.querySelector('[data-js-sachet-journey-target]');
  const startSection = root.querySelector('section');
  const endSection = target?.closest('section');
  if (!item || !sachetImg || !target || !startSection || !endSection) return;

  if (!prefersReducedMotion()) {
    initAppear(root, startSection, sachetImg);
  }

  initFlight(root, item, target, endSection);
}
