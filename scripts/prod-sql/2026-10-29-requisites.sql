-- Реквизиты ИП Волкова А. П. (29.10.2026): заглушки в «Документах» + строка в подвале.
-- Выполнить в phpMyAdmin хостинга (вкладка SQL) один раз. Префикс таблиц — wp_ (как в wp-config.php).
-- Повторный запуск безопасен: заменяются только заглушки, опция вставляется/обновляется.

SET NAMES utf8mb4;

-- Тексты документов: complex arctik_documents на странице «Документы» или, после разбивки на страницы (29.10.2026), поле arctik_document_text страниц документов
-- В согласиях — дательный падеж («даю ИП Волкову…»); вторая замена — на случай, если заглушку уже заменили именительным
UPDATE wp_postmeta SET meta_value = REPLACE(REPLACE(meta_value,
  'даю [ПОЛНОЕ НАИМЕНОВАНИЕ ПРОДАВЦА / ОПЕРАТОРА]', 'даю ИП Волкову Алексею Петровичу'),
  'даю ИП Волков Алексей Петрович', 'даю ИП Волкову Алексею Петровичу')
WHERE meta_key LIKE '\_arctik\_documents|text|%' OR meta_key = '_arctik_document_text';

UPDATE wp_postmeta SET meta_value = REPLACE(meta_value,
  '[ПОЛНОЕ НАИМЕНОВАНИЕ ПРОДАВЦА / ОПЕРАТОРА]',
  'ИП Волков Алексей Петрович')
WHERE meta_key LIKE '\_arctik\_documents|text|%' OR meta_key = '_arctik_document_text';

UPDATE wp_postmeta SET meta_value = REPLACE(meta_value,
  'ИНН: [ИНН] · ОГРН/ОГРНИП: [НОМЕР] · Адрес регистрации: [АДРЕС]',
  'ИНН: 510502631360 · ОГРНИП: 310510509700021 · Адрес регистрации: Мурманская область, Кольский район, пгт Кильдинстрой, ул. Набережная, д. 15, кв. 28')
WHERE meta_key LIKE '\_arctik\_documents|text|%' OR meta_key = '_arctik_document_text';

UPDATE wp_postmeta SET meta_value = REPLACE(meta_value,
  '<p>Продавец: [УТОЧНИТЬ: ООО «АрктикБиоМарин» или другое юридическое лицо/ИП — по договору эквайринга и кассовому чеку]</p>\n<p>ИНН: [УТОЧНИТЬ] · ОГРН/ОГРНИП: [УТОЧНИТЬ] · КПП (для ООО): [УТОЧНИТЬ]</p>\n<p>Юридический адрес: [УТОЧНИТЬ]</p>\n<p>Электронная почта: arcticbiomarine@yandex.ru</p>',
  '<p>Продавец: ИП Волков Алексей Петрович</p>\n<p>ИНН: 510502631360 · ОГРНИП: 310510509700021</p>\n<p>Адрес регистрации: Мурманская область, Кольский район, пгт Кильдинстрой, ул. Набережная, д. 15, кв. 28</p>\n<p>Электронная почта продавца: a.volcov@mail.ru</p>\n<p>Вопросы и претензии: arcticbiomarine@yandex.ru</p>')
WHERE meta_key LIKE '\_arctik\_documents|text|%' OR meta_key = '_arctik_document_text';

UPDATE wp_postmeta SET meta_value = REPLACE(meta_value,
  'Продавец: [ПОДТВЕРДИТЬ ЮРИДИЧЕСКОЕ ЛИЦО И ЕГО РЕКВИЗИТЫ].',
  'Продавец: ИП Волков Алексей Петрович (ИНН 510502631360, ОГРНИП 310510509700021).')
WHERE meta_key LIKE '\_arctik\_documents|text|%' OR meta_key = '_arctik_document_text';

UPDATE wp_postmeta SET meta_value = REPLACE(meta_value,
  'Изготовитель продукции (по предоставленным сведениям): ИП Волков Алексей Петрович;',
  'Изготовитель продукции: ИП Волков Алексей Петрович (продавец);')
WHERE meta_key LIKE '\_arctik\_documents|text|%' OR meta_key = '_arctik_document_text';

-- Подвал: «Настройки сайта → Подвал → Реквизиты продавца» (поле есть в теме с этой версии)
DELETE FROM wp_options WHERE option_name = '_arctik_requisites';
INSERT INTO wp_options (option_name, option_value, autoload)
VALUES ('_arctik_requisites', 'ИП Волков Алексей Петрович\nИНН 510502631360\nОГРНИП 310510509700021', 'off');

-- Проверка: должно вернуть 0
SELECT COUNT(*) AS placeholders_left FROM wp_postmeta
WHERE meta_key LIKE '\_arctik\_documents|text|%'
  AND (meta_value LIKE '%[ПОЛНОЕ НАИМЕНОВАНИЕ%' OR meta_value LIKE '%[ИНН]%' OR meta_value LIKE '%Продавец: [УТОЧНИТЬ%' OR meta_value LIKE '%[ПОДТВЕРДИТЬ ЮРИДИЧЕСКОЕ%');
