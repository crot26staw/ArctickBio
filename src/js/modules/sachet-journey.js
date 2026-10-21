import { gsap, ScrollTrigger, prefersReducedMotion } from './motion.js';

/**
 * Сцена блоков about → product-scene, вся анимация привязана к скроллу (scrub).
 *
 * Фаза 1 — появление (комментарии в Figma к блоку about):
 *   about доходит до верха экрана и закрепляется (pin всей обёртки на APPEAR.pin экрана);
 *   по скроллу стик проявляется снизу из прозрачности, затем карточки по очереди выезжают снизу и наслаиваются.
 * Фаза 2 — перелёт (ноды 18126:51425, 18126:51426, 18126:51464):
 *   от конца пина до «product-scene у верха экрана» стик опускается,
 *   уменьшается до размера саше в пачке и встаёт на место [data-js-sachet-journey-target].
 *
 * Фаза 3 — catalog наезжает на закреплённую product-scene (initCover).
 *
 * Фаза 1 двигает картинку стика, фаза 2 — его обёртку, поэтому они не конфликтуют.
 */

const APPEAR = {
  pin: 2.2, // длина закрепления about, в экранах скролла
  sachetY: 400,
  cardY: 300,
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

// Возвращает ScrollTrigger пина — от его конца стартует перелёт
function initAppear(root, sachetImg) {
  const cards = root.querySelectorAll('[data-js-sachet-journey-card]');

  const tl = gsap.timeline({
    scrollTrigger: {
      trigger: root,
      start: 'top top',
      end: `+=${APPEAR.pin * 100}%`,
      pin: true,
      // На время пина — в body: у внешней обёртки (фаза 3) GSAP ставит transform, и fixed внутри неё не держится
      pinReparent: true,
      scrub: 1,
    },
  });

  // Шкала = путь пина: стик, затем карточки по одной; в конце небольшая пауза перед перелётом
  tl.from(sachetImg, { y: APPEAR.sachetY, opacity: 0, duration: 0.3, ease: APPEAR.ease }, 0)
    .from(cards, { y: APPEAR.cardY, opacity: 0, duration: 0.3, stagger: 0.2, ease: APPEAR.ease }, 0.3)
    .to({}, { duration: 0.15 });

  return tl.scrollTrigger;
}

function initFlight(root, item, target, endSection, pinTrigger) {
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
    scrollTrigger: pinTrigger
      ? {
          // Числами: обёртка закреплена, её 'top top' и позиции секций внутри ScrollTrigger сдвигает
          start: () => pinTrigger.end,
          end: () => pinTrigger.end + offsetWithin(endSection, root).y,
          scrub: 0.6,
          invalidateOnRefresh: true,
        }
      : {
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
  const endSection = target?.closest('section');
  if (!item || !sachetImg || !target || !endSection) return;

  const pinTrigger = prefersReducedMotion() ? null : initAppear(root, sachetImg);

  initFlight(root, item, target, endSection, pinTrigger);

  if (pinTrigger) initCover(root, endSection, pinTrigger);
}

// Фаза 3 — product-scene на весь экран у верха закрепляется (внешняя обёртка cover, без pinSpacing),
// и следующая секция (catalog) наезжает на неё снизу. Старт = конец перелёта.
function initCover(root, endSection, pinTrigger) {
  const cover = root.closest('[data-js-sachet-journey-cover]');
  if (!cover) return;

  ScrollTrigger.create({
    trigger: cover,
    start: () => pinTrigger.end + offsetWithin(endSection, root).y,
    end: () => pinTrigger.end + offsetWithin(endSection, root).y + endSection.offsetHeight,
    pin: true,
    pinSpacing: false,
    invalidateOnRefresh: true,
  });
}
