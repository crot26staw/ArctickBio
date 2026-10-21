import Swiper from 'swiper';
import { Autoplay, EffectFade, Navigation } from 'swiper/modules';
import { prefersReducedMotion } from './motion.js';

/**
 * Слайдер новостей (страница О компании): зацикленный, с автопролистыванием и плавной сменой (fade),
 * стрелки поверх фото. На наведении пауза; при prefers-reduced-motion автопролистывание выключено.
 * Разметка: [data-js-news-slider] > .swiper-wrapper > .swiper-slide, [data-js-news-slider-prev|next].
 */

const DELAY = 5000;

export function initNewsSliders() {
  document.querySelectorAll('[data-js-news-slider]').forEach((el) => {
    new Swiper(el, {
      modules: [Autoplay, EffectFade, Navigation],
      loop: true,
      effect: 'fade',
      fadeEffect: { crossFade: true },
      speed: 600,
      grabCursor: true,
      navigation: {
        prevEl: el.querySelector('[data-js-news-slider-prev]'),
        nextEl: el.querySelector('[data-js-news-slider-next]'),
      },
      autoplay: prefersReducedMotion()
        ? false
        : { delay: DELAY, disableOnInteraction: false, pauseOnMouseEnter: true },
    });
  });
}
