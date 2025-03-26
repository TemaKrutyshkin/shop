<?php
// Включение полного логгирования ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Установка заголовков
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Обработка предварительных OPTIONS-запросов CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Подключение к базе данных
require_once 'config.php';

/**
 * Валидация токена пользователя
 */
function validateToken($pdo, $token) {
    if (empty($token)) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Token validation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Проверка авторизации (для корзины и других защищенных разделов)
 */
function checkAuthorization($pdo) {
    $headers = getallheaders();
    $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
    
    $user = validateToken($pdo, $token);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
        exit;
    }
    
    return $user;
}

// Получение токена из заголовков
$headers = getallheaders();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
$user = validateToken($pdo, $token);

// Проверка авторизации для защищенных методов
$protectedActions = ['get_user', 'update_user', 'logout', 'check', 'get_cart'];
if (in_array($_GET['action'] ?? '', $protectedActions)) {
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
        exit;
    }
    $userId = $user['id'];
}

// Получение входных данных
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'register':
            // Логируем полученные данные
            error_log('Register request: ' . print_r($input, true));
            
            // Валидация
            $errors = [];
            if (empty($input['username'])) $errors[] = 'Логин не может быть пустым';
            if (empty($input['email'])) $errors[] = 'Email не может быть пустым';
            if (empty($input['password'])) $errors[] = 'Пароль не может быть пустым';
            
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
                exit;
            }
        
            $username = trim($input['username']);
            $email = trim($input['email']);
            $password = $input['password'];
        
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Некорректный email']);
                exit;
            }
        
            if (strlen($password) < 6) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Пароль должен быть не менее 6 символов']);
                exit;
            }
        
            try {
                // Проверка существования пользователя
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                
                if ($stmt->fetch()) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'message' => 'Пользователь с таким логином или email уже существует']);
                    exit;
                }
        
                // Создание пользователя
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$username, $email, $passwordHash])) {
                    error_log("User registered: $username");
                    echo json_encode([
                        'success' => true,
                        'message' => 'Регистрация успешна',
                        'user' => [
                            'username' => $username,
                            'email' => $email
                        ]
                    ]);
                } else {
                    throw new Exception('Ошибка выполнения запроса');
                }
            } catch (PDOException $e) {
                error_log("DB Error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
            }
            break;

        case 'login':
            // Валидация
            if (empty($input['username']) || empty($input['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Логин и пароль обязательны']);
                exit;
            }

            $username = trim($input['username']);
            $password = $input['password'];

            // Поиск пользователя
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
                exit;
            }

            // Проверка пароля
            if (!password_verify($password, $user['password'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Неверный пароль']);
                exit;
            }

            // Генерация токена
            $token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE users SET token = ? WHERE id = ?");
            
            if ($stmt->execute([$token, $user['id']])) {
                echo json_encode([
                    'success' => true,
                    'token' => $token,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                throw new Exception('Ошибка при обновлении токена');
            }
            break;

        case 'logout':
            $stmt = $pdo->prepare("UPDATE users SET token = NULL WHERE id = ?");
            if ($stmt->execute([$userId])) {
                echo json_encode(['success' => true, 'message' => 'Выход выполнен']);
            } else {
                throw new Exception('Ошибка при выходе из системы');
            }
            break;

        case 'get_cart':
            // Проверка авторизации уже выполнена ранее
            $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'cart' => $cartItems
            ]);
            break;

        case 'check':
            echo json_encode([
                'success' => true,
                'authenticated' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ]);
            break;

        case 'get_user':
            echo json_encode([
                'success' => true,
                'user' => [
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ]);
            break;

        case 'update_user':
            $updateFields = [];
            $params = [];
            
            if (!empty($input['username'])) {
                $updateFields[] = 'username = ?';
                $params[] = trim($input['username']);
            }
            
            if (!empty($input['password'])) {
                $updateFields[] = 'password = ?';
                $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
            }
            
            if (!empty($input['email'])) {
                $updateFields[] = 'email = ?';
                $params[] = trim($input['email']);
            }
            
            if (empty($updateFields)) {
                echo json_encode(['success' => false, 'message' => 'Нет данных для обновления']);
                exit;
            }
            
            $params[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute($params);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Данные обновлены' : 'Ошибка обновления'
            ]);
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Действие не найдено']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
} catch (Exception $e) {
    error_log("System error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Системная ошибка']);
}
?>