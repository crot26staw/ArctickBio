import { getLenis } from './smooth-scroll.js';
import { prefersReducedMotion } from './motion.js';

const OPEN_CLASS = 'is-menu-open';
const DARK_CLASS = 'is-menu-dark';
const DARK_DELAY = 750; // мс: панель меню (въезжает справа, _mobile-menu.scss) накрыла шапку — шапка в тёмную тему
const LOCK_CLASS = 'is-scroll-locked';
const DESKTOP = window.matchMedia('(min-width: 1200px)');

/**
 * Меню планшета и мобилки (макет 18129:38632): бургер [data-js-mobile-menu-toggle] открывает [data-js-mobile-menu].
 * Класс is-menu-open — на шапке (появление меню), is-menu-dark — тёмная тема шапки: при открытии с задержкой,
 * когда панель её накрыла, при закрытии снимается сразу. Пока меню открыто, страница не скроллится
 * (Lenis stop, без Lenis — overflow у html). Закрывается бургером, Escape, переходом по ссылке и при выходе на десктоп.
 */
export function initMobileMenu() {
  const header = document.querySelector('[data-js-header]');
  const toggle = document.querySelector('[data-js-mobile-menu-toggle]');
  const menu = document.querySelector('[data-js-mobile-menu]');
  if (!header || !toggle || !menu) return;

  const isOpen = () => header.classList.contains(OPEN_CLASS);
  let darkTimer;

  const setOpen = (open) => {
    header.classList.toggle(OPEN_CLASS, open);
    clearTimeout(darkTimer);
    if (open) darkTimer = setTimeout(() => header.classList.add(DARK_CLASS), prefersReducedMotion() ? 0 : DARK_DELAY);
    else header.classList.remove(DARK_CLASS);
    document.documentElement.classList.toggle(LOCK_CLASS, open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
    menu.inert = !open;

    if (open) getLenis()?.stop();
    else getLenis()?.start();
  };

  toggle.addEventListener('click', () => setOpen(!isOpen()));

  menu.addEventListener('click', (event) => {
    if (event.target.closest('a')) setOpen(false);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || !isOpen()) return;
    setOpen(false);
    toggle.focus();
  });

  DESKTOP.addEventListener('change', (event) => {
    if (event.matches && isOpen()) setOpen(false);
  });
}
