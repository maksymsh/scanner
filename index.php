<?php

// Connect to DB
$host = '127.0.0.1';
$db = 'scanner';
$user = 'root';
$pass = '';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $db = new PDO($dsn, $user, $pass, $opt);
} catch (PDOException $e) {
    die('Подключение не удалось: ' . $e->getMessage());
}

// Get currencies from DB
$stmt = $db->query('SELECT * FROM currencies');
$currencies = $stmt->fetchAll();

// Fetch data from API

$url = "https://octaex.com/api/trade/all/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if ($data && is_array($data)) {
    foreach ($data as $curr => $values) {

        if (in_array($curr, ['ctb_btc', 'ctb_eth', 'btc_usd', 'eth_btc', 'eth_usd'])) {
            $db_curr = array_search($curr, array_column($currencies, 'code'));
            if ($db_curr === false) {
                $sql = 'INSERT INTO currencies SET code="' . $curr . '", name="' . $curr . '";';
                $stm = $db->prepare($sql);
                $stm->execute();
                $id = $db->lastInsertId();

            } elseif (is_numeric($db_curr)) {
                $id = $currencies[$db_curr]['id'];
            }


            $sql = 'INSERT INTO `values` SET 
            currency_id="' . $id . '", 
            new_price="' . $values['new_price'] . '", 
            buy_price="' . $values['buy_price'] . '", 
            sell_price="' . $values['sell_price'] . '", 
            min_price="' . $values['min_price'] . '", 
            max_price="' . $values['max_price'] . '", 
            amount="' . $values['amount'] . '";';

            $stm = $db->prepare($sql);
            $stm->execute();
        }

    }
}