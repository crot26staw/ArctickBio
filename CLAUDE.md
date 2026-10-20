# АрктикБио — вёрстка сайта

## О проекте
Статическая вёрстка сайта **АрктикБио** (бренд АрктикБиоМарин, морской коллаген) на сборщике **Vite**. Готовая вёрстка в дальнейшем натягивается на **WordPress + WooCommerce** (отдельный этап, делается не здесь). Всё, что верстаем, должно легко переноситься в PHP-шаблоны темы.

Общение с пользователем — на русском языке.

## Порядок работы (важно)
1. Страницы верстаются **по одной**, в порядке, который задаёт пользователь (список страниц он даёт по ходу работы).
2. Сначала только **десктоп (1440)**. Планшет и мобилку **не делаем**, пока пользователь не проверит десктоп и явно не даст команду на адаптив.
3. После команды на адаптив — адаптируем под планшет, затем под мобилку.
4. Не переходить к следующей странице без подтверждения пользователя.
5. Если в макете что-то неясно (состояния, ховеры, отсутствующие тексты/картинки, анимации) — спрашивать, а не додумывать.
6. После вёрстки секции — сверять с макетом визуально (скриншот страницы vs `get_screenshot` из Figma).

## Статус страниц
Обновлять этот список по мере работы.

| Страница | Файл | Figma node | Десктоп | Планшет | Мобилка |
|---|---|---|---|---|---|
| Главная | `src/index.html` | `18541:70747` | на проверке | — | — |

Статусы: `в работе` / `на проверке` / `принято`.

## Открытые вопросы к пользователю
- Анимации GSAP: заголовки сделаны; какие ещё сцены анимировать (саше из блока «О бренде» → пачка, состав, процесс и т.д.) — пользователь описывает в комментариях к макету.
- Видео в блоке «Мы собираем ягоды»: ссылка/файл и как открывать (модалка?).
- Ответы для FAQ (в макете есть только один текст, он подставлен во все пункты).
- Лента товаров в футере — статичный ряд или слайдер/бегущая строка (отзывы уже листаются перетаскиванием).
- Слайдер товаров в hero: 5 вкусов из меню футера (брусника, черника, голубика, клюква, морошка), у всех одна картинка и цена 1440₽ — нужны реальные фото/цены. Точки под фото — пагинация слайдера (Swiper Pagination в `.hero__products-dots`, свои точки карточек скрыты); точек = число товаров (в макете 3).
- «Исследования», пункт 4: ссылки на PDF-документы пока `#`, размер «8.5 MB» из макета; ховер документа в макете не задан.
- Попапы шагов «Процесса» (`partials/production-step.html`) сделаны у всех 4 шагов, но в макете есть только попап шага 3 (заголовок «Измельчение», хотя шаг — «Сортировка»). У шагов 1, 2, 4 заголовок = название шага, описание и фото — заглушки из шага 3. Нужен реальный контент.

## Стек
- Vite 8 (multi-page): `root` = `src/`, каждая страница — отдельный `.html` в `src/`, все прописаны в `vite.config.js` → `pages`.
- SCSS (модульный синтаксис `@use`), минификация Lightning CSS (встроена в Vite 8).
- Ванильный JS (ES-модули), без фреймворков. GSAP для анимаций. Слайдеры — только после согласования, предпочтительно Swiper (установлен; CSS подключается в `main.scss` в секции Vendor, не из JS — иначе перебивает наши стили).
- Partials — собственный плагин `vite/html-include.js` (см. ниже).

## Partials (vite/html-include.js)
```html
<include src="partials/header.html"></include>
<include src="partials/product-card.html" data='{"title": "Коллаген", "price": "1440₽"}'></include>
```
- Путь `src` — от `src/`. В partial переменные пишутся как `{{ title }}`, незаполненные → пустая строка.
- `data` — JSON в **одинарных** кавычках, поэтому внутри текста не использовать `'`.
- Partials могут подключать другие partials. При изменении partial дев-сервер делает full reload.
- Секции страницы лежат в `partials/<страница>/` (например `partials/home/hero.html`) — это будущие template parts.

## Структура
```
src/
  index.html                  — главная
  partials/
    header.html, footer.html  — общие
    mini-card.html, product-card.html, review-card.html, accordion-item.html — компоненты
    home/                     — секции главной
  scss/
    base/        — _functions (rem), _variables (+ CSS custom properties), _mixins (медиа, типографика), _fonts, _reset, _global
    components/  — кнопки, лого, карточки, соцсети
    blocks/      — секции и шапка/подвал
    main.scss    — точка входа (подключает всё через @use)
  js/
    main.js      — точка входа
    modules/     — header.js (шапка position: fixed; is-scrolled → кнопки-плашки «стекло»;
                   data-theme = data-header-theme секции под шапкой: light → белые плашки, тёмный текст/лого.
                   У КАЖДОЙ новой секции ставить data-header-theme="light|dark". Макет: ноды 18115:24184, 18181:42374, 18117:25912),
                   motion.js (GSAP + ScrollTrigger + prefersReducedMotion),
                   accordion.js (в [data-js-accordion-group] открыт только один пункт),
                   drag-slider.js (лента, листаемая зажатием/свайпом: GSAP Draggable + Inertia)
                   product-slider.js (Swiper: товары в hero, loop + autoplay + fade, [data-js-product-slider];
                   стекло — у рамки .hero__products, карточки внутри — .mini-card--bare)
                   research.js (список «Исследований»: открыт один пункт, по умолчанию первый; раскрывается при наведении/фокусе/тапе,
                   анимация в CSS через grid-template-rows 0fr↔1fr. Макет состояний: 18126:48798–18126:48801)
                   number-ticker.js (счётчик 0 → data-value, как magicui number-ticker: GSAP expo.out 1.6s;
                   разметка <span data-js-number-ticker data-value="98">98</span>, в research — при появлении и при раскрытии пункта)
                   cards-rise.js (карточки выезжают снизу лесенкой с проявлением по скроллу, как в about: y 120, stagger 0.1, scrub;
                   [data-js-cards-rise] > [data-js-cards-rise-item]; сейчас в benefits, макет 18126:51002)
                   swipe-button.js (ховер всех кнопок как animata swipe-button: содержимое уезжает вверх, снизу въезжают копия и фон --swipe-bg;
                   [data-js-swipe] на кнопке, [data-js-swipe-group] — все ссылки внутри (меню), [data-swipe-trigger] — ховер предка,
                   [data-swipe-static] — не двигать (бейдж корзины). Стили components/_swipe.scss. НОВЫЕ КНОПКИ: ставить data-js-swipe и --swipe-bg вместо hover-фона)
  assets/
    images/  — webp (фото), logo.png (маска логотипа)
    icons/   — svg
    fonts/   — woff2
public/        — файлы, копируемые без обработки
vite/          — плагины сборки
```
В каждом SCSS-файле: `@use '../base' as *;` — даёт `rem()`, переменные и миксины.

## Правила вёрстки
- **БЭМ** для классов (`block__element--modifier`). Никаких стилей по тегам и id (кроме base/reset).
- Семантичная разметка, один `h1` на страницу.
- Цвета — CSS custom properties (`var(--color-accent)` и т.д.) из `_variables.scss`; шрифты, контейнер, брейкпоинты — SCSS-переменные.
- Размеры — `rem(px)` (функция из `_functions.scss`), `px` — для бордеров/мелочей.
- Типографика — миксины из `_mixins.scss`, названия как стили в Figma: `h1`, `h2`, `h3`, `body-l`, `display-l`, `display-s`, `body-m`, `body-m-strong`, `body-s`, `body-s-strong`, `desc-l-strong`, `desc-m`, `desc-s`.
- Контейнер `.container`: max-width 1440, отступы по 50px.
- Сцены с абсолютным позиционированием из макета (пачка с саше, состав, процесс) — позиции в rem от «сцены» 1440.
- **desktop-first**: адаптив — `@include media(tablet)` / `@include media(mobile)` (max-width 1199 / 767).
- Ховеры — через миксин `@include hover { ... }` (только для устройств с hover).
- Картинки: `<img>` с `alt`, `width`/`height`, `loading="lazy"` ниже первого экрана. Фото — webp (конвертировать через sharp, ресайзить под размер показа ×2), иконки — svg.
- JS вешается на `data-js-*` атрибуты, не на БЭМ-классы.
- Тексты берём из макета, не lorem ipsum.
- **Не писать `-webkit-backdrop-filter` вручную**: Lightning CSS склеивает его с `backdrop-filter` и выкидывает непрефиксную версию — размытие пропадает. Префиксы он расставляет сам.

## Подготовка под WordPress / WooCommerce
- Каждый повторяющийся блок — отдельный partial (будущий template part).
- Динамический контент — один шаблон элемента, повторяемый в цикле.
- Меню — разметка `wp_nav_menu`: `ul.menu > li.menu-item > a`, классы `current-menu-item`, `menu-item-has-children`, `sub-menu`.
- Карточки товаров — классы WooCommerce: `ul.products > li.product`, `.woocommerce-loop-product__title`, `.price`, `.button.add_to_cart_button.ajax_add_to_cart` с `data-product_id`.
- Формы — простая разметка под Contact Form 7 или аналог.
- Пути к ассетам — относительные (`./assets/...`), `base: './'`.

## Команды
```
npm install       — установка зависимостей
npm run dev       — дев-сервер
npm run build     — сборка в dist/
npm run preview   — просмотр сборки
```

## Макет (Figma)
- Файл: https://www.figma.com/design/BsTrHOa11XeyAQDDrcNB9N/АрктикБио (fileKey `BsTrHOa11XeyAQDDrcNB9N`)
- Работа через Figma MCP: перед `get_design_context` загружать скилл `figma:figma-design-to-code`. Вывод MCP — референс (React+Tailwind), переводить в SCSS + БЭМ.
- Страница целиком слишком большая для `get_design_context` — вызывать с `forceCode: true` (результат сохраняется в файл) или по секциям.
- **Подвох MCP**: вложенные инстансы иконок часто экспортируются как иконка корзины. Всегда сверять иконки со скриншотом и при необходимости запрашивать `get_design_context` на конкретную ноду иконки (например `I16653:101521;18124:39200`).
- Режим заливки картинок (fill / fit / crop) MCP передаёт неточно — сверять со скриншотом. Например, `object-bottom size-full` без `object-*` в макете бывает fill (растянуть).
- Ноды секций главной: hero `16647:94085`, about `18117:26183`, product-animation `18117:26306`, catalog `16653:101439`, compound `18123:31322`, video `16653:97450`, research `16653:97661`, production `18631:79585`, comparison `18123:32786`, usage `16715:156280`, benefits `16653:113874`, faq `18124:40859`, review `18124:40858`, footer `16447:40536`.

## Брейкпоинты
- Десктоп — макет **1440**
- Планшет — макет **768**
- Мобилка — макет **375**

## Анимации
- **GSAP** (`gsap` + ScrollTrigger), импорт через `js/modules/motion.js`. Анимации — отдельными модулями в `js/modules/`, инициализация по `data-js-*`.
- Учитывать `prefers-reduced-motion` (`prefersReducedMotion()` из `motion.js`).
- **Появление about** (комментарии в Figma): пока about поднимается от 80% экрана к верху, стик проявляется снизу из прозрачности, потом карточки (`data-js-sachet-journey-card`) выезжают снизу лесенкой. Всё по скроллу (scrub), фаза 1 в `sachet-journey.js`; двигает `<img>` стика, а перелёт — его обёртку.
- **Переход about → product-scene** (макет: 18126:51425, 18126:51426, 18126:51464): в `index.html` обёртка `.sachet-journey` вокруг двух секций, в ней летящий стик; `sachet-journey.js` (ScrollTrigger, scrub) переносит его в позицию `[data-js-sachet-journey-target]` в пачке с уменьшением. Слои через z-index: карточки about 3 > стик 2 > камни; передняя стенка пачки 3. У `.product-scene__product` и цели нельзя использовать transform (ломает слои и расчёт offset).
- **Заголовки** (h1/h2 секций): атрибут `data-js-title-reveal` → `title-reveal.js` (GSAP SplitText по строкам, эффект «mask reveal up» — референс animata.design/docs/text/mask-reveal-up: y 17px, blur 6px, opacity 0 → 0, 0.55s, stagger 0.065s, ease cubic-bezier(0.22,1,0.36,1); запуск при появлении, один раз). Ставить на все новые заголовки секций. До разбивки заголовок скрыт (класс `js` на `<html>` + `.is-split`). Внутрь такого заголовка не класть абсолютно позиционированные элементы — выносить в обёртку (как TM в «Процессе»).

## Шрифты
Локально, woff2, `@font-face` в `scss/base/_fonts.scss`:
- **Involve** — вариативный (`Involve-VF.woff2`, сконвертирован из `~/Desktop/Шрифты/Involve/Involve-VF.ttf`), веса 400/500.
- **Wix Madefor Text** и **Wix Madefor Display** — 400/500/600, кириллица + латиница (взяты из @fontsource).
