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
-- Table structure for table `resortamenities`
--

CREATE TABLE `resortamenities` (
  `resortServiceID` int(11) NOT NULL,
  `RServiceName` varchar(200) NOT NULL,
  `RSprice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `RScapacity` int(11) DEFAULT 0,
  `RSmaxCapacity` int(11) DEFAULT 0,
  `RSduration` varchar(50) DEFAULT '0',
  `RScategoryID` int(11) NOT NULL,
  `RSdescription` text DEFAULT NULL,
  `RSimageData` varchar(255) DEFAULT NULL,
  `RSAvailabilityID` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resortamenities`
--

INSERT INTO `resortamenities` (`resortServiceID`, `RServiceName`, `RSprice`, `RScapacity`, `RSmaxCapacity`, `RSduration`, `RScategoryID`, `RSdescription`, `RSimageData`, `RSAvailabilityID`) VALUES
(1, 'Umbrella 1', 500.00, 5, 5, '0', 2, ' Good for 5 pax', 'Cottage_cottage1.jpg', 1),
(2, 'Umbrella 2', 500.00, 5, 5, '0', 2, '  Good for 5 pax', 'Cottage_cottage2.jpg', 1),
(3, 'Umbrella 3', 500.00, 5, 5, '0', 2, '  Good for 5 pax', 'Cottage_cottage3.jpg', 1),
(4, 'Umbrella 4', 500.00, 5, 5, '0', 2, '  Good for 5 pax', 'Cottage_cottage4.jpg', 1),
(5, 'Umbrella 5', 500.00, 5, 5, '0', 2, '  Good for 5 pax', 'Cottage_cottage5.jpg', 1),
(6, 'Cottage 1', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage1.jpg', 1),
(7, 'Cottage 2', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage2.jpg', 1),
(8, 'Cottage 3', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage4.jpg', 1),
(9, 'Cottage 4', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage4.jpg', 1),
(10, 'Cottage 5', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage4.jpg', 1),
(11, 'Cottage 6', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage5.jpg', 1),
(12, 'Cottage 7', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage5.jpg', 1),
(13, 'Cottage 8', 900.00, 12, 12, '0', 2, ' Good for 12 pax', 'Cottage_cottage5.jpg', 1),
(14, 'Cottage 9', 900.00, 12, 12, '0', 2, '  Good for 12 pax', 'Cottage_cottage5.jpg', 1),
(15, 'Cottage 10', 900.00, 12, 12, '0', 2, '  Good for 12 pax', 'Cottage_cottage5.jpg', 1),
(16, 'Cottage 11', 900.00, 12, 12, '0', 2, '  Good for 12 pax', 'Cottage_cottage2.jpg', 1),
(17, 'Cottage 12', 1000.00, 15, 15, '0', 2, ' Good for 15 pax', 'Cottage_cottage5.jpg', 1),
(18, 'Cottage 13', 1000.00, 15, 15, '0', 2, '  Good for 15 pax', 'Cottage_cottage5.jpg', 1),
(19, 'Cottage 14', 1000.00, 15, 15, '0', 2, '   Good for 15 pax', 'Cottage_cottage3.jpg', 1),
(20, 'Cottage Stage', 2000.00, 25, 25, '0', 2, ' Good for 25 pax', 'Cottage_cottage5.jpg', 1),
(21, 'Pavilion Hall', 0.00, 1, 350, 'None', 4, ' None', 'Event Hall_pav5.jpg', 1),
(22, 'Mini Pavilion Hall', 0.00, 1, 50, 'None', 4, ' None', 'Event Hall_miniPav5.jpeg', 1),
(23, 'Videoke A', 800.00, 0, 0, 'None', 3, ' None', 'Entertainment_videoke1.jpg', 1),
(24, 'Billiard', 200.00, 0, 0, '1 hour', 3, ' None', 'Entertainment_billiardPic3.png', 1),
(25, 'Massage Chair', 100.00, 0, 0, '40 minutes', 3, ' None', 'Entertainment_massageChair.png', 1),
(26, 'Videoke B', 800.00, 0, 0, 'None', 3, ' None', 'Entertainment_videoke2.jpg', 1),
(28, 'Room 1', 2500.00, 2, 2, '22 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ', 'Hotel_41_hotel1.jpg', 1),
(29, 'Room 2', 2500.00, 2, 2, '22 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons                                            ', 'Hotel_hotel2.jpg', 1),
(30, 'Room 3', 2500.00, 2, 2, '22 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons                                            ', 'Hotel_hotel3.jpg', 1),
(31, 'Room 4', 2500.00, 2, 2, '22 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons                      ', 'Hotel_80_hotel4.jpg', 1),
(32, 'Room 5', 3500.00, 3, 3, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons                      ', 'Hotel_86_hotel5.jpeg', 1),
(33, 'Room 6', 3500.00, 3, 6, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_40_hotel4.jpg', 1),
(34, 'Room 7', 3500.00, 3, 3, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons                      ', 'Hotel_28_hotel5.jpeg', 4),
(35, 'Room 8', 3500.00, 3, 6, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_49_hotel4.jpg', 1),
(36, 'Room 9', 3500.00, 3, 3, '22 hours', 1, 'Barkada Room                      ', 'Hotel_91_hotel3.jpg', 1),
(37, 'Room 10', 3500.00, 3, 6, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_81_hotel3.jpg', 1),
(38, 'Room 11', 3500.00, 3, 6, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_87_hotel1.jpg', 1),
(39, 'Room 11', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_63_hotel4.jpg', 1),
(40, 'Room 10', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_60_hotel3.jpg', 1),
(41, 'Room 9', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_81_hotel3.jpg', 1),
(42, 'Room 8', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_34_hotel3.jpg', 1),
(43, 'Room 7', 2500.00, 3, 3, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons                      ', 'Hotel_76_hotel4.jpg', 4),
(44, 'Room 6', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_49_hotel3.jpg', 1),
(45, 'Room 5', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_15_hotel5.jpeg', 1),
(46, 'Room 4', 2000.00, 2, 4, '11 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons', 'Hotel_53_hotel5.jpeg', 1),
(47, 'Room 3', 2000.00, 2, 4, '11 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons', 'Hotel_90_hotel1.jpg', 1),
(48, 'Room 2', 2000.00, 2, 4, '11 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons', 'Hotel_52_hotel3.jpg', 1),
(49, 'Room 1', 2000.00, 2, 4, '11 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons', 'Hotel_32_hotel3.jpg', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `resortamenities`
--
ALTER TABLE `resortamenities`
  ADD PRIMARY KEY (`resortServiceID`),
  ADD KEY `RSAvailabilityID` (`RSAvailabilityID`),
  ADD KEY `RScategoryID` (`RScategoryID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `resortamenities`
--
ALTER TABLE `resortamenities`
  MODIFY `resortServiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `resortamenities`
--
ALTER TABLE `resortamenities`
  ADD CONSTRAINT `resortamenities_ibfk_1` FOREIGN KEY (`RSAvailabilityID`) REFERENCES `serviceavailability` (`availabilityID`),
  ADD CONSTRAINT `resortamenities_ibfk_2` FOREIGN KEY (`RScategoryID`) REFERENCES `resortservicescategories` (`categoryID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
