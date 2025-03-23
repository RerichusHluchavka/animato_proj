<?php

declare(strict_types=1);

function prepareProductPDO(): PDO
{
    $host_products = 'db_produkty';
    $dbname_products = 'productsDb';
    $user_products = 'user';
    $password_products = 'user';

    try {
        $pdo_products = new PDO("mysql:host=$host_products;dbname=$dbname_products;charset=utf8", $user_products, $password_products);
        $pdo_products->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo_products;
    } catch (PDOException $e) {
        die("Chyba připojení k databázi produktů: " . $e->getMessage());
    }
}

function prepareorOrdersPDO(): PDO
{
    $host_orders = 'db_objednavky';
    $dbname_orders = 'ordersDb';
    $user_orders = 'user';
    $password_orders = 'user';

    try {
        $pdo_orders = new PDO("mysql:host=$host_orders;dbname=$dbname_orders;charset=utf8", $user_orders, $password_orders);
        $pdo_orders->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo_orders;
    } catch (PDOException $e) {
        die("Chyba připojení k databázi objednávek: " . $e->getMessage());
    }
} 

function xmlPrepareOutputRoot(): SimpleXMLElement
{
    //responsePack
    $xml = new SimpleXMLElement('<rsp:responsePack xmlns:rsp="http://www.stormware.cz/schema/version_2/response.xsd" 
    xmlns:lst="http://www.stormware.cz/schema/version_2/list.xsd" 
    xmlns:ord="http://www.stormware.cz/schema/version_2/order.xsd" 
    xmlns:typ="http://www.stormware.cz/schema/version_2/type.xsd"/>');

    $xml->addAttribute('version', '2.0');
    $xml->addAttribute('id', '001');
    $xml->addAttribute('state', 'ok');
    $xml->addAttribute('note', '');
    $xml->addAttribute('programVersion', '9801.8 (19.5.2011)');

    return $xml;
}
function xmlMakeOrderIdentities($xml, $order, $pdo_orders): void
{
    $stmt = $pdo_orders->prepare("SELECT * FROM partners WHERE id = ?");
    $stmt->execute([$order['partner_id']]);
    $partner = $stmt->fetch(PDO::FETCH_ASSOC);

    //partnerIdentity
    $partnerIdentity = $xml->addChild('ord:partnerIdentity');
    $partnerIdentity->addChild('typ:id', (string)$partner['id'], "http://www.stormware.cz/schema/version_2/type.xsd");
    $address = $partnerIdentity->addChild('typ:address', null, "http://www.stormware.cz/schema/version_2/type.xsd");
    $address->addChild('typ:company', $partner['param_company']);
    $address->addChild('typ:name', $partner['partner_name']);
    $address->addChild('typ:city', $partner['partner_city']);
    $address->addChild('typ:street', $partner['partner_street']);
    $address->addChild('typ:zip', $partner['partner_zip']);
    $address->addChild('typ:ico', $partner['partner_ico']);
    $address->addChild('typ:dic', $partner['partner_dic']);
    $address->addChild('typ:phone', $partner['partner_phone']);
    $address->addChild('typ:fax', $partner['partner_fax']);

    //myIdentity
    $myIdentity = $xml->addChild('ord:myIdentity');
    $myAddress = $myIdentity->addChild('typ:address', null, "http://www.stormware.cz/schema/version_2/type.xsd");
    $myAddress->addChild('typ:company', 'Novák');
    $myAddress->addChild('typ:surname', 'Novák');
    $myAddress->addChild('typ:name', 'Jan');
    $myAddress->addChild('typ:city', 'Jihlava 1');
    $myAddress->addChild('typ:street', 'Horní');
    $myAddress->addChild('typ:number', '15');
    $myAddress->addChild('typ:zip', '586 01');
    $myAddress->addChild('typ:ico', '12345678');
    $myAddress->addChild('typ:dic', 'CZ12345678');
    $myAddress->addChild('typ:phone', '569 876 542');
    $myAddress->addChild('typ:mobilPhone', '602 852 369');
    $myAddress->addChild('typ:fax', '564 563 216');
    $myAddress->addChild('typ:email', 'info@novak.cz');
    $myAddress->addChild('typ:www', 'www.novak.cz');
}

function xmlMakeOrderHaed($xml, $order, $pdo_orders): void
{
    $orderHeader = $xml->addChild('ord:orderHeader', null, "http://www.stormware.cz/schema/version_2/order.xsd");
    $orderHeader->addChild('ord:id', (string)$order['id']);
    $orderHeader->addChild('ord:orderType', $order['orderType']);

    //Evidenční číslo dokladu tvořeno náhodně
    $number = $orderHeader->addChild('ord:number');
    $number->addChild('typ:id', (string)mt_rand(0, 1000), "http://www.stormware.cz/schema/version_2/type.xsd");
    $number->addChild('typ:ids', (string)mt_rand(1000, 2000), "http://www.stormware.cz/schema/version_2/type.xsd");
    $number->addChild('typ:numberRequested', (string)mt_rand(112000001, 112999999), "http://www.stormware.cz/schema/version_2/type.xsd");

    $orderHeader->addChild('ord:date', $order['order_date']);
    $orderHeader->addChild('ord:dateDelivery', $order['delivery_date']);
    $orderHeader->addChild('ord:text', $order['order_text']);

    xmlMakeOrderIdentities($orderHeader, $order, $pdo_orders);

    //paymentType
    $stmt = $pdo_orders->prepare("SELECT * FROM payment_type WHERE id = ?");
    $stmt->execute([$order['payment_type_id']]);
    $paymentType = $stmt->fetch(PDO::FETCH_ASSOC);

    $xml_paymentType = $orderHeader->addChild('ord:paymentType');

    $xml_paymentType->addChild('typ:id', (string)$paymentType['id'], "http://www.stormware.cz/schema/version_2/type.xsd");
    $xml_paymentType->addChild('typ:ids', $paymentType['payment_ids'], "http://www.stormware.cz/schema/version_2/type.xsd");
    $xml_paymentType->addChild('typ:paymentType', $paymentType['payment_type'], "http://www.stormware.cz/schema/version_2/type.xsd");

    $orderHeader->addChild('ord:isExecuted', 'false');
    $orderHeader->addChild('ord:isDelivered', 'false');
}

function xmlMakeOrderDetail($xml, $order, $pdo_products, $pdo_orders): void
{
    $orderDetail = $xml->addChild('ord:orderDetail', null, "http://www.stormware.cz/schema/version_2/order.xsd");

    $stmt = $pdo_orders->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order['id']]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //foreach pro orderItems
    foreach ($orderItems as $orderItem) {
        $stmt = $pdo_products->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$orderItem['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        //orderItem
        $xml_orderItem = $orderDetail->addChild('ord:orderItem');
        $xml_orderItem->addChild('ord:id', (string)$product['id']);
        $xml_orderItem->addChild('ord:text', $product['product_description']);
        $xml_orderItem->addChild('ord:quantity', (string)$orderItem['quantity']);
        $xml_orderItem->addChild('ord:delivered', "0.0");
        $xml_orderItem->addChild('ord:unit', 'ks');
        $xml_orderItem->addChild('ord:coefficient', "1.0");
        $xml_orderItem->addChild('ord:payVAT', 'false');
        $xml_orderItem->addChild('ord:rateVAT', 'high');
        $xml_orderItem->addChild('ord:discountPercentage', "0.0");

        //homeCurrency
        $homeCurrency = $xml_orderItem->addChild('ord:homeCurrency');
        $homeCurrency->addChild('typ:unitPrice', "2500");
        $homeCurrency->addChild('typ:price', "12500");
        $homeCurrency->addChild('typ:priceVAT', (string)$product['price_vat']);
        $homeCurrency->addChild('typ:priceSum', "15000");

        $xml_orderItem->addChild('ord:code', substr($product['product'], 0, 3) . $product['id']);

        //stockItem
        $stockItem = $xml_orderItem->addChild('ord:stockItem');
        $store = $stockItem->addChild('typ:store', null, "http://www.stormware.cz/schema/version_2/type.xsd");
        $store->addChild('typ:id', "1");
        $store->addChild('typ:ids', 'ZBOŽÍ');
        $stockItem->addChild('typ:stockItem')->addChild('typ:id', "9");
        $stockItem->addChild('typ:stockItem')->addChild('typ:ids', $product['product']);
        $stockItem->addChild('typ:stockItem')->addChild('typ:PLU', "624");
    }
}

function xmlMakeOrderSummary($xml): void
{
    //orderSummary
    $orderSummary = $xml->addChild('ord:orderSummary', null, "http://www.stormware.cz/schema/version_2/order.xsd");
    $orderSummary->addChild('ord:roundingDocument', 'none');
    $orderSummary->addChild('ord:roundingVAT', 'none');

    //homeCurrency
    $homeCurrencySummary = $orderSummary->addChild('ord:homeCurrency');
    $homeCurrencySummary->addChild('typ:priceNone', "0", "http://www.stormware.cz/schema/version_2/type.xsd");
    $homeCurrencySummary->addChild('typ:priceLow', "0", "http://www.stormware.cz/schema/version_2/type.xsd");
    $homeCurrencySummary->addChild('typ:priceLowVAT', "0", "http://www.stormware.cz/schema/version_2/type.xsd");
    $homeCurrencySummary->addChild('typ:priceLowSum', "0", "http://www.stormware.cz/schema/version_2/type.xsd");
    $homeCurrencySummary->addChild('typ:priceHigh', "46100", "http://www.stormware.cz/schema/version_2/type.xsd");
    $homeCurrencySummary->addChild('typ:priceHighVAT', "9220", "http://www.stormware.cz/schema/version_2/type.xsd");
    $homeCurrencySummary->addChild('typ:priceHighSum', "55320", "http://www.stormware.cz/schema/version_2/type.xsd");
    $homeCurrencySummary->addChild('typ:round', null, "http://www.stormware.cz/schema/version_2/type.xsd")->addChild('typ:priceRound', "0");
}

function xmlMakeOrder($xml, $pdo_orders, $pdo_products, $order): void
{
    $xml_order = $xml->addChild('lst:order');
    $xml_order->addAttribute('version', '2.0');

    xmlMakeOrderHaed($xml_order, $order, $pdo_orders);

    xmlMakeOrderDetail($xml_order, $order, $pdo_products, $pdo_orders);

    xmlMakeOrderSummary($xml);
}

$pdo_orders = prepareorOrdersPDO();
$pdo_products = prepareProductPDO();

$dateFrom = $_GET['dateFrom'] ?? '1970-01-01';
$dateTill = $_GET['dateTill'] ?? date('Y-m-d');

$stmt = $pdo_orders->prepare("SELECT * FROM orders WHERE order_date BETWEEN ? AND ?");
$stmt->execute([$dateFrom, $dateTill]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


$output_xml = xmlPrepareOutputRoot();

//responsePackItem
$responsePackItem = $output_xml->addChild('rsp:responsePackItem');
$responsePackItem->addAttribute('version', '2.0');
$responsePackItem->addAttribute('id', 'li1');
$responsePackItem->addAttribute('state', 'ok');

//listOrder
$listOrder = $responsePackItem->addChild('lst:listOrder', null, "http://www.stormware.cz/schema/version_2/list.xsd");
$listOrder->addAttribute('version', '2.0');
$listOrder->addAttribute('dateTimeStamp', '2011-05-27T10:48:25Z');
$listOrder->addAttribute('dateValidFrom', '2011-05-27');
$listOrder->addAttribute('state', 'ok');

//foreach pro orders
foreach ($orders as $order) {

    xmlMakeOrder($listOrder, $pdo_orders, $pdo_products, $order);
}
$unescapedXmlString = html_entity_decode($output_xml->asXML(), ENT_QUOTES | ENT_XML1, 'UTF-8');
file_put_contents("../output.xml", $unescapedXmlString);

header('Content-Type: application/xml');
echo $output_xml->asXML();