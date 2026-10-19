<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
/** @var array $arParams */

$actionUrl = $arResult['ACTION_URL'];
$active = $arResult['ACTIVE'];
$stats = $arResult['STATS'];

// Утилита: строим URL с текущими параметрами
$buildUrl = function (array $override) use ($actionUrl, $active) {
    $params = [];
    foreach ($active as $code => $ids) {
        $key = strtolower($code);
        $params[$key] = $override[$code] ?? $ids;
    }
    // Удаляем пустые
    $params = array_filter($params, fn($v) => !empty($v));

    $query = '';
    foreach ($params as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $item) {
                $query .= urlencode($k . '[]') . '=' . urlencode($item) . '&';
            }
        } else {
            $query .= urlencode($k) . '=' . urlencode($v) . '&';
        }
    }
    return $actionUrl . ($query ? '?' . rtrim($query, '&') : '');
};
?>

    <form class="pet-filter" method="GET" action="<?= htmlspecialcharsbx($actionUrl) ?>">

        <div class="pet-filter__groups">
            <?php foreach ($arResult['ITEMS'] as $code => $group): ?>
                <div class="pet-filter__group">
                    <div class="pet-filter__group-title"><?= htmlspecialcharsbx($group['NAME']) ?></div>

                    <?php foreach ($group['VALUES'] as $value): ?>
                        <?php
                        $isChecked = in_array($value['ID'], $active[$code] ?? [], true);
                        $inputId = 'pf-' . strtolower($code) . '-' . $value['ID'];
                        ?>
                        <label class="pet-filter__item" for="<?= $inputId ?>">
                            <input
                                    type="checkbox"
                                    id="<?= $inputId ?>"
                                    name="<?= strtolower($code) ?>[]"
                                    value="<?= $value['ID'] ?>"
                                    <?= $isChecked ? 'checked' : '' ?>
                            >
                            <span class="pet-filter__item-text">
                            <?= htmlspecialcharsbx($value['TEXT']) ?>
                            <span class="pet-filter__item-count">(<?= $value['COUNT'] ?>)</span>
                        </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pet-filter__actions">
            <button type="submit" class="pet-filter__btn pet-filter__btn--apply">
                Показать
            </button>
            <a href="<?= htmlspecialcharsbx($actionUrl) ?>" class="pet-filter__btn pet-filter__btn--reset">
                Сбросить
            </a>
        </div>

    </form>

<?php if (!$stats['skipped']): ?>
    <div class="pet-filter__stats">
        <div class="pet-filter__stats-row">
            <span>Найдено:</span> <strong><?= number_format($stats['count'], 0, '.', ' ') ?></strong>
        </div>
        <div class="pet-filter__stats-row">
            <span>Активных фильтров:</span> <strong><?= $stats['filters_used'] ?></strong>
        </div>
        <div class="pet-filter__stats-row">
            <span>COUNT запрос:</span> <strong><?= $stats['time_ms'] ?> мс</strong>
        </div>
        <div class="pet-filter__stats-row">
            <span>Выборка страницы:</span> <strong><?= $stats['page_time_ms'] ?> мс</strong>
        </div>
        <div class="pet-filter__stats-row pet-filter__stats-row--total">
            <span>Итого:</span>
            <strong><?= round($stats['time_ms'] + $stats['page_time_ms'], 2) ?> мс</strong>
        </div>
        <div class="pet-filter__stats-row pet-filter__stats-row--hint">
            (для сравнения: стандартный фильтр Bitrix на тех же данных — 2990 мс)
        </div>
        <div class="pet-filter__stats-row pet-filter__stats-row--hint" style="margin-top:10px;color:#666">
            <span>buildFilterItems:</span> <?= $arResult['PROFILING']['build_items_ms'] ?> мс &nbsp;
            <span>runFilter:</span> <?= $arResult['PROFILING']['filter_ms'] ?> мс &nbsp;
            <span>всего компонент:</span> <?= $arResult['PROFILING']['total_ms'] ?> мс
        </div>
    </div>
<?php endif; ?>