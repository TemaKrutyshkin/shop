<?php
// Убедимся, что никакой вывод не происходит
if (headers_sent()) {
    die('Headers already sent!');
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Проверка существования файла
if (!file_exists(__DIR__ . '/config.php')) {
    error_log('Config file not found!');
    die(json_encode(['success' => false, 'message' => 'System error']));
}

$host = 'localhost';
$dbname = 'online_store';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    error_log('DB Connection failed: ' . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}
?>