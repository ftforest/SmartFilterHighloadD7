<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\UI\PageNavigation;
use PetCatalog\FilterQuery;

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/lib/FilterQuery.php';

class PetCatalogComponent extends CBitrixComponent
{
    public function executeComponent(): void
    {
        $t0 = microtime(true);

        $filters = FilterQuery::parseFiltersFromRequest();
        $pageSize = (int)($this->arParams['PAGE_SIZE'] ?? 24);

        // 1. Настройка пагинации
        // Отказываемся от initFromUri() — он теряется при массивах в URL
        $nav = new PageNavigation('page');
        $nav->allowAllRecords(false)
            ->setPageSize($pageSize);

        // 2. Считаем общее количество
        $countResult = FilterQuery::getCount($filters);
        $totalCount = $countResult['count'];
        $nav->setRecordCount($totalCount);

        // 3. Явно читаем текущую страницу из $_GET
        $currentPage = max(1, (int)($_GET['page'] ?? 1));
        $nav->setCurrentPage($currentPage);

        // 4. Получаем товары для текущей страницы
        $items = FilterQuery::getElements(
            $filters,
            $nav->getLimit(),
            $nav->getOffset()
        );

        $this->arResult['ITEMS'] = $items;
        $this->arResult['NAV']   = $nav;
        $this->arResult['TOTAL'] = $totalCount;

        $this->arResult['PROFILING'] = [
            'count_ms' => $countResult['time_ms'],
            'total_ms' => round((microtime(true) - $t0) * 1000, 2),
            'page'     => $currentPage,     // для отладки
            'offset'   => $nav->getOffset(), // для отладки
        ];

        $this->includeComponentTemplate();
    }
}