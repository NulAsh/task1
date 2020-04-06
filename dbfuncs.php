<?php

require_once('./net.php');

function db_connect() {

        // Define connection as a static variable, to avoid connecting more than once 
    static $connection;

        // Try and connect to the database, if a connection has not been established yet
    if(!isset($connection)) {
             // Load configuration as an array. Use the actual location of your configuration file
        $config = parse_ini_file('../private/config.ini'); 
        $connection = mysqli_connect($config['servername'],$config['username'],$config['password'],$config['dbname']);
    }

        // If connection was not successful, handle the error
    if($connection === false) {
            // Handle error - notify administrator, log to a file, show an error screen, etc.
        return mysqli_connect_error(); 
    }
    return $connection;
}

function db_quote($value) {
    $connection = db_connect();
    return "'" . mysqli_real_escape_string($connection,$value) . "'";
}

function db_prepare() {
    $sql = 'SELECT 1 FROM currency LIMIT 1';
    $connection = db_connect();
    $result = $connection->query($sql);
    if (!$result) {
        $sql = 'CREATE TABLE currency ('.
            '`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY ,'.
            '`valuteID` varchar(10) NOT NULL,'.
            '`numCode` char(3) NOT NULL,'.
            '`сharCode` char(3) NOT NULL,'.
            '`nominal` int NOT NULL,'.
            '`name` varchar(50) NOT NULL,'.
            '`value` decimal(10,4) NOT NULL,'.
            '`date` date NOT NULL'.
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;';
        $result = $connection->query($sql);
        if (!$result) {
            echo "Error dp". __LINE__;
            exit;
        }
        populate_table($connection);
    }
}

// ==================================================== READ ========================

function db_read_valutes() {
    $sql = 'SELECT DISTINCT valuteID,numCode,charCode,name FROM currency ORDER BY name';
    $connection = db_connect();
    if (!$connection) {
        echo 'Error dg'.__LINE__;
        exit;
    }
    $result = $connection->query($sql);
    if (!$result) {
        echo 'Error dg'.__LINE__;
        exit;
    }
    return mysqli_fetch_all($result);
}

function db_read_excerpt($valuteID, $dateFrom, $dateTo) {
    $sql = 'SELECT nominal,value,date FROM currency WHERE valuteID=? AND date BETWEEN ? AND ? ORDER BY date';
    $connection = db_connect();
    $stmt = mysqli_stmt_init($connection);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        echo "Error dg". __LINE__;
        exit;
    }
    mysqli_stmt_bind_param($stmt, "sss", $valuteID, $dateFrom, $dateTo);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $nominal, $value, $date);
    $result = array();
    while (mysqli_stmt_fetch($stmt)) {
        array_push($result, array($nominal, $value, $date));
    }
    mysqli_stmt_close($stmt);
    return $result;
}

// ------------------ POPULATE --------------------------

function populate_table($connection) {
    $sql = 'INSERT INTO `currency` (`valuteID`, `numCode`, `charCode`, `nominal`, `name`, `value`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?)';
    $stmt = mysqli_stmt_init($connection);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        echo "Error pt". __LINE__;
        exit;
    }
    $now = new DateTime();
    $s = $now->format('Y-m-d');
    $i = 0;
    while ($i < 30) {
        $s = $now->format('d/m/Y');
        $test = get_data('http://www.cbr.ru/scripts/XML_daily.asp?date_req='.$s);
        $doc = new DOMDocument();
        $doc->loadXML($test, LIBXML_PARSEHUGE);
        $date = $doc->documentElement->attributes->getNamedItem('Date')->value;
        $date = substr($date, 6, 4) . '-' . substr($date, 3, 2) . '-' . substr($date, 0, 2);
        $elements = $doc->getElementsByTagName('Valute');
        foreach ($elements as $node) {
            $id = $node->attributes->getNamedItem('ID')->value;
            $numcode = $node->getElementsByTagName('NumCode')->item(0)->nodeValue;
            $charcode = $node->getElementsByTagName('CharCode')->item(0)->nodeValue;
            $nominal = $node->getElementsByTagName('Nominal')->item(0)->nodeValue;
            $name = $node->getElementsByTagName('Name')->item(0)->nodeValue;
            $value = str_replace(',', '.', $node->getElementsByTagName('Value')->item(0)->nodeValue);
            mysqli_stmt_bind_param($stmt, "sssisds", $id, $numcode, $charcode, $nominal, $name, $value, $date);
            mysqli_stmt_execute($stmt);
        }
        $now = new DateTime($date);
        $now->sub(new DateInterval('P1D'));
        $i++;
    }
    mysqli_stmt_close($stmt);
}
