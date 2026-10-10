-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2026 at 09:38 PM
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
-- Database: `venue_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `instruktur`
--

CREATE TABLE `instruktur` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nomor_lisensi` varchar(50) NOT NULL,
  `level_sertifikasi` enum('Daerah','Nasional','Internasional') NOT NULL,
  `cabang` varchar(255) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `tarif` int(11) NOT NULL,
  `status` enum('Aktif','Nonaktif') NOT NULL DEFAULT 'Aktif',
  `tanggal_gabung` date DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instruktur`
--

INSERT INTO `instruktur` (`id`, `nama`, `nomor_lisensi`, `level_sertifikasi`, `cabang`, `telepon`, `tarif`, `status`, `tanggal_gabung`, `catatan`) VALUES
(2, 'Miu Miu', 'LSN-2024-1020', 'Internasional', 'Voli', '08312379678', 300000, 'Aktif', '2024-10-20', ''),
(3, 'Louis', 'LSN-2024-0130', 'Internasional', 'Futsal, Basket', '0833451276', 349985, 'Aktif', '2024-01-30', ''),
(4, 'Ronaldo', 'LSN-2024-0210', 'Nasional', 'Futsal', '082838123838', 249982, 'Aktif', '2024-02-10', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `instruktur`
--
ALTER TABLE `instruktur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_lisensi` (`nomor_lisensi`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `instruktur`
--
ALTER TABLE `instruktur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
