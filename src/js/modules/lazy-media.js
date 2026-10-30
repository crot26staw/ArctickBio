/**
 * Отложенная загрузка видео (оптимизация, 30.10.2026): у <video> нет loading="lazy", а poster браузер грузит всегда —
 * даже у видео далеко внизу страницы. Поэтому постер и данные видео — только когда его секция подошла к экрану (NEAR).
 *
 * <video data-js-lazy-media data-poster="…" data-poster-mobile="…"> — poster ставится, когда секция видео рядом с экраном;
 * на мобилке — data-poster-mobile, если есть (WordPress: уменьшенная копия из медиатеки — постер 2560 весил 260 КБ).
 * Следим за секцией, не за видео: видео бывает скрыто (плеер блока video — hidden, display: none), такое не пересекается.
 * whenNear(el, callback) — то же для своих модулей (scroll-video.js — прогрев видео стика в «Составе»).
 * loadOnTouch(strip) — горизонтальная лента: loading="lazy" внутри неё срабатывает, только когда картинка уже видна
 * (лента обрезает её по overflow), и при свайпе фото «моргает». При первом касании / прокрутке ленты — все её картинки сразу.
 */
const NEAR = '150% 0px'; // полтора экрана до видео — успевает загрузиться и на медленном интернете

export function whenNear(el, callback) {
  const target = el.closest('section') || el;
  const observer = new IntersectionObserver(
    ([entry]) => {
      if (!entry.isIntersecting) return;
      observer.disconnect();
      callback();
    },
    { rootMargin: NEAR },
  );
  observer.observe(target);
}

export function loadOnTouch(strip) {
  const load = () => {
    strip.querySelectorAll('img[loading="lazy"]').forEach((img) => (img.loading = 'eager'));
    ['pointerdown', 'scroll'].forEach((type) => strip.removeEventListener(type, load));
  };
  ['pointerdown', 'scroll'].forEach((type) => strip.addEventListener(type, load, { passive: true }));
}

const mobileQuery = window.matchMedia('(max-width: 767px)');

export function setLazyPoster(video) {
  const poster = (mobileQuery.matches && video.dataset.posterMobile) || video.dataset.poster;
  if (poster) video.poster = poster;
}

export function initLazyMedia() {
  document.querySelectorAll('[data-js-lazy-media]').forEach((video) => {
    whenNear(video, () => setLazyPoster(video));
  });
}
