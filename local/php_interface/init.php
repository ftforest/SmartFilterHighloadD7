<?php

// ВРЕМЕННО: для замеров на этапе разработки
if (isset($_GET['debug_sql']) && $_GET['debug_sql'] === 'Y') {
    global $DB;
    $DB->ShowSqlStat = true;
    $DB->Debug = true;  // <- добавь эту строку, она важна
    // Для отладки: выводим факт срабатывания
    echo '<div style="position:fixed;top:0;left:0;background:#fff;color:#000;padding:10px;z-index:9999;border:1px solid #ccc;">DEBUG: SHOW_SQL_STAT enabled</div>';
}

require_once __DIR__ . '/events.php';