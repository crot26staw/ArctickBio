import { gsap, prefersReducedMotion } from './motion.js';

/**
 * Счётчик чисел (референс magicui.design/docs/components/number-ticker):
 * число отсчитывается от 0 до data-value с плавным замедлением в конце.
 * Разметка: <span data-js-number-ticker data-value="98">98</span> — в HTML итоговое значение (без JS / reduced motion).
 */

const DURATION = 1.6;
const format = new Intl.NumberFormat('ru-RU');

const getValue = (el) => Number(el.dataset.value);

// Сбросить в 0 до запуска (чтобы не мелькало итоговое число)
export function resetNumberTickers(root) {
  if (prefersReducedMotion()) return;
  root.querySelectorAll('[data-js-number-ticker]').forEach((el) => {
    el.textContent = format.format(0);
  });
}

export function playNumberTickers(root) {
  root.querySelectorAll('[data-js-number-ticker]').forEach((el) => {
    const value = getValue(el);
    gsap.killTweensOf(el);

    if (prefersReducedMotion()) {
      el.textContent = format.format(value);
      return;
    }

    const counter = { value: 0 };
    el.textContent = format.format(0);
    gsap.to(counter, {
      value,
      duration: DURATION,
      ease: 'expo.out',
      onUpdate: () => {
        el.textContent = format.format(Math.round(counter.value));
      },
    });
  });
}
