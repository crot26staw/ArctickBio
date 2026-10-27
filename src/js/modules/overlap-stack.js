/**
 * Наезд на секцию выше экрана (.overlap-stack--tall, см. scss/blocks/_overlap-stack.scss): первая секция прилипает,
 * когда её низ дошёл до низа экрана — top = 100svh − её высота. Высоту CSS не знает, поэтому модуль отдаёт её
 * переменной --overlap-height у секции и обновляет при изменении размера.
 */
function initStack(stack) {
  const first = stack.firstElementChild;
  if (!first) return;

  const update = () => first.style.setProperty('--overlap-height', `${first.offsetHeight}px`);

  update();
  new ResizeObserver(update).observe(first);
}

export function initOverlapStacks() {
  document.querySelectorAll('[data-js-overlap-stack]').forEach(initStack);
}
