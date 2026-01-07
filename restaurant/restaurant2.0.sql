-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2025 at 03:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `email`, `created_at`) VALUES
(1, 'Customer_1', NULL, '2025-09-22 13:09:14');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`id`, `employee_id`, `name`, `email`, `phone`, `address`, `password`, `role`, `created_at`) VALUES
(1, '100', 'Delvin Jose', 'd@gmail.com', '4545454545', 'hgdtydtfftftf', '$2y$10$n7K39ldA.HU7PfK2sotlz.dmEoCqhDHL8WZN25mbL.BmwtksnQ/KC', 'employee', '2025-08-01 14:34:37'),
(2, '101', 'joseph', 'j@gmail.com', '7878787878', 'jhghdytfjh', '$2y$10$XmZOeqkGrcxa.vz5VY1.oevlstKllMEivp2zzUQy447WYX5AC5Z6m', 'employee', '2025-08-01 14:41:58');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rating` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `message`, `created_at`, `rating`) VALUES
(8, 'kiran', 'kiran@gmail.com', 'good food', '2025-09-23 01:16:28', 5);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `name`, `price`, `category`, `image`) VALUES
(6, 'Berry Cheese Cake', 250.00, 'Desserts', 'berry-cheesecake.jpg'),
(7, 'CHOCOLATE MARSHMALLOW CAKE', 300.00, 'Desserts', 'chocolate-marshmallow-cake.jpg'),
(9, 'BIRIYANI', 140.00, 'Main Course', 'Biriyani.jpg'),
(11, 'TURKEY BURGER', 200.00, 'Burger', 'turkey_burger.png'),
(12, 'THAI SWEET CHILI TURKEY BURGER', 250.00, 'Burger', 'Thai_Sweet_Chili_Turkey_Burger.png'),
(13, 'SLUTTY VEGAN\'S ONE NIGHT STAND BURGER', 300.00, 'Burger', 'Slutty_Vegan_s_One_Night_Stand_Burger.png'),
(14, 'CHICKEN & BROCCOLI', 150.00, 'Main Course', 'Chicken___Broccoli.png'),
(15, 'CHICKEN FRIED RICE', 150.00, 'Main Course', 'Chicken_Fried_Rice.png'),
(16, 'WAFFELS WITH CREAM AND JAM', 80.00, 'Desserts', 'waffles-with-cream-and-jam.jpg'),
(17, 'DRAGON FRUIT DROP MARTINI', 60.00, 'Drinks', 'Dragon_Fruit_Drop_Martini.png'),
(18, 'KEY LIME MOJITOS', 50.00, 'Drinks', 'Key_Lime_Mojitos.png'),
(19, 'WATERMELON MARGARITAS', 50.00, 'Drinks', 'Watermelon_Margaritas.png'),
(20, 'BBQ CHICKEN PIZZA', 200.00, 'Pizza', 'BBQ_Chicken_Pizza.png'),
(21, 'FRENCH PIZZA BREAD', 250.00, 'Pizza', 'French_Pizza_Bread.png'),
(22, 'VEGAN PIZZA', 300.00, 'Pizza', 'Vegan_Pizza.png'),
(23, 'TOFU NUGGETS', 100.00, 'Snacks', 'Tofu_Nuggets.png');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('Paid','Pending','Failed') DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `customer_id`, `amount`, `payment_method`, `status`, `payment_date`) VALUES
('PAY20250923030914290', 1, 200.00, '0', 'Paid', '2025-09-22 13:09:14'),
('PAY20250923031158794', 1, 500.00, '0', 'Paid', '2025-09-22 13:11:58');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `table_id` int(11) NOT NULL,
  `table_number` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  `table_status` enum('Available','Reserved') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`table_id`, `table_number`, `capacity`, `table_status`) VALUES
(15, 1, 4, 'Available'),
(16, 2, 4, 'Available'),
(17, 3, 5, 'Available'),
(18, 4, 2, 'Available'),
(19, 5, 2, 'Available'),
(20, 6, 2, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `table_reservations`
--

CREATE TABLE `table_reservations` (
  `reservation_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `table_number` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reservation_date` date DEFAULT NULL,
  `reservation_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_reservations`
--

INSERT INTO `table_reservations` (`reservation_id`, `table_id`, `table_number`, `capacity`, `reserved_at`, `reservation_date`, `reservation_time`) VALUES
(10, 20, 6, 2, '2025-09-19 00:24:49', '2025-09-19', '13:55:00'),
(11, 16, 2, 4, '2025-09-22 16:18:24', '2025-09-22', '11:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `customerId` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `customerId`, `first_name`, `last_name`, `phone`, `email`, `password`, `role`, `created_at`) VALUES
(1, '100', 'kasinathan', 'P B', '4545454545', 'k@gmail.com', '$2y$10$YacdZCr9Fm21oKTjMUJeL.0hsP1heUam3YbdSWUWjRx2AzEs5nmIS', 'admin', '2025-07-20 08:29:33'),
(2, '101', 'Mahendran', 'B', '7878787878', 'm@gmail.com', '$2y$10$84RF2Th1G8X5t1zzkTG0/.qOdkvmufwCEiRVasifrWdrTNq9VbMcK', 'user', '2025-07-20 08:30:05'),
(3, '102', 'Delvin', 'jose', '1212121212', 'd@gmail.com', '$2y$10$QE8sO5/Rk3nrFtHikqwcBOR5XPiojf6ee1cqJaWn1D.SZ/Mi0vVmy', 'user', '2025-07-20 14:29:17'),
(4, '103', 'Joseph', 'joseph', '9696969696', 'j@gmail.com', '$2y$10$RUzcfy16mE0fFwgzESoHDO/aZcPtH9tP0vsJLuLIBsMZ99tSbfKUu', 'user', '2025-07-20 14:32:32'),
(7, '104', 'Alan', 'jacob', '9544987166', 'a@gmail.com', '$2y$10$ZgdHL58HRU9/odWX9JCQO.hVdr0uH4.jc.YMw0S7KrWsoZ5sTn84u', 'user', '2025-07-26 15:52:04'),
(8, '108', 'Gopi', 'Shankar', '9544987166', 'g@gmail.com', '$2y$10$tMcr2/4luIxDXqG9Av0vsuBIZBLr/xKhgjycvucT/xwVs8TimMGYW', 'user', '2025-08-01 18:13:00'),
(10, '110', 'Kailasnath', 'cv', '9778255905', 'ka@gmail.com', '$2y$10$Os9ztXTk4H5l7Hk1kkBCMeHJ6aItknn.b6kwg5Aq0EViBmdzTu2pG', 'user', '2025-09-16 01:27:59'),
(11, '200', 'Vishnu', ' K Shaji', '9061607843', 'v@gmail.com', '$2y$10$77r/KRlRGQulcR97qHAgQOAU.k.b50Yj1YNkp2Qt7WuYdLDkfdFmy', 'user', '2025-09-22 16:36:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`table_id`);

--
-- Indexes for table `table_reservations`
--
ALTER TABLE `table_reservations`
  ADD PRIMARY KEY (`reservation_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customerId` (`customerId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `table_reservations`
--
ALTER TABLE `table_reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
