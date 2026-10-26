import { gsap, ScrollTrigger, prefersReducedMotion } from './motion.js';
import { getLenis } from './smooth-scroll.js';
import { playNumberTickers, resetNumberTickers } from './number-ticker.js';

/**
 * Блок «Исследования»: список, где раскрыт один пункт (по умолчанию первый), секция — 100svh.
 * Пункты сменяются по скроллу (референс darknode.army, блок values): секция закрепляется (pin), смену ведёт
 * таймлайн со scrub — всё идёт вслед за скроллом со сглаживанием, а не отыгрывает по времени.
 * Смена пункта: содержимое текущего гаснет и уходит вниз → высоты перетекают (текущий сворачивается в строку,
 * следующий раскрывается) → содержимое следующего проявляется снизу → пауза. Фокус с клавиатуры прокручивает к пункту.
 * При prefers-reduced-motion пина нет — пункт раскрывается классом is-active при наведении/фокусе/тапе (CSS, _research.scss).
 * Цифры раскрытого пункта отсчитываются от 0 (number-ticker.js): у первого — при появлении блока, у остальных — при раскрытии.
 */

const ACTIVE_CLASS = 'is-active';
const SCRUBBED_CLASS = 'is-scrubbed';
const STEP = 0.9; // путь скролла на один пункт, в экранах
const SCRUB = 1.5; // сглаживание, с

// Доли таймлайна (относительные): пауза на открытом пункте и фазы смены
const TIMING = {
  hold: 0.6,
  fadeOut: 0.6, // содержимое текущего гаснет
  size: 1.4, // высоты перетекают (начинается на середине fadeOut)
  fadeIn: 0.8, // содержимое следующего проявляется (с середины size)
};
const SHIFT = 16; // px, на сколько уходит/всплывает содержимое

function getParts(item) {
  return {
    expands: [...item.querySelectorAll('[data-js-research-expand]')],
    collapse: item.querySelector('[data-js-research-collapse]'),
    contents: [...item.querySelectorAll('[data-js-research-content]')],
  };
}

// Полная высота обёртки — по её содержимому (у обёртки overflow: hidden)
const fullHeight = (el) => () => el.firstElementChild.offsetHeight;

function buildTimeline(items) {
  const tl = gsap.timeline({ defaults: { ease: 'none' } });
  const parts = items.map(getParts);
  const switchTimes = []; // моменты смены активного пункта (середина перетекания высот)
  const holdTimes = [TIMING.hold / 2]; // середины пауз на пунктах

  parts.forEach(({ expands, collapse, contents }, i) => {
    const open = i === 0;
    gsap.set(expands, { height: open ? 'auto' : 0 });
    if (collapse) gsap.set(collapse, { height: open ? 0 : 'auto', opacity: open ? 0 : 1 });
    gsap.set(contents, { opacity: open ? 1 : 0, y: open ? 0 : SHIFT });
  });

  let t = TIMING.hold;

  for (let i = 0; i < parts.length - 1; i += 1) {
    const from = parts[i];
    const to = parts[i + 1];
    const sizeAt = t + TIMING.fadeOut / 2;
    const fadeInAt = sizeAt + TIMING.size / 2;

    tl.to(from.contents, { opacity: 0, y: SHIFT, duration: TIMING.fadeOut, ease: 'sine.in' }, t);

    from.expands.forEach((el) => {
      tl.fromTo(el, { height: fullHeight(el) }, { height: 0, duration: TIMING.size, ease: 'power1.inOut', immediateRender: false }, sizeAt);
    });
    to.expands.forEach((el) => {
      tl.fromTo(el, { height: 0 }, { height: fullHeight(el), duration: TIMING.size, ease: 'power1.inOut', immediateRender: false }, sizeAt);
    });

    if (from.collapse) {
      tl.fromTo(from.collapse, { height: 0 }, { height: fullHeight(from.collapse), duration: TIMING.size, ease: 'power1.inOut', immediateRender: false }, sizeAt);
      tl.fromTo(from.collapse, { opacity: 0 }, { opacity: 1, duration: TIMING.size / 2, immediateRender: false }, fadeInAt);
    }
    if (to.collapse) {
      tl.fromTo(to.collapse, { height: fullHeight(to.collapse) }, { height: 0, duration: TIMING.size, ease: 'power1.inOut', immediateRender: false }, sizeAt);
      tl.fromTo(to.collapse, { opacity: 1 }, { opacity: 0, duration: TIMING.size / 3, immediateRender: false }, sizeAt);
    }

    tl.fromTo(to.contents, { opacity: 0, y: SHIFT }, { opacity: 1, y: 0, duration: TIMING.fadeIn, ease: 'sine.out', immediateRender: false }, fadeInAt);

    switchTimes.push(sizeAt + TIMING.size / 2);
    t = fadeInAt + TIMING.fadeIn + TIMING.hold;
    holdTimes.push(t - TIMING.hold / 2);
  }

  // Пауза на последнем пункте
  tl.to({}, { duration: TIMING.hold }, t - TIMING.hold);

  return { tl, switchTimes, holdTimes };
}

function initScrollSteps(section, list, items, activate) {
  list.classList.add(SCRUBBED_CLASS);
  const { tl, switchTimes, holdTimes } = buildTimeline(items);

  const trigger = ScrollTrigger.create({
    trigger: section,
    // Секция ниже экрана — закрепляем по центру, выше — у верха
    start: () => (section.offsetHeight < window.innerHeight ? 'center center' : 'top top'),
    end: `+=${items.length * STEP * 100}%`,
    pin: true,
    scrub: SCRUB,
    animation: tl,
    invalidateOnRefresh: true,
    onUpdate: () => {
      const index = switchTimes.filter((time) => tl.time() >= time).length;
      activate(items[index]);
    },
  });

  // Фокус с клавиатуры — прокрутить к середине паузы пункта
  items.forEach((item, i) => {
    item.addEventListener('focusin', () => {
      const y = trigger.start + (trigger.end - trigger.start) * (holdTimes[i] / tl.duration());
      const lenis = getLenis();
      if (lenis) lenis.scrollTo(y);
      else window.scrollTo(0, y);
    });
  });
}

export function initResearch() {
  document.querySelectorAll('[data-js-research-list]').forEach((list) => {
    const items = [...list.querySelectorAll('[data-js-research-item]')];
    const section = list.closest('section');
    const byScroll = !prefersReducedMotion() && section;
    let isSeen = false;

    const activate = (item) => {
      if (item.classList.contains(ACTIVE_CLASS)) return;
      items.forEach((other) => other.classList.toggle(ACTIVE_CLASS, other === item));
      if (isSeen) playNumberTickers(item);
    };

    if (byScroll) {
      initScrollSteps(section, list, items, activate);
    } else {
      items.forEach((item) => {
        item.addEventListener('focusin', () => activate(item));
        if (window.matchMedia('(hover: hover)').matches) item.addEventListener('mouseenter', () => activate(item));
        item.addEventListener('click', () => activate(item));
      });
    }

    resetNumberTickers(list);

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return;
        isSeen = true;
        observer.disconnect();
        const active = items.find((item) => item.classList.contains(ACTIVE_CLASS));
        if (active) playNumberTickers(active);
      },
      { threshold: 0.4 },
    );
    observer.observe(list);
  });
}
