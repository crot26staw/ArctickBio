import { getLenis } from './smooth-scroll.js';

const OPEN_CLASS = 'is-popup-open';
const LEFT_CLASS = 'is-popup-left';
const TABLET = window.matchMedia('(max-width: 1199px)');
const MOBILE = window.matchMedia('(max-width: 767px)');
const HOVER = window.matchMedia('(hover: hover)');

/**
 * Попап шага «Процесса изготовления» по нажатию на маркер — на планшете и мобилке
 * (на десктопе попап открывается наведением, это CSS). Разметка: [data-js-step-popup] (шаг) > [data-js-step-popup-toggle] (маркер).
 * Открыт один попап; закрывается повторным нажатием, нажатием мимо и Escape.
 * Если вправо от маркера попап не помещается в экран — открывается влево (класс is-popup-left, стили в _production.scss).
 * На мобилке попап — окно на весь экран: закрывают крестик [data-js-step-popup-close], нажатие мимо карточки [data-js-step-popup-card]
 * и Escape; на время окна Lenis стоит, фокус — на крестике.
 * Видео попапа [data-js-step-popup-video] играет с начала, пока попап открыт (наведение, фокус или нажатие), иначе на паузе.
 * Грузится заранее (preload auto), когда шаг подходит к экрану.
 */
export function initStepPopups() {
  const steps = [...document.querySelectorAll('[data-js-step-popup]')];
  if (!steps.length) return;

  let current = null;

  // Попап открыт — то же условие, что в CSS (_production.scss)
  const syncVideo = (step) => {
    const video = step.querySelector('[data-js-step-popup-video]');
    if (!video?.getAttribute('src')) return;

    const toggle = step.querySelector('[data-js-step-popup-toggle]');
    const isOpen =
      step.classList.contains(OPEN_CLASS) ||
      (HOVER.matches && toggle?.matches(':hover')) ||
      (!MOBILE.matches && toggle?.matches(':focus-visible'));

    if (isOpen && video.paused) {
      video.currentTime = 0;
      video.play().catch(() => {});
    } else if (!isOpen && !video.paused) {
      video.pause();
    }
  };

  const close = () => {
    if (!current) return;
    const step = current;
    const toggle = step.querySelector('[data-js-step-popup-toggle]');
    const hadFocus = step.querySelector('[data-js-step-popup-card]')?.contains(document.activeElement);
    step.classList.remove(OPEN_CLASS);
    toggle?.setAttribute('aria-expanded', 'false');
    current = null;
    syncVideo(step);
    getLenis()?.start();
    if (hadFocus) toggle?.focus({ preventScroll: true });
  };

  const open = (step, toggle) => {
    close();
    const popup = toggle.nextElementSibling;
    const fitsRight = toggle.getBoundingClientRect().left + (popup?.offsetWidth ?? 0) <= window.innerWidth;
    step.classList.toggle(LEFT_CLASS, !fitsRight);
    step.classList.add(OPEN_CLASS);
    toggle.setAttribute('aria-expanded', 'true');
    current = step;
    syncVideo(step);

    if (MOBILE.matches) {
      getLenis()?.stop();
      // В первый кадр перехода окно ещё visibility: hidden — фокус не встанет
      requestAnimationFrame(() => {
        requestAnimationFrame(() => step.querySelector('[data-js-step-popup-close]')?.focus({ preventScroll: true }));
      });
    }
  };

  steps.forEach((step) => {
    const toggle = step.querySelector('[data-js-step-popup-toggle]');
    if (!toggle) return;

    toggle.addEventListener('click', () => {
      if (!TABLET.matches) return;
      if (current === step) close();
      else open(step, toggle);
    });

    step.querySelector('[data-js-step-popup-close]')?.addEventListener('click', close);

    ['pointerenter', 'pointerleave', 'focus', 'blur'].forEach((type) => {
      toggle.addEventListener(type, () => syncVideo(step));
    });
  });

  // Видео подгружаются, когда шаг рядом с экраном (лента едет вбок — запас по горизонтали)
  const preloader = new IntersectionObserver(
    (entries) => {
      entries.forEach(({ isIntersecting, target }) => {
        if (!isIntersecting) return;
        const video = target.querySelector('[data-js-step-popup-video]');
        if (video?.getAttribute('src')) video.preload = 'auto';
        preloader.unobserve(target);
      });
    },
    { rootMargin: '0px 50% 0px 50%' },
  );
  steps.forEach((step) => preloader.observe(step));

  // Нажатие мимо карточки закрывает (на мобилке подложка окна — внутри шага, поэтому проверка по карточке)
  document.addEventListener('pointerdown', (event) => {
    if (!current) return;
    const card = current.querySelector('[data-js-step-popup-card]');
    const toggle = current.querySelector('[data-js-step-popup-toggle]');
    if (!card?.contains(event.target) && !toggle?.contains(event.target)) close();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') close();
  });

  TABLET.addEventListener('change', close);
  MOBILE.addEventListener('change', close);
}
