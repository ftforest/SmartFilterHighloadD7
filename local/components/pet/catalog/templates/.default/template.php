<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
/** @var array $arParams */

$nav = $arResult['NAV'];
?>

<?php
// Строит URL пагинации, сохраняя фильтры
$buildPageUrl = function (int $page) {
    $params = $_GET;
    $params['page'] = $page;

    $query = [];
    foreach ($params as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $item) {
                $query[] = urlencode($k . '[]') . '=' . urlencode($item);
            }
        } else {
            $query[] = urlencode($k) . '=' . urlencode($v);
        }
    }
    return '?' . implode('&', $query);
};
?>

<div class="pet-catalog">
    <?php if (empty($arResult['ITEMS'])): ?>
        <p>Ничего не найдено. <a href="/catalog/">Сбросить фильтры</a></p>
    <?php else: ?>

        <div class="pet-catalog__grid">
            <?php foreach ($arResult['ITEMS'] as $item): ?>
                <div class="pet-catalog__item">
                    <a href="<?= htmlspecialcharsbx($item['DETAIL_PAGE_URL']) ?>">
                        <?= htmlspecialcharsbx($item['NAME']) ?>
                    </a>
                    <p><?= htmlspecialcharsbx($item['PREVIEW_TEXT']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Пагинация -->
        <div class="pet-catalog__nav">
            <?php
            $currentPage = $nav->getCurrentPage();
            $pageCount   = $nav->getPageCount();

            if ($pageCount > 1):
                // Кнопка "Пред."
                if ($currentPage > 1) {
                    echo '<a href="' . $buildPageUrl($currentPage - 1) . '">Пред.</a>';
                } else {
                    echo '<span class="is-disabled">Пред.</span>';
                }

                // Окно страниц: 2 слева + текущая + 2 справа
                $window = 2;
                $start = max(1, $currentPage - $window);
                $end = min($pageCount, $currentPage + $window);

                // Первая страница + троеточие
                if ($start > 1) {
                    echo '<a href="' . $buildPageUrl(1) . '">1</a>';
                    if ($start > 2) {
                        echo '<span class="is-dots">...</span>';
                    }
                }

                // Окно
                for ($i = $start; $i <= $end; $i++) {
                    if ($i === $currentPage) {
                        echo '<strong>' . $i . '</strong>';
                    } else {
                        echo '<a href="' . $buildPageUrl($i) . '">' . $i . '</a>';
                    }
                }

                // Троеточие + последняя страница
                if ($end < $pageCount) {
                    if ($end < $pageCount - 1) {
                        echo '<span class="is-dots">...</span>';
                    }
                    echo '<a href="' . $buildPageUrl($pageCount) . '">' . $pageCount . '</a>';
                }

                // Кнопка "След."
                if ($currentPage < $pageCount) {
                    echo '<a href="' . $buildPageUrl($currentPage + 1) . '">След.</a>';
                } else {
                    echo '<span class="is-disabled">След.</span>';
                }
            endif;
            ?>
        </div>

        <div class="pet-catalog__stats">
            Страница <?= $nav->getCurrentPage() ?> из <?= $nav->getPageCount() ?>.
            Всего: <?= number_format($arResult['TOTAL'], 0, '.', ' ') ?> товаров.
            <br>
            <small>
                COUNT: <?= $arResult['PROFILING']['count_ms'] ?> мс,
                всего: <?= $arResult['PROFILING']['total_ms'] ?> мс
                | page=<?= $arResult['PROFILING']['page'] ?>,
                offset=<?= $arResult['PROFILING']['offset'] ?>
            </small>
        </div>

    <?php endif; ?>
</div>