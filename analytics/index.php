<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use ClickHouseDB\Client;

// 1. Подключение к ClickHouse
$config = [
    'host'     => '127.0.0.1',
    'port'     => '8123',
    'username' => 'default',
    'password' => 'pet_clickhouse_pass',
];
$db = new Client($config);
$db->database('default');

// 2. Выполняем запросы
// Воронка
$funnel = $db->select('
    SELECT event_type, COUNT(*) AS total_events 
    FROM events 
    GROUP BY event_type 
    ORDER BY total_events DESC
')->rows();

// Выручка по месяцам
$revenue = $db->select('
    SELECT toYYYYMM(order_time) AS month, SUM(total_amount) AS revenue 
    FROM orders 
    WHERE status = \'payed\' 
    GROUP BY month 
    ORDER BY month ASC
')->rows();

// Топ-10 по выручке
$topRevenue = $db->select('
    SELECT oi.product_id, SUM(oi.quantity * oi.price) AS total_revenue 
    FROM order_items oi 
    INNER JOIN orders o ON oi.order_id = o.order_id 
    WHERE o.status = \'payed\' 
    GROUP BY oi.product_id 
    ORDER BY total_revenue DESC 
    LIMIT 10
')->rows();

// Топ-10 по добавлениям в корзину
$topCart = $db->select('
    SELECT product_id, COUNT(*) AS add_count 
    FROM events 
    WHERE event_type = \'add_to_cart\' 
    GROUP BY product_id 
    ORDER BY add_count DESC 
    LIMIT 10
')->rows();

// 3. Подготовка данных для графиков (Chart.js)
$funnelLabels = json_encode(array_column($funnel, 'event_type'));
$funnelData   = json_encode(array_column($funnel, 'total_events'));

$revLabels = json_encode(array_column($revenue, 'month'));
$revData   = json_encode(array_column($revenue, 'revenue'));
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Аналитика ClickHouse</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        h1 { margin-bottom: 20px; color: #1a1a1a; }
        .dashboard { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .card h2 { font-size: 16px; margin-top: 0; margin-bottom: 15px; color: #555; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #fafafa; font-weight: 600; }
        .chart-container { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body>

    <h1>📊 Аналитика интернет-магазина (ClickHouse)</h1>

    <div class="dashboard">
        <!-- График: Воронка -->
        <div class="card">
            <h2>Воронка событий</h2>
            <div class="chart-container">
                <canvas id="funnelChart"></canvas>
            </div>
        </div>

        <!-- График: Выручка по месяцам -->
        <div class="card">
            <h2>Выручка по месяцам (оплаченные)</h2>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Таблица: Топ-10 по выручке -->
        <div class="card">
            <h2>Топ-10 товаров по выручке</h2>
            <table>
                <tr><th>ID Товара</th><th>Выручка (руб.)</th></tr>
                <?php foreach ($topRevenue as $row): ?>
                    <tr>
                        <td><?= $row['product_id'] ?></td>
                        <td><?= number_format($row['total_revenue'], 2, '.', ' ') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Таблица: Топ-10 по корзине -->
        <div class="card">
            <h2>Топ-10 по добавлениям в корзину</h2>
            <table>
                <tr><th>ID Товара</th><th>Добавлений</th></tr>
                <?php foreach ($topCart as $row): ?>
                    <tr>
                        <td><?= $row['product_id'] ?></td>
                        <td><?= $row['add_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <script>
        // 1. Настройка графика воронки
        const ctxFunnel = document.getElementById('funnelChart').getContext('2d');
        new Chart(ctxFunnel, {
            type: 'bar',
            data: {
                labels: <?= $funnelLabels ?>,
                datasets: [{
                    label: 'Количество событий',
                    data: <?= $funnelData ?>,
                    backgroundColor: ['#5b47d6', '#7c6ff0', '#a59af5', '#cec7fa'],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // 2. Настройка графика выручки
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: <?= $revLabels ?>,
                datasets: [{
                    label: 'Выручка (руб.)',
                    data: <?= $revData ?>,
                    borderColor: '#5b47d6',
                    backgroundColor: 'rgba(91, 71, 214, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>