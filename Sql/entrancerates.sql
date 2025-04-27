-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 08:47 AM
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
-- Database: `mamyr`
--

-- --------------------------------------------------------

--
-- Table structure for table `entrancerates`
--

CREATE TABLE `entrancerates` (
  `resortEntranceID` int(11) NOT NULL,
  `session_type` varchar(20) DEFAULT NULL,
  `time_range` varchar(50) DEFAULT NULL,
  `category` varchar(10) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entrancerates`
--

INSERT INTO `entrancerates` (`resortEntranceID`, `session_type`, `time_range`, `category`, `price`) VALUES
(1, 'Day', '9:00 am - 4:00 pm', 'Adult', 150.00),
(2, 'Day', '9:00 am - 4:00 pm', 'Kids', 100.00),
(3, 'Night', '12:00 pm - 8:00 pm', 'Adult', 180.00),
(4, 'Night', '12:00 pm - 8:00 pm', 'Kids', 130.00),
(5, 'Overnight', '8:00 pm - 5:00 am', 'Adult', 250.00),
(6, 'Overnight', '8:00 pm - 5:00 am', 'Kids', 200.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `entrancerates`
--
ALTER TABLE `entrancerates`
  ADD PRIMARY KEY (`resortEntranceID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `entrancerates`
--
ALTER TABLE `entrancerates`
  MODIFY `resortEntranceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
