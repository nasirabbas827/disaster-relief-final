-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2026 at 12:14 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `disaster_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `affected_families`
--

CREATE TABLE `affected_families` (
  `id` int(11) NOT NULL,
  `head_name` varchar(150) NOT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `family_size` int(11) DEFAULT NULL,
  `original_location` varchar(200) DEFAULT NULL,
  `registered_by` int(11) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `affected_families`
--

INSERT INTO `affected_families` (`id`, `head_name`, `contact`, `family_size`, `original_location`, `registered_by`, `registered_at`) VALUES
(3, 'Ahmed Khan', '0301-1111111', 5, 'Flood Area A - Village Noor', 3, '2026-01-09 09:06:13'),
(4, 'Fatima Bibi', '0302-2222222', 4, 'Flood Area B - Village Rehman', 3, '2026-01-09 09:06:13'),
(5, 'Muhammad Ali', '0303-3333333', 6, 'River Belt - Sector 12', 3, '2026-01-09 09:06:13'),
(6, 'Ayesha Malik', '0304-4444444', 3, 'Lowland Zone - Block C', 3, '2026-01-09 09:06:13'),
(7, 'Sajid Hussain', '0305-5555555', 7, 'Disaster Zone East', 3, '2026-01-09 09:06:13'),
(8, 'Rubina Shaheen', '0306-6666666', 2, 'Evacuation Camp Road', 3, '2026-01-09 09:06:13'),
(9, 'Naveed Akram', '0307-7777777', 8, 'Floodplain Area North', 3, '2026-01-09 09:06:13'),
(10, 'Shazia Parveen', '0308-8888888', 4, 'Village Amanabad', 3, '2026-01-09 09:06:13'),
(11, 'Imran Yousaf', '0309-9999999', 5, 'River Bank Colony', 3, '2026-01-09 09:06:13'),
(12, 'Khalid Mehmood', '0310-0000000', 6, 'Temporary Shelter Zone', 3, '2026-01-09 09:06:13');

-- --------------------------------------------------------

--
-- Table structure for table `aid_distribution`
--

CREATE TABLE `aid_distribution` (
  `id` int(11) NOT NULL,
  `family_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `quantity_given` int(11) NOT NULL DEFAULT 0,
  `distributed_by` int(11) NOT NULL,
  `distribution_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aid_distribution`
--

INSERT INTO `aid_distribution` (`id`, `family_id`, `resource_id`, `quantity_given`, `distributed_by`, `distribution_date`) VALUES
(1, 3, 10, 44, 1, '2026-01-09 10:23:23');

-- --------------------------------------------------------

--
-- Table structure for table `camp_needs`
--

CREATE TABLE `camp_needs` (
  `id` int(11) NOT NULL,
  `camp_id` int(11) NOT NULL,
  `resource_type` enum('food','water','medicine','shelter','clothing','other') NOT NULL,
  `quantity_required` int(11) NOT NULL,
  `unit_type` enum('kg','litre','pcs','other') DEFAULT 'pcs',
  `priority_level` enum('low','medium','high','critical') NOT NULL,
  `status` enum('pending','partial','fulfilled') DEFAULT 'pending',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `camp_needs`
--

INSERT INTO `camp_needs` (`id`, `camp_id`, `resource_type`, `quantity_required`, `unit_type`, `priority_level`, `status`, `updated_at`) VALUES
(2, 1, 'food', 500, 'kg', 'high', 'pending', '2026-01-09 07:45:24'),
(3, 1, 'water', 1000, 'litre', 'critical', 'pending', '2026-01-09 07:45:24'),
(4, 1, 'medicine', 200, 'pcs', 'high', 'pending', '2026-01-09 07:45:24'),
(5, 1, 'shelter', 50, 'pcs', 'critical', 'pending', '2026-01-09 07:45:24'),
(6, 1, 'clothing', 300, 'pcs', 'medium', 'pending', '2026-01-09 07:45:24'),
(7, 1, 'food', 200, 'kg', 'medium', 'pending', '2026-01-09 07:45:24'),
(8, 1, 'water', 500, 'litre', 'high', 'pending', '2026-01-09 07:45:24'),
(9, 1, 'medicine', 100, 'pcs', 'medium', 'pending', '2026-01-09 07:45:24'),
(10, 1, 'clothing', 150, 'pcs', 'low', 'pending', '2026-01-09 07:45:24'),
(11, 1, 'other', 50, 'pcs', 'low', 'pending', '2026-01-09 07:45:24');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `supporter_id` int(11) NOT NULL,
  `donation_type` enum('cash','in_kind') NOT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('received','stored','allocated','distributed') DEFAULT 'received',
  `donated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `supporter_id`, `donation_type`, `amount`, `description`, `status`, `donated_at`) VALUES
(1, 2, 'in_kind', 123.00, 'fds', 'received', '2026-01-09 08:01:45'),
(2, 2, 'cash', 5000.00, 'Donation for flood relief', 'received', '2026-01-09 08:03:41'),
(3, 2, 'in_kind', 0.00, '20 blankets for families', 'received', '2026-01-09 08:03:41'),
(4, 2, 'cash', 2500.50, 'Emergency medical fund', 'received', '2026-01-09 08:03:41'),
(5, 2, 'in_kind', 0.00, '50 liters of clean water', 'received', '2026-01-09 08:03:41'),
(6, 2, 'cash', 2000.00, 'School supplies for children', 'received', '2026-01-09 08:03:41'),
(7, 2, 'in_kind', 0.00, '20 tents for displaced families', 'received', '2026-01-09 08:03:41'),
(8, 2, 'cash', 3000.00, 'Food packages for 30 families', 'received', '2026-01-09 08:03:41'),
(9, 2, 'in_kind', 0.00, 'Clothing items for adults and children', 'received', '2026-01-09 08:03:41'),
(10, 2, 'cash', 2500.00, 'Fuel and logistics support', 'received', '2026-01-09 08:03:41'),
(11, 2, 'in_kind', 0.00, 'Medical kits and first aid supplies', 'received', '2026-01-09 08:03:41');

-- --------------------------------------------------------

--
-- Table structure for table `family_camp_link`
--

CREATE TABLE `family_camp_link` (
  `id` int(11) NOT NULL,
  `family_id` int(11) NOT NULL,
  `camp_id` int(11) NOT NULL,
  `status` enum('registered','relocated','aid_received','medical_required') DEFAULT 'registered',
  `added_by` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_camp_link`
--

INSERT INTO `family_camp_link` (`id`, `family_id`, `camp_id`, `status`, `added_by`, `last_updated`) VALUES
(1, 3, 1, 'registered', 3, '2026-01-09 10:06:01');

-- --------------------------------------------------------

--
-- Table structure for table `family_losses`
--

CREATE TABLE `family_losses` (
  `id` int(11) NOT NULL,
  `family_id` int(11) NOT NULL,
  `house_damage` tinyint(1) DEFAULT 0,
  `livestock_loss` tinyint(1) DEFAULT 0,
  `crop_loss` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_losses`
--

INSERT INTO `family_losses` (`id`, `family_id`, `house_damage`, `livestock_loss`, `crop_loss`, `notes`, `added_by`, `created_at`) VALUES
(3, 3, 1, 0, 1, 'Partial house damage, crops destroyed', 3, '2026-01-09 09:17:52'),
(4, 4, 1, 1, 0, 'House and livestock severely affected', 3, '2026-01-09 09:17:52'),
(5, 5, 0, 1, 1, 'Livestock loss and crop damage due to flood', 3, '2026-01-09 09:17:52'),
(6, 6, 1, 1, 1, 'Complete loss of house, livestock, and crops', 3, '2026-01-09 09:17:52'),
(7, 7, 0, 0, 1, 'Only crops damaged', 3, '2026-01-09 09:17:52'),
(8, 8, 1, 0, 0, 'Minor structural damage to house', 3, '2026-01-09 09:17:52'),
(9, 9, 0, 1, 0, 'Loss of cattle and goats', 3, '2026-01-09 09:17:52'),
(10, 10, 1, 0, 1, 'House damaged, crops destroyed', 3, '2026-01-09 09:17:52'),
(11, 11, 0, 1, 1, 'Severe crop loss and livestock death', 3, '2026-01-09 09:17:52'),
(12, 12, 1, 1, 0, 'House and livestock affected', 3, '2026-01-09 09:17:52');

-- --------------------------------------------------------

--
-- Table structure for table `family_special_needs`
--

CREATE TABLE `family_special_needs` (
  `id` int(11) NOT NULL,
  `family_id` int(11) NOT NULL,
  `need_type` enum('medical','elderly','children','disabled','other') NOT NULL,
  `details` text DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_special_needs`
--

INSERT INTO `family_special_needs` (`id`, `family_id`, `need_type`, `details`, `added_by`, `created_at`) VALUES
(2, 3, 'medical', 'Diabetes patient requires regular insulin supply', 3, '2026-01-09 09:23:50'),
(3, 4, 'elderly', 'Two elderly members need mobility assistance', 3, '2026-01-09 09:23:50'),
(4, 5, 'children', 'Three children under 5 require nutritional support', 3, '2026-01-09 09:23:50'),
(5, 6, 'disabled', 'One physically disabled member needs wheelchair', 3, '2026-01-09 09:23:50'),
(6, 7, 'medical', 'Pregnant woman requires prenatal medical care', 3, '2026-01-09 09:23:50'),
(7, 8, 'elderly', 'Elderly couple needs daily medication support', 3, '2026-01-09 09:23:50'),
(8, 9, 'children', 'School-age children need hygiene kits and schooling support', 3, '2026-01-09 09:23:50'),
(9, 10, 'disabled', 'Hearing-impaired family member requires special aid', 3, '2026-01-09 09:23:50'),
(10, 11, 'medical', 'Child with asthma needs inhalers and monitoring', 3, '2026-01-09 09:23:50'),
(11, 12, 'other', 'Psychological counseling required due to trauma', 3, '2026-01-09 09:23:50');

-- --------------------------------------------------------

--
-- Table structure for table `relief_camps`
--

CREATE TABLE `relief_camps` (
  `id` int(11) NOT NULL,
  `camp_name` varchar(200) NOT NULL,
  `province` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `location_details` text DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `current_occupancy` int(11) DEFAULT 0,
  `facilities` text DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `relief_camps`
--

INSERT INTO `relief_camps` (`id`, `camp_name`, `province`, `district`, `location_details`, `capacity`, `current_occupancy`, `facilities`, `manager_id`, `created_at`) VALUES
(1, 'Jampur Camp', 'Punjab', 'Rajanpur', 'Jampur', 41, 40, 'fdsa', 1, '2026-01-09 07:20:47');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `resource_name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit` varchar(50) DEFAULT NULL,
  `current_location` varchar(150) DEFAULT NULL,
  `camp_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `resource_name`, `category`, `quantity`, `unit`, `current_location`, `camp_id`, `created_at`) VALUES
(2, 'Rice', 'Food', 500, 'kg', 'Main Storage', 1, '2026-01-09 10:17:03'),
(3, 'Wheat Flour', 'Food', 300, 'kg', 'Main Storage', 1, '2026-01-09 10:17:03'),
(4, 'Bottled Water', 'Water', 1000, 'liters', 'Storage Room', 1, '2026-01-09 10:17:03'),
(5, 'Cooking Oil', 'Food', 200, 'liters', 'Main Storage', 1, '2026-01-09 10:17:03'),
(6, 'Blankets', 'Clothing', 150, 'pieces', 'Warehouse', 1, '2026-01-09 10:17:03'),
(7, 'Medicines', 'Medical', 50, 'boxes', 'Medical Room', 1, '2026-01-09 10:17:03'),
(8, 'First Aid Kits', 'Medical', 25, 'kits', 'Medical Room', 1, '2026-01-09 10:17:03'),
(9, 'Tents', 'Shelter', 20, 'pieces', 'Storage Yard', 1, '2026-01-09 10:17:03'),
(10, 'Sanitary Kits', 'Hygiene', 100, 'kits', 'Warehouse', 1, '2026-01-09 10:17:03'),
(11, 'Clothing Packs', 'Clothing', 120, 'sets', 'Warehouse', 1, '2026-01-09 10:17:03');

-- --------------------------------------------------------

--
-- Table structure for table `task_assignments`
--

CREATE TABLE `task_assignments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `volunteer_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completion_status` enum('pending','completed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_assignments`
--

INSERT INTO `task_assignments` (`id`, `task_id`, `volunteer_id`, `assigned_at`, `completion_status`) VALUES
(1, 2, 2, '2026-01-09 08:09:59', 'completed'),
(2, 3, 2, '2026-01-09 11:00:11', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('camp_manager','supporter','field_staff') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `availability` varchar(100) DEFAULT NULL,
  `donation_preferences` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `skills`, `availability`, `donation_preferences`, `status`, `created_at`) VALUES
(1, 'manager Haider', 'manager@gmail.com', '$2y$10$se5FBWb9Q.7YYDHV.ZFzy.k0s4fPSecr5zxPya8ur9YQjoxJs2LbW', 'camp_manager', '03176526823', 'Nonea', 'From 9 to 10 Pm', 'None', 'active', '2026-01-09 07:14:12'),
(2, 'Supporter', 'supporter@gmail.com', '$2y$10$Yp0oiJ5kN2S5n2zZv3Pc3ewWHForKApoAix1i05/nwY1fUVIIx.jm', 'supporter', '3176526827', 'none', 'YEs', 'None', 'active', '2026-01-09 07:27:02'),
(3, 'Field Staff', 'staff@gmail.com', '$2y$10$IPPKoMkw9eJGDODJUuAYOePiMi7OJ7Y/a8AgcJ2WRS3XYmzDZwzzi', 'field_staff', NULL, NULL, NULL, NULL, 'active', '2026-01-09 08:13:12');

-- --------------------------------------------------------

--
-- Table structure for table `volunteer_tasks`
--

CREATE TABLE `volunteer_tasks` (
  `id` int(11) NOT NULL,
  `camp_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `required_skills` text DEFAULT NULL,
  `status` enum('open','assigned','completed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `volunteer_tasks`
--

INSERT INTO `volunteer_tasks` (`id`, `camp_id`, `title`, `description`, `required_skills`, `status`, `created_at`) VALUES
(2, 1, 'Distribute Food', 'Distribute food packages to affected families', 'Food handling, Communication', 'completed', '2026-01-09 07:56:55'),
(3, 1, 'Clean Camp Area', 'Clean and organize the camp premises', 'Cleaning, Organization', 'assigned', '2026-01-09 07:56:55'),
(4, 1, 'Medical Assistance', 'Assist medical staff in health checkups', 'First aid, Medical knowledge', 'open', '2026-01-09 07:56:55'),
(5, 1, 'Water Supply', 'Ensure water distribution to all tents', 'Physical work, Coordination', 'open', '2026-01-09 07:56:55'),
(6, 1, 'Shelter Setup', 'Set up tents and sleeping arrangements', 'Carpentry, Teamwork', 'open', '2026-01-09 07:56:55'),
(7, 1, 'Clothing Distribution', 'Distribute clothing items to families', 'Organizing, Communication', 'open', '2026-01-09 07:56:55'),
(8, 1, 'Child Care', 'Help in taking care of children in the camp', 'Child care, Patience', 'open', '2026-01-09 07:56:55'),
(9, 1, 'Documentation', 'Maintain records of aid distribution', 'Computer skills, Attention to detail', 'open', '2026-01-09 07:56:55'),
(10, 1, 'Security Assistance', 'Support camp security staff', 'Alertness, Physical fitness', 'open', '2026-01-09 07:56:55'),
(11, 1, 'Counseling', 'Provide emotional support to affected families', 'Counseling, Communication', 'open', '2026-01-09 07:56:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `affected_families`
--
ALTER TABLE `affected_families`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registered_by` (`registered_by`);

--
-- Indexes for table `aid_distribution`
--
ALTER TABLE `aid_distribution`
  ADD PRIMARY KEY (`id`),
  ADD KEY `family_id` (`family_id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `distributed_by` (`distributed_by`);

--
-- Indexes for table `camp_needs`
--
ALTER TABLE `camp_needs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `camp_id` (`camp_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supporter_id` (`supporter_id`);

--
-- Indexes for table `family_camp_link`
--
ALTER TABLE `family_camp_link`
  ADD PRIMARY KEY (`id`),
  ADD KEY `family_id` (`family_id`),
  ADD KEY `camp_id` (`camp_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `family_losses`
--
ALTER TABLE `family_losses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `family_id` (`family_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `family_special_needs`
--
ALTER TABLE `family_special_needs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `family_id` (`family_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `relief_camps`
--
ALTER TABLE `relief_camps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `camp_id` (`camp_id`);

--
-- Indexes for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `volunteer_id` (`volunteer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `volunteer_tasks`
--
ALTER TABLE `volunteer_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `camp_id` (`camp_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `affected_families`
--
ALTER TABLE `affected_families`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `aid_distribution`
--
ALTER TABLE `aid_distribution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `camp_needs`
--
ALTER TABLE `camp_needs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `family_camp_link`
--
ALTER TABLE `family_camp_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `family_losses`
--
ALTER TABLE `family_losses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `family_special_needs`
--
ALTER TABLE `family_special_needs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `relief_camps`
--
ALTER TABLE `relief_camps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `task_assignments`
--
ALTER TABLE `task_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `volunteer_tasks`
--
ALTER TABLE `volunteer_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `affected_families`
--
ALTER TABLE `affected_families`
  ADD CONSTRAINT `affected_families_ibfk_1` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `aid_distribution`
--
ALTER TABLE `aid_distribution`
  ADD CONSTRAINT `aid_distribution_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `affected_families` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aid_distribution_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aid_distribution_ibfk_3` FOREIGN KEY (`distributed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `camp_needs`
--
ALTER TABLE `camp_needs`
  ADD CONSTRAINT `camp_needs_ibfk_1` FOREIGN KEY (`camp_id`) REFERENCES `relief_camps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`supporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `family_camp_link`
--
ALTER TABLE `family_camp_link`
  ADD CONSTRAINT `family_camp_link_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `affected_families` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `family_camp_link_ibfk_2` FOREIGN KEY (`camp_id`) REFERENCES `relief_camps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `family_camp_link_ibfk_3` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `family_losses`
--
ALTER TABLE `family_losses`
  ADD CONSTRAINT `family_losses_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `affected_families` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `family_losses_ibfk_2` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `family_special_needs`
--
ALTER TABLE `family_special_needs`
  ADD CONSTRAINT `family_special_needs_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `affected_families` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `family_special_needs_ibfk_2` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `relief_camps`
--
ALTER TABLE `relief_camps`
  ADD CONSTRAINT `relief_camps_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`camp_id`) REFERENCES `relief_camps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD CONSTRAINT `task_assignments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `volunteer_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_assignments_ibfk_2` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `volunteer_tasks`
--
ALTER TABLE `volunteer_tasks`
  ADD CONSTRAINT `volunteer_tasks_ibfk_1` FOREIGN KEY (`camp_id`) REFERENCES `relief_camps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
