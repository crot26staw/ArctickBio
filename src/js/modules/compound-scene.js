import { gsap, prefersReducedMotion } from './motion.js';
import { createVideoScrubber } from './scroll-video.js';

/**
 * Сцена «Состава» (комментарии дизайнера к макетам 18392:67399 → 18126:48398 → 18126:48397):
 * экран сцены стоит на месте (CSS sticky в высокой секции [data-js-compound], длина — $compound-pin в _compound.scss),
 * всё идёт по скроллу одной шкалой: видео стика [data-js-compound-video] медленно проигрывается; пока стик цельный —
 * из его центра выезжают цифры [data-js-compound-stat] и проявляются; когда стик раскрывается, цифры гаснут
 * и тем же способом появляются бейджи витаминов [data-js-compound-vitamin]. В конце пауза, затем наезжает video.
 * Шкала — от 'top top' (экран прилип) до 'bottom 200%' (начинается наезд video), доли — в SCENE.
 * Сдвиг — transform (x/y); центровка витаминов в CSS — свойством translate, поэтому не конфликтует.
 * При reduced motion — сразу итог: витамины, последний кадр видео, цифр нет.
 */
const SCENE = {
  video: [0.04, 0.9], // видео целиком; раскрытие начинается на 52% видео — ≈ 0.49 шкалы
  statsIn: [0.08, 0.3],
  statsOut: [0.44, 0.52], // гаснут, когда стик начинает раскрываться
  vitaminsIn: [0.52, 0.8], // по одному, шаг — vitaminsStagger
  vitaminsStagger: 0.03,
  from: 0.85, // доля пути от центра стика, с которой элемент выезжает (1 — из самого центра)
  scale: 0.8,
  ease: 'power2.out',
  scrub: 0.6,
};

function initCompoundScene(section) {
  const video = section.querySelector('[data-js-compound-video]');
  const sachet = section.querySelector('[data-js-compound-sachet]');
  const stats = [...section.querySelectorAll('[data-js-compound-stat]')];
  const vitamins = [...section.querySelectorAll('[data-js-compound-vitamin]')];
  if (!video || !sachet) return;

  const setVideo = createVideoScrubber(video);

  if (prefersReducedMotion()) {
    setVideo(1);
    gsap.set(stats, { autoAlpha: 0 });
    return;
  }

  // Сдвиг элемента к центру стика (по offset*, не зависит от transform)
  const toCenter = (el, axis) => {
    const center = axis === 'x' ? sachet.offsetLeft : sachet.offsetTop; // у рамки стика центр — в left/top (translate -50%)
    const own = axis === 'x' ? el.offsetLeft + el.offsetWidth / 2 : el.offsetTop + el.offsetHeight / 2;
    return (center - own) * SCENE.from;
  };
  const fromCenter = (items) => ({
    x: (i) => toCenter(items[i], 'x'),
    y: (i) => toCenter(items[i], 'y'),
    scale: SCENE.scale,
    autoAlpha: 0,
  });
  const shown = { x: 0, y: 0, scale: 1, autoAlpha: 1, ease: SCENE.ease };
  const span = ([a, b]) => b - a;

  // У раскладки (stagger) начальное состояние сразу получает только первый элемент — остальным задаём заранее
  gsap.set(vitamins, fromCenter(vitamins));

  const progress = { value: 0 };
  const tl = gsap.timeline({
    defaults: { ease: 'none' },
    scrollTrigger: {
      trigger: section,
      start: 'top top',
      end: 'bottom 200%',
      scrub: SCENE.scrub,
      invalidateOnRefresh: true,
    },
  });

  tl.fromTo(
    progress,
    { value: 0 },
    { value: 1, duration: span(SCENE.video), onUpdate: () => setVideo(progress.value) },
    SCENE.video[0],
  )
    .fromTo(stats, fromCenter(stats), { ...shown, duration: span(SCENE.statsIn) }, SCENE.statsIn[0])
    .to(stats, { autoAlpha: 0, scale: 0.95, duration: span(SCENE.statsOut) }, SCENE.statsOut[0])
    .fromTo(
      vitamins,
      fromCenter(vitamins),
      {
        ...shown,
        duration: span(SCENE.vitaminsIn) - SCENE.vitaminsStagger * (vitamins.length - 1),
        stagger: SCENE.vitaminsStagger,
      },
      SCENE.vitaminsIn[0],
    )
    .to({}, { duration: 0 }, 1);
}

export function initCompoundScenes() {
  document.querySelectorAll('[data-js-compound]').forEach(initCompoundScene);
}
