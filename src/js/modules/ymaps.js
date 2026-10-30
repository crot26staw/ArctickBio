let loading = null;

// Не загрузились за это время — считаем, что карт нет (скрипт мог повиснуть без onerror)
const TIMEOUT = 10000;

/**
 * Яндекс Карты JS API 2.1: скрипт подключается один раз, по первому запросу (попап пункта выдачи).
 * Ключ — «Настройки сайта → Доставка» (window.arctik.pickup.mapKey); без ключа карта работает с ограничениями Яндекса.
 * Ошибка загрузки, ошибка ymaps.ready (неверный ключ) или TIMEOUT — reject.
 */
export function loadYmaps(apiKey = '') {
  loading ??= new Promise((resolve, reject) => {
    const script = document.createElement('script');
    const params = new URLSearchParams({ lang: 'ru_RU' });
    if (apiKey) params.set('apikey', apiKey);
    script.src = `https://api-maps.yandex.ru/2.1/?${params}`;
    script.async = true;
    const timer = setTimeout(() => reject(new Error('Яндекс Карты не загрузились за отведённое время')), TIMEOUT);
    script.onload = () =>
      window.ymaps.ready(
        () => {
          clearTimeout(timer);
          resolve(window.ymaps);
        },
        (error) => {
          clearTimeout(timer);
          reject(error);
        },
      );
    script.onerror = () => {
      clearTimeout(timer);
      loading = null;
      reject(new Error('Яндекс Карты не загрузились'));
    };
    document.head.append(script);
  });

  return loading;
}
