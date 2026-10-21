import { gsap } from './motion.js';

/**
 * «Стекло» без backdrop-filter: в карточку подкладывается заранее размытая копия фона секции,
 * выровненная по самому фону. Выглядит как blur(), но видеокарта не размывает фон на каждом кадре скролла
 * (живой backdrop-filter у карточек отзывов и benefits заметно грузил скролл).
 *
 * Разметка: [data-js-fake-glass] — область; внутри картинка фона [data-js-fake-glass-source] (object-fit: cover)
 * и карточки [data-js-fake-glass-item]. Размытая картинка и слой в карточке — в CSS (var --glass-*),
 * включаются классом is-fake-glass на области; без JS остаётся обычный backdrop-filter.
 * Позиции пересчитываются при ресайзе, по событию drag-slider:update (лента отзывов)
 * и каждый кадр, пока область в кадре (карточки benefits выезжают по скроллу).
 */

const ACTIVE_CLASS = 'is-fake-glass';

// Прямоугольник, который реально занимает картинка при object-fit: cover (object-position: center)
function coverRect(img) {
  const box = img.getBoundingClientRect();
  const naturalW = img.naturalWidth || Number(img.getAttribute('width'));
  const naturalH = img.naturalHeight || Number(img.getAttribute('height'));
  const scale = Math.max(box.width / naturalW, box.height / naturalH);
  const width = naturalW * scale;
  const height = naturalH * scale;

  return {
    left: box.left + (box.width - width) / 2,
    top: box.top + (box.height - height) / 2,
    width,
    height,
  };
}

function setup(area) {
  const source = area.querySelector('[data-js-fake-glass-source]');
  const items = [...area.querySelectorAll('[data-js-fake-glass-item]')];
  if (!source || !items.length) return;

  let frame = 0;

  const update = () => {
    frame = 0;
    const bg = coverRect(source);

    items.forEach((item) => {
      const rect = item.getBoundingClientRect();
      item.style.setProperty('--glass-x', `${bg.left - rect.left}px`);
      item.style.setProperty('--glass-y', `${bg.top - rect.top}px`);
      item.style.setProperty('--glass-w', `${bg.width}px`);
      item.style.setProperty('--glass-h', `${bg.height}px`);
    });
  };

  const requestUpdate = () => {
    if (!frame) frame = requestAnimationFrame(update);
  };

  update();
  area.classList.add(ACTIVE_CLASS);

  new ResizeObserver(requestUpdate).observe(area);
  source.addEventListener('load', requestUpdate);
  area.addEventListener('drag-slider:update', update);

  // Карточки могут двигаться по скроллу со сглаживанием (cards-rise.js, scrub) — и после остановки скролла.
  // Пока область в кадре, пересчитываем каждый кадр тикера GSAP (после отрисовки анимаций): 5–10 замеров на кадр
  new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting) gsap.ticker.add(update);
    else gsap.ticker.remove(update);
  }).observe(area);
}

export function initFakeGlass() {
  document.querySelectorAll('[data-js-fake-glass]').forEach(setup);
}
