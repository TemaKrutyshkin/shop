document.querySelector('a[href="index.html"]').addEventListener('click', function(e) {
    // Дополнительная логика перед переходом, если нужно
    window.location.href = 'index.html';
    e.preventDefault();
});
document.addEventListener('DOMContentLoaded', function() {
    // Временная функция для отладки
    function showDebugInfo(data) {
        console.log('Debug:', data);
        const debugDiv = document.getElementById('debugInfo');
        if (debugDiv) {
            debugDiv.style.display = 'block';
            document.getElementById('debugOutput').textContent = JSON.stringify(data, null, 2);
        }
    }

// Добавьте этот код в auth.js
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            username: document.getElementById('username').value.trim(),
            email: document.getElementById('email').value.trim(),
            password: document.getElementById('password').value
        };

        console.log('Отправка данных:', formData);

        try {
            const response = await fetch('php/auth.php?action=register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            
            console.log('Статус ответа:', response.status);
            
            const result = await response.json();
            console.log('Ответ сервера:', result);
            
            if (result.success) {
                alert('Регистрация успешна!');
                window.location.href = 'login.html';
            } else {
                alert('Ошибка: ' + (result.message || 'Неизвестная ошибка'));
            }
        } catch (error) {
            console.error('Ошибка:', error);
            alert('Ошибка соединения: ' + error.message);
        }
    });
}

    // Обработчик формы входа
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const loginData = {
                username: document.getElementById('login-username').value.trim(),
                password: document.getElementById('login-password').value
            };

            console.log('Sending:', loginData); // Логируем данные перед отправкой

            try {
                const response = await fetch('php/auth.php?action=login', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(loginData)
                });

                console.log('Response status:', response.status);
                
                const result = await response.json();
                showDebugInfo(result);
                
                if (result.success && result.token) {
                    localStorage.setItem('auth_token', result.token);
                    alert('Вход выполнен успешно!');
                    window.location.href = 'index.html';
                } else {
                    alert(result.message || 'Ошибка входа. Проверьте логин и пароль');
                }
            } catch (error) {
                console.error('Full error:', error);
                alert('Ошибка при входе: ' + error.message);
            }
        });
    }
});
// Добавьте этот код в конец auth.js
function setupAuthUI() {
    const authNav = document.getElementById('auth-nav');
    const token = localStorage.getItem('auth_token');
    
    if (authNav) {
        if (token) {
            // Показываем кнопку выхода для авторизованных
            authNav.innerHTML = `
                <button id="logoutBtn" class="nav-button">Выйти</button>
                <a href="cart.html" class="nav-button">Корзина</a>
            `;
        } else {
            // Показываем кнопки входа/регистрации
            authNav.innerHTML = `
                <a href="login.html" class="nav-button">Войти</a>
                <a href="register.html" class="nav-button">Регистрация</a>
            `;
        }
    }
}

// Обработчик выхода
function setupLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) return;

                const response = await fetch('php/auth.php?action=logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    localStorage.removeItem('auth_token');
                    window.location.href = 'login.html';
                }
            } catch (error) {
                console.error('Logout error:', error);
                alert('Ошибка при выходе');
            }
        });
    }
}

// Инициализация при загрузке
document.addEventListener('DOMContentLoaded', () => {
    setupAuthUI();
    setupLogout();
    
    // Проверка авторизации для защищенных страниц
    if (!localStorage.getItem('auth_token') && 
        !['login.html', 'register.html'].includes(window.location.pathname.split('/').pop())) {
        window.location.href = 'login.html';
    }
});
// Добавьте в конец файла auth.js
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для ссылки "Главная"
    const homeLink = document.getElementById('home-link');
    if (homeLink) {
        homeLink.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'index.html';
        });
    }
});
