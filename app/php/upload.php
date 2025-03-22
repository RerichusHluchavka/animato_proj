<?php

declare(strict_types=1);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['xml_file'])) {
    $xmlFile = $_FILES['xml_file']['tmp_name'];

    if (!is_uploaded_file($xmlFile)) {
        die("Chyba při nahrávání souboru.");
    }

    $xml = simplexml_load_file($xmlFile);
    if ($xml === false) {
        die("Chyba při načítání XML souboru.");
    }

    foreach ($xml->SHOPITEM as $shopitem) {
        //vkládání do tabulky s produkty
        $stmt = $pdo_products->prepare("INSERT IGNORE INTO products (id, product, product_description, product_url, img_url, img_url_alternative, price_vat, vat, manufacturer,
         category_text, ean, delivery_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $vat = rtrim((string)$shopitem->VAT,'%');
        $stmt->execute([
            (int)$shopitem->ID,
            (string)$shopitem->PRODUCT,
            (string)$shopitem->DESCRIPTION,
            (string)$shopitem->URL,
            (string)$shopitem->IMGURL,
            (string)$shopitem->IMGURL_ALTERNATIVE,
            (float)$shopitem->PRICE_VAT,
            (int)$vat,
            (string)$shopitem->MANUFACTURER,
            (string)$shopitem->CATEGORYTEXT,
            (string)$shopitem->EAN,
            (int)$shopitem->DELIVERY_DATE
        ]); 

        //vkládání do tabulky s parametry
        foreach ($shopitem->PARAM as $param) {
            $stmt = $pdo_products->prepare("INSERT IGNORE INTO params (id, param_name) VALUES (?, ?)");
            $stmt->execute([
                (int)$param['param-id'],
                (string)$param->PARAM_NAME,
            ]);
        }

        //vkládání do tabulky propojující produkty s parametry
        foreach ($shopitem->PARAM as $param) {
            $stmt = $pdo_products->prepare("INSERT IGNORE INTO product_params (product_id, param_id, val) VALUES (?, ?, ?)");
            $stmt->execute([
                (int)$shopitem->ID,
                (int)$param['param-id'],
                (string)$param->VAL,
            ]);
        }
    }
} else {
    echo "Nebyl nahrán žádný soubor.";
}
