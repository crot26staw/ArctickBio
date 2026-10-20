import { gsap, prefersReducedMotion } from './motion.js';

const OPEN_CLASS = 'is-open';
const DURATION = 0.35;

function toggle(item, open) {
  const button = item.querySelector('[data-js-accordion-toggle]');
  const panel = item.querySelector('[data-js-accordion-panel]');
  const duration = prefersReducedMotion() ? 0 : DURATION;

  button.setAttribute('aria-expanded', String(open));

  if (open) {
    item.classList.add(OPEN_CLASS);
    gsap.fromTo(panel, { height: 0 }, { height: 'auto', duration, ease: 'power2.out' });
  } else {
    gsap.to(panel, {
      height: 0,
      duration,
      ease: 'power2.out',
      onComplete: () => item.classList.remove(OPEN_CLASS),
    });
  }
}

// В группе [data-js-accordion-group] может быть открыт только один пункт
function closeSiblings(item) {
  const group = item.closest('[data-js-accordion-group]');
  if (!group) return;

  group.querySelectorAll('[data-js-accordion]').forEach((other) => {
    const isOpen = other.querySelector('[data-js-accordion-toggle]').getAttribute('aria-expanded') === 'true';
    if (other !== item && isOpen) {
      toggle(other, false);
    }
  });
}

export function initAccordions() {
  document.querySelectorAll('[data-js-accordion]').forEach((item) => {
    const button = item.querySelector('[data-js-accordion-toggle]');

    button.addEventListener('click', () => {
      const open = button.getAttribute('aria-expanded') !== 'true';
      if (open) {
        closeSiblings(item);
      }
      toggle(item, open);
    });
  });
}
