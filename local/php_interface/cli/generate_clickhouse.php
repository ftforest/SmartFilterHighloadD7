<?php
/**
 * Генератор тестовых данных для ClickHouse.
 * Создает 200 000 событий и 10 000 заказов.
 *
 * Запуск: php local/php_interface/cli/generate_clickhouse.php
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use ClickHouseDB\Client;
use Faker\Factory;

// 1. Подключение к ClickHouse (порт 8123 - HTTP)
$config = [
    'host'     => '127.0.0.1',
    'port'     => '8123',
    'username' => 'default',
    'password' => 'pet_clickhouse_pass',
];
$db = new Client($config);
$db->database('default');

// 2. Настройки генерации
$faker = Factory::create('ru_RU');
$totalEvents = 200000;
$totalOrders = 10000;
$batchSize = 10000; // Вставляем по 10 000 строк за раз

// ID товаров из вашего каталога Bitrix
$minProductId = 54707;
$maxProductId = 104806;

$eventTypes = ['view', 'click', 'add_to_cart', 'remove_from_cart'];
$statuses = ['new', 'payed', 'canceled'];

echo "=== Старт генерации данных для ClickHouse ===\n";
$startTime = microtime(true);

// ==========================================
// ГЕНЕРАЦИЯ СОБЫТИЙ (EVENTS)
// ==========================================
echo "\n[1/2] Генерация {$totalEvents} событий (events)...\n";

$eventsBatch = [];
for ($i = 1; $i <= $totalEvents; $i++) {
    $timestamp = $faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d H:i:s');

    $eventsBatch[] = [
        $faker->uuid,                           // event_id
        $faker->numberBetween(1, 5000),         // user_id (5000 уникальных юзеров)
        $faker->numberBetween($minProductId, $maxProductId), // product_id
        $faker->randomElement($eventTypes),     // event_type
        $timestamp,                             // event_time
        '/catalog/item/' . $faker->slug,        // url
    ];

    if (count($eventsBatch) === $batchSize) {
        $db->insert('events', $eventsBatch);
        $eventsBatch = [];
        echo "  ... вставлено {$i} событий\n";
    }
}

// Остаток
if (!empty($eventsBatch)) {
    $db->insert('events', $eventsBatch);
}

// ==========================================
// ГЕНЕРАЦИЯ ЗАКАЗОВ (ORDERS + ORDER_ITEMS)
// ==========================================
echo "\n[2/2] Генерация {$totalOrders} заказов (orders + order_items)...\n";

$ordersBatch = [];
$itemsBatch = [];

for ($i = 1; $i <= $totalOrders; $i++) {
    $orderId = $i;
    $userId = $faker->numberBetween(1, 5000);
    $orderTime = $faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d H:i:s');

    // Генерируем 1-5 позиций в заказе
    $itemsCount = $faker->numberBetween(1, 5);
    $totalAmount = 0;

    for ($j = 1; $j <= $itemsCount; $j++) {
        $price = $faker->randomFloat(2, 500, 50000);
        $quantity = $faker->numberBetween(1, 3);
        $totalAmount += ($price * $quantity);

        $itemsBatch[] = [
            $orderId,
            $faker->numberBetween($minProductId, $maxProductId), // product_id
            $orderTime,
            $quantity,
            $price,
        ];
    }

    $ordersBatch[] = [
        $orderId,
        $userId,
        $orderTime,
        round($totalAmount, 2),
        $faker->randomElement($statuses),
    ];

    // Батч-вставка для заказов и позиций
    if (count($ordersBatch) === $batchSize) {
        $db->insert('orders', $ordersBatch);
        $db->insert('order_items', $itemsBatch);
        $ordersBatch = [];
        $itemsBatch = [];
        echo "  ... вставлено {$i} заказов\n";
    }
}

// Остаток
if (!empty($ordersBatch)) {
    $db->insert('orders', $ordersBatch);
    $db->insert('order_items', $itemsBatch);
}

$totalTime = round(microtime(true) - $startTime, 2);
echo "\n=== ГОТОВО ===\n";
echo "Время выполнения: {$totalTime} сек\n";