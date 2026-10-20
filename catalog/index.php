<?php $GLOBALS['PET_PAGE_START'] = microtime(true); ?>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Каталог товаров");
?>


    <h1>Каталог товаров</h1>

    <div style="display: flex; gap: 20px; align-items: flex-start;">

        <!-- ЛЕВАЯ КОЛОНКА: КАСТОМНЫЙ ФИЛЬТР -->
        <div style="flex: 0 0 280px;">
            <?
            // Устанавливает $GLOBALS['arrPetFilter'] = ['=ID' => [...]]
            $APPLICATION->IncludeComponent(
                    "pet:filter", // "bitrix:catalog.smart.filter",
                    "",
                    [
                            "ACTION_URL" => "/catalog/",
                    ],
                    false
            );
            ?>
        </div>

        <!-- ПРАВАЯ КОЛОНКА: СПИСОК ТОВАРОВ -->
        <div style="flex: 1;">
            <?
            $APPLICATION->IncludeComponent(
                    "pet:catalog",
                    "",
                    [
                            "PAGE_SIZE" => 24,
                    ],
                    false
            );
            ?>
        </div>
    </div>

<?php
//echo $debug->Output();
if (isset($_GET['debug_sql'])) {
    echo '<div style="background:#ffe;padding:15px;border:2px solid #cc0;font:14px monospace;margin:10px">';
    echo '<strong>PHP PAGE TOTAL:</strong> ' . round(microtime(true) - $GLOBALS['PET_PAGE_START'], 3) . ' сек<br>';
    echo '<strong>PEAK MEMORY:</strong> ' . round(memory_get_peak_usage(true) / 1024 / 1024, 1) . ' MB';
    echo '</div>';
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");



