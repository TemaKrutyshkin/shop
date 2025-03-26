<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Регистрация</h1>
            <nav>
                <a href="index.html">Главная</a>
                <a href="login.php">Вход</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php
        // Подключение к базе данных
        include 'db_connect.php';
        
        $error = '';
        $success = '';
        
        // Обработка формы регистрации
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $password_confirm = $_POST['password_confirm'];
            $buyer_type = $_POST['buyer_type'];
            
            // Валидация данных
            if (empty($username) || empty($email) || empty($password) || empty($buyer_type)) {
                $error = 'Все поля обязательны для заполнения';
            } elseif ($password !== $password_confirm) {
                $error = 'Пароли не совпадают';
            } elseif (strlen($password) < 6) {
                $error = 'Пароль должен содержать минимум 6 символов';
            } else {
                // Проверка уникальности логина и email
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                
                if ($stmt->rowCount() > 0) {
                    $error = 'Пользователь с таким логином или email уже существует';
                } else {
                    // Хеширование пароля
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Сохранение в базу данных
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, buyer_type) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $password_hash, $buyer_type]);
                    
                    $success = 'Регистрация прошла успешно! Теперь вы можете <a href="login.php">войти</a>.';
                }
            }
        }
        ?>

        <form id="registerForm" class="auth-form" method="post">
            <h2>Создать аккаунт</h2>
            
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?= $success ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Логин:</label>
                <input type="text" id="username" name="username" required 
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Подтвердите пароль:</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            
            <div class="form-group">
                <label>Тип покупателя:</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="buyer_type" value="retail" 
                               <?= isset($_POST['buyer_type']) && $_POST['buyer_type'] === 'retail' ? 'checked' : '' ?> required>
                        Розничный покупатель
                    </label>
                    <label>
                        <input type="radio" name="buyer_type" value="wholesale" 
                               <?= isset($_POST['buyer_type']) && $_POST['buyer_type'] === 'wholesale' ? 'checked' : '' ?>>
                        Оптовый покупатель
                    </label>
                </div>
            </div>
            
            <button type="submit" class="auth-button">Зарегистрироваться</button>
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </form>
    </main>

    <script src="js/auth.js"></script>
</body>
</html>