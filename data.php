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

$data = [];

foreach ($currencies as $currency) {
    $stmt = $db->query('SELECT * FROM `values` WHERE currency_id="' . $currency['id'] . '" ORDER BY time DESC LIMIT 1');
    $current = $stmt->fetch();


    $data[$currency['code']] = compact('current');
}

var_dump($data);