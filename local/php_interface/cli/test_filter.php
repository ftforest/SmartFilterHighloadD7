<?php
/**
 * Тестовый прогон FilterQuery.
 * Запуск: php local/php_interface/cli/test_filter.php
 */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../..');
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/lib/FilterQuery.php');

use PetCatalog\FilterQuery;

$scenarios = [
    'A (без фильтра)' => [],
    'B (BRAND=Apple)' => ['BRAND' => [307]],
    'C (3 фильтра)'   => ['BRAND' => [307], 'COLOR' => [315], 'MATERIAL' => [322]],
    'D (5 фильтров)'  => ['BRAND' => [307], 'COLOR' => [315], 'MATERIAL' => [322], 'SIZE' => [331], 'COUNTRY' => [333]],
    'E (сложный)'     => ['BRAND' => [307, 308, 311], 'COLOR' => [315, 316, 318], 'COUNTRY' => [333, 336]],
];

echo "=== Тест FilterQuery ===\n\n";
echo sprintf(
    "%-18s | %10s | %10s | %10s | %s\n",
    'Сценарий', 'COUNT мс', 'Всего', 'Стр. мс', 'Стр. ID'
);
echo str_repeat('-', 90) . "\n";

foreach ($scenarios as $name => $filters) {
    try {
        // 1. COUNT через getCount()
        $countResult = FilterQuery::getCount($filters);

        // 2. Первые 24 ID (эмуляция страницы каталога)
        $pageResult = FilterQuery::getProductIds($filters, 24);

        $firstIds = implode(', ', array_slice($pageResult['ids'], 0, 5));

        echo sprintf(
            "%-18s | %10.2f | %10d | %10.2f | %s\n",
            $name,
            $countResult['time_ms'],
            $countResult['count'],
            $pageResult['time_ms'],
            $firstIds
        );
    } catch (\Throwable $e) {
        echo sprintf("%-18s | ОШИБКА: %s\n", $name, $e->getMessage());
    }
}

echo "\n=== Готово ===\n";