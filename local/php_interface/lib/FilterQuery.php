<?php

namespace PetCatalog;

use Bitrix\Main\Loader;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Iblock\ElementTable;
use Bitrix\Highloadblock\HighloadBlockTable;

/**
 * Кастомный фильтр товаров через D7 ORM + JOIN к Highload-блокам.
 * Возвращает ID товаров, подходящих под заданные фильтры.
 */
class FilterQuery
{
    private const IBLOCK_ID = 34;

    /**
     * Соответствие: код свойства → ID HL-блока.
     * Из b_hlblock_entity: 6=Brand, 7=Color, 8=Material, 9=Size, 10=Country
     */
    private const HL_MAP = [
        'BRAND'    => 6,
        'COLOR'    => 7,
        'MATERIAL' => 8,
        'SIZE'     => 9,
        'COUNTRY'  => 10,
    ];

    /** @var array<int,string> Кэш скомпилированных DataClass'ов */
    private static array $entityClasses = [];

    /**
     * Возвращает общее количество товаров, подходящих под фильтры.
     * Использует прямой SQL: COUNT(DISTINCT e.ID) с JOIN'ами.
     *
     * @param array<string,int[]> $filters
     * @return array{count:int, time_ms:float}
     */
    public static function getCount(array $filters): array
    {
        Loader::includeModule('iblock');
        Loader::includeModule('highloadblock');

        global $DB;

        $t0 = microtime(true);

        // 1. Собираем JOIN'ы и WHERE
        $joins = [];
        $where = [
            "e.IBLOCK_ID = " . self::IBLOCK_ID,
            "e.ACTIVE = 'Y'",
        ];

        foreach ($filters as $code => $valueIds) {
            if (empty($valueIds) || !isset(self::HL_MAP[$code])) {
                continue;
            }

            $table = self::getTableName(self::HL_MAP[$code]);
            $alias = 'ref_' . strtolower($code);
            $idsStr = implode(',', array_map('intval', $valueIds));

            $joins[] = "INNER JOIN `{$table}` {$alias} ON {$alias}.UF_PRODUCT_ID = e.ID";
            $where[] = "{$alias}.UF_VALUE_ID IN ({$idsStr})";
        }

        $sql = "SELECT COUNT(DISTINCT e.ID) AS CNT
                FROM b_iblock_element e
                " . implode("\n", $joins) . "
                WHERE " . implode("\n  AND ", $where);

        // 2. Выполняем
        $row = $DB->Query($sql)->Fetch();
        $count = $row ? (int)$row['CNT'] : 0;

        return [
            'count'   => $count,
            'time_ms' => round((microtime(true) - $t0) * 1000, 2),
        ];
    }

    /**
     * Парсит фильтры из $_GET.
     * Поддерживает ?brand[]=307&brand[]=308 и ?brand=307,308
     */
    public static function parseFiltersFromRequest(): array
    {
        $active = [];
        foreach (self::HL_MAP as $code => $hlId) {
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
     * Возвращает товары с пагинацией (LIMIT + OFFSET).
     *
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array Массив строк: ID, NAME, PREVIEW_TEXT, DETAIL_PAGE_URL
     */
    public static function getElements(array $filters, int $limit = 24, int $offset = 0): array
    {
        Loader::includeModule('iblock');
        Loader::includeModule('highloadblock');

        $runtime = [];
        $filter = [
            '=IBLOCK_ID' => self::IBLOCK_ID,
            '=ACTIVE'    => 'Y',
        ];

        foreach ($filters as $code => $valueIds) {
            if (empty($valueIds) || !isset(self::HL_MAP[$code])) {
                continue;
            }
            $refName = 'REF_' . $code;
            $entityClass = self::getEntityClass(self::HL_MAP[$code]);

            $runtime[] = new ReferenceField(
                $refName,
                $entityClass,
                Join::on('this.ID', 'ref.UF_PRODUCT_ID'),
                ['join_type' => 'INNER']
            );

            $filter['=' . $refName . '.UF_VALUE_ID'] = array_map('intval', $valueIds);
        }

        $params = [
            // Запрашиваем шаблон URL из настроек инфоблока
            'select' => [
                'ID',
                'NAME',
                'PREVIEW_TEXT',
                'CODE',
                'IBLOCK_SECTION_ID',
                'DETAIL_PAGE_URL_TEMPLATE' => 'IBLOCK.DETAIL_PAGE_URL', // <-- ключевой момент
            ],
            'filter' => $filter,
            'order'  => ['ID' => 'ASC'],
            'limit'  => $limit,
            'offset' => $offset,
        ];

        if (!empty($runtime)) {
            $params['runtime'] = $runtime;
            // Для MySQL 8 с ONLY_FULL_GROUP_BY нужно добавить все поля в GROUP BY
            $params['group'] = [
                'ID',
                'NAME',
                'PREVIEW_TEXT',
                'CODE',
                'IBLOCK_SECTION_ID',
                'IBLOCK.DETAIL_PAGE_URL',
            ];
        }

        $result = ElementTable::getList($params);

        $items = [];
        while ($row = $result->fetch()) {
            // Подставляем реальные данные элемента в шаблон URL
            $row['DETAIL_PAGE_URL'] = \CIBlock::ReplaceDetailUrl(
                $row['DETAIL_PAGE_URL_TEMPLATE'],
                $row,
                true,
                'E'
            );
            $items[] = $row;
        }

        return $items;
    }

    /**
     * Возвращает имя таблицы HL-блока по его ID.
     */
    private static function getTableName(int $hlBlockId): string
    {
        static $cache = [];
        if (!isset($cache[$hlBlockId])) {
            $hlblock = HighloadBlockTable::getById($hlBlockId)->fetch();
            if (!$hlblock) {
                throw new \RuntimeException("HL-блок с ID={$hlBlockId} не найден");
            }
            // Bitrix хранит имя таблицы с префиксом из b_hlblock_entity.TABLE_NAME
            // Твой TABLE_NAME = 'pet_product_brand', реальная таблица — тоже без префикса b_
            $cache[$hlBlockId] = $hlblock['TABLE_NAME'];
        }
        return $cache[$hlBlockId];
    }

    /**
     * Возвращает ID товаров, подходящих под фильтры.
     *
     * @param array<string,int[]> $filters  ['BRAND' => [307, 308], ...]
     * @param int $limit                    Лимит (0 = без лимита)
     * @return array{ids:int[], count:int, time_ms:float, sql_count:int}
     */
    public static function getProductIds(array $filters, int $limit = 0): array
    {
        Loader::includeModule('iblock');
        Loader::includeModule('highloadblock');

        $t0 = microtime(true);
        $sqlBefore = self::getSqlCount();

        // 1. Формируем runtime (JOIN'ы) и filter
        $runtime = [];
        $filter = [
            '=IBLOCK_ID' => self::IBLOCK_ID,
            '=ACTIVE'    => 'Y',
        ];

        foreach ($filters as $code => $valueIds) {
            if (empty($valueIds) || !isset(self::HL_MAP[$code])) {
                continue;
            }

            $refName = 'REF_' . $code;
            $entityClass = self::getEntityClass(self::HL_MAP[$code]);

            // INNER JOIN e.ID = ref.UF_PRODUCT_ID
            $runtime[] = new ReferenceField(
                $refName,
                $entityClass,
                Join::on('this.ID', 'ref.UF_PRODUCT_ID'),
                ['join_type' => 'INNER']
            );

            // WHERE ref.UF_VALUE_ID IN (...)
            $filter['=' . $refName . '.UF_VALUE_ID'] = array_map('intval', $valueIds);
        }

        // 2. Параметры запроса
        $params = [
            'select' => ['ID'],
            'filter' => $filter,
            'group'  => ['ID'],          // дедупликация при multiple-свойствах
            'order'  => ['ID' => 'ASC'],
        ];

        if (!empty($runtime)) {
            $params['runtime'] = $runtime;
        }

        if ($limit > 0) {
            $params['limit'] = $limit;
        }

        // 3. Выполняем запрос через getList
        $result = ElementTable::getList($params);

        $ids = [];
        while ($row = $result->fetch()) {
            $ids[] = (int)$row['ID'];
        }

        $total = count($ids);

        $timeMs = round((microtime(true) - $t0) * 1000, 2);
        $sqlCount = self::getSqlCount() - $sqlBefore;

        return [
            'ids'       => $ids,
            'count'     => $total,
            'time_ms'   => $timeMs,
            'sql_count' => $sqlCount,
        ];
    }

    /**
     * Компилирует DataClass HL-блока и кэширует.
     */
    private static function getEntityClass(int $hlBlockId): string
    {
        if (!isset(self::$entityClasses[$hlBlockId])) {
            $hlblock = HighloadBlockTable::getById($hlBlockId)->fetch();
            if (!$hlblock) {
                throw new \RuntimeException("HL-блок с ID={$hlBlockId} не найден");
            }
            $entity = HighloadBlockTable::compileEntity($hlblock);
            self::$entityClasses[$hlBlockId] = $entity->getDataClass();
        }
        return self::$entityClasses[$hlBlockId];
    }

    /**
     * Текущее количество SQL-запросов (если доступно).
     */
    private static function getSqlCount(): int
    {
        global $DB;
        return method_exists($DB, 'GetQueriesCount') ? (int)$DB->GetQueriesCount() : 0;
    }
}