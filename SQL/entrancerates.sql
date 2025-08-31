-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 28, 2025 at 02:33 PM
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
  `entranceRateID` int(11) NOT NULL,
  `sessionType` varchar(20) DEFAULT NULL,
  `timeRangeID` int(11) DEFAULT NULL,
  `ERcategory` enum('Adult','Kids') NOT NULL,
  `ERprice` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entrancerates`
--

INSERT INTO `entrancerates` (`entranceRateID`, `sessionType`, `timeRangeID`, `ERcategory`, `ERprice`) VALUES
(1, 'Day', 1, 'Adult', 150.00),
(2, 'Day', 1, 'Kids', 100.00),
(3, 'Night', 2, 'Adult', 180.00),
(4, 'Night', 2, 'Kids', 130.00),
(5, 'Overnight', 3, 'Kids', 200.00),
(6, 'Overnight', 3, 'Adult', 250.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `entrancerates`
--
ALTER TABLE `entrancerates`
  ADD PRIMARY KEY (`entranceRateID`),
  ADD KEY `timeRangeID` (`timeRangeID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `entrancerates`
--
ALTER TABLE `entrancerates`
  MODIFY `entranceRateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `entrancerates`
--
ALTER TABLE `entrancerates`
  ADD CONSTRAINT `entrancerates_ibfk_1` FOREIGN KEY (`timeRangeID`) REFERENCES `entrancetimeranges` (`timeRangeID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
