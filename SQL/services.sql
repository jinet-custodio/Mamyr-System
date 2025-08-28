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
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `serviceID` int(11) NOT NULL,
  `resortServiceID` int(11) DEFAULT NULL,
  `partnershipServiceID` int(11) DEFAULT NULL,
  `entranceRateID` int(11) DEFAULT NULL,
  `serviceType` enum('Resort','Partnership','Entrance') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`serviceID`, `resortServiceID`, `partnershipServiceID`, `entranceRateID`, `serviceType`) VALUES
(1, 1, NULL, NULL, 'Resort'),
(2, 2, NULL, NULL, 'Resort'),
(3, 3, NULL, NULL, 'Resort'),
(4, 4, NULL, NULL, 'Resort'),
(5, 5, NULL, NULL, 'Resort'),
(6, 6, NULL, NULL, 'Resort'),
(7, 7, NULL, NULL, 'Resort'),
(8, 8, NULL, NULL, 'Resort'),
(9, 9, NULL, NULL, 'Resort'),
(10, 10, NULL, NULL, 'Resort'),
(11, 11, NULL, NULL, 'Resort'),
(12, 12, NULL, NULL, 'Resort'),
(13, 13, NULL, NULL, 'Resort'),
(14, 14, NULL, NULL, 'Resort'),
(15, 15, NULL, NULL, 'Resort'),
(16, 16, NULL, NULL, 'Resort'),
(17, 17, NULL, NULL, 'Resort'),
(18, 18, NULL, NULL, 'Resort'),
(19, 19, NULL, NULL, 'Resort'),
(20, 20, NULL, NULL, 'Resort'),
(21, 21, NULL, NULL, 'Resort'),
(22, 22, NULL, NULL, 'Resort'),
(23, 23, NULL, NULL, 'Resort'),
(24, 24, NULL, NULL, 'Resort'),
(25, 25, NULL, NULL, 'Resort'),
(26, 26, NULL, NULL, 'Resort'),
(27, NULL, NULL, 1, 'Entrance'),
(28, NULL, NULL, 2, 'Entrance'),
(29, NULL, NULL, 3, 'Entrance'),
(30, NULL, NULL, 4, 'Entrance'),
(31, NULL, NULL, 5, 'Entrance'),
(32, NULL, NULL, 6, 'Entrance');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`serviceID`),
  ADD UNIQUE KEY `resortServiceID` (`resortServiceID`),
  ADD UNIQUE KEY `partnershipServiceID` (`partnershipServiceID`),
  ADD KEY `entranceRateID` (`entranceRateID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `serviceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`partnershipServiceID`) REFERENCES `partnershipservices` (`partnershipServiceID`),
  ADD CONSTRAINT `services_ibfk_2` FOREIGN KEY (`resortServiceID`) REFERENCES `resortamenities` (`resortServiceID`),
  ADD CONSTRAINT `services_ibfk_3` FOREIGN KEY (`entranceRateID`) REFERENCES `entrancerates` (`entranceRateID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
