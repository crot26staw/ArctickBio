/**
 * Меню разделов страницы ([data-js-anchor-nav] со ссылками href="#id"): активна ссылка раздела,
 * верх которого последним прошёл линию ACTIVE_LINE экрана. Позиции считаются при скролле, а не заранее —
 * высота разделов меняется, когда раскрываются аккордеоны.
 * Плавный переход по ссылке делает Lenis (anchors: true), отступ под шапку — scroll-margin-top у раздела.
 */
const ACTIVE_CLASS = 'is-active';
const ACTIVE_LINE = 0.4; // доля высоты экрана

export function initAnchorNavs() {
  document.querySelectorAll('[data-js-anchor-nav]').forEach((nav) => {
    const items = [...nav.querySelectorAll('a[href^="#"]')]
      .map((link) => ({ link, target: document.querySelector(link.getAttribute('href')) }))
      .filter(({ target }) => target);
    if (!items.length) return;

    let current = null;
    let frame = 0;

    const update = () => {
      frame = 0;
      const line = window.innerHeight * ACTIVE_LINE;
      const active = items.reduce(
        (found, item) => (item.target.getBoundingClientRect().top <= line ? item : found),
        items[0],
      );
      if (active === current) return;

      current = active;
      items.forEach(({ link }) => {
        const isActive = link === active.link;
        link.classList.toggle(ACTIVE_CLASS, isActive);
        if (isActive) link.setAttribute('aria-current', 'true');
        else link.removeAttribute('aria-current');
      });
    };

    const requestUpdate = () => {
      if (!frame) frame = requestAnimationFrame(update);
    };

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate);
    update();
  });
}
