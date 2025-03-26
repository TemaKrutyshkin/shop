document.addEventListener('DOMContentLoaded', function() {
    checkAuthAndLoadCart();
    
    document.getElementById('logout').addEventListener('click', function(e) {
        e.preventDefault();
        logoutUser();
    });

    document.getElementById('checkout-button').addEventListener('click', checkout);
});

async function checkAuthAndLoadCart() {
    try {
        const token = localStorage.getItem('auth_token');
        if (!token) throw new Error('Требуется авторизация');

        const response = await fetch('php/auth.php?action=check', {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        
        if (!response.ok) throw new Error('Ошибка авторизации');
        
        const data = await response.json();
        if (!data.success) throw new Error('Неверный токен');
        
        await loadCartItems();
    } catch (error) {
        console.error('Ошибка:', error);
        alert(error.message);
        localStorage.removeItem('auth_token');
        window.location.href = 'login.html';
    }
}

async function loadCartItems() {
    try {
        const response = await fetch('php/cart.php?action=get', {
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('auth_token') }
        });
        
        if (!response.ok) throw new Error('Ошибка загрузки корзины');
        
        const items = await response.json();
        renderCartItems(items);
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Не удалось загрузить корзину');
        throw error;
    }
}

async function changeQuantity(productId, delta) {
    try {
        const response = await fetch('php/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
            },
            body: JSON.stringify({
                action: 'update',
                product_id: productId,
                delta: delta // Отправляем изменение количества (+1 или -1)
            })
        });
        
        if (!response.ok) throw new Error('Ошибка обновления количества');
        
        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Ошибка при обновлении');
        
        await loadCartItems(); // Обновляем отображение корзины
    } catch (error) {
        console.error('Ошибка:', error);
        alert(error.message);
    }
}

function renderCartItems(items) {
    const container = document.getElementById('cart-items');
    let total = 0;
    
    if (!items || items.length === 0) {
        container.innerHTML = '<p>Ваша корзина пуста</p>';
        document.getElementById('total-price').textContent = '0';
        return;
    }

    container.innerHTML = '';
    items.forEach(item => {
        total += item.price * item.quantity;
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div class="cart-item-image">
                <img src="images/${item.image}" alt="${item.name}" onerror="this.src='images/no-image.jpg'">
            </div>
            <div class="cart-item-info">
                <h3>${item.name}</h3>
                <p>${item.price} руб. x ${item.quantity}</p>
            </div>
            <div class="cart-item-actions">
                <button onclick="changeQuantity(${item.product_id}, -1)">-</button>
                <button onclick="changeQuantity(${item.product_id}, 1)">+</button>
                <button onclick="removeFromCart(${item.product_id})">Удалить</button>
            </div>
        `;
        container.appendChild(cartItem);
    });

    document.getElementById('total-price').textContent = total.toFixed(2);
}

async function updateQuantity(productId, newQuantity) {
    try {
        // Преобразуем в число и округляем
        newQuantity = Math.max(1, Math.round(Number(newQuantity)));
        
        const response = await fetch('php/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
            },
            body: JSON.stringify({
                action: 'update',
                product_id: productId,
                quantity: newQuantity  // Отправляем конкретное количество, не дельту!
            })
        });
        
        if (!response.ok) throw new Error('Ошибка обновления');
        
        const data = await response.json();
        if (data.success) {
            await loadCartItems(); // Обновляем отображение корзины
        } else {
            throw new Error(data.message || 'Ошибка при обновлении количества');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        alert(error.message);
        await loadCartItems(); // Восстанавливаем актуальное состояние
    }
}

async function removeFromCart(productId) {
    try {
        const response = await fetch('php/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
            },
            body: JSON.stringify({
                action: 'remove',
                product_id: productId
            })
        });
        
        if (!response.ok) throw new Error('Ошибка удаления');
        
        await loadCartItems();
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Не удалось удалить товар');
    }
}

async function checkout() {
    try {
        const response = await fetch('php/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
            },
            body: JSON.stringify({
                action: 'clear'
            })
        });
        
        if (!response.ok) throw new Error('Ошибка оформления заказа');
        
        alert('Заказ оформлен! Спасибо за покупку.');
        await loadCartItems();
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Не удалось оформить заказ');
    }
}

async function logoutUser() {
    try {
        const response = await fetch('php/auth.php?action=logout', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
            }
        });
        
        localStorage.removeItem('auth_token');
        window.location.href = 'index.html';
    } catch (error) {
        console.error('Ошибка:', error);
        localStorage.removeItem('auth_token');
        window.location.href = 'index.html';
    }
}