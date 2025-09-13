-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 28, 2025 at 11:21 AM
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
-- Table structure for table `websitecontentimages`
--

CREATE TABLE `websitecontentimages` (
  `WCImageID` int(11) NOT NULL,
  `contentID` int(11) NOT NULL,
  `imageData` varchar(500) DEFAULT NULL,
  `altText` varchar(255) DEFAULT NULL,
  `imageOrder` int(11) DEFAULT 1,
  `uploadedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `websitecontentimages`
--

INSERT INTO `websitecontentimages` (`WCImageID`, `contentID`, `imageData`, `altText`, `imageOrder`, `uploadedAt`) VALUES
(1, 19, 'resortPic1.png', 'Mamyr Resort Image', 1, '2025-07-30 00:44:24'),
(2, 20, 'img1.png', 'Mamyr Gallery Image 1', 1, '2025-07-30 00:44:23'),
(3, 20, 'img2.png', 'Mamyr Gallery Image 2', 2, '2025-07-30 00:44:23'),
(4, 20, 'img3.png', 'Mamyr Gallery Image 3', 3, '2025-07-30 00:44:23'),
(5, 20, 'img4.png', 'Mamyr Gallery Image 4', 4, '2025-07-30 00:44:23'),
(6, 20, 'img5.png', 'Mamyr Gallery Image 5', 5, '2025-07-30 00:44:23'),
(7, 20, 'img6.png', 'Mamyr Gallery Image 6', 6, '2025-07-30 00:44:24'),
(8, 27, 'firstPic.jpg', 'About Image 1', 1, '2025-08-06 17:54:49'),
(9, 30, 'resort.png', 'Resort Logo', 1, '2025-08-06 17:54:49'),
(10, 32, 'events.png', 'Events Logo', 1, '2025-08-06 17:54:49'),
(11, 34, 'hotel.png', 'Hotel Logo', 1, '2025-08-06 17:54:49'),
(12, 36, 'aboutImage.jpg', 'About Image 2', 1, '2025-08-06 17:54:49'),
(13, 38, 'poolPic.jpg', 'About Image 3', 1, '2025-08-06 17:54:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `websitecontentimages`
--
ALTER TABLE `websitecontentimages`
  ADD PRIMARY KEY (`WCImageID`),
  ADD KEY `contentID` (`contentID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `websitecontentimages`
--
ALTER TABLE `websitecontentimages`
  MODIFY `WCImageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `websitecontentimages`
--
ALTER TABLE `websitecontentimages`
  ADD CONSTRAINT `websitecontentimages_ibfk_1` FOREIGN KEY (`contentID`) REFERENCES `websitecontents` (`contentID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
