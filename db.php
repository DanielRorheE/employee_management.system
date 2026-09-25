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

$conn->query("
    CREATE TABLE IF NOT EXISTS employee_history (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NULL,
        full_name VARCHAR(150) NOT NULL,
        action VARCHAR(50) NOT NULL,
        details VARCHAR(255) NOT NULL DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

function log_employee_history($employeeId, $fullName, $action, $details = '') {
    global $conn;

    $stmt = $conn->prepare(
        "INSERT INTO employee_history (employee_id, full_name, action, details, created_at)
         VALUES (?, ?, ?, ?, NOW())"
    );

    $bindName = trim((string) $fullName);
    $bindAction = trim((string) $action);
    $bindDetails = trim((string) $details);

    $stmt->bind_param("isss", $employeeId, $bindName, $bindAction, $bindDetails);
    $stmt->execute();
    $stmt->close();
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
