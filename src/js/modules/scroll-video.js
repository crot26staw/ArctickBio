import { gsap, prefersReducedMotion } from './motion.js';

/**
 * Видео, которое проматывает скролл (сейчас — стик в «Составе»: саше раскрывается, пока блок выезжает на экран).
 * Разметка: <video data-js-scroll-video muted playsinline preload="auto"> внутри секции.
 *   [data-scroll-video-start] / [data-scroll-video-end] — свой путь (start / end ScrollTrigger по секции),
 *   по умолчанию SCROLL_VIDEO.start → SCROLL_VIDEO.end: от появления секции до момента, когда она встала у верха экрана.
 * Плавная перемотка возможна, только если каждый кадр ключевой (ffmpeg: -g 1) — иначе каждый seek декодирует группу кадров.
 * Новый seek — только когда закончился предыдущий (seeked), иначе браузер копит очередь и видео отстаёт от скролла.
 * При reduced motion — сразу последний кадр.
 */
const SCROLL_VIDEO = {
  // Саше начинает раскрываться на 52% видео (кадр 51 из 97) — когда верх секции на ~14% высоты экрана (по просьбе пользователя)
  start: 'top 30%',
  end: 'top top',
  scrub: 0.4,
};

function initScrollVideo(video) {
  const section = video.closest('section') || video.parentElement;
  let target = 0;

  const seek = () => {
    if (!video.duration || video.seeking) return;
    // Последний кадр — чуть раньше конца: currentTime = duration у части браузеров показывает пустой кадр
    const time = Math.min(target * video.duration, video.duration - 0.05);
    if (Math.abs(video.currentTime - time) > 0.01) video.currentTime = time;
  };

  video.addEventListener('seeked', seek);
  video.addEventListener('loadedmetadata', seek);

  // iOS Safari не грузит видео без воспроизведения: короткий play/pause (видео без звука — разрешено)
  video.play()?.then(() => {
    video.pause();
    seek();
  }).catch(() => {});

  if (prefersReducedMotion()) {
    target = 1;
    seek();
    return;
  }

  const progress = { value: 0 };
  gsap.to(progress, {
    value: 1,
    ease: 'none',
    scrollTrigger: {
      trigger: section,
      start: video.dataset.scrollVideoStart || SCROLL_VIDEO.start,
      end: video.dataset.scrollVideoEnd || SCROLL_VIDEO.end,
      scrub: SCROLL_VIDEO.scrub,
    },
    onUpdate: () => {
      target = progress.value;
      seek();
    },
  });
}

export function initScrollVideos() {
  document.querySelectorAll('[data-js-scroll-video]').forEach(initScrollVideo);
}
