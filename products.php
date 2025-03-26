<?php
header('Content-Type: application/json');
require_once 'config.php';

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_all':
            $stmt = $pdo->query("SELECT * FROM products");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($products);
            break;
        
        default:
            echo json_encode(['error' => 'Неизвестное действие']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>