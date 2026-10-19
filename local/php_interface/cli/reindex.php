<?php
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../..');
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
Loader::includeModule('iblock');

$iblockId = 34;
$startTime = microtime(true);

echo "Переиндексация инфоблока {$iblockId}...\n";

// Пересчёт фасетного индекса
$index = \Bitrix\Iblock\PropertyIndex\Manager::createIndexer($iblockId);
$index->startIndex();
while ($index->continueIndex(0)) {
    echo ".";
}
$index->endIndex();

$time = round(microtime(true) - $startTime, 1);
echo "\nГотово за {$time} сек\n";