import { closeModal } from './modal.js';

const COOKIE_NAME = 'arctik_cookie_consent';
const MAX_AGE = 365 * 24 * 60 * 60;
const VISIBLE_CLASS = 'is-visible';
const HIDE_DELAY = 400; // = переход .cookies в blocks/_cookies.scss

// Выбор посетителя: список разрешённых категорий через запятую (necessary всегда есть), нет cookie — выбора ещё не было
export function getCookieConsent() {
  const match = document.cookie.match(new RegExp(`(?:^|; )${COOKIE_NAME}=([^;]*)`));
  return match ? decodeURIComponent(match[1]).split(',') : null;
}

// Для скриптов аналитики и рекламы: подключать, только если категория разрешена (и по событию cookies:change)
export function hasCookieConsent(category) {
  return getCookieConsent()?.includes(category) ?? false;
}

function saveConsent(categories) {
  const secure = location.protocol === 'https:' ? '; Secure' : '';
  document.cookie = `${COOKIE_NAME}=${encodeURIComponent(categories.join(','))}; max-age=${MAX_AGE}; path=/; SameSite=Lax${secure}`;
  document.dispatchEvent(new CustomEvent('cookies:change', { detail: { categories } }));
}

/**
 * Баннер cookie (partials/cookies.html, стили blocks/_cookies.scss): [data-js-cookies] — полоса внизу экрана,
 * выезжает, если выбора ещё нет. «Хорошо» [data-js-cookies-accept] разрешает все категории.
 * Окно настроек — попап modal.js ([data-js-modal="cookies"]), форма [data-js-cookies-form]: переключатели по категориям,
 * отправка сохраняет выбор и закрывает окно и баннер; закрыли без сохранения — переключатели вернутся к сохранённому.
 */
export function initCookies() {
  const banner = document.querySelector('[data-js-cookies]');
  const form = document.querySelector('[data-js-cookies-form]');
  if (!form) return;

  const inputs = [...form.querySelectorAll('input[type="checkbox"]')];

  const syncInputs = () => {
    const consent = getCookieConsent();
    inputs.forEach((input) => {
      if (!input.disabled) input.checked = consent?.includes(input.name) ?? false;
    });
  };

  // В WordPress полосы нет, если выбор уже сделан (template-parts/cookies.php), окно настроек остаётся
  const hideBanner = () => {
    if (!banner || banner.hidden) return;
    banner.classList.remove(VISIBLE_CLASS);
    // По таймеру, а не transitionend: без перехода (reduced motion) событие не придёт
    setTimeout(() => (banner.hidden = true), HIDE_DELAY);
  };

  const save = (categories) => {
    saveConsent(categories);
    hideBanner();
  };

  syncInputs();

  if (banner && !getCookieConsent()) {
    banner.hidden = false;
    requestAnimationFrame(() => requestAnimationFrame(() => banner.classList.add(VISIBLE_CLASS)));
  }

  banner?.querySelector('[data-js-cookies-accept]')?.addEventListener('click', () => {
    save(inputs.map((input) => input.name));
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    save(inputs.filter((input) => input.checked).map((input) => input.name));
    closeModal();
  });

  form.closest('[data-js-modal]').addEventListener('modal:open', syncInputs);
}
