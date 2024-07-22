-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2024 at 10:07 PM
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
-- Database: `shopfy_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_description` text DEFAULT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `image_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`item_id`, `item_name`, `item_description`, `item_price`, `item_quantity`, `image_name`) VALUES
(1, 'Toothpaste', 'Fluoride toothpaste for cavity protection', 16.00, 500, 'toothpaste.jpg'),
(2, 'Shampoo', 'Moisturizing shampoo for all hair types', 35.00, 300, 'shampoo.jpg'),
(3, 'Hand Soap', 'Liquid hand soap with antibacterial properties', 49.50, 400, 'hand_soap.jpg'),
(4, 'Laundry Detergent', 'High-efficiency liquid laundry detergent', 27.00, 200, 'laundry_detergent.jpg'),
(5, 'Dish Soap', 'Lemon-scented dish soap for sparkling clean dishes', 25.00, 350, 'dish_soap.jpg'),
(6, 'Paper Towels', 'Absorbent paper towels for everyday spills', 20.00, 250, 'paper_towels.jpg'),
(7, 'Toilet Paper', 'Soft and strong toilet paper', 14.00, 300, 'toilet_paper.jpg'),
(8, 'Garbage Bags', 'Heavy-duty garbage bags for kitchen use', 50.00, 150, 'garbage_bags.jpg'),
(9, 'Batteries', 'AA batteries for household devices', 80.00, 200, 'batteries.jpg'),
(10, 'Light Bulbs', 'Energy-efficient LED light bulbs', 120.00, 100, 'light_bulbs.jpg'),
(11, 'Coffee', 'Ground coffee for a rich and smooth taste', 67.00, 180, 'coffee.jpg'),
(12, 'Tea Bags', 'Assorted tea bags for a relaxing experience', 21.00, 250, 'tea_bags.jpg'),
(13, 'Cereal', 'Healthy whole grain cereal', 90.00, 200, 'cereal.jpg'),
(14, 'Pasta', 'Durum wheat semolina pasta', 150.00, 300, 'pasta.jpg'),
(15, 'Rice', 'Long grain white rice', 350.00, 250, 'rice.jpg'),
(16, 'Canned Soup', 'Hearty vegetable soup', 85.00, 200, 'canned_soup.jpg'),
(17, 'Olive Oil', 'Extra virgin olive oil', 59.00, 150, 'olive_oil.jpg'),
(18, 'Spices', 'Assorted spices for cooking', 110.00, 100, 'spices.jpg'),
(19, 'Cleaning Spray', 'Multi-surface cleaning spray', 39.50, 200, 'cleaning_spray.jpg'),
(20, 'Sponges', 'Non-scratch kitchen sponges', 25.00, 180, 'sponges.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `order_table`
--

CREATE TABLE `order_table` (
  `order_id` int(11) NOT NULL,
  `order_status` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `item_price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`) VALUES
(1, 'aschilledeleon', 'aschilledeleon@gmail.com', '$2y$10$Lwl/VIKTKT29fuCWyclSYOYD6hzp7q5L4em7LRuGvzBobpI56cxDS');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `order_table`
--
ALTER TABLE `order_table`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_table`
--
ALTER TABLE `order_table`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_table`
--
ALTER TABLE `order_table`
  ADD CONSTRAINT `order_table_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_table_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
