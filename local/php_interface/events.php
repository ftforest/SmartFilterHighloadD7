<?php

use Bitrix\Main\EventManager;
use PetCatalog\HlSync;

require_once __DIR__ . '/lib/HlSync.php';

$eventManager = EventManager::getInstance();

// При добавлении товара
$eventManager->addEventHandler(
    'iblock',
    'OnAfterIBlockElementAdd',
    function ($arFields) {
        if ((int)$arFields['IBLOCK_ID'] !== 34) {
            return;
        }
        HlSync::syncElement((int)$arFields['ID']);
    }
);

// При обновлении товара
$eventManager->addEventHandler(
    'iblock',
    'OnAfterIBlockElementUpdate',
    function ($arFields) {
        if ((int)$arFields['IBLOCK_ID'] !== 34) return;
        $t0 = microtime(true);
        HlSync::syncElement((int)$arFields['ID']);
        $dt = round((microtime(true) - $t0) * 1000, 1);
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/local/var/log/hl_sync.log',
            date('H:i:s') . " | ID={$arFields['ID']} | {$dt} ms\n",
            FILE_APPEND
        );
    }
);

// При удалении товара
$eventManager->addEventHandler(
    'iblock',
    'OnAfterIBlockElementDelete',
    function ($arFields) {
        if ((int)$arFields['IBLOCK_ID'] !== 34) {
            return;
        }
        HlSync::deleteElement((int)$arFields['ID']);
    }
);