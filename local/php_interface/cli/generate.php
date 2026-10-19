<?php

\PetCatalog\HlSync::disable();

/**
 * Генератор товаров в инфоблок products (ID=34).
 * V2: корректная запись списковых свойств через ID значений.
 * Запуск: php local/php_interface/cli/generate.php [количество]
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
use Faker\Factory;

Loader::includeModule('iblock');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

// ============================================================
// 2. НАСТРОЙКИ
// ============================================================
set_time_limit(0);
ini_set('memory_limit', '1024M');

$iblockId = 34;
$total = isset($argv[1]) ? (int)$argv[1] : 50000;
$faker = Factory::create('ru_RU');

// ---- ID значений списковых свойств (получены из b_iblock_property_enum) ----
$brandIds     = [307, 308, 309, 310, 311, 312, 313, 314];      // Apple..LG
$colorIds     = [315, 316, 317, 318, 319, 320, 321];           // Чёрный..Серый
$materialIds  = [322, 323, 324, 325, 326];                     // Металл..Дерево
$sizeIds      = [327, 328, 329, 330, 331, 332];                // XS..XXL
$countryIds   = [333, 334, 335, 336, 337, 338];                // Китай..Россия

// ---- Разделы каталога ----
$sections = [38, 39, 40, 41, 42, 43, 44];

// ============================================================
// 3. ПОДГОТОВКА
// ============================================================
$el = new CIBlockElement();
$startTime = microtime(true);
$success = 0;
$errors = [];

$logDir = $_SERVER['DOCUMENT_ROOT'] . '/local/var/log';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
$logFile = $logDir . '/generate_errors.log';

$progressStep = max(1, (int)floor($total / 20));

function progress($i, $total, $startTime, $success) {
    $elapsed = round(microtime(true) - $startTime, 1);
    $mem = round(memory_get_usage(true) / 1024 / 1024, 1);
    $speed = $i > 0 ? round($i / max($elapsed, 0.01), 1) : 0;
    $eta = $speed > 0 ? round(($total - $i) / $speed, 0) : 0;

    echo sprintf(
        "[%6d/%-6d] %5.1f%% | %6.1f сек | %5.1f эл/сек | ETA: %4d сек | RAM: %5.1f MB | OK: %d\n",
        $i, $total, ($i / $total) * 100, $elapsed, $speed, $eta, $mem, $success
    );
}

// ============================================================
// 4. ГЛАВНЫЙ ЦИКЛ
// ============================================================
echo "=== Генерация {$total} товаров в инфоблок {$iblockId} ===\n";
echo "Старт: " . date('Y-m-d H:i:s') . "\n\n";

for ($i = 1; $i <= $total; $i++) {
    // Случайные ID значений для множественных свойств
    $itemColors    = (array)array_rand(array_flip($colorIds), random_int(1, 2));
    $itemMaterials = (array)array_rand(array_flip($materialIds), random_int(1, 2));
    $itemSizes     = (array)array_rand(array_flip($sizeIds), random_int(1, 3));

    $brandId   = $brandIds[array_rand($brandIds)];
    $countryId = $countryIds[array_rand($countryIds)];

    $price    = $faker->randomFloat(2, 500, 150000);
    $oldPrice = $faker->boolean(30) ? round($price * 1.15, 2) : null;

    // ВАЖНО: формат ['VALUE' => ID] для списковых, ['VALUE' => value] для чисел/строк
    $arFields = [
        'IBLOCK_ID'         => $iblockId,
        'NAME'              => $faker->word() . ' ' . $faker->randomNumber(4),
        'ACTIVE'            => 'Y',
        'CODE'              => 'product-' . $i . '-' . substr(md5(uniqid()), 0, 6),
        'PREVIEW_TEXT'      => $faker->realText(150),
        'DETAIL_TEXT'       => $faker->realText(800),
        'SORT'              => 500,
        'IBLOCK_SECTION_ID' => $sections[array_rand($sections)],
        'PROPERTY_VALUES'   => [
            'BRAND'     => [['VALUE' => $brandId]],
            'COLOR'     => array_map(fn($id) => ['VALUE' => $id], array_values($itemColors)),
            'MATERIAL'  => array_map(fn($id) => ['VALUE' => $id], array_values($itemMaterials)),
            'SIZE'      => array_map(fn($id) => ['VALUE' => $id], array_values($itemSizes)),
            'COUNTRY'   => [['VALUE' => $countryId]],
            'PRICE'     => [['VALUE' => $price]],
            'OLD_PRICE' => $oldPrice ? [['VALUE' => $oldPrice]] : [],
            'QUANTITY'  => [['VALUE' => random_int(0, 500)]],
            'ARTICLE'   => [['VALUE' => strtoupper($faker->bothify('??-####-??'))]],
        ],
    ];

    $id = $el->Add($arFields);

    if ($id) {
        $success++;
    } else {
        file_put_contents(
            $logFile,
            date('Y-m-d H:i:s') . " | #{$i} | " . $el->LAST_ERROR . "\n",
            FILE_APPEND
        );
        $errors[] = $el->LAST_ERROR;
    }

    if ($i % $progressStep === 0 || $i === $total) {
        progress($i, $total, $startTime, $success);
    }
}

// ============================================================
// 5. ИТОГИ
// ============================================================
$totalTime = round(microtime(true) - $startTime, 1);
$peakMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 1);

echo "\n=== ГОТОВО ===\n";
echo "Всего: {$total}\n";
echo "Успешно: {$success}\n";
echo "Ошибок: " . count($errors) . "\n";
echo "Время: {$totalTime} сек\n";
echo "Память: {$peakMemory} MB\n";
echo "Скорость: " . round($total / max($totalTime, 0.01), 1) . " эл/сек\n";
if (!empty($errors)) {
    echo "Лог: {$logFile}\n";
}