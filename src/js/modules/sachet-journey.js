import { gsap, ScrollTrigger, prefersReducedMotion } from './motion.js';

/**
 * Сцена блоков about → product-scene, вся анимация привязана к скроллу (scrub).
 *
 * Фаза 1 — появление (комментарии в Figma к блоку about):
 *   about доходит до верха экрана и закрепляется (pin всей обёртки на APPEAR.pin экрана);
 *   по скроллу стик проявляется снизу из прозрачности, затем появляются карточки (initCards, референс — asana-spa.ru).
 * Фаза 2 — перелёт (ноды 18126:51425, 18126:51426, 18126:51464):
 *   от конца пина до «product-scene у верха экрана» стик опускается,
 *   уменьшается до размера саше в пачке и встаёт на место [data-js-sachet-journey-target].
 *
 * Облачная завеса (initVeil, референс — white-desert.com): в конце пина about снизу поднимается завеса,
 *   закрывает about (он прячется), и product-scene выходит из-под облаков: снизу завеса срезана чуть ниже верха секции (VEIL.cut),
 *   край скалы остаётся под облаками; когда секция почти у верха экрана, остаток завесы растворяется. Стик летит поверх облаков.
 *
 * Фаза 3 — catalog наезжает на закреплённую product-scene (initCover).
 *
 * Фаза 1 двигает картинку стика, фаза 2 — его обёртку, поэтому они не конфликтуют.
 */

// Шкала появления — условные единицы на весь пин (APPEAR.length = путь пина)
const APPEAR = {
  pin: 2.6, // длина закрепления about, в экранах скролла
  length: 1.5, // стик (0–0.3) + карточки (0.3–~1.2) + пауза: в ней поднимается завеса (initVeil)
  // Стик выезжает снизу и проявляется почти сразу (комментарий дизайнера: долгий полупрозрачный подъём — «как привидение»)
  sachetY: 160,
  sachetDuration: 0.3,
  sachetFade: 0.06, // за сколько шкалы стик становится непрозрачным
  ease: 'power3.out',
};

// Появление карточек (референс дизайнера — asana-spa.ru, блок с тремя карточками)
const CARDS = {
  start: 0.3, // после стика
  // Десктоп: карточки по очереди целиком поднимаются из прозрачности, следом — их содержимое; фон чуть «отъезжает» (scale)
  cardY: 80,
  cardDuration: 0.45,
  cardStagger: 0.16,
  cardDelay: 0.06,
  contentY: 44,
  contentDuration: 0.42,
  contentDelay: 0.1, // после начала появления карточки
  iconScale: 0.9,
  bgScale: 1.07,
  // Планшет/мобилка (стопка): следующая карточка въезжает снизу и ложится на предыдущую, потом — её содержимое
  stackFrom: 90, // старт — ниже позиции «колонкой» (без наложения)
  stackDuration: 0.36,
  stackStep: 0.26,
  stackFade: 0.12, // мобилка: содержимое предыдущей карточки гаснет под следующей
  ease: 'power3.out',
};

// Тень над catalog, пока он наезжает на product-scene (фаза 3) — доли пути наезда
const COVER_SHADOW = {
  in: 0.1, // проявляется
  out: 0.25, // гаснет к концу, когда catalog подходит к верху экрана
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

  // Шкала = путь пина: стик, дальше — карточки (initCards, своя шкала на тот же путь) и пауза перед перелётом
  tl.from(sachetImg, { y: APPEAR.sachetY, duration: APPEAR.sachetDuration, ease: APPEAR.ease }, 0)
    .from(sachetImg, { opacity: 0, duration: APPEAR.sachetFade, ease: 'none' }, 0)
    .to({}, { duration: APPEAR.length - APPEAR.sachetDuration });

  return tl.scrollTrigger;
}

// Содержимое карточки поднимается из прозрачности, иконка ещё и «дозревает» (прозрачность, масштаб)
function revealContent(tl, card, position) {
  const icon = card.querySelector('[data-js-sachet-journey-card-icon]');
  const content = [...card.children];

  tl.fromTo(
    content,
    { y: CARDS.contentY, opacity: 0 },
    { y: 0, opacity: 1, duration: CARDS.contentDuration, ease: CARDS.ease },
    position,
  );
  if (icon) {
    tl.fromTo(
      icon,
      { scale: CARDS.iconScale },
      { scale: 1, duration: CARDS.contentDuration * 0.7, ease: 'power2.out' },
      position + 0.1,
    );
  }
}

/**
 * Карточки about — отдельная шкала на тот же путь, что у пина (start/end — числами из pinTrigger),
 * чтобы менять её по брейкпоинту (gsap.matchMedia), не пересоздавая пин: от него считаются перелёт и завеса.
 */
function initCards(root, pinTrigger) {
  const cards = [...root.querySelectorAll('[data-js-sachet-journey-card]')];
  const bg = root.querySelector('[data-js-sachet-journey-bg]');
  if (!cards.length) return;

  const createTimeline = () =>
    gsap.timeline({
      scrollTrigger: {
        start: () => pinTrigger.start,
        end: () => pinTrigger.end,
        scrub: 1,
        invalidateOnRefresh: true,
      },
    });

  const mm = gsap.matchMedia();

  // Десктоп: карточки в ряд
  mm.add('(min-width: 1200px)', () => {
    const tl = createTimeline();

    if (bg) {
      gsap.set(bg, { transformOrigin: '50% 100%' });
      tl.fromTo(bg, { scale: CARDS.bgScale }, { scale: 1, duration: APPEAR.length - CARDS.start, ease: 'none' }, CARDS.start);
    }
    cards.forEach((card, i) => {
      const start = CARDS.start + CARDS.cardDelay + i * CARDS.cardStagger;

      tl.fromTo(
        card,
        { y: CARDS.cardY, opacity: 0 },
        { y: 0, opacity: 1, duration: CARDS.cardDuration, ease: CARDS.ease },
        start,
      );
      revealContent(tl, card, start + CARDS.contentDelay);
    });
    tl.to({}, { duration: 0 }, APPEAR.length);
  });

  // Планшет/мобилка: карточки стопкой (CSS). Планшет: первая на месте, остальные въезжают снизу из позиции «колонкой».
  // Мобилка: все карточки, и первая тоже, выезжают из-под низа экрана и ложатся на предыдущую целиком —
  // содержимое предыдущей гаснет, чтобы не просвечивало сквозь стекло
  const initStack = (isMobile) => {
    const tl = createTimeline();
    // Планшет: сдвиг от позиции в стопке до позиции «колонкой» (одна под другой) + CARDS.stackFrom;
    // мобилка: карточки в одном месте — сдвиг на высоту карточки + CARDS.stackFrom (ниже края секции)
    const fromY = (card, i) =>
      isMobile
        ? card.offsetHeight + CARDS.stackFrom
        : cards[0].offsetTop + i * cards[0].offsetHeight - card.offsetTop + CARDS.stackFrom;

    (isMobile ? cards : cards.slice(1)).forEach((card, n) => {
      const i = cards.indexOf(card);
      const start = CARDS.start + n * CARDS.stackStep;

      tl.fromTo(card, { y: () => fromY(card, i) }, { y: 0, duration: CARDS.stackDuration, ease: CARDS.ease }, start);
      revealContent(tl, card, start + 0.08);
      if (isMobile && i > 0) {
        tl.to([...cards[i - 1].children], { opacity: 0, duration: CARDS.stackFade, ease: 'none' }, start + 0.06);
      }
    });
    tl.to({}, { duration: 0 }, APPEAR.length);
  };

  mm.add('(min-width: 768px) and (max-width: 1199px)', () => initStack(false));
  mm.add('(max-width: 767px)', () => initStack(true));
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

// Всё — в экранах скролла (1 = высота окна), отсчёт от конца пина about (0)
const VEIL = {
  speed: 2, // скорость подъёма завесы на экране (страница — 1)
  startY: 0.2, // верх завесы на экране в момент конца пина (0 — верх, 1 — низ окна); плотная часть уже под низом окна
  dense: 0.4, // с какой доли высоты картинки облака закрывают карточки — тогда стик поднимается над завесой
  hideAbout: 0.45, // экран полностью закрыт (плотная часть с 0.64 высоты картинки облаков) — прячем about
  cut: [0.2, 1], // срез завесы снизу: от и до (доли высоты скалы .product-scene__rock-top от верха product-scene) — край скалы под облаками
  stickBehind: 0.75, // стик (уже ниже среза) — за переднюю стенку пачки
  // Остаток завесы начинает гаснуть ровно, когда верх product-scene встал у верха экрана (конец length), и гаснет плавно,
  // по времени (fadeOut, сек): до этого она закрывает ровный верхний край скалы (замечание дизайнера — срез не должен быть виден),
  // а дальше край уже у верха экрана (десктоп — product-scene закреплена, мобилка — уходит вверх)
  fadeOut: 0.8,
  fadeIn: 0.4, // скролл назад — завеса возвращается
  length: 1, // до конца — product-scene у верха экрана
};

/**
 * Стик «за карточками about» (они стеклянные — стик просвечивает сквозь них размытым): z 2 < карточки 3 < завеса 4.
 * Когда облака плотно накрыли верх карточек под стиком (или about уже спрятан), стик поднимается над завесой (z 5)
 * и виден целиком — в облаках карточки уже не видны, перескок слоя незаметен.
 */
function isStickAboveVeil(item, cards, about, veil) {
  if (about.style.visibility === 'hidden') return true;

  const stick = item.getBoundingClientRect();
  // Верх самой высокой карточки, которая по горизонтали пересекается со стиком
  const cardsTop = Math.min(
    ...[...cards]
      .map((card) => card.getBoundingClientRect())
      .filter((card) => card.right > stick.left && card.left < stick.right)
      .map((card) => card.top),
  );
  if (stick.bottom <= cardsTop) return true; // на карточку не заходит

  // Картинка облаков — первый экран завесы, плотнеет книзу
  const denseTop = veil.getBoundingClientRect().top + window.innerHeight * VEIL.dense;
  return denseTop <= cardsTop;
}

/**
 * Завеса [data-js-cloud-veil] — внутри .sachet-journey (иначе стик не поднять над ней: у обёртки свой z-index).
 * Её y — в координатах обёртки: во время пина обёртка стоит, после — уезжает со скроллом,
 * поэтому шкала из двух отрезков с одинаковой скоростью на экране.
 */
function initVeil(veil, item, about, scene, pinTrigger) {
  const cards = about.querySelectorAll('[data-js-sachet-journey-card]');
  const rock = scene.querySelector('[data-js-cloud-veil-edge]') || scene;
  let stickActive = false;
  const update = () => {
    if (stickActive) gsap.set(item, { zIndex: isStickAboveVeil(item, cards, about, veil) ? 5 : '' });
    // Завеса едет быстрее секции — срез пересчитываем каждый кадр (в координатах завесы)
    const top = scene.getBoundingClientRect().top - veil.getBoundingClientRect().top;
    const [from, to] = VEIL.cut.map((k) => `${Math.round(top + rock.offsetHeight * k)}px`);
    veil.style.setProperty('--veil-cut-start', from);
    veil.style.setProperty('--veil-cut-end', to);
  };
  // Срез завесы считаем каждый кадр, пока она не пропала (конец VEIL.length),
  // слой стика — пока завеса плотная (до VEIL.stickBehind: дальше стик ниже среза, у пачки — за её передней стенкой)
  const setActive = (active) => {
    if (active) {
      gsap.ticker.add(update);
    } else {
      gsap.ticker.remove(update);
    }
  };
  const setStick = (active) => {
    stickActive = active;
    if (!active) gsap.set(item, { zIndex: '' });
  };

  const vh = () => window.innerHeight;
  const lead = (1 - VEIL.startY) / VEIL.speed; // сколько экранов до конца пина завеса уже поднимается

  gsap
    .timeline({
      scrollTrigger: {
        // Числами: обёртка закреплена, позиции внутри ScrollTrigger сдвигает
        start: () => pinTrigger.end - lead * vh(),
        end: () => pinTrigger.end + VEIL.length * vh(),
        scrub: 0.6,
        invalidateOnRefresh: true,
      },
    })
    .set(veil, { opacity: 1 }, 0.001)
    // Стик за карточками или над облаками (isStickAboveVeil), пока завеса не рассеялась
    .call(setActive, [false], 0.001)
    .call(setActive, [true], 0.002)
    .call(setStick, [false], 0.001)
    .call(setStick, [true], 0.002)
    // Пин: обёртка стоит — завеса поднимается со своей скоростью
    .fromTo(veil, { y: () => vh() }, { y: () => VEIL.startY * vh(), duration: lead, ease: 'none' }, 0)
    // После пина обёртка уезжает со скоростью 1 — завесе остаётся разница
    .to(veil, { y: () => (VEIL.startY - (VEIL.speed - 1) * VEIL.length) * vh(), duration: VEIL.length, ease: 'none' })
    .set(about, { autoAlpha: 0 }, lead + VEIL.hideAbout)
    .call(setStick, [true], lead + VEIL.stickBehind - 0.001)
    .call(setStick, [false], lead + VEIL.stickBehind)
    .call(setActive, [true], lead + VEIL.length - 0.001)
    .call(setActive, [false], lead + VEIL.length);

  // Угасание — отдельным триггером без scrub: у шкалы выше плавное отставание (scrub 0.6), а гаснуть завеса должна
  // точно с момента, когда верх product-scene у верха экрана; само угасание — по времени
  ScrollTrigger.create({
    start: () => pinTrigger.end + VEIL.length * vh(),
    invalidateOnRefresh: true,
    onEnter: () => gsap.to(veil, { autoAlpha: 0, duration: VEIL.fadeOut, ease: 'power1.out', overwrite: 'auto' }),
    onLeaveBack: () => gsap.to(veil, { autoAlpha: 1, duration: VEIL.fadeIn, ease: 'power1.out', overwrite: 'auto' }),
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
  if (pinTrigger) initCards(root, pinTrigger);

  const veil = root.querySelector('[data-js-cloud-veil]');
  const about = root.querySelector('[data-js-sachet-journey-card]')?.closest('section');
  if (veil && about && pinTrigger) initVeil(veil, item, about, endSection, pinTrigger);

  initFlight(root, item, target, endSection, pinTrigger);

  // На мобилке catalog не наезжает на product-scene (комментарий дизайнера в макете 375)
  if (pinTrigger) {
    gsap.matchMedia().add('(min-width: 768px)', () => initCover(root, endSection, pinTrigger));
  }
}

// Фаза 3 — product-scene на весь экран у верха закрепляется (внешняя обёртка cover, без pinSpacing),
// и следующая секция (catalog) наезжает на неё снизу. Старт = конец перелёта.
function initCover(root, endSection, pinTrigger) {
  const cover = root.closest('[data-js-sachet-journey-cover]');
  if (!cover) return;

  // Тень над наезжающей секцией (.catalog::before): проявляется в начале наезда, гаснет к концу — когда секция у верха экрана
  const next = cover.nextElementSibling;
  const shadow = next
    ? gsap
        .timeline({ defaults: { ease: 'none' } })
        .fromTo(next, { '--cover-shadow': 0 }, { '--cover-shadow': 1, duration: COVER_SHADOW.in })
        .to(next, { '--cover-shadow': 1, duration: 1 - COVER_SHADOW.in - COVER_SHADOW.out })
        .to(next, { '--cover-shadow': 0, duration: COVER_SHADOW.out })
    : undefined;

  ScrollTrigger.create({
    trigger: cover,
    start: () => pinTrigger.end + offsetWithin(endSection, root).y,
    end: () => pinTrigger.end + offsetWithin(endSection, root).y + endSection.offsetHeight,
    pin: true,
    pinSpacing: false,
    animation: shadow,
    scrub: true,
    invalidateOnRefresh: true,
  });
}
