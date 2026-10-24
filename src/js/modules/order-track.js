const CURRENT_CLASS = 'is-current';

/**
 * Шкала статусов заказа (partials/order/status.html): data-step у [data-js-order-track] — текущий шаг (с 1).
 * Текущему шагу — is-current, градиентная «капсула» (::before) ставится за текущим и следующим шагом (у последнего — за двумя последними),
 * как в макете 18281:47397 / 18282:57850. Высота шагов зависит от переносов строк, поэтому позиция считается по факту.
 */
function initTrack(track) {
  const steps = [...track.querySelectorAll('[data-js-order-track-step]')];
  const current = Math.min(Math.max(Number(track.dataset.step) || 1, 1), steps.length) - 1;
  const from = steps[Math.min(current, steps.length - 2)];
  const to = steps[Math.min(current + 1, steps.length - 1)];

  steps.forEach((step, index) => step.classList.toggle(CURRENT_CLASS, index === current));

  const place = () => {
    track.style.setProperty('--track-top', `${from.offsetTop}px`);
    track.style.setProperty('--track-height', `${to.offsetTop + to.offsetHeight - from.offsetTop}px`);
  };

  place();
  new ResizeObserver(place).observe(track);
}

export function initOrderTracks() {
  document.querySelectorAll('[data-js-order-track]').forEach(initTrack);
}
