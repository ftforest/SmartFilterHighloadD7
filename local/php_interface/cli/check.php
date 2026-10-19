<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

// Определяем DOCUMENT_ROOT относительно расположения скрипта
$root = realpath(__DIR__ . '/../../../');
if (!$root || !is_dir($root . '/bitrix')) {
    die('Ошибка: не удалось определить DOCUMENT_ROOT или папка bitrix не найдена. Текущий путь: ' . __DIR__ . PHP_EOL);
}
$_SERVER['DOCUMENT_ROOT'] = $root;

// Подключаем ядро напрямую, без пролога
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog.php');

echo "Bitrix OK\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "PHP: " . PHP_VERSION . "\n";

global $DB;
if (isset($DB)) {
    echo "MySQL: " . $DB->GetVersion() . "\n";
} else {
    echo "MySQL: not initialized\n";
}
