<?php

namespace PetCatalog;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;

class HlSync
{
    private const IBLOCK_ID = 34;

    // Соответствие: код свойства ИБ → имя HL-таблицы
    private const MAP = [
        'BRAND'    => 'pet_product_brand',
        'COLOR'    => 'pet_product_color',
        'MATERIAL' => 'pet_product_material',
        'SIZE'     => 'pet_product_size',
        'COUNTRY'  => 'pet_product_country',
    ];

    private static bool $enabled = true;

    public static function disable(): void { self::$enabled = false; }
    public static function enable(): void { self::$enabled = true; }

    /**
     * Полная синхронизация одного товара.
     * Вызывается при Add/Update.
     */
    public static function syncElement(int $elementId): void
    {
        if (!self::$enabled) return;

        // Сбрасываем кэш счётчиков фильтра — данные могли измениться
        $cache = \Bitrix\Main\Data\Cache::createInstance();
        $cache->clean('pet_filter_items', '/pet/filter');

        global $DB;

        Loader::includeModule('iblock');
        Loader::includeModule('highloadblock');

        // 1. Читаем все значения фильтруемых свойств
        $propCodes = array_keys(self::MAP);
        $codesStr = "'" . implode("','", $propCodes) . "'";

        $rs = $DB->Query("
            SELECT p.CODE AS PROP_CODE, ep.VALUE AS VALUE_ID, e.VALUE AS VALUE_TEXT
            FROM b_iblock_element_property ep
            JOIN b_iblock_property p ON p.ID = ep.IBLOCK_PROPERTY_ID
            LEFT JOIN b_iblock_property_enum e ON e.ID = ep.VALUE
            WHERE ep.IBLOCK_ELEMENT_ID = {$elementId}
              AND p.IBLOCK_ID = " . self::IBLOCK_ID . "
              AND p.CODE IN ({$codesStr})
        ");

        // 2. Группируем: [code][] = ['id' => X, 'text' => Y]
        $grouped = [];
        while ($row = $rs->Fetch()) {
            $grouped[$row['PROP_CODE']][] = [
                'id'   => (int)$row['VALUE_ID'],
                'text' => (string)$row['VALUE_TEXT'],
            ];
        }

        // 3. Для каждой HL-таблицы: удаляем старые + вставляем новые
        foreach (self::MAP as $code => $table) {
            // Удаляем все старые записи этого товара
            $DB->Query("DELETE FROM `{$table}` WHERE UF_PRODUCT_ID = {$elementId}");

            if (empty($grouped[$code])) {
                continue;
            }

            // Формируем batch INSERT
            $rows = [];
            foreach ($grouped[$code] as $item) {
                $rows[] = sprintf(
                    "(%d, %d, '%s')",
                    $elementId,
                    $item['id'],
                    $DB->ForSql($item['text'])
                );
            }

            $DB->Query("
                INSERT INTO `{$table}` (UF_PRODUCT_ID, UF_VALUE_ID, UF_VALUE)
                VALUES " . implode(',', $rows)
            );
        }
    }

    /**
     * Удаление всех записей товара из HL-таблиц.
     */
    public static function deleteElement(int $elementId): void
    {
        // Сбрасываем кэш счётчиков фильтра — данные могли измениться
        $cache = \Bitrix\Main\Data\Cache::createInstance();
        $cache->clean('pet_filter_items', '/pet/filter');
        global $DB;
        Loader::includeModule('highloadblock');

        foreach (self::MAP as $table) {
            $DB->Query("DELETE FROM `{$table}` WHERE UF_PRODUCT_ID = {$elementId}");
        }
    }
}