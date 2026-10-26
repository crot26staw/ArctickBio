import { gsap, prefersReducedMotion } from './motion.js';
import { getLenis } from './smooth-scroll.js';

/**
 * Блок video: превью и полное видео.
 * Превью [data-js-video-preview] — без звука, по кругу; грузится и играет, только пока блок рядом с экраном
 * (при prefers-reduced-motion не играет — остаётся постер).
 * «Смотреть» [data-js-video-open] или клик мышью в блоке — плеер [data-js-video-player] раскрывается из карточки превью
 * на весь блок (clip-path; на планшете/мобилке карточки нет — из нажатой кнопки), и полное видео [data-js-video-main] играет со звуком.
 * Закрывают крестик [data-js-video-close] и Escape — плеер сжимается обратно. Пока открыт, Lenis остановлен.
 */

const OPEN = { duration: 0.9, ease: 'power3.inOut' };
const CLOSE = { duration: 0.7, ease: 'power3.inOut' };
const OPEN_CLASS = 'is-video-open';

// clip-path плеера по прямоугольнику элемента внутри секции
function insetOf(el, section) {
  const box = section.getBoundingClientRect();
  const rect = el.getBoundingClientRect();
  const radius = getComputedStyle(el).borderRadius || '0px';
  return `inset(${rect.top - box.top}px ${box.right - rect.right}px ${box.bottom - rect.bottom}px ${rect.left - box.left}px round ${radius})`;
}

const FULL = 'inset(0px 0px 0px 0px round 0px)';

function initPreview(section, preview) {
  if (!preview || prefersReducedMotion()) return;

  const observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) preview.play().catch(() => {});
      else preview.pause();
    },
    { rootMargin: '200px 0px' },
  );
  observer.observe(section);
}

function initVideo(section) {
  const player = section.querySelector('[data-js-video-player]');
  const main = section.querySelector('[data-js-video-main]');
  const preview = section.querySelector('[data-js-video-preview]');
  if (!player || !main) return;

  initPreview(section, preview);

  // Плеер раскрывается из карточки превью; на планшете/мобилке её нет (display: none) — из нажатой кнопки
  const card = preview?.closest('[data-js-video-open]');
  let source = null; // откуда раскрылся плеер (туда же и сжимается)
  const reduced = prefersReducedMotion();

  const open = (trigger) => {
    if (section.classList.contains(OPEN_CLASS)) return;
    source = card && card.getClientRects().length ? card : trigger;
    player.hidden = false;
    section.classList.add(OPEN_CLASS);

    // Блок виден не целиком — доводим до него (clip-path считается от секции, поэтому раскрытию это не мешает), потом стоп
    const lenis = getLenis();
    if (lenis) lenis.scrollTo(section, { duration: OPEN.duration, lock: true, force: true, onComplete: () => section.classList.contains(OPEN_CLASS) && lenis.stop() });
    else section.scrollIntoView({ block: 'start' });

    // play() — сразу в обработчике клика, иначе браузер не даст звук
    main.currentTime = 0;
    main.muted = false;
    main.play().catch(() => {});
    preview?.pause();

    const onOpened = () => {
      main.controls = true;
      player.querySelector('[data-js-video-close]').focus({ preventScroll: true });
    };

    if (reduced) {
      onOpened();
      return;
    }

    gsap.fromTo(player, { clipPath: insetOf(source, section) }, { clipPath: FULL, ...OPEN, onComplete: onOpened });
  };

  const close = () => {
    if (!section.classList.contains(OPEN_CLASS)) return;
    main.controls = false;
    section.classList.remove(OPEN_CLASS);

    const onClosed = () => {
      main.pause();
      player.hidden = true;
      gsap.set(player, { clearProps: 'clipPath' });
      getLenis()?.start();
      if (preview && !reduced) preview.play().catch(() => {});
      source?.focus({ preventScroll: true });
    };

    if (reduced || !source) {
      onClosed();
      return;
    }

    gsap.to(player, { clipPath: insetOf(source, section), ...CLOSE, onComplete: onClosed });
  };

  const triggers = [...section.querySelectorAll('[data-js-video-open]')];
  triggers.forEach((trigger) => {
    trigger.addEventListener('click', () => open(trigger));
  });

  // Клик мышью в любом месте блока (курсор скрыт, под ним — кнопка у курсора, cursor-follow.js) — открываем,
  // даже если кнопка ещё догоняет курсор
  const follower = section.querySelector('[data-js-cursor-follow]');
  section.addEventListener('click', (event) => {
    if (!follower || !section.classList.contains('is-cursor-follow')) return; // только мышь (класс ставит cursor-follow.js)
    if (event.target.closest('[data-js-video-open], [data-js-video-player], a, button')) return;
    open(follower);
  });
  player.querySelector('[data-js-video-close]').addEventListener('click', close);
  main.addEventListener('ended', close);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') close();
  });
}

export function initVideos() {
  document.querySelectorAll('[data-js-video]').forEach(initVideo);
}
