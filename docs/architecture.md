# Архитектура каталога с умным фильтром

## Проблема

Стандартный умный фильтр Bitrix на инфоблоках при 50 000 товаров показывает:
- Сценарий A (без фильтра): 1.13 сек SQL
- Сценарий B (1 фильтр): 2.41 сек SQL
- Сценарий D (5 фильтров): 2.51 сек SQL

Причина: громоздкие EXISTS-подзапросы к `b_iblock_element_property` (350k+ строк).

## Решение: вынос фильтруемых свойств в Highload-блоки

### Структура данных

**Инфоблок products (ID=34):**
- Товар → все свойства (BRAND, COLOR, MATERIAL, SIZE, COUNTRY, PRICE, ...)
- 50 100 товаров, ~350 000 записей в b_iblock_element_property

**Highload-блоки (5 шт.):**
````
| HL-блок | Таблица | Поля | Строк |
|---|---|---|---|
| ProductBrand | pet_product_brand | UF_PRODUCT_ID, UF_VALUE_ID, UF_VALUE | 50 100 |
| ProductColor | pet_product_color | ... | 75 084 |
| ProductMaterial | pet_product_material | ... | 75 138 |
| ProductSize | pet_product_size | ... | 100 198 |
| ProductCountry | pet_product_country | ... | 50 100 |
````
**Индексы:**
- `idx_product` — на UF_PRODUCT_ID
- `idx_value` — на UF_VALUE_ID
- `idx_product_value` — составной (UF_PRODUCT_ID, UF_VALUE_ID)

### Синхронизация ИБ → HL

**Первичная (CLI):**
- `local/php_interface/cli/sync_ib_to_hl.php`
- 350 620 строк за **7 секунд** (777 batch-INSERT вместо 350 620)
- Скорость: **7 142 строк/сек**

**Инкрементальная (events):**
- `OnAfterIBlockElementAdd/Update/Delete` → `HlSync::syncElement/deleteElement`
- Полная перезапись HL-записей товара
- +1.5–2.6 мс к операции сохранения

### Кастомный фильтр на D7

Строит SQL через `ReferenceField` JOIN'ы вместо подзапросов:
- 1 фильтр → 1 INNER JOIN
- 5 фильтров → 5 INNER JOIN
- Прямой доступ по индексам, без EXISTS