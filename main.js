document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
});
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('logout')?.addEventListener('click', logoutUser);
});

function loadProducts() {
    fetch('php/products.php?action=get_all')
        .then(response => response.json())
        .then(products => {
            const container = document.getElementById('products-container');
            container.innerHTML = '';
            
            products.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'product-card';
                productCard.innerHTML = `
                    <img src="images/${product.image}" alt="${product.name}" class="product-image">
                    <div class="product-info">
                        <h3>${product.name}</h3>
                        <p>${product.description}</p>
                        <p class="price">${product.price} руб.</p>
                        <button class="add-to-cart" onclick="addToCart(${product.id})">В корзину</button>
                    </div>
                `;
                container.appendChild(productCard);
            });
        })
        .catch(error => console.error('Ошибка:', error));
}

function addToCart(productId) {
    if (!localStorage.getItem('auth_token')) {
        alert('Пожалуйста, войдите в систему, чтобы добавить товар в корзину');
        window.location.href = 'login.html';
        return;
    }

    // Блокируем кнопку на время запроса
    const addButton = event.target;
    addButton.disabled = true;
    addButton.textContent = 'Добавление...';

    fetch('php/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Ошибка сети');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Показываем уведомление о добавлении
            showNotification('Товар добавлен в корзину');
        } else {
            throw new Error(data.message || 'Ошибка при добавлении в корзину');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert(error.message);
    })
    .finally(() => {
        addButton.disabled = false;
        addButton.textContent = 'В корзину';
    });
}

// Функция для показа уведомлений
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 500);
    }, 2000);
}

// Функция для показа уведомлений
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 500);
    }, 2000);
}

function updateAuthUI() {
    const isLoggedIn = localStorage.getItem('auth_token') !== null;
    const authNav = document.getElementById('auth-nav');
    
    if (authNav) {
        authNav.innerHTML = isLoggedIn ? `
            <a href="index.html">Главная</a>
            <a href="cart.html">Корзина</a>
            <a href="#" id="logout">Выйти</a>
        ` : `
            <a href="index.html">Главная</a>
            <a href="cart.html">Корзина</a>
            <a href="login.html">Вход</a>
            <a href="register.html">Регистрация</a>
        `;
        
        // Добавляем обработчик для кнопки выхода
        document.getElementById('logout').addEventListener('click', function(e) {
            e.preventDefault();
            logoutUser ();
        });
    }
}

// Вызываем при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    updateAuthUI();
    loadProducts();
});

