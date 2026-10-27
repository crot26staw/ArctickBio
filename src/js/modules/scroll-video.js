import { gsap, prefersReducedMotion } from './motion.js';

/**
 * Видео, которое проматывает скролл.
 * createVideoScrubber(video) — функция «показать долю видео 0…1»: её вызывает своя шкала (сейчас — compound-scene.js, стик в «Составе»).
 * [data-js-scroll-video] — самостоятельное видео со своим ScrollTrigger по секции:
 *   [data-scroll-video-start] / [data-scroll-video-end] — свой путь, по умолчанию SCROLL_VIDEO.start → SCROLL_VIDEO.end.
 * Плавная перемотка возможна, только если каждый кадр ключевой (ffmpeg: -g 1) — иначе каждый seek декодирует группу кадров.
 * Новый seek — только когда закончился предыдущий (seeked), иначе браузер копит очередь и видео отстаёт от скролла.
 * При reduced motion — сразу последний кадр.
 */
const SCROLL_VIDEO = {
  start: 'top 30%',
  end: 'top top',
  scrub: 0.4,
};

export function createVideoScrubber(video) {
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
  video
    .play()
    ?.then(() => {
      video.pause();
      seek();
    })
    .catch(() => {});

  return (progress) => {
    target = Math.min(Math.max(progress, 0), 1);
    seek();
  };
}

function initScrollVideo(video) {
  const section = video.closest('section') || video.parentElement;
  const setProgress = createVideoScrubber(video);

  if (prefersReducedMotion()) {
    setProgress(1);
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
    onUpdate: () => setProgress(progress.value),
  });
}

export function initScrollVideos() {
  document.querySelectorAll('[data-js-scroll-video]').forEach(initScrollVideo);
}
