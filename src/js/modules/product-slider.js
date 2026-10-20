import Swiper from 'swiper';
import { Autoplay, EffectFade, Pagination } from 'swiper/modules';
import { prefersReducedMotion } from './motion.js';

/**
 * Слайдер товаров в hero: зацикленный, с автопролистыванием и плавной сменой (fade).
 * На наведении пауза; при prefers-reduced-motion автопролистывание выключено, листается свайпом.
 */

const DELAY = 4000;

export function initProductSliders() {
  document.querySelectorAll('[data-js-product-slider]').forEach((el) => {
    new Swiper(el, {
      modules: [Autoplay, EffectFade, Pagination],
      loop: true,
      effect: 'fade',
      fadeEffect: { crossFade: true },
      speed: 600,
      grabCursor: true,
      // Точки как в карточке (.mini-card__dot), но общие для слайдера — лежат в неподвижной рамке
      pagination: {
        el: el.querySelector('[data-js-product-slider-pagination]'),
        clickable: true,
        bulletClass: 'mini-card__dot',
        bulletActiveClass: 'is-active',
      },
      autoplay: prefersReducedMotion()
        ? false
        : { delay: DELAY, disableOnInteraction: false, pauseOnMouseEnter: true },
    });
  });
}
