-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2025 at 05:11 PM
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
-- Database: `db_udara`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$Jba4d3c58t23VDrumSUwduYZI7R59cGAzqykHUBPo9MihUYkHe0IS'),
(4, 'seirios', '$2y$10$.iZ6r7vR7yq3FHk7z6YBcuFzk6BgyHi8kISrrfdGwAoJ587bBWzQ.');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi_log`
--

CREATE TABLE `notifikasi_log` (
  `id` int(11) NOT NULL,
  `waktu` datetime DEFAULT current_timestamp(),
  `penerima` varchar(100) DEFAULT NULL,
  `pesan` text DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifikasi_log`
--

INSERT INTO `notifikasi_log` (`id`, `waktu`, `penerima`, `pesan`, `status`) VALUES
(1, '2025-11-29 16:08:58', 'Mass Email (3 users)', 'Alert AQI: 42', 'Sent');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `api_token` text NOT NULL,
  `latitude` varchar(20) NOT NULL,
  `longitude` varchar(20) NOT NULL,
  `threshold_bahaya` int(11) DEFAULT 150,
  `email_admin` varchar(100) DEFAULT 'admin@example.com'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `api_token`, `latitude`, `longitude`, `threshold_bahaya`, `email_admin`) VALUES
(1, '596d39cb3f64efecfd8928449884552956ded22e', '-6.3243', '107.3060', 150, 'admin@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_aqi`
--

CREATE TABLE `riwayat_aqi` (
  `id` int(11) NOT NULL,
  `kota` varchar(50) NOT NULL,
  `aqi_level` int(11) NOT NULL,
  `suhu` decimal(4,1) DEFAULT NULL,
  `waktu_catat` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_aqi`
--

INSERT INTO `riwayat_aqi` (`id`, `kota`, `aqi_level`, `suhu`, `waktu_catat`) VALUES
(316, 'UBP Karawang', 79, 31.0, '2025-11-24 14:41:29'),
(317, 'UBP Karawang', 85, 30.0, '2025-11-24 15:41:29'),
(318, 'UBP Karawang', 97, 31.0, '2025-11-24 16:41:29'),
(319, 'UBP Karawang', 93, 34.0, '2025-11-24 17:41:29'),
(320, 'UBP Karawang', 100, 31.0, '2025-11-24 18:41:29'),
(321, 'UBP Karawang', 87, 25.0, '2025-11-24 19:41:29'),
(322, 'UBP Karawang', 89, 24.0, '2025-11-24 20:41:29'),
(323, 'UBP Karawang', 111, 24.0, '2025-11-24 21:41:29'),
(324, 'UBP Karawang', 112, 24.0, '2025-11-24 22:41:29'),
(325, 'UBP Karawang', 118, 27.0, '2025-11-24 23:41:29'),
(326, 'UBP Karawang', 106, 25.0, '2025-11-25 00:41:29'),
(327, 'UBP Karawang', 102, 27.0, '2025-11-25 01:41:29'),
(328, 'UBP Karawang', 111, 25.0, '2025-11-25 02:41:29'),
(329, 'UBP Karawang', 109, 24.0, '2025-11-25 03:41:29'),
(330, 'UBP Karawang', 129, 24.0, '2025-11-25 04:41:29'),
(331, 'UBP Karawang', 122, 25.0, '2025-11-25 05:41:29'),
(332, 'UBP Karawang', 115, 34.0, '2025-11-25 06:41:29'),
(333, 'UBP Karawang', 116, 30.0, '2025-11-25 07:41:29'),
(334, 'UBP Karawang', 132, 33.0, '2025-11-25 08:41:29'),
(335, 'UBP Karawang', 118, 31.0, '2025-11-25 09:41:29'),
(336, 'UBP Karawang', 109, 32.0, '2025-11-25 10:41:29'),
(337, 'UBP Karawang', 128, 33.0, '2025-11-25 11:41:29'),
(338, 'UBP Karawang', 111, 31.0, '2025-11-25 12:41:29'),
(339, 'UBP Karawang', 106, 33.0, '2025-11-25 13:41:29'),
(340, 'UBP Karawang', 115, 31.0, '2025-11-25 14:41:29'),
(341, 'UBP Karawang', 115, 32.0, '2025-11-25 15:41:29'),
(342, 'UBP Karawang', 104, 30.0, '2025-11-25 16:41:29'),
(343, 'UBP Karawang', 96, 30.0, '2025-11-25 17:41:29'),
(344, 'UBP Karawang', 97, 32.0, '2025-11-25 18:41:29'),
(345, 'UBP Karawang', 75, 27.0, '2025-11-25 19:41:29'),
(346, 'UBP Karawang', 72, 25.0, '2025-11-25 20:41:29'),
(347, 'UBP Karawang', 87, 24.0, '2025-11-25 21:41:29'),
(348, 'UBP Karawang', 85, 28.0, '2025-11-25 22:41:29'),
(349, 'UBP Karawang', 87, 25.0, '2025-11-25 23:41:29'),
(350, 'UBP Karawang', 74, 26.0, '2025-11-26 00:41:29'),
(351, 'UBP Karawang', 70, 27.0, '2025-11-26 01:41:29'),
(352, 'UBP Karawang', 73, 26.0, '2025-11-26 02:41:29'),
(353, 'UBP Karawang', 70, 26.0, '2025-11-26 03:41:29'),
(354, 'UBP Karawang', 45, 27.0, '2025-11-26 04:41:29'),
(355, 'UBP Karawang', 39, 26.0, '2025-11-26 05:41:29'),
(356, 'UBP Karawang', 63, 31.0, '2025-11-26 06:41:29'),
(357, 'UBP Karawang', 62, 34.0, '2025-11-26 07:41:29'),
(358, 'UBP Karawang', 42, 34.0, '2025-11-26 08:41:29'),
(359, 'UBP Karawang', 39, 31.0, '2025-11-26 09:41:29'),
(360, 'UBP Karawang', 37, 33.0, '2025-11-26 10:41:29'),
(361, 'UBP Karawang', 32, 32.0, '2025-11-26 11:41:29'),
(362, 'UBP Karawang', 51, 32.0, '2025-11-26 12:41:29'),
(363, 'UBP Karawang', 33, 34.0, '2025-11-26 13:41:29'),
(364, 'UBP Karawang', 54, 31.0, '2025-11-26 14:41:29'),
(365, 'UBP Karawang', 27, 32.0, '2025-11-26 15:41:29'),
(366, 'UBP Karawang', 38, 31.0, '2025-11-26 16:41:29'),
(367, 'UBP Karawang', 54, 32.0, '2025-11-26 17:41:29'),
(368, 'UBP Karawang', 35, 32.0, '2025-11-26 18:41:29'),
(369, 'UBP Karawang', 57, 26.0, '2025-11-26 19:41:29'),
(370, 'UBP Karawang', 54, 25.0, '2025-11-26 20:41:29'),
(371, 'UBP Karawang', 46, 25.0, '2025-11-26 21:41:29'),
(372, 'UBP Karawang', 58, 24.0, '2025-11-26 22:41:29'),
(373, 'UBP Karawang', 68, 26.0, '2025-11-26 23:41:29'),
(374, 'UBP Karawang', 73, 28.0, '2025-11-27 00:41:29'),
(375, 'UBP Karawang', 74, 28.0, '2025-11-27 01:41:29'),
(376, 'UBP Karawang', 60, 24.0, '2025-11-27 02:41:29'),
(377, 'UBP Karawang', 70, 25.0, '2025-11-27 03:41:29'),
(378, 'UBP Karawang', 70, 27.0, '2025-11-27 04:41:29'),
(379, 'UBP Karawang', 74, 24.0, '2025-11-27 05:41:29'),
(380, 'UBP Karawang', 83, 33.0, '2025-11-27 06:41:29'),
(381, 'UBP Karawang', 88, 32.0, '2025-11-27 07:41:29'),
(382, 'UBP Karawang', 98, 30.0, '2025-11-27 08:41:29'),
(383, 'UBP Karawang', 81, 31.0, '2025-11-27 09:41:29'),
(384, 'UBP Karawang', 100, 30.0, '2025-11-27 10:41:29'),
(385, 'UBP Karawang', 92, 32.0, '2025-11-27 11:41:29'),
(386, 'UBP Karawang', 117, 34.0, '2025-11-27 12:41:29'),
(387, 'UBP Karawang', 99, 30.0, '2025-11-27 13:41:29'),
(388, 'UBP Karawang', 126, 34.0, '2025-11-27 14:41:29'),
(389, 'UBP Karawang', 125, 34.0, '2025-11-27 15:41:29'),
(390, 'UBP Karawang', 131, 32.0, '2025-11-27 16:41:29'),
(391, 'UBP Karawang', 132, 31.0, '2025-11-27 17:41:29'),
(392, 'UBP Karawang', 115, 32.0, '2025-11-27 18:41:29'),
(393, 'UBP Karawang', 131, 26.0, '2025-11-27 19:41:29'),
(394, 'UBP Karawang', 108, 26.0, '2025-11-27 20:41:29'),
(395, 'UBP Karawang', 107, 24.0, '2025-11-27 21:41:29'),
(396, 'UBP Karawang', 115, 28.0, '2025-11-27 22:41:29'),
(397, 'UBP Karawang', 132, 28.0, '2025-11-27 23:41:29'),
(398, 'UBP Karawang', 130, 25.0, '2025-11-28 00:41:29'),
(399, 'UBP Karawang', 115, 26.0, '2025-11-28 01:41:29'),
(400, 'UBP Karawang', 107, 26.0, '2025-11-28 02:41:29'),
(401, 'UBP Karawang', 117, 24.0, '2025-11-28 03:41:29'),
(402, 'UBP Karawang', 96, 27.0, '2025-11-28 04:41:29'),
(403, 'UBP Karawang', 104, 27.0, '2025-11-28 05:41:29'),
(404, 'UBP Karawang', 107, 31.0, '2025-11-28 06:41:29'),
(405, 'UBP Karawang', 101, 32.0, '2025-11-28 07:41:29'),
(406, 'UBP Karawang', 104, 31.0, '2025-11-28 08:41:29'),
(407, 'UBP Karawang', 78, 32.0, '2025-11-28 09:41:29'),
(408, 'UBP Karawang', 88, 32.0, '2025-11-28 10:41:29'),
(409, 'UBP Karawang', 89, 32.0, '2025-11-28 11:41:29'),
(410, 'UBP Karawang', 72, 33.0, '2025-11-28 12:41:29'),
(411, 'UBP Karawang', 77, 34.0, '2025-11-28 13:41:29'),
(412, 'UBP Karawang', 60, 31.0, '2025-11-28 14:41:29'),
(413, 'UBP Karawang', 77, 30.0, '2025-11-28 15:41:29'),
(414, 'UBP Karawang', 78, 32.0, '2025-11-28 16:41:29'),
(415, 'UBP Karawang', 51, 31.0, '2025-11-28 17:41:29'),
(416, 'UBP Karawang', 36, 25.0, '2025-11-29 01:15:52'),
(417, 'UBP Karawang', 36, 25.0, '2025-11-29 01:16:15'),
(418, 'UBP Karawang', 36, 25.0, '2025-11-29 01:19:10'),
(419, 'UBP Karawang', 36, 25.0, '2025-11-29 01:19:12'),
(420, 'UBP Karawang', 36, 25.0, '2025-11-29 01:20:25'),
(422, 'UBP Karawang', 42, 30.0, '2025-11-29 16:08:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_subscribed` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `password`, `is_subscribed`) VALUES
(2, 'MUHAMMAD HABIBI RAHMAN', 'm.habibir07@gmail.com', '$2y$10$H.M09EakHb5SA0Jh4mNdnemo9HKRvjwuy6VuXXQWOmJNKJrYcnb0u', 1),
(3, 'habibi', 'if24.muhammadrahman@mhs.ubpkarawang.ac.id', '$2y$10$v59q4RPyqmCIiIvQ49sjmOVxBf8gWX64MyOJkOF6M3RV5FZboFQUe', 1),
(6, 'Seirios', 'm.habibir01@gmail.com', '$2y$10$svBCIfLLydra4iXw9sEkf.eowsgFTAYCXn5FknXFP72ktY6sxnTTW', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifikasi_log`
--
ALTER TABLE `notifikasi_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `riwayat_aqi`
--
ALTER TABLE `riwayat_aqi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifikasi_log`
--
ALTER TABLE `notifikasi_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `riwayat_aqi`
--
ALTER TABLE `riwayat_aqi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=423;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
