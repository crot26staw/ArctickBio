const OPEN_CLASS = 'is-popup-open';
const LEFT_CLASS = 'is-popup-left';
const TABLET = window.matchMedia('(max-width: 1199px)');

/**
 * Попап шага «Процесса изготовления» по нажатию на маркер — на планшете и мобилке
 * (на десктопе попап открывается наведением, это CSS). Разметка: [data-js-step-popup] (шаг) > [data-js-step-popup-toggle] (маркер).
 * Открыт один попап; закрывается повторным нажатием, нажатием мимо и Escape.
 * Если вправо от маркера попап не помещается в экран — открывается влево (класс is-popup-left, стили в _production.scss).
 */
export function initStepPopups() {
  const steps = [...document.querySelectorAll('[data-js-step-popup]')];
  if (!steps.length) return;

  let current = null;

  const close = () => {
    if (!current) return;
    current.classList.remove(OPEN_CLASS);
    current.querySelector('[data-js-step-popup-toggle]')?.setAttribute('aria-expanded', 'false');
    current = null;
  };

  const open = (step, toggle) => {
    close();
    const popup = toggle.nextElementSibling;
    const fitsRight = toggle.getBoundingClientRect().left + (popup?.offsetWidth ?? 0) <= window.innerWidth;
    step.classList.toggle(LEFT_CLASS, !fitsRight);
    step.classList.add(OPEN_CLASS);
    toggle.setAttribute('aria-expanded', 'true');
    current = step;
  };

  steps.forEach((step) => {
    const toggle = step.querySelector('[data-js-step-popup-toggle]');
    if (!toggle) return;

    toggle.addEventListener('click', () => {
      if (!TABLET.matches) return;
      if (current === step) close();
      else open(step, toggle);
    });
  });

  document.addEventListener('pointerdown', (event) => {
    if (current && !current.contains(event.target)) close();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') close();
  });

  TABLET.addEventListener('change', close);
}
