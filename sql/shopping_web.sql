-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost
-- Thời gian đã tạo: Th5 12, 2025 lúc 05:25 AM
-- Phiên bản máy phục vụ: 10.4.28-MariaDB
-- Phiên bản PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
CREATE DATABASE IF NOT EXISTS `shopping_web` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `shopping_web`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `image_path`, `created_at`) VALUES
(1, 'Laptops', 'High-performance laptops for gaming and professional use', '/assets/images/local/gaming_laptop_x1_1.png', '2025-05-07 10:55:23'),
(2, 'Accessories', 'Essential accessories for electronic devices', '/assets/images/local/wireless_headphones_1.png', '2025-05-07 10:55:23'),
(3, 'Phones', 'Smartphones with advanced features', '/assets/images/local/smartphone_z9_1.png', '2025-05-07 10:55:23'),
(4, 'Cameras', 'Cameras for photography, action recording, and security', '/assets/images/local/action_camera_4k_1.png', '2025-05-07 10:55:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jwt_blacklist`
--

CREATE TABLE `jwt_blacklist` (
  `id` int(11) NOT NULL,
  `token` varchar(512) NOT NULL,
  `blacklisted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `city`, `country`, `zip_code`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES
(6, 5, 'Minh', 'Le', 'minhlh@gmail.com', '0987654321', 'Phu Xuyen', 'Ha Noi', 'Viet Nam', '', 200.00, 'cancelled', '2025-05-09 13:20:21', '2025-05-10 09:38:10'),
(7, 5, 'Hao', 'Nguyen', 'yinghao36@gmail.com', '0987654321', 'Thanh Hoa', 'Thanh Hoa', 'Viet Nam', '', 820.00, 'pending', '2025-05-10 04:46:48', '2025-05-10 09:46:48'),
(8, 1, 'Minh', 'Le', 'minhlh56@example.com', '0987654321', 'Phu Xuyen', 'Ha Noi', 'Viet Nam', '12345', 840.00, 'shipped', '2025-05-10 05:05:10', '2025-05-10 13:55:20'),
(9, 1, 'Minh', 'Le', 'minhlh56@example.com', '0987654321', 'Phu Xuyen', 'Ha Noi', 'Viet Nam', '', 1170.00, 'pending', '2025-05-10 05:07:30', '2025-05-10 10:07:30'),
(10, 1, 'Minh', 'Le', 'minhlh56@example.com', '0987654321', 'Ha Dong', 'Ha Noi', 'Viet Nam', '', 3750.00, 'pending', '2025-05-10 05:12:55', '2025-05-10 10:12:55'),
(11, 1, 'Minh', 'Le', 'minhlh56@example.com', '0987654321', 'Hà Đông', 'Hà Nội', 'Việt Nam', '', 2400.00, 'completed', '2025-05-10 05:14:44', '2025-05-10 15:53:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(6, 6, 7, 1, 200.00),
(7, 7, 7, 1, 200.00),
(8, 7, 4, 1, 600.00),
(9, 7, 3, 1, 20.00),
(10, 8, 8, 1, 70.00),
(11, 8, 3, 1, 20.00),
(12, 8, 5, 1, 750.00),
(13, 9, 8, 1, 70.00),
(14, 9, 7, 1, 200.00),
(15, 9, 6, 1, 900.00),
(16, 10, 2, 2, 1200.00),
(17, 10, 1, 1, 1350.00),
(18, 11, 4, 4, 600.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `sale_price`, `stock`, `created_at`, `updated_at`) VALUES
(1, 1, 'Gaming Laptop X1', '15.6-inch gaming laptop with RTX 3060 GPU, 16GB RAM', 1500.00, 1350.00, 24, '2025-05-07 10:55:23', '2025-05-10 10:12:55'),
(2, 1, 'Ultrabook Pro', '13.3-inch ultrabook with Intel i7, 512GB SSD', 1200.00, NULL, 28, '2025-05-07 10:55:23', '2025-05-10 10:12:55'),
(3, 2, 'Wireless Headphones', 'Noise-cancelling wireless headphones with 30-hour battery', 150.00, 20.00, 78, '2025-05-07 10:55:23', '2025-05-10 10:05:10'),
(4, 2, 'Tablet Pro', '10.5-inch tablet with 256GB storage, stylus support', 600.00, NULL, 495, '2025-05-07 10:55:23', '2025-05-10 10:14:44'),
(5, 3, 'Smartphone Z9', '6.7-inch AMOLED smartphone with 128GB storage', 800.00, 750.00, 49, '2025-05-07 10:55:23', '2025-05-10 10:05:10'),
(6, 4, 'DSLR Camera Pro', '24MP DSLR camera with 18-55mm lens, ideal for professional photography', 1000.00, 900.00, 19, '2025-05-07 10:55:23', '2025-05-10 10:07:30'),
(7, 4, 'Action Camera 4K', 'Waterproof 4K action camera with wide-angle lens, perfect for outdoor adventures', 200.00, NULL, 48, '2025-05-07 10:55:23', '2025-05-10 10:07:30'),
(8, 4, 'Security Camera HD', '1080p Wi-Fi security camera with night vision and motion detection', 80.00, 70.00, 98, '2025-05-07 10:55:23', '2025-05-10 10:07:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_path`, `is_primary`, `created_at`) VALUES
(1, 1, '/assets/images/local/gaming_laptop_x1_1.png', 1, '2025-05-07 10:56:48'),
(2, 1, '/assets/images/local/gaming_laptop_x1_2.png', 0, '2025-05-07 10:56:48'),
(3, 2, '/assets/images/local/ultrabook_pro_1.png', 1, '2025-05-07 10:56:48'),
(4, 2, '/assets/images/local/ultrabook_pro_2.png', 0, '2025-05-07 10:56:48'),
(5, 3, '/assets/images/local/wireless_headphones_1.png', 1, '2025-05-07 10:56:48'),
(6, 3, '/assets/images/local/wireless_headphones_2.png', 0, '2025-05-07 10:56:48'),
(7, 4, '/assets/images/local/tablet_pro_1.png', 1, '2025-05-07 10:56:48'),
(8, 4, '/assets/images/local/tablet_pro_2.png', 0, '2025-05-07 10:56:48'),
(9, 5, '/assets/images/local/smartphone_z9_1.png', 1, '2025-05-07 10:56:48'),
(10, 5, '/assets/images/local/smartphone_z9_2.png', 0, '2025-05-07 10:56:48'),
(11, 6, '/assets/images/local/dslr_camera_pro_1.png', 1, '2025-05-07 10:56:48'),
(12, 6, '/assets/images/local/dslr_camera_pro_2.png', 0, '2025-05-07 10:56:48'),
(13, 7, '/assets/images/local/action_camera_4k_1.png', 1, '2025-05-07 10:56:48'),
(14, 7, '/assets/images/local/action_camera_4k_1.png', 0, '2025-05-07 10:56:48'),
(15, 7, '/assets/images/local/action_camera_4k_1.png', 0, '2025-05-07 10:56:48'),
(16, 8, '/assets/images/local/security_camera_hd_1.png', 1, '2025-05-07 10:56:48');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `remember_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `full_name`, `phone`, `address`, `avatar_path`, `is_active`, `created_at`, `updated_at`, `reset_token`, `reset_expires`, `remember_token`, `remember_expires`) VALUES
(1, 'minhlh56', 'minhlh56@gmail.com', '$2y$10$Uo01iR.9kII.a0kcZyo7iOvBQPwF3/2tZ91N57wCIRl93NYyC98nK', 'admin', 'Minh Lê', '0343331888', 'Hà Nội', '', 1, '2025-05-06 11:09:11', '2025-05-12 03:12:25', NULL, NULL, NULL, NULL),
(2, 'nmt3107', 'nmt3107@example.com', '$2y$10$z.jI8b6sOaLpvTpVJB7OM.1EBLwVY8I6Tpd.Jc2biSum0M1HXqYBa', 'user', 'Tuan Nguyen', '0987654444', 'Tây Mỗ, Hà Nội', NULL, 1, '2025-05-06 11:17:30', '2025-05-10 18:15:31', NULL, NULL, NULL, NULL),
(3, 'ntk128', 'ntk128@example.com', '$2y$10$kfiQzBb3QNf6u1A4Xz80veujUdJJ2pTGe/Q.BO0DMI8sfJTZWSwiG', 'user', 'Kien Nguyen', '0987654321', 'Bac Ninh', NULL, 1, '2025-05-06 11:29:45', '2025-05-10 18:17:57', NULL, NULL, NULL, NULL),
(4, 'kientran123', 'kientran123@example.com', '$2y$10$aM4t4sMhT1UXXbJClurnPOyh7Npe/uU3cgZG42GKahrR5BObnewJG', 'user', 'Kiên Trần', '0987654321', 'Bắc Giang', NULL, 0, '2025-05-06 11:31:28', '2025-05-11 08:36:13', NULL, NULL, NULL, NULL),
(5, 'yinghao36', 'yinghao3203@gmail.com', '$2y$10$4ZBXNXPUhlVQ94vNTNcOzuJuowy6i717QtmtmCi8jIs8EtJ5eo3j.', 'user', 'Hao Nguyen', '0987654321', 'Nghệ An', '', 1, '2025-05-06 11:47:02', '2025-05-11 15:26:35', NULL, NULL, NULL, NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Chỉ mục cho bảng `jwt_blacklist`
--
ALTER TABLE `jwt_blacklist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`(255)),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_product_name` (`product_name`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `jwt_blacklist`
--
ALTER TABLE `jwt_blacklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
