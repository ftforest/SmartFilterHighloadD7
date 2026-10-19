<?php
// clear_iblock_34_cli.php

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

// Поднимаемся на 3 уровня вверх до корня сайта
$siteRoot = realpath(__DIR__ . '/../../..');

if (!$siteRoot || !is_dir($siteRoot . '/bitrix')) {
    fwrite(STDERR, "Ошибка: не найден корень сайта по пути: $siteRoot\n");
    exit(1);
}

// ВАЖНО: вручную задаём DOCUMENT_ROOT — без этого автолоадер Битрикса ломается
$_SERVER['DOCUMENT_ROOT'] = $siteRoot;

require $siteRoot . '/bitrix/modules/main/include/prolog_before.php';

if (!CModule::IncludeModule('iblock')) {
    fwrite(STDERR, "Модуль iblock не подключён.\n");
    exit(1);
}

$iblockId = 34;
$batchSize = 200;
$deletedTotal = 0;

do {
    $rsElements = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $iblockId],
        false,
        ['nTopCount' => $batchSize],
        ['ID']
    );

    $batchDeleted = 0;
    while ($el = $rsElements->Fetch()) {
        $result = CIBlockElement::Delete($el['ID']);
        if ($result) {
            $batchDeleted++;
        } else {
            global $APPLICATION;
            $ex = $APPLICATION->GetException();
            fwrite(STDERR, sprintf(
                "Не удалось удалить элемент %d: %s\n",
                $el['ID'],
                $ex ? $ex->GetString() : 'неизвестная ошибка'
            ));
        }
    }

    $deletedTotal += $batchDeleted;
    echo sprintf("Пакет: удалено %d, всего %d\n", $batchDeleted, $deletedTotal);

} while ($batchDeleted === $batchSize);

echo "Готово. Всего удалено элементов: $deletedTotal\n";
?>
