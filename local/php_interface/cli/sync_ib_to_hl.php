<?php
/**
 * Первичная синхронизация ИБ 34 → Highload-блоки.
 * Запуск: php local/php_interface/cli/sync_ib_to_hl.php
 */

// ============================================================
// 1. ИНИЦИАЛИЗАЦИЯ
// ============================================================
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../..');
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;

Loader::includeModule('iblock');
Loader::includeModule('highloadblock');

set_time_limit(0);
ini_set('memory_limit', '1024M');

global $DB;

$iblockId = 34;

// Соответствие: код свойства ИБ → имя таблицы HL
$hlTables = [
    'BRAND'    => 'pet_product_brand',
    'COLOR'    => 'pet_product_color',
    'MATERIAL' => 'pet_product_material',
    'SIZE'     => 'pet_product_size',
    'COUNTRY'  => 'pet_product_country',
];

$startTime = microtime(true);

// ============================================================
// 2. ОЧИСТКА HL-ТАБЛИЦ (для идемпотентного прогона)
// ============================================================
echo "=== Очистка HL-таблиц ===\n";
foreach ($hlTables as $table) {
    $DB->Query("TRUNCATE TABLE `{$table}`");
    echo "  truncated: {$table}\n";
}

// ============================================================
// 3. СПРАВОЧНИК ENUM (ID значения → текст)
// ============================================================
echo "\n=== Загрузка справочника значений ===\n";
$enumMap = []; // [prop_code][enum_id] = text
$rs = $DB->Query("
    SELECT p.CODE AS PROP_CODE, e.ID AS ENUM_ID, e.VALUE AS ENUM_VALUE
    FROM b_iblock_property p
    JOIN b_iblock_property_enum e ON e.PROPERTY_ID = p.ID
    WHERE p.IBLOCK_ID = {$iblockId}
      AND p.CODE IN ('BRAND','COLOR','MATERIAL','SIZE','COUNTRY')
");
while ($row = $rs->Fetch()) {
    $enumMap[$row['PROP_CODE']][(int)$row['ENUM_ID']] = $row['ENUM_VALUE'];
}
echo "Загружено свойств: " . count($enumMap) . "\n";
foreach ($enumMap as $code => $values) {
    echo "  {$code}: " . count($values) . " значений\n";
}

// ============================================================
// 4. ID СВОЙСТВ ИБ (для фильтрации)
// ============================================================
$propIds = [];    // [code] => prop_id
$reverseMap = []; // [prop_id] => code
$rs = $DB->Query("
    SELECT ID, CODE FROM b_iblock_property
    WHERE IBLOCK_ID = {$iblockId}
      AND CODE IN ('BRAND','COLOR','MATERIAL','SIZE','COUNTRY')
");
while ($row = $rs->Fetch()) {
    $id = (int)$row['ID'];
    $propIds[$row['CODE']] = $id;
    $reverseMap[$id] = $row['CODE'];
}
echo "\nСвойств к синхронизации: " . count($propIds) . "\n";

if (empty($propIds)) {
    die("ОШИБКА: свойства не найдены. Проверь IBLOCK_ID и коды.\n");
}

// ============================================================
// 5. ГЛАВНЫЙ ЦИКЛ: ИБ → HL
// ============================================================
echo "\n=== Синхронизация ===\n";

$batchSize = 1000;   // сколько элементов читаем за раз
$insertChunk = 500;  // сколько строк в одном INSERT
$lastId = 0;

$stats = [
    'elements' => 0,
    'rows'     => array_fill_keys(array_keys($hlTables), 0),
    'queries'  => 0,
];

$totalStartTime = microtime(true);

while (true) {
    // 5.1 Получаем очередную порцию ID
    $rs = $DB->Query("
        SELECT ID FROM b_iblock_element
        WHERE IBLOCK_ID = {$iblockId} AND ID > {$lastId}
        ORDER BY ID ASC
        LIMIT {$batchSize}
    ");
    $ids = [];
    while ($row = $rs->Fetch()) {
        $ids[] = (int)$row['ID'];
    }
    if (empty($ids)) break;

    $lastId = end($ids);
    $idsStr = implode(',', $ids);
    $propIdsStr = implode(',', array_values($propIds));

    // 5.2 Читаем все значения свойств для этих элементов
    $rs = $DB->Query("
        SELECT IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE
        FROM b_iblock_element_property
        WHERE IBLOCK_ELEMENT_ID IN ({$idsStr})
          AND IBLOCK_PROPERTY_ID IN ({$propIdsStr})
    ");

    // 5.3 Группируем: [element_id][code][] = value_id
    $elementProps = [];
    while ($row = $rs->Fetch()) {
        $elId   = (int)$row['IBLOCK_ELEMENT_ID'];
        $code   = $reverseMap[(int)$row['IBLOCK_PROPERTY_ID']] ?? null;
        if (!$code) continue;
        $valId  = (int)$row['VALUE'];
        $elementProps[$elId][$code][] = $valId;
    }

    // 5.4 Формируем буферы для вставки
    $buffers = array_fill_keys(array_keys($hlTables), []);
    foreach ($elementProps as $elId => $props) {
        foreach ($hlTables as $code => $table) {
            if (empty($props[$code])) continue;
            foreach ($props[$code] as $valueId) {
                $valueText = $enumMap[$code][$valueId] ?? '';
                $buffers[$code][] = sprintf(
                    "(%d, %d, '%s')",
                    $elId,
                    $valueId,
                    $DB->ForSql($valueText)
                );
                $stats['rows'][$code]++;
            }
        }
    }

    // 5.5 Флашим батчами по 500 строк
    foreach ($buffers as $code => $rows) {
        if (empty($rows)) continue;
        $table = $hlTables[$code];
        foreach (array_chunk($rows, $insertChunk) as $chunk) {
            $DB->Query("
                INSERT INTO `{$table}` (UF_PRODUCT_ID, UF_VALUE_ID, UF_VALUE)
                VALUES " . implode(',', $chunk)
            );
            $stats['queries']++;
        }
    }

    $stats['elements'] += count($ids);

    if ($stats['elements'] % 5000 === 0) {
        $elapsed = round(microtime(true) - $totalStartTime, 1);
        $speed = round($stats['elements'] / max($elapsed, 0.01), 1);
        echo sprintf(
            "  [%6d] %5.1f сек | %5.1f эл/сек | вставлено: %d строк\n",
            $stats['elements'],
            $elapsed,
            $speed,
            array_sum($stats['rows'])
        );
    }
}

// ============================================================
// 6. ИТОГИ
// ============================================================
$totalTime = round(microtime(true) - $totalStartTime, 1);

echo "\n=== ГОТОВО ===\n";
echo "Элементов обработано: {$stats['elements']}\n";
echo "SQL-запросов INSERT:  {$stats['queries']}\n";
echo "Общее время:          {$totalTime} сек\n";
echo "\nЗаписей по HL-блокам:\n";
foreach ($stats['rows'] as $code => $cnt) {
    echo sprintf("  %-10s %7d\n", $code, $cnt);
}
echo sprintf("  %-10s %7d\n", 'ИТОГО', array_sum($stats['rows']));