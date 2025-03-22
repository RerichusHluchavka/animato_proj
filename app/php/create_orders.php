<?php

declare(strict_types=1);

function generateRandomDay($start_day, $end_day): string{
    $day = mt_rand($start_day, $end_day);
    $month = "03";
    $year = "2025";

    return date('Y-m-d', strtotime($year . '-' . $month . '-' . $day));
}


$host_products = 'db_produkty';
$dbname_products = 'productsDb';
$user_products = 'user';
$password_products = 'user';

try {
    $pdo_products = new PDO("mysql:host=$host_products;dbname=$dbname_products;charset=utf8", $user_products, $password_products);
    $pdo_products->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Chyba připojení k databázi produktů: " . $e->getMessage());
}

$stmt = $pdo_products->prepare("SELECT id  FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_COLUMN);

$host_products = 'db_objednavky';
$dbname_products = 'ordersDb';
$user_products = 'user';
$password_products = 'user';

try {
    $pdo_orders = new PDO("mysql:host=$host_products;dbname=$dbname_products;charset=utf8", $user_products, $password_products);
    $pdo_orders->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Chyba připojení k databázi produktů: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['count'])) {
    $count = (int)$_POST['count'];  
    for ($i = 0; $i < $count; $i++) {
        $order_date = generateRandomDay(1,22);
        //vkládání do tabulky s objednávkami
        
        $stmt = $pdo_orders->prepare("INSERT INTO orders (order_date, delivery_date, orderType, order_text, partner_id, payment_type_id)
         VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $order_date,
            generateRandomDay((int)date('d', strtotime($order_date)), 31),
            "issuedOrder",
            "Objednáváme u Vás níže uvedené zboží",
            1,
            1
        ]);

        $orderId = $pdo_orders->lastInsertId();

        for ($j = 0; $j < mt_rand(1,3); $j++) {
            $product = array_rand($products, 1);
            $stmt = $pdo_orders->prepare("INSERT INTO order_items (order_id, product_id, quantity)
             VALUES (?, ?, ?)");
            $stmt->execute([
                $orderId,
                $product,
                mt_rand(1, 5)
            ]);
        }

    }
}