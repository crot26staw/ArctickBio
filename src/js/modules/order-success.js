import { gsap, prefersReducedMotion } from './motion.js';

/**
 * «Заказ оформлен» (partials/order/received.html): два кадра макета (18252:77498 → 18253:80920) — галочка в пульсирующих
 * кольцах, затем кольца гаснут и снизу появляются заголовок, текст и кнопка. Тайминги в макете не заданы.
 */
export function initOrderSuccess() {
  const root = document.querySelector('[data-js-order-success]');
  if (!root) return;

  const rings = [...root.querySelectorAll('[data-js-order-success-ring]')].reverse(); // изнутри наружу
  const check = root.querySelector('[data-js-order-success-check]');
  const content = root.querySelector('[data-js-order-success-content]');

  if (prefersReducedMotion()) {
    gsap.set(content, { opacity: 1 });
    return;
  }

  gsap
    .timeline({ defaults: { ease: 'power2.out' } })
    .from(check, { scale: 0.4, opacity: 0, duration: 0.5, ease: 'back.out(2)' })
    .fromTo(rings, { scale: 0.6, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.5, stagger: 0.12 }, '-=0.2')
    .to(rings, { scale: 1.08, duration: 0.45, stagger: 0.08, yoyo: true, repeat: 1, ease: 'sine.inOut' })
    .to(rings, { scale: 1.25, opacity: 0, duration: 0.5, stagger: -0.08 }, '+=0.1')
    .fromTo(content, { opacity: 0, y: 24 }, { opacity: 1, y: 0, duration: 0.8, ease: 'expo.out' }, '-=0.25');
}
