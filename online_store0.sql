-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Мар 26 2025 г., 17:29
-- Версия сервера: 8.0.30
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `online_store0`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`) VALUES
(9, 1, 2, 10),
(10, 1, 4, 4),
(12, 1, 3, 1),
(17, 2, 2, 6),
(18, 2, 3, 4),
(20, 7, 3, 5),
(21, 7, 4, 4),
(22, 7, 10, 6),
(23, 7, 11, 1),
(24, 7, 12, 2),
(25, 8, 2, 1),
(26, 8, 3, 1),
(27, 8, 4, 1),
(28, 8, 1, 1),
(31, 11, 2, 1),
(32, 11, 3, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text,
  `image` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `image`, `category`) VALUES
(1, 'Пластиковый контейнер 1л', '150.00', 'Герметичный контейнер для хранения продуктов', 'container1.jpg', 'Контейнеры'),
(2, 'Пластиковый контейнер 2л', '220.00', 'Большой контейнер с крышкой', 'container2.jpg', 'Контейнеры'),
(3, 'Набор пищевых контейнеров', '450.00', '3 контейнера разного объема', 'set1.jpg', 'Наборы'),
(4, 'Пластиковая бутылка 0.5л', '80.00', 'Бутылка для воды с крышкой', 'bottle1.jpg', 'Бутылки'),
(5, 'Пластиковая бутылка 1л', '120.00', 'Большая бутылка для напитков', 'bottle2.jpg', 'Бутылки'),
(6, 'Пакеты для мусора 30л (10шт)', '200.00', 'Прочные мусорные пакеты', 'bags1.jpg', 'Пакеты'),
(7, 'Пакеты для мусора 60л (10шт)', '280.00', 'Большие мусорные пакеты', 'bags2.jpg', 'Пакеты'),
(8, 'Пищевая пленка 30см', '90.00', 'Пленка для упаковки продуктов', 'film1.jpg', 'Пленка'),
(9, 'Пищевая пленка 45см', '120.00', 'Широкая пищевая пленка', 'film2.jpg', 'Пленка'),
(10, 'Набор пластиковой посуды', '350.00', '20 предметов: тарелки, стаканы, приборы', 'set2.jpg', 'Наборы'),
(11, 'Пластиковый поднос', '180.00', 'Поднос для сервировки', 'tray1.jpg', 'Посуда'),
(12, 'Пластиковые стаканы 200мл (50шт)', '250.00', 'Одноразовые стаканы', 'cups1.jpg', 'Посуда'),
(13, 'Пластиковые тарелки (20шт)', '220.00', 'Одноразовые тарелки', 'plates1.jpg', 'Посуда'),
(14, 'Пластиковая корзина для белья', '320.00', 'Вместительная корзина', 'basket1.jpg', 'Для дома'),
(15, 'Пластиковый органайзер', '190.00', 'Для хранения мелочей', 'organizer1.jpg', 'Для дома');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_type` enum('retail','wholesale') DEFAULT 'retail',
  `buyer_type` enum('retail','wholesale') NOT NULL DEFAULT 'retail'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `token`, `created_at`, `user_type`, `buyer_type`) VALUES
(1, 'user1', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'token1', '2025-03-26 09:56:09', 'retail', 'retail'),
(2, 'user2', 'user2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'token2', '2025-03-26 09:56:09', 'retail', 'retail'),
(7, 'user7', 'user7@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'token7', '2025-03-26 09:56:09', 'retail', 'retail'),
(8, 'qazwsxedc', 'qazwsxedc@gmail.com', '$2y$10$C6KKTQyUJXSquqMg9kshUOuiRtYnWpvoYwzrR5/iYzG7xXST7Hja.', '39aa19430efcf514b8b97cb26fb88da3657d77a006c833fdf0f7c4ae882ef36f', '2025-03-26 09:58:08', 'retail', 'retail'),
(10, '9999999999', '9999999999@gmail.com', '$2y$10$qgug4sB5LgqexF2zYXxtl.K8iK6w/Rmz.USTeg4BBQcX354Y/IGtS', '5b12e4b9ee99c2b1495287e2f2a65998e3f7f663415fb5e8546e7e9ba0abe08a', '2025-03-26 11:29:35', 'retail', 'retail'),
(11, '123456', '123456@gmail.com', '$2y$10$SPaTfXWl1lQrnqJhsnNuTOks0fUbqf1l2YNnyjOtuG1FZkql37Ko2', '03783ccdd77d7eace008323ae3bd323bfeeac833be4a30a57a2d57050ac856c7', '2025-03-26 14:12:23', 'retail', 'retail');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
