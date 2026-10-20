# Pet-проект: Каталог с умным фильтром на Bitrix D7 + Highload

Демонстрационный проект, показывающий оптимизацию фильтрации каталога
Bitrix при 50 000 товаров через D7 ORM, Highload-блоки и JOIN-запросы.

## 📊 Результаты

| Метрика | Baseline (умный фильтр Bitrix) | Оптимизированный фильтр | Ускорение |
|---|---|---|---|
| PHP PAGE TOTAL | 2.990 сек | **0.491 сек** | **×6.1** |
| SQL-запросов | 117 | **17** | **×6.9** |
| Время SQL | 2.5409 сек | **0.0503 сек** | **×50.5** |

## 📷 Скриншот

![Каталог с фильтром](/docs/screenshots/catalog-filter.png)

*Каталог с активным фильтром (4 фильтра, 840 товаров). Время ответа 0.703 сек
при 20 SQL-запросах. Для сравнения: baseline показывает 2.99 сек при 117 запросах.*

## 🏗️ Архитектура
````
b_iblock_element (50 100 товаров)
│
├── pet_product_brand (50 100 строк) ── idx_product_value
├── pet_product_color (75 084 строки) ── idx_product_value
├── pet_product_material (75 138 строк) ── idx_product_value
├── pet_product_size (100 198 строк) ── idx_product_value
└── pet_product_country (50 100 строк) ── idx_product_value
````
Фильтр работает через D7 ORM ReferenceField JOIN к HL-таблицам.

## 🔧 Стек

- Bitrix «Управление сайтом» (Бизнес)
- PHP 8.4, MySQL 8.4
- Bitrix D7 ORM (`Bitrix\Main\Entity\ReferenceField`)
- Highload-блоки с кастомными индексами
- Composer (Faker для генерации тестовых данных)

## 📂 Структура репозитория
````
local/
├── components/pet/filter/ # Кастомный компонент фильтра
├── php_interface/
│ ├── lib/
│ │ ├── FilterQuery.php # D7 ORM фильтр
│ │ └── HlSync.php # Синхронизация ИБ → HL
│ ├── events.php # Обработчики событий
│ ├── init.php
│ └── cli/
│ ├── generate.php # Генерация 50 000 товаров
│ ├── sync_ib_to_hl.php # Первичная синхронизация
│ └── test_filter.php # CLI-бенчмарк

docs/
├── baseline.md # Замеры «до»
├── benchmark_after.md # Замеры «после»
├── architecture.md # Схема данных
└── db/ # Дампы структуры БД
````

## 📈 Этапы

1. Развёртывание Bitrix + Git
2. Проектирование инфоблока (9 свойств, 7 разделов)
3. Генерация 50 100 товаров через CLI (70 мин)
4. Baseline замеры (PHP 8.4, MySQL 8.4)
5. Проектирование Highload-блоков + индексы
6. Синхронизация ИБ → HL (350 620 строк за 7 сек)
7. Инкрементальная синхронизация (events, +1.5 мс)
8. Кастомный D7-фильтр с ReferenceField JOIN
9. Кэш счётчиков фильтра (1374 мс → 0.78 мс)
10. Финальный бенчмарк (×50 по SQL)

## 🎯 Ключевые технические решения

### 1. Batch-INSERT для синхронизации
- 350 620 строк вставлено за 7 секунд (777 batch-запросов по 500 строк)
- В 450 раз меньше SQL-запросов, чем построчная вставка через `DataManager::add()`

### 2. ReferenceField JOIN вместо EXISTS
```php
$runtime[] = new ReferenceField(
    'REF_BRAND',
    $brandClass,
    Join::on('this.ID', 'ref.UF_PRODUCT_ID'),
    ['join_type' => 'INNER']
);
$filter['=REF_BRAND.UF_VALUE_ID'] = [307, 308];
```
### 3. Кэш счётчиков фильтра
- buildFilterItems() кэшируется на 5 минут

- Инвалидируется в HlSync при любом изменении товара

### 4. Собственный компонент `pet:catalog` с пагинацией

Стандартный `catalog.section` не подходит для кастомного фильтра:
он передаёт все ID в `WHERE ID IN (...)`, что создаёт огромный SQL и
тормозит на больших выборках.

Решение — свой компонент `pet:catalog`:
- Принимает фильтры из `$_GET` через `FilterQuery::parseFiltersFromRequest()`
- Считает общее количество через `FilterQuery::getCount()`
- Применяет `LIMIT` и `OFFSET` в SQL-запросе через D7 ORM
- Строит умную пагинацию (окно 2 слева + 2 справа + первая/последняя)

**Результат:** пагинация на 261 страницу (6 249 товаров) работает за
**~80 мс** на любом offset — от 0 до 6 240. Без деградации производительности
на глубоких страницах.

### ⚠️ Известные ограничения
- Пагинация при активном фильтре ограничена первой страницей (24 ID).
Все найденные ID не передаются в catalog.section — компромисс
ради производительности SQL.

- Счётчики фильтра кэшируются на 5 минут. При массовом импорте
через CLI-скрипт кэш не инвалидируется автоматически.

## 🚀 Как запустить

```bash
git clone <repo>
composer install
# Настроить .settings.php, dbconn.php
php local/php_interface/cli/generate.php 50000
php local/php_interface/cli/sync_ib_to_hl.php
php local/php_interface/cli/reindex.php
```

## 📚 Future work
- Полноценный компонент pet:catalog на D7 (с LIMIT/OFFSET для пагинации)

- Кэш результатов FilterQuery::getProductIds() по хэшу фильтров

- Использование SQL_CALC_FOUND_ROWS вместо COUNT(DISTINCT)

- Живой поиск по фильтру через AJAX без перезагрузки страницы


### Задача 7.3 — Финальные коммиты

```bash
git add .
git commit -m "feat: pet-проект с кастомным D7-фильтром на Highload-блоках

- Генерация 50 100 товаров через CLI
- Синхронизация ИБ → HL (batch-INSERT, 350 620 строк за 7 сек)
- Инкрементальная синхронизация через event-обработчики
- Кастомный фильтр на D7 ReferenceField JOIN
- Кэш счётчиков фильтра (1374 → 0.78 мс)
- Ускорение SQL в 50 раз vs baseline

Benchmark: docs/benchmark_after.md
Архитектура: docs/architecture.md"

git push
```

### Известные ограничения

- **COUNT при 5+ фильтрах с мультизначениями**: 200–300 мс
  (CLI-тест давал 28 мс на 3 фильтрах). Причина — `COUNT(DISTINCT e.ID)`
  по 4+ INNER JOIN'ам. Кандидат на оптимизацию через EXISTS-подход.

- **Пагинация при активном фильтре ограничена первой страницей** (24 ID).
  ...

## 🎓 Что демонстрирует проект

- **D7 ORM**: `ReferenceField`, `Join::on()`, `runtime`-поля.
- **Highload-блоки**: `compileEntity()`, кастомные индексы через ALTER TABLE.
- **Batch-INSERT**: 777 запросов вместо 350 620 при синхронизации.
- **Событийная модель Bitrix**: `OnAfterIBlockElementAdd/Update/Delete`.
- **Профилирование SQL**: `SHOW_SQL_STAT`, `$DB->GetQueriesCount()`, `microtime`.
- **Кэширование**: `Bitrix\Main\Data\Cache` + инвалидация.
- **Архитектурное решение**: отказ от стандартного `catalog.smart.filter`
  в пользу кастомного компонента.

