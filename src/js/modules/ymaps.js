let loading = null;

/**
 * Яндекс Карты JS API 2.1: скрипт подключается один раз, по первому запросу (попап пункта выдачи).
 * Ключ — «Настройки сайта → Доставка» (window.arctik.pickup.mapKey); без ключа карта работает с ограничениями Яндекса.
 */
export function loadYmaps(apiKey = '') {
  loading ??= new Promise((resolve, reject) => {
    const script = document.createElement('script');
    const params = new URLSearchParams({ lang: 'ru_RU' });
    if (apiKey) params.set('apikey', apiKey);
    script.src = `https://api-maps.yandex.ru/2.1/?${params}`;
    script.async = true;
    script.onload = () => window.ymaps.ready(() => resolve(window.ymaps));
    script.onerror = () => {
      loading = null;
      reject(new Error('Яндекс Карты не загрузились'));
    };
    document.head.append(script);
  });

  return loading;
}
