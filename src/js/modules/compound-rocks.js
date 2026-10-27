/**
 * Камни «Состава» не заходят на стик (просьба пользователя): камни прижаты к низу экрана, а стик стоит в сцене под заголовком,
 * поэтому на невысоких экранах впадина между камнями оказывалась выше низа стика. Камни [data-js-compound-rocks]
 * опускаются (CSS-переменная --rocks-shift, translate) ровно настолько, чтобы впадина была ниже стика [data-js-compound-sachet]
 * на ROCKS.gap; если и так ниже — остаются на месте. Лишнее уходит за низ секции (overflow: hidden).
 * Пересчёт — при изменении размера секции.
 */
const ROCKS = {
  dip: 0.578, // верх камней под стиком — доля высоты блока камней (впадина в картинках rock-bottom*.webp)
  gap: 40, // от низа рамки стика до впадины, px макета (rem)
};

function initRocks(rocks) {
  const section = rocks.closest('section');
  const sachet = section?.querySelector('[data-js-compound-sachet]');
  if (!sachet) return;

  const update = () => {
    const rem = parseFloat(getComputedStyle(document.documentElement).fontSize) / 16;
    const current = parseFloat(rocks.style.getPropertyValue('--rocks-shift')) || 0;
    const box = rocks.getBoundingClientRect();
    const dipTop = box.top - current + box.height * ROCKS.dip;
    const shift = Math.max(0, sachet.getBoundingClientRect().bottom + ROCKS.gap * rem - dipTop);

    rocks.style.setProperty('--rocks-shift', `${Math.round(shift)}px`);
  };

  update();
  new ResizeObserver(update).observe(section);
}

export function initCompoundRocks() {
  document.querySelectorAll('[data-js-compound-rocks]').forEach(initRocks);
}
