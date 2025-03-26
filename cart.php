<?php
header('Content-Type: application/json');
require_once 'config.php';

// Получаем токен из заголовков
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (empty($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

try {
    // Проверяем токен и получаем ID пользователя
    $stmt = $pdo->prepare("SELECT id FROM users WHERE token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Неверный токен авторизации']);
        exit;
    }

    $userId = $user['id'];
    $action = $_GET['action'] ?? '';

    // Обработка GET-запроса для получения содержимого корзины
    if ($action === 'get') {
        $stmt = $pdo->prepare("
            SELECT p.id as product_id, p.name, p.price, p.image, c.quantity 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($cartItems);
        exit;
    }

    // Обработка POST-запросов
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Неверный формат данных']);
        exit;
    }

    $productId = $input['product_id'] ?? null;
    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Не указан ID товара']);
        exit;
    }

    // ДОБАВЛЕННАЯ ПРОВЕРКА НА ЧИСЛОВОЙ ФОРМAT - НАЧАЛО
    if (!is_numeric($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
        exit;
    }
    $productId = (int)$productId;
    // ДОБАВЛЕННАЯ ПРОВЕРКА НА ЧИСЛОВОЙ ФОРМAT - КОНЕЦ

    switch ($input['action'] ?? '') {
        case 'add':
            // Проверяем существование товара
            $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Товар не найден']);
                exit;
            }
        
            // Проверяем, есть ли уже товар в корзине
            $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $existingItem = $stmt->fetch();
        
            if ($existingItem) {
                // Увеличиваем количество ровно на 1
                $newQuantity = $existingItem['quantity'] + 1;
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$newQuantity, $userId, $productId]);
            } else {
                // Добавляем новый товар с количеством 1
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                $stmt->execute([$userId, $productId]);
            }
            break;

            case 'update':
                // Получаем текущее количество из корзины
                $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $productId]);
                $currentItem = $stmt->fetch();
                
                if (!$currentItem) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Товар не найден в корзине']);
                    exit;
                }
                
                // Получаем delta (изменение количества) из запроса
                $delta = isset($input['delta']) ? (int)$input['delta'] : 0;
                
                // Вычисляем новое количество
                $newQuantity = $currentItem['quantity'] + $delta;
                
                // Гарантируем, что количество не меньше 1
                $newQuantity = max(1, $newQuantity);
                
                // Обновляем количество в базе данных
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$newQuantity, $userId, $productId]);
                break;
        case 'remove':
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            break;

        case 'clear':
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
            exit;
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера: ' . $e->getMessage()]);
}
?>