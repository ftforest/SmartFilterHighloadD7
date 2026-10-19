<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use PetCatalog\FilterQuery;

Loader::includeModule('iblock');
Loader::includeModule('highloadblock');

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/lib/FilterQuery.php';

class PetCatalogFilterComponent extends CBitrixComponent
{
    private const IBLOCK_ID = 34;

    /** Коды свойств и их HL-блоки */
    private const PROPERTIES = [
        'BRAND'    => ['HL_ID' => 6],
        'COLOR'    => ['HL_ID' => 7],
        'MATERIAL' => ['HL_ID' => 8],
        'SIZE'     => ['HL_ID' => 9],
        'COUNTRY'  => ['HL_ID' => 10],
    ];

    public function executeComponent(): void
    {
        $t0 = microtime(true);

        $cache = \Bitrix\Main\Data\Cache::createInstance();
        $cacheId = 'pet_filter_items';
        if ($cache->initCache(300, $cacheId)) {
            $items = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $items = $this->buildFilterItems();  // тяжёлые запросы
            $cache->endDataCache($items);
        }
        $this->arResult['ITEMS'] = $items;
        $tBuild = microtime(true);

        $this->arResult['ACTIVE'] = $this->getActiveFilters();
        $this->arResult['STATS'] = $this->runFilter();
        $tFilter = microtime(true);

        $this->arResult['ACTION_URL'] = $this->arParams['ACTION_URL']
            ?? $this->arParams['SEF_FOLDER']
            ?? '/catalog/';

        $this->arResult['PROFILING'] = [
            'build_items_ms' => round(($tBuild - $t0) * 1000, 2),
            'filter_ms'      => round(($tFilter - $tBuild) * 1000, 2),
            'total_ms'       => round(($tFilter - $t0) * 1000, 2),
        ];

        $this->includeComponentTemplate();
    }

    /**
     * Строит список значений для фильтра (с счетчиками).
     */
    private function buildFilterItems(): array
    {
        global $DB;
        $items = [];

        foreach (self::PROPERTIES as $code => $conf) {
            $hlBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById($conf['HL_ID'])->fetch();
            if (!$hlBlock) continue;
            $table = $hlBlock['TABLE_NAME'];

            // Считаем, сколько товаров с каждым значением
            $rs = $DB->Query("
                SELECT t.UF_VALUE_ID AS VALUE_ID, t.UF_VALUE AS VALUE_TEXT,
                       COUNT(DISTINCT t.UF_PRODUCT_ID) AS CNT
                FROM `{$table}` t
                INNER JOIN b_iblock_element e
                    ON e.ID = t.UF_PRODUCT_ID
                   AND e.IBLOCK_ID = " . self::IBLOCK_ID . "
                   AND e.ACTIVE = 'Y'
                GROUP BY t.UF_VALUE_ID, t.UF_VALUE
                ORDER BY t.UF_VALUE
            ");

            $values = [];
            while ($row = $rs->Fetch()) {
                $values[] = [
                    'ID'    => (int)$row['VALUE_ID'],
                    'TEXT'  => $row['VALUE_TEXT'],
                    'COUNT' => (int)$row['CNT'],
                ];
            }

            $items[$code] = [
                'CODE'   => $code,
                'NAME'   => $this->getPropName($code),
                'VALUES' => $values,
            ];
        }

        return $items;
    }

    /**
     * Человеко-понятные названия для секций фильтра.
     */
    private function getPropName(string $code): string
    {
        return [
            'BRAND'    => 'Бренд',
            'COLOR'    => 'Цвет',
            'MATERIAL' => 'Материал',
            'SIZE'     => 'Размер',
            'COUNTRY'  => 'Страна',
        ][$code] ?? $code;
    }

    /**
     * Вытаскивает активные значения из $_GET.
     * Поддерживает ?brand[]=307&brand[]=308 и ?brand=307,308
     */
    private function getActiveFilters(): array
    {
        $active = [];
        foreach (self::PROPERTIES as $code => $conf) {
            $param = strtolower($code);
            if (empty($_GET[$param])) {
                continue;
            }

            $raw = $_GET[$param];
            if (is_array($raw)) {
                $ids = array_filter(array_map('intval', $raw), fn($v) => $v > 0);
            } else {
                $ids = array_filter(
                    array_map('intval', explode(',', (string)$raw)),
                    fn($v) => $v > 0
                );
            }

            if (!empty($ids)) {
                $active[$code] = array_values(array_unique($ids));
            }
        }
        return $active;
    }

    /**
     * Запускает фильтр и возвращает статистику + ID.
     * Также устанавливает $GLOBALS['arrPetFilter'] для catalog.section.
     */
    private function runFilter(): array
    {
        $active = $this->getActiveFilters();

        // Нет активных фильтров → не трогаем глобальную переменную
        if (empty($active)) {
            return [
                'time_ms'       => 0,
                'page_time_ms'  => 0,
                'count'         => 0,
                'ids'           => [],
                'filters_used'  => 0,
                'skipped'       => true,
            ];
        }

        $countResult = FilterQuery::getCount($active);
        $pageResult  = FilterQuery::getProductIds($active, 24);

        // Передаём ID в catalog.section через глобальную переменную
        $GLOBALS['arrPetFilter'] = ['=ID' => $pageResult['ids']];

        return [
            'time_ms'       => $countResult['time_ms'],
            'page_time_ms'  => $pageResult['time_ms'],
            'count'         => $countResult['count'],
            'ids'           => $pageResult['ids'],
            'filters_used'  => count($active),
            'skipped'       => false,
        ];
    }
}