<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'employee_system';
$charset = 'utf8mb4';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset($charset);
} catch (mysqli_sql_exception $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function getPDO(): PDO {
    global $host, $database, $username, $password, $charset;

    $dsn = "mysql:host={$host};dbname={$database};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        return new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
        throw new PDOException('Database connection failed: ' . $e->getMessage());
    }
}

?>
