-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 22, 2025 at 08:29 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u497406430_mamyr`
--

-- --------------------------------------------------------

--
-- Table structure for table `additionalcharge`
--

CREATE TABLE `additionalcharge` (
  `additionalChargeID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `chargeDescription` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `fullName` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auditlog`
--

CREATE TABLE `auditlog` (
  `logID` int(11) NOT NULL,
  `adminID` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target` varchar(100) DEFAULT NULL,
  `logDetails` varchar(255) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `bookingID` int(11) NOT NULL,
  `bookingCode` varchar(20) NOT NULL,
  `userID` int(11) NOT NULL,
  `bookingType` enum('Resort','Hotel','Event') NOT NULL,
  `customPackageID` int(11) DEFAULT NULL,
  `additionalRequest` text DEFAULT NULL,
  `toddlerCount` int(11) DEFAULT 0,
  `kidCount` int(11) DEFAULT 0,
  `adultCount` int(11) DEFAULT 0,
  `guestCount` int(11) NOT NULL,
  `durationCount` int(11) NOT NULL,
  `arrivalTime` time DEFAULT NULL,
  `startDate` datetime NOT NULL,
  `endDate` datetime NOT NULL,
  `paymentMethod` varchar(50) NOT NULL,
  `bookingOrigin` enum('Online','Walk-In') NOT NULL DEFAULT 'Online',
  `addOns` varchar(255) NOT NULL DEFAULT 'N/A',
  `customerChoice` enum('Proceed','Cancel') DEFAULT NULL,
  `totalCost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `additionalCharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `downpayment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bookingStatus` int(11) DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `approvedBy` varchar(25) DEFAULT NULL,
  `approvedDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookingservice`
--

CREATE TABLE `bookingservice` (
  `bookingServiceID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `serviceID` int(11) NOT NULL,
  `guests` int(11) NOT NULL,
  `bookingServicePrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_cancellation`
--

CREATE TABLE `booking_cancellation` (
  `cancellationID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `reasonID` int(11) NOT NULL,
  `otherReason` text DEFAULT NULL,
  `cancelledAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_rejection`
--

CREATE TABLE `booking_rejection` (
  `rejectionID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `adminID` int(11) NOT NULL,
  `reasonID` int(11) NOT NULL,
  `otherReason` text DEFAULT NULL,
  `rejectedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `businesspartneravailedservice`
--

CREATE TABLE `businesspartneravailedservice` (
  `BPavailedService` int(11) NOT NULL,
  `partnershipServiceID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `approvalStatus` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `availedDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `confirmedbooking`
--

CREATE TABLE `confirmedbooking` (
  `confirmedBookingID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `downpaymentImage` varchar(255) DEFAULT 'defaultDownpayment.png',
  `discountAmount` decimal(10,2) DEFAULT 0.00,
  `additionalCharge` decimal(10,2) DEFAULT 0.00,
  `finalBill` decimal(10,2) DEFAULT 0.00,
  `amountPaid` decimal(10,2) DEFAULT 0.00,
  `userBalance` decimal(10,2) DEFAULT 0.00,
  `paymentApprovalStatus` int(11) DEFAULT 1,
  `approvedBy` int(11) DEFAULT NULL,
  `approvedDate` datetime DEFAULT NULL,
  `paymentStatus` int(11) NOT NULL DEFAULT 1,
  `paymentDueDate` datetime DEFAULT NULL,
  `downpaymentDueDate` datetime DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custompackage`
--

CREATE TABLE `custompackage` (
  `customPackageID` int(11) NOT NULL,
  `eventTypeID` int(11) DEFAULT NULL,
  `userID` int(11) NOT NULL,
  `foodPricingPerHeadID` int(11) DEFAULT NULL,
  `totalFoodPrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `venuePricing` decimal(10,2) NOT NULL DEFAULT 0.00,
  `additionalServicePrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `customPackageTotalPrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `customPackageNotes` varchar(255) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custompackageitem`
--

CREATE TABLE `custompackageitem` (
  `customPackageItemID` int(11) NOT NULL,
  `customPackageID` int(11) NOT NULL,
  `serviceID` int(11) DEFAULT NULL,
  `foodItemID` int(11) DEFAULT NULL,
  `servicePrice` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entrancerate`
--

CREATE TABLE `entrancerate` (
  `entranceRateID` int(11) NOT NULL,
  `sessionType` varchar(20) DEFAULT NULL,
  `timeRangeID` int(11) DEFAULT NULL,
  `ERcategory` enum('Adult','Kids') NOT NULL,
  `ERprice` decimal(10,2) DEFAULT 0.00,
  `availability` enum('Enabled','Disabled') DEFAULT 'Enabled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entrancerate`
--

INSERT INTO `entrancerate` (`entranceRateID`, `sessionType`, `timeRangeID`, `ERcategory`, `ERprice`, `availability`) VALUES
(1, 'Day', 1, 'Adult', 150.00, 'Enabled'),
(2, 'Day', 1, 'Kids', 100.00, 'Enabled'),
(3, 'Night', 2, 'Adult', 180.00, 'Enabled'),
(4, 'Night', 2, 'Kids', 130.00, 'Enabled'),
(5, 'Overnight', 3, 'Kids', 200.00, 'Enabled'),
(6, 'Overnight', 3, 'Adult', 250.00, 'Enabled');

-- --------------------------------------------------------

--
-- Table structure for table `entrancetimerange`
--

CREATE TABLE `entrancetimerange` (
  `timeRangeID` int(11) NOT NULL,
  `session_type` varchar(20) DEFAULT NULL,
  `time_range` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entrancetimerange`
--

INSERT INTO `entrancetimerange` (`timeRangeID`, `session_type`, `time_range`) VALUES
(1, 'Day', '9:00 am - 4:00 pm'),
(2, 'Night', '12:00 pm - 8:00 pm'),
(3, 'Overnight', '8:00 pm - 5:00 am');

-- --------------------------------------------------------

--
-- Table structure for table `eventcategory`
--

CREATE TABLE `eventcategory` (
  `categoryID` int(11) NOT NULL,
  `categoryName` varchar(200) NOT NULL,
  `eventDescription` text DEFAULT NULL,
  `imagePath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eventcategory`
--

INSERT INTO `eventcategory` (`categoryID`, `categoryName`, `eventDescription`, `imagePath`) VALUES
(1, 'Birthday', 'Celebrating the joy of life at Mamyr Resort and Events Place—where every year brings new moments to cherish!', 'birthday.jpg'),
(2, 'Wedding', 'Celebrating love and lifelong memories at Mamyr Resort—where every wedding is a dream come true!', 'pav4.jpg'),
(3, 'Team Building', 'Creating great ideas and strong bonds at Mamyr Resort—where teamwork and leadership thrive in inspiring surroundings!', 'teamBuilding.jpg'),
(4, 'Christening/Dedication', 'Make lasting memories at Mamyr Resort where every celebration, from christenings to dedications, is a moment to treasure.', 'christening.jpg'),
(5, 'Thanksgiving Party', 'Celebrating gratitude and togetherness at Mamyr Resort—where good food and great company make every moment unforgettable!', 'thanksgiving.jpg'),
(6, 'Christmas Party', 'Embracing the magic of the holidays at Mamyr Resort—where grand feasts and unforgettable moments bring joy to all!', 'xmas.jpg'),
(7, 'Kids Party', 'Creating magical moments at Mamyr Resort and Events Place—where every kids\\\' party is filled with joy, laughter, and unforgettable memories!', 'kidsParty.jpg'),
(8, 'Debut', 'Celebrating a milestone at Mamyr Resort and Events Place—where every debut marks a new chapter of unforgettable memories', 'debut.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `menuitem`
--

CREATE TABLE `menuitem` (
  `foodItemID` int(11) NOT NULL,
  `foodName` varchar(200) NOT NULL,
  `foodDescription` text DEFAULT NULL,
  `foodCategory` varchar(100) DEFAULT NULL,
  `availabilityID` int(11) DEFAULT 1,
  `ageGroup` enum('Adult','Child','Both') NOT NULL DEFAULT 'Adult'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menuitem`
--

INSERT INTO `menuitem` (`foodItemID`, `foodName`, `foodDescription`, `foodCategory`, `availabilityID`, `ageGroup`) VALUES
(1, 'Chicken Pastil', NULL, 'Chicken', 1, 'Adult'),
(2, 'Chicken Barbecue Sauce', NULL, 'Chicken', 1, 'Adult'),
(3, 'Chicken Cordon Bleu', NULL, 'Chicken', 1, 'Adult'),
(4, 'Chicken Teriyaki', NULL, 'Chicken', 1, 'Adult'),
(5, 'Butter Garlic Chicken', NULL, 'Chicken', 1, 'Adult'),
(6, 'Pork Asado', NULL, 'Pork', 1, 'Adult'),
(7, 'Pork Mechado Roll', NULL, 'Pork', 1, 'Adult'),
(8, 'Pork Caldereta', NULL, 'Pork', 1, 'Adult'),
(9, 'Pork Hamonado', NULL, 'Pork', 1, 'Adult'),
(10, 'Roasted Pork', NULL, 'Pork', 1, 'Adult'),
(11, 'Beef Spicy Caldereta', NULL, 'Beef', 1, 'Adult'),
(12, 'Beef Broccolli', NULL, 'Beef', 1, 'Adult'),
(13, 'Roast Beef', NULL, 'Beef', 1, 'Adult'),
(14, 'Beef Teriyaki', NULL, 'Beef', 1, 'Adult'),
(15, 'Beef Creamy Mushroom Sauce', NULL, 'Beef', 1, 'Adult'),
(16, 'Creamy Carbonara', NULL, 'Pasta', 1, 'Adult'),
(17, 'Filipino Style Spaghetti', NULL, 'Pasta', 1, 'Adult'),
(18, 'Pasta Pesto Sauce', NULL, 'Pasta', 1, 'Adult'),
(19, 'Canton and Bihon Guisado', NULL, 'Pasta', 1, 'Adult'),
(20, 'Japchae Noodles', NULL, 'Pasta', 1, 'Adult'),
(21, 'Mixed Veggies Saute', NULL, 'Vegetables', 1, 'Adult'),
(22, 'Oriental Mix Veggies', NULL, 'Vegetables', 1, 'Adult'),
(23, 'Chopsuey', NULL, 'Vegetables', 1, 'Adult'),
(24, 'Chinese Chopsuey', NULL, 'Vegetables', 1, 'Adult'),
(25, 'Butter Creamy Veggies', NULL, 'Vegetables', 1, 'Adult'),
(26, 'Crispy Fish Fillet with Creamy Mayo', NULL, 'Seafood', 1, 'Adult'),
(27, 'Shrimp Tempura', NULL, 'Seafood', 1, 'Adult'),
(28, 'Butter Garlic Shrimp', NULL, 'Seafood', 1, 'Adult'),
(29, 'Squid Gambas', NULL, 'Seafood', 1, 'Adult'),
(30, 'Nut Crushed Fish Fillet with Sweet Chili Sauce', NULL, 'Seafood', 1, 'Adult'),
(31, 'Lemon Iced Tea', NULL, 'Drink', 1, 'Adult'),
(32, 'Orange Juice', NULL, 'Drink', 1, 'Adult'),
(33, 'Pineapple Juice', NULL, 'Drink', 1, 'Adult'),
(34, 'Red Iced Tea', NULL, 'Drink', 1, 'Adult'),
(35, 'Lemonade', NULL, 'Drink', 1, 'Adult'),
(36, 'Fruit Salad', NULL, 'Dessert', 1, 'Adult'),
(37, 'Buko Pandan', NULL, 'Dessert', 1, 'Adult'),
(38, 'Buko Salad', NULL, 'Dessert', 1, 'Adult'),
(39, 'Coffee Jelly', NULL, 'Dessert', 1, 'Adult');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notificationID` int(11) NOT NULL,
  `bookingID` int(11) DEFAULT NULL,
  `partnershipID` int(11) DEFAULT NULL,
  `senderID` int(11) DEFAULT NULL,
  `receiverID` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `receiver` enum('Admin','Business Partner','Customer','Partnership Applicant') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partnership`
--

CREATE TABLE `partnership` (
  `partnershipID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `validID` varchar(500) NOT NULL,
  `companyName` varchar(100) NOT NULL,
  `businessEmail` varchar(255) NOT NULL,
  `partnerAddress` text NOT NULL,
  `documentLink` varchar(500) NOT NULL,
  `partnerStatusID` int(11) NOT NULL DEFAULT 1,
  `requestDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partnershipservice`
--

CREATE TABLE `partnershipservice` (
  `partnershipServiceID` int(11) NOT NULL,
  `partnershipID` int(11) NOT NULL,
  `partnerTypeID` int(11) NOT NULL,
  `PBName` varchar(200) NOT NULL,
  `PBPrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `serviceImage` varchar(255) DEFAULT 'noImage.png',
  `PBDescription` text DEFAULT NULL,
  `PBcapacity` int(11) DEFAULT 1,
  `PBduration` varchar(255) DEFAULT NULL,
  `PSAvailabilityID` int(11) DEFAULT 1,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partnershiptype`
--

CREATE TABLE `partnershiptype` (
  `partnerTypeID` int(11) NOT NULL,
  `partnerType` varchar(50) NOT NULL,
  `partnerTypeDescription` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partnershiptype`
--

INSERT INTO `partnershiptype` (`partnerTypeID`, `partnerType`, `partnerTypeDescription`) VALUES
(1, 'photography', 'Photography/Videography'),
(2, 'sound-lighting', 'Sound and Lighting'),
(3, 'event-hosting', 'Event Hosting'),
(4, 'photo-booth', 'Photo Booth'),
(5, 'performer', 'Performer'),
(6, 'food-cart', 'Food Cart'),
(7, 'Other', 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `partnership_partnertype`
--

CREATE TABLE `partnership_partnertype` (
  `pptID` int(11) NOT NULL,
  `partnershipID` int(11) NOT NULL,
  `partnerTypeID` int(11) NOT NULL,
  `otherPartnerType` varchar(50) DEFAULT NULL,
  `isApproved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partnerstatus`
--

CREATE TABLE `partnerstatus` (
  `partnerStatusID` int(11) NOT NULL,
  `statusName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partnerstatus`
--

INSERT INTO `partnerstatus` (`partnerStatusID`, `statusName`) VALUES
(2, 'Approved'),
(5, 'Expired'),
(1, 'Pending'),
(3, 'Rejected'),
(6, 'Suspended'),
(4, 'Terminated');

-- --------------------------------------------------------

--
-- Table structure for table `partner_rejection`
--

CREATE TABLE `partner_rejection` (
  `rejectionID` int(11) NOT NULL,
  `partnershipID` int(11) NOT NULL,
  `adminID` int(11) NOT NULL,
  `reasonID` int(11) NOT NULL,
  `otherReason` text DEFAULT NULL,
  `rejectedOn` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `paymentID` int(11) NOT NULL,
  `confirmedBookingID` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `downpaymentImage` varchar(255) NOT NULL DEFAULT 'defaultDownpayment.png',
  `paymentDate` datetime DEFAULT current_timestamp(),
  `paymentMethod` enum('gcash','cash') NOT NULL DEFAULT 'gcash',
  `notes` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paymentstatus`
--

CREATE TABLE `paymentstatus` (
  `paymentStatusID` int(11) NOT NULL,
  `statusName` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymentstatus`
--

INSERT INTO `paymentstatus` (`paymentStatusID`, `statusName`) VALUES
(3, 'Fully Paid'),
(2, 'Partially Paid'),
(4, 'Payment Issue'),
(5, 'Payment Sent'),
(1, 'Unpaid');

-- --------------------------------------------------------

--
-- Table structure for table `reason`
--

CREATE TABLE `reason` (
  `reasonID` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `reasonDescription` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reason`
--

INSERT INTO `reason` (`reasonID`, `category`, `reasonDescription`) VALUES
(1, 'Cancellation', 'Change of plans'),
(2, 'Cancellation', 'Found a better place or event'),
(3, 'Cancellation', 'Price is too expensive'),
(4, 'Cancellation', 'Family or personal emergency'),
(5, 'Cancellation', 'Health or medical issue'),
(6, 'Cancellation', 'Unable to travel'),
(7, 'Cancellation', 'Incorrect booking details'),
(8, 'Cancellation', 'Trip or event was cancelled'),
(9, 'Cancellation', 'Other'),
(10, 'BookingRejection', 'No rooms, seats, or spaces available'),
(11, 'BookingRejection', 'Missing or incorrect information'),
(12, 'BookingRejection', 'Booking appears unsafe or suspicious'),
(13, 'BookingRejection', 'Does not meet booking rules or requirements'),
(14, 'BookingRejection', 'Duplicate booking detected'),
(15, 'BookingRejection', 'Breaks property or event policy'),
(16, 'BookingRejection', 'Invalid booking date selected'),
(17, 'BookingRejection', 'Other'),
(18, 'PaymentRejection', 'Payment was not completed or declined'),
(19, 'PaymentRejection', 'Payment method is invalid or expired'),
(20, 'PaymentRejection', 'Insufficient payment'),
(21, 'PaymentRejection', 'Payment could not be verified'),
(22, 'PaymentRejection', 'Duplicate payment detected'),
(23, 'PaymentRejection', 'Other'),
(24, 'PartnerRejection', 'Incomplete or missing documents'),
(25, 'PartnerRejection', 'Invalid or unverifiable business information'),
(26, 'PartnerRejection', 'Duplicate partnership request'),
(27, 'PartnerRejection', 'Existing active partnership for same partner type and service'),
(28, 'PartnerRejection', 'Partner type not eligible for requested service'),
(29, 'PartnerRejection', 'Service not aligned with partnership criteria'),
(30, 'PartnerRejection', 'Failed validation or background check'),
(31, 'PartnerRejection', 'Non-compliance with policy requirements'),
(32, 'PartnerRejection', 'Other'),
(33, 'PartnerServiceRejection', 'Schedule conflict / fully booked'),
(34, 'PartnerServiceRejection', 'Event type not supported'),
(35, 'PartnerServiceRejection', 'Insufficient notice for preparation'),
(36, 'PartnerServiceRejection', 'Unable to meet specific client customizations'),
(37, 'PartnerServiceRejection', 'Required resources or equipment unavailable'),
(38, 'PartnerServiceRejection', 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `resortamenity`
--

CREATE TABLE `resortamenity` (
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
-- Dumping data for table `resortamenity`
--

INSERT INTO `resortamenity` (`resortServiceID`, `RServiceName`, `RSprice`, `RScapacity`, `RSmaxCapacity`, `RSduration`, `RScategoryID`, `RSdescription`, `RSimageData`, `RSAvailabilityID`) VALUES
(1, 'Umbrella 1', 500.00, 5, 5, '0', 2, ' Good for 5 pax', 'Cottage_cottage1.jpg', 1),
(2, 'Umbrella 2', 500.00, 5, 5, '0', 2, '  Good for 5 pax', 'Cottage_cottage2.jpg', 1),
(3, 'Umbrella 3', 500.00, 5, 5, '0', 2, '  Good for 5 pax', 'Cottage_cottage3.jpg', 1),
(4, 'Umbrella 4', 500.00, 5, 5, '0', 2, '  Good for 5 pax', 'Cottage_umbrella4.jpg', 1),
(5, 'Umbrella 5', 500.00, 5, 5, '0', 2, '  Good for 5 pax', 'Cottage_umbrella5.jpg', 1),
(6, 'Cottage 1', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage1.jpg', 1),
(7, 'Cottage 2', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage2.jpg', 1),
(8, 'Cottage 3', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage4.jpg', 1),
(9, 'Cottage 4', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage4.jpg', 1),
(10, 'Cottage 5', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage4.jpg', 1),
(11, 'Cottage 6', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage5.jpg', 1),
(12, 'Cottage 7', 800.00, 10, 10, '0', 2, ' Good for 10 pax', 'Cottage_cottage5.jpg', 1),
(13, 'Cottage 8', 900.00, 12, 12, '0', 2, ' Good for 12 pax', 'Cottage_cottage5.jpg', 1),
(14, 'Cottage 9', 900.00, 12, 12, '0', 2, '  Good for 12 pax', 'Cottage_cottage9.jpg', 1),
(15, 'Cottage 10', 900.00, 12, 12, '0', 2, '  Good for 12 pax', 'Cottage_cottage5.jpg', 1),
(16, 'Cottage 11', 900.00, 12, 12, '0', 2, '  Good for 12 pax', 'Cottage_cottage2.jpg', 1),
(17, 'Cottage 12', 1000.00, 15, 15, '0', 2, ' Good for 15 pax', 'Cottage_cottage5.jpg', 1),
(18, 'Cottage 13', 1000.00, 15, 15, '0', 2, '  Good for 15 pax', 'Cottage_cottage5.jpg', 1),
(19, 'Cottage 14', 1000.00, 15, 15, '0', 2, '   Good for 15 pax', 'Cottage_cottage3.jpg', 1),
(20, 'Cottage Stage', 2000.00, 25, 25, '0', 2, ' Good for 25 pax', 'Cottage_cottagestage.jpg', 1),
(21, 'Main Function Hall', 30000.00, 1, 350, 'An Elegant, fully air-conditioned function room, F', 4, ' None', 'Event Hall_pav5.jpg', 1),
(22, 'Mini Function Hall', 7000.00, 1, 50, 'An Intimate, fully air-conditioned function room, ', 4, ' None', 'Event Hall_miniPav5.jpeg', 1),
(23, 'Videoke A', 800.00, 0, 0, 'None', 3, ' None', 'Entertainment_videoke1.jpg', 1),
(24, 'Billiard', 200.00, 0, 0, '1 hour', 3, 'Billiards', 'Entertainment_billiardPic3.png', 1),
(25, 'Massage Chair', 100.00, 0, 0, '40 minutes', 3, ' None', 'Entertainment_massageChair.jpg', 1),
(26, 'Videoke B', 800.00, 0, 0, 'None', 3, ' None', 'Entertainment_videoke2.jpg', 1),
(27, 'Room 1', 2500.00, 2, 2, '22 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ', 'Hotel_41_hotel1.jpg', 1),
(28, 'Room 2', 2500.00, 2, 2, '22 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons                                                                    ', 'Hotel_hotel2.jpg', 1),
(29, 'Room 3', 2500.00, 2, 2, '22 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons                                            ', 'Hotel_hotel3.jpg', 1),
(30, 'Room 4', 2500.00, 2, 2, '22 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons                      ', 'Hotel_80_hotel4.jpg', 1),
(31, 'Room 5', 3500.00, 3, 3, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons                      ', 'Hotel_86_hotel5.jpeg', 1),
(32, 'Room 6', 3500.00, 3, 6, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_40_hotel4.jpg', 1),
(33, 'Room 7', 3500.00, 3, 3, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons                      ', 'Hotel_28_hotel5.jpeg', 4),
(34, 'Room 8', 3500.00, 3, 6, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_49_hotel4.jpg', 1),
(35, 'Room 9', 3500.00, 3, 3, '22 hours', 1, 'Barkada Room', 'Hotel_91_hotel3.jpg', 1),
(36, 'Room 10', 3500.00, 3, 6, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_81_hotel3.jpg', 1),
(37, 'Room 11', 3500.00, 3, 6, '22 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_87_hotel1.jpg', 1),
(38, 'Room 11', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_63_hotel4.jpg', 1),
(39, 'Room 10', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_60_hotel3.jpg', 1),
(40, 'Room 9', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_81_hotel3.jpg', 1),
(41, 'Room 8', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_34_hotel3.jpg', 1),
(42, 'Room 7', 2500.00, 3, 3, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons                      ', 'Hotel_76_hotel4.jpg', 4),
(43, 'Room 6', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_49_hotel3.jpg', 1),
(44, 'Room 5', 2500.00, 3, 6, '11 hours', 1, 'Good for 3, Free access to swimming pool, Queen Size Bed, 1 Free extra bed, Maximum of 6 persons', 'Hotel_15_hotel5.jpeg', 1),
(45, 'Room 4', 2000.00, 2, 4, '11 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons', 'Hotel_53_hotel5.jpeg', 1),
(46, 'Room 3', 2000.00, 2, 4, '11 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons', 'Hotel_90_hotel1.jpg', 1),
(47, 'Room 2', 2000.00, 2, 4, '11 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons', 'Hotel_52_hotel3.jpg', 1),
(48, 'Room 1', 2000.00, 2, 4, '11 hours', 1, 'Good for 2, Free access to swimming pool, Double Size Bed, Maximum of 4 persons', 'Hotel_32_hotel3.jpg', 1),
(49, 'Catering Service', 300.00, 0, 0, '', 5, '  Basic Design for tables, chairs, and stage\\r\\n4 Dishes (vegetables is included)\\r\\nw/ Rice and Juice/Drink\\r\\nDessert', 'defaultImage.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `resortinfo`
--

CREATE TABLE `resortinfo` (
  `resortInfoID` int(11) NOT NULL,
  `resortInfoTitle` varchar(200) NOT NULL,
  `resortInfoName` varchar(200) NOT NULL,
  `resortInfoDetail` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resortinfo`
--

INSERT INTO `resortinfo` (`resortInfoID`, `resortInfoTitle`, `resortInfoName`, `resortInfoDetail`) VALUES
(1, 'BusinessInformation', 'DisplayName', 'Mamyr'),
(2, 'BusinessInformation', 'FullName', 'Mamyr Resort and Events Place'),
(3, 'BusinessInformation', 'ShortDesc', 'Welcome to Mamyr Resort and Event Place! We\'re more than just a resort, we\'re a place where memories are made. Whether you\'re here for a relaxing getaway, a family gathering, or a special event, we\'re'),
(4, 'BusinessInformation', 'ContactNum', '(0998) 962 4697'),
(5, 'BusinessInformation', 'Email', 'mamyresort128@gmail.com'),
(6, 'BusinessInformation', 'Address', 'Sitio Colonia Gabihan, San Ildefonso, Bulacan'),
(7, 'BusinessInformation', 'ShortDesc2', ' Welcome to Mamyr Resort and Events Place, where relaxation and unforgettable moments await you. Whether you\'re here for a peaceful retreat or a special celebration, we\'re dedicated to making your exp'),
(8, 'BusinessInformation', 'FBLink', 'https://www.facebook.com/p/Mamyr-Resort-Restaurant-Events-Place-100083298304476/'),
(9, 'BusinessInformation', 'GmailAdd', 'mamyresort128@gmail.com'),
(10, 'Logo', 'MamyrLogo.png', 'Mamyr Logo'),
(11, 'Resort Owner', 'Owner Full Name', 'Myrna C. Dela Cruz - Prop.'),
(12, 'paymentInformation ', 'gcashNumber', '(0998) 962 4697 - Mamerto Dela Cruz');

-- --------------------------------------------------------

--
-- Table structure for table `resortservicescategory`
--

CREATE TABLE `resortservicescategory` (
  `categoryID` int(11) NOT NULL,
  `categoryName` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resortservicescategory`
--

INSERT INTO `resortservicescategory` (`categoryID`, `categoryName`) VALUES
(1, 'Hotel'),
(2, 'Cottage'),
(3, 'Entertainment'),
(4, 'Event Hall'),
(5, 'Catering');

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `serviceID` int(11) NOT NULL,
  `resortServiceID` int(11) DEFAULT NULL,
  `partnershipServiceID` int(11) DEFAULT NULL,
  `entranceRateID` int(11) DEFAULT NULL,
  `serviceType` enum('Resort','Partnership','Entrance') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`serviceID`, `resortServiceID`, `partnershipServiceID`, `entranceRateID`, `serviceType`) VALUES
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
(27, 27, NULL, NULL, 'Resort'),
(28, 28, NULL, NULL, 'Resort'),
(29, 29, NULL, NULL, 'Resort'),
(30, 30, NULL, NULL, 'Resort'),
(31, 31, NULL, NULL, 'Resort'),
(32, 32, NULL, NULL, 'Resort'),
(33, 33, NULL, NULL, 'Resort'),
(34, 34, NULL, NULL, 'Resort'),
(35, 35, NULL, NULL, 'Resort'),
(36, 36, NULL, NULL, 'Resort'),
(37, 37, NULL, NULL, 'Resort'),
(38, 38, NULL, NULL, 'Resort'),
(39, 39, NULL, NULL, 'Resort'),
(40, 40, NULL, NULL, 'Resort'),
(41, 41, NULL, NULL, 'Resort'),
(42, 42, NULL, NULL, 'Resort'),
(43, 43, NULL, NULL, 'Resort'),
(44, 44, NULL, NULL, 'Resort'),
(45, 45, NULL, NULL, 'Resort'),
(46, 46, NULL, NULL, 'Resort'),
(47, 47, NULL, NULL, 'Resort'),
(48, 48, NULL, NULL, 'Resort'),
(49, NULL, NULL, 1, 'Entrance'),
(50, NULL, NULL, 2, 'Entrance'),
(51, NULL, NULL, 3, 'Entrance'),
(52, NULL, NULL, 4, 'Entrance'),
(53, NULL, NULL, 5, 'Entrance'),
(54, NULL, NULL, 6, 'Entrance');

-- --------------------------------------------------------

--
-- Table structure for table `serviceavailability`
--

CREATE TABLE `serviceavailability` (
  `availabilityID` int(11) NOT NULL,
  `availabilityName` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `serviceavailability`
--

INSERT INTO `serviceavailability` (`availabilityID`, `availabilityName`) VALUES
(1, 'Available'),
(3, 'Maintenance'),
(5, 'Not Available'),
(2, 'Occupied'),
(4, 'Private');

-- --------------------------------------------------------

--
-- Table structure for table `servicepricing`
--

CREATE TABLE `servicepricing` (
  `pricingID` int(11) NOT NULL,
  `pricingType` enum('Per Head','Per Hour') DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `chargeType` enum('Room','Food','Event') NOT NULL,
  `ageGroup` enum('Adult','Child','Both') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `servicepricing`
--

INSERT INTO `servicepricing` (`pricingID`, `pricingType`, `price`, `chargeType`, `ageGroup`, `notes`, `createdAt`) VALUES
(1, 'Per Head', 300.00, 'Food', 'Both', 'Meal charges calculated at ₱300.00 per person', '2025-12-22 08:27:35'),
(2, 'Per Head', 250.00, 'Room', 'Both', 'Extra charge per person if the room occupancy limit is exceeded.', '2025-12-22 08:27:35'),
(3, 'Per Hour', 500.00, 'Room', 'Both', '500 per hour\r\n', '2025-12-22 08:27:35'),
(4, 'Per Hour', 2000.00, 'Event', 'Both', NULL, '2025-12-22 08:27:35');

-- --------------------------------------------------------

--
-- Table structure for table `serviceunavailabledate`
--

CREATE TABLE `serviceunavailabledate` (
  `serviceUnavailableID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `resortServiceID` int(11) DEFAULT NULL,
  `partnershipServiceID` int(11) DEFAULT NULL,
  `unavailableStartDate` datetime NOT NULL,
  `unavailableEndDate` datetime NOT NULL,
  `status` enum('hold','confirmed','cancelled') NOT NULL DEFAULT 'hold',
  `expiresAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `statusID` int(11) NOT NULL,
  `statusName` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`statusID`, `statusName`) VALUES
(2, 'Approved'),
(4, 'Cancelled'),
(6, 'Done'),
(7, 'Expired'),
(1, 'Pending'),
(5, 'Rejected'),
(3, 'Reserved');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `middleInitial` varchar(5) DEFAULT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `userAddress` text NOT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `birthDate` date DEFAULT NULL,
  `userProfile` longblob DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `isTemporaryPassword` tinyint(1) DEFAULT 0,
  `userRole` int(11) NOT NULL DEFAULT 1,
  `userOTP` varchar(100) DEFAULT NULL,
  `OTP_expiration_at` datetime DEFAULT NULL,
  `userStatusID` int(11) NOT NULL DEFAULT 1,
  `isTermsAccepted` tinyint(1) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `isDeleted` tinyint(1) DEFAULT 0,
  `dateDeleted` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userreview`
--

CREATE TABLE `userreview` (
  `userReviewID` int(11) NOT NULL,
  `bookingID` int(11) DEFAULT NULL,
  `bookingType` varchar(50) DEFAULT NULL,
  `reviewRating` decimal(2,1) DEFAULT NULL,
  `reviewComment` text DEFAULT NULL,
  `dateReviewed` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userstatus`
--

CREATE TABLE `userstatus` (
  `userStatusID` int(11) NOT NULL,
  `statusName` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userstatus`
--

INSERT INTO `userstatus` (`userStatusID`, `statusName`) VALUES
(4, 'Deleted'),
(3, 'Not Verified'),
(1, 'Pending'),
(2, 'Verified');

-- --------------------------------------------------------

--
-- Table structure for table `usertype`
--

CREATE TABLE `usertype` (
  `userTypeID` int(11) NOT NULL,
  `typeName` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usertype`
--

INSERT INTO `usertype` (`userTypeID`, `typeName`) VALUES
(3, 'Admin'),
(1, 'Customer'),
(2, 'Partner'),
(4, 'Partner Request');

-- --------------------------------------------------------

--
-- Table structure for table `walkin_sales_summary`
--

CREATE TABLE `walkin_sales_summary` (
  `salesID` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `bookingType` enum('Resort','Hotel','Event') NOT NULL,
  `bookingCount` int(50) NOT NULL DEFAULT 0,
  `salesAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `websitecontent`
--

CREATE TABLE `websitecontent` (
  `contentID` int(11) NOT NULL,
  `adminID` int(11) NOT NULL,
  `sectionName` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `lastUpdated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `websitecontent`
--

INSERT INTO `websitecontent` (`contentID`, `adminID`, `sectionName`, `title`, `content`, `lastUpdated`) VALUES
(1, 1, 'Landing', 'Heading', 'Discover the Perfect Destination for Relaxation and Celebration at Mamyr Resort & Events Place.', '2025-10-22 20:43:31'),
(2, 1, 'Landing', 'Welcome', 'Welcome to Mamyr Resort & Events Place gdfg', '2025-10-22 20:43:31'),
(3, 1, 'Landing', 'Subheading', 'Whether you’re here for a peaceful getaway or planning your next event, we’re here to make your time memorable and enjoyable.', '2025-10-22 20:43:31'),
(4, 1, 'Landing', 'Heading2', 'Escape to Paradise', '2025-10-22 20:43:31'),
(5, 1, 'Landing', 'Subheading2', 'At Mamyr Resort & Events Place, we offer more than just a getaway—we provide an unforgettable experience. Whether you\'re seeking a peaceful retreat, fun outdoor activities, or a beautiful venue for your special events, we’ve got you covered. Nestled in nature’s embrace, our resort is the perfect escape for families, couples, and groups alike.', '2025-10-22 20:43:31'),
(6, 1, 'Landing', 'BookNow', 'Book Your Stay or Event Today!', '2025-10-22 20:43:31'),
(7, 1, 'Landing', 'BookNowDesc', 'Whether you’re looking to escape for a weekend of relaxation or host an unforgettable event, Mamyr Resort & Events Place is ready to welcome you. Our dedicated team is here to ensure your stay or event exceeds expectations, offering personalized service and attention to every detail.', '2025-10-22 20:43:31'),
(8, 1, 'Landing', 'Reviews', 'Why Guests Keep Coming Back', '2025-10-22 20:43:31'),
(9, 1, 'Landing', 'ReviewsDesc', 'Guests often return to Mamyr Resort and Events Place for the exceptional experience we offer across every aspect of the resort. From our beautiful grounds to our attentive service, we’re dedicated to creating spaces where visitors feel at home. Consistent feedback reflects our commitment to quality, making Mamyr a standout destination for relaxation and celebration alike.', '2025-10-22 20:43:31'),
(10, 1, 'Landing', 'Map', 'Discover Mamyr Resort & Events Place', '2025-10-22 20:43:31'),
(11, 1, 'Landing', 'MapDesc', 'Experience paradise just a drive away at Mamyr Resort & Events Place, where every moment becomes a memory — whether you\'re unwinding by the pool, celebrating life’s milestones, or escaping for a weekend retreat. Visit us and experience where nature and comfort meet.', '2025-10-22 20:43:31'),
(12, 1, 'Blog', 'MainTitle', 'The Mamyr Resort Blog: Stay Infomed, Stay Inspired', '2025-10-22 20:43:31'),
(13, 1, 'Blog', 'Sub-title', 'Inspiration, Updates, and Insights Straight from Mamyr Resort', '2025-10-22 20:43:31'),
(14, 1, 'Blog', 'BlogPost1-EventType', 'Private Event', '2025-10-22 20:43:31'),
(15, 1, 'Blog', 'BlogPost1-EventDate', '2025-08-01', '2025-10-22 20:43:31'),
(16, 1, 'Blog', 'BlogPost1-EventHeader', 'Mamyr Resort is Closed for a Private Event', '2025-10-22 20:43:31'),
(17, 1, 'Blog', 'BlogPost1-Content', 'We are sorry, but we will be closed on Sunday, June 1, 2025, due to a private event. Invitation-only guests will be allowed, and we will not be accepting walk-ins on this date. Thank you for your understanding.', '2025-10-22 20:43:31'),
(18, 1, 'Blog', 'BlogPost2-EventType', '7th Birthday ', '2025-10-22 20:43:31'),
(19, 1, 'Blog', 'BlogPost2-EventDate', '2025-06-02', '2025-10-22 20:43:31'),
(20, 1, 'Blog', 'BlogPost2-EventHeader', 'Yohan\'s Magical 7th Birthday Celebration at Mamyr!', '2025-10-22 20:43:31'),
(21, 1, 'Blog', 'BlogPost2-Content', 'Yohan marked his 7th birthday in style at Mamyr, where the day was packed with exciting activities, laughter, and unforgettable moments that made it a celebration to remember.', '2025-10-22 20:43:31'),
(22, 1, 'Blog', 'BlogPost3-EventType', 'Thanksgiving Party', '2025-10-22 20:43:31'),
(23, 1, 'Blog', 'BlogPost3-EventDate', '2025-01-02', '2025-10-22 20:43:31'),
(24, 1, 'Blog', 'BlogPost3-EventHeader', 'XYZ Company\'s Thanksgiving Party ', '2025-10-22 20:43:31'),
(25, 1, 'Blog', 'BlogPost3-Content', 'XYZ Company\'s Thanksgiving Party: A Heartfelt Celebration of Gratitude, Team Spirit, and the Strong Bonds We’ve Built Together. This special gathering brought everyone together to reflect on the year\'s achievements, share delicious food, and create lasting memories with colleagues.', '2025-10-22 20:43:31'),
(26, 1, 'Blog', 'BlogPost4-EventType', 'Debut', '2025-10-22 20:43:31'),
(27, 1, 'Blog', 'BlogPost4-EventDate', '2024-09-02', '2025-10-22 20:43:31'),
(28, 1, 'Blog', 'BlogPost4-EventHeader', 'Jannah\'s 18th Birthday', '2025-10-22 20:43:31'),
(29, 1, 'Blog', 'BlogPost4-Content', 'Jannah\'s 18th Birthday: A Joyous Celebration Marking the Transition into Adulthood with Laughter, Love, and Unforgettable Memories. Surrounded by family and friends, this milestone birthday was a beautiful blend of cherished moments, heartfelt wishes, and the excitement of new beginnings.', '2025-10-22 20:43:31'),
(30, 1, 'About', 'Header', 'Compassionate Service, Unforgettable Family Moments', '2025-10-22 20:43:31'),
(31, 1, 'About', 'AboutMamyr', 'Mamyr Resort and Events Place is a peaceful getaway located in Gabihan, San Ildefonso, Bulacan, built on a story of resilience, love, and family. Before it became a resort, the land was used for pig farming. When the business faced financial challenges, owners Mamerto Dela Cruz and Myrna Dela Cruz looked for a new opportunity—something that would not only support their family but also bring joy to others.', '2025-10-22 20:43:31'),
(32, 1, 'About', 'ServicesDesc', 'Mamyr isn’t just a resort; it’s a family-oriented getaway with comfortable rooms and a versatile event venue for gatherings and celebrations. It offers a relaxed, fun environment for all ages to enjoy.', '2025-10-22 20:43:31'),
(33, 1, 'About', 'Service1', 'Resort', '2025-10-22 20:43:31'),
(34, 1, 'About', 'Service1Desc', 'Mamyr features three refreshing pools, providing the perfect spots for family fun, relaxation, and leisurely swims.', '2025-10-22 20:43:31'),
(35, 1, 'About', 'Service2', 'Events Place', '2025-10-22 20:43:31'),
(36, 1, 'About', 'Service2Desc', 'Mamyr’s versatile event venue offers a spacious and welcoming setting, ideal for family gatherings, reunions, and celebrations of all kinds.', '2025-10-22 20:43:31'),
(37, 1, 'About', 'Service3', 'Hotel', '2025-10-22 20:43:31'),
(38, 1, 'About', 'Service3Desc', 'Mamyr’s cozy hotel features 11 comfortable rooms, perfect for a relaxing stay with family and friends.', '2025-10-22 20:43:31'),
(39, 1, 'About', 'Explore', 'At Mamyr Resort, we treat every guest like family, offering an experience that goes beyond just comfort. From our humble beginnings to the thriving retreat we are today, we\'ve poured our heart and soul into creating a sanctuary where nature and relaxation meet. Our story is built on passion, growth, and a deep commitment to providing an unforgettable experience. When you visit, you’ll discover not just stunning surroundings and luxurious comfort, but the warm, welcoming spirit that defines us. Come join us and see firsthand what makes Mamyr Resort a place where memories are made, and guests feel right at home.', '2025-10-22 20:43:31'),
(40, 1, 'About', 'HistoryParagraph1', 'Mamyr Resort and Events Place is a peaceful getaway located in Gabihan, San Ildefonso, Bulacan, built on a story of resilience, love, and family. Before it became a resort, the land was used for pig farming. When the business faced financial challenges, owners Mamerto Dela Cruz and Myrna Dela Cruz looked for a new opportunity— something that would not only support their family but also bring joy to others.', '2025-10-22 20:43:31'),
(41, 1, 'About', 'HistoryParagraph2', 'With faith and hard work, they transformed the land into a relaxing resort that people could enjoy. Their vision and dedication shaped the landscape into a serene retreat where visitors could unwind and create lasting memories. The name \"Mamyr\" came from their own names—Mamerto and Myrna—a symbol of the spirit of unity that brought the resort to life, making it not just a place to stay, but a reflection of their dreams and the love they poured into every corner of the property.', '2025-10-22 20:43:31'),
(42, 1, 'About', 'HistoryParagraph3', 'Opened in 2022, Mamyr Resort has become a popular and welcoming place for people looking to relax and enjoy nature. The resort is known for its clean swimming pools, spacious function areas, beautiful surroundings, and warm hospitality. Guests can enjoy the resort\'s three refreshing swimming pools, two elegant pavilions, cozy cottages to stay in, as well as 11 comfortable hotel rooms for those who prefer a more private stay, and a spacious parking lot to accommodate all guests conveniently.', '2025-10-22 20:43:31'),
(43, 1, 'About', 'HistoryParagraph4', 'At Mamyr Resort, we treat every guest like family, making sure your stay is special and enjoyable. Whether you\'re celebrating an important event, spending time with loved ones, or just looking for a peaceful break, we have everything you need to feel comfortable and relaxed. Our team works hard to create a warm and welcoming atmosphere where you can make lasting memories. Visit us and see for yourself why we\'re so proud of how much we\'ve grown.', '2025-10-22 20:43:31'),
(44, 1, 'Amenities', 'Amenity1', 'Swimming Pools', '2025-10-22 20:43:31'),
(45, 1, 'Amenities', 'Amenity1Desc', 'We offer three spacious pools designed for relaxation and fun. Whether you’re looking to take a refreshing dip or lounge by the water, each pool provides a perfect setting to unwind and enjoy your stay. Dive in and make the most of your resort experience!', '2025-10-22 20:43:31'),
(46, 1, 'Amenities', 'Amenity2', 'Cottages', '2025-10-22 20:43:31'),
(47, 1, 'Amenities', 'Amenity2Desc', 'Our cozy cottages offer a relaxing retreat with spacious porches, secure surroundings, and a refreshing ambiance. Enjoy a perfect blend of nature and modern facilities designed for your comfort.', '2025-10-22 20:43:31'),
(48, 1, 'Amenities', 'Amenity3', 'Videoke Area', '2025-10-22 20:43:31'),
(49, 1, 'Amenities', 'Amenity3Desc', 'Enjoy nonstop fun just steps away from your cottage! Our videoke area is conveniently located beside the cottages, making it easy to sing, laugh, and bond without going far. With a great sound system and cozy setup, it’s the perfect spot for music-filled memories in the heart of the resort.', '2025-10-22 20:43:31'),
(50, 1, 'Amenities', 'Amenity4', 'Pavilion Hall', '2025-10-22 20:43:31'),
(51, 1, 'Amenities', 'Amenity4Desc', 'Our Pavilion Hall offers the perfect space for events, gatherings, and special occasions. With its spacious and elegant design, it’s ideal for everything from weddings to corporate events, comfortably accommodating up to 350 guests. Included with your rental is exclusive access to one private air-conditioned room and a dedicated powder room with separate comfort rooms for both male and female guests.', '2025-10-22 20:43:31'),
(52, 1, 'Amenities', 'Amenity5', 'Mini Pavilion', '2025-10-22 20:43:31'),
(53, 1, 'Amenities', 'Amenity5Desc', 'Our mini pavilion offers an intimate and charming space perfect for small gatherings and special occasions. Designed to comfortably accommodate up to 50 guests, it’s ideal for birthdays, reunions, meetings, or any cozy celebration. Surrounded by a refreshing resort atmosphere, it provides both functionality and a relaxing vibe.', '2025-10-22 20:43:31'),
(54, 1, 'Amenities', 'Amenity6', 'Mamyr Hotel', '2025-10-22 20:43:31'),
(55, 1, 'Amenities', 'Amenity6Desc', 'We offer 11 thoughtfully designed hotel rooms, each providing a peaceful and comfortable retreat. Perfect for guests looking for a relaxing space to unwind after a day of exploration, our rooms offer all the essentials for a restful stay with a touch of convenience.', '2025-10-22 20:43:31'),
(56, 1, 'Amenities', 'Amenity7', 'Parking Space', '2025-10-22 20:43:31'),
(57, 1, 'Amenities', 'Amenity7Desc', 'We provide ample parking spaces to ensure a hassle-free stay. Whether you’re arriving by car or with a group, our secure parking area is conveniently located, giving you peace of mind throughout your visit.', '2025-10-22 20:43:31'),
(113, 1, 'BusinessInformation', 'DisplayName', 'Mamyr', '2025-10-22 20:43:31'),
(114, 1, 'BusinessInformation', 'FullName', 'Mamyr Resort and Events Place', '2025-10-22 20:43:31'),
(115, 1, 'BusinessInformation', 'ShortDesc', 'Welcome to Mamyr Resort and Event Place! We\'re more than just a resort, we\'re a place where memories are made. Whether you\'re here for a relaxing getaway, a family gathering, or a special event, we’re dedicated to making your stay unforgettable.', '2025-10-22 20:43:31'),
(116, 1, 'BusinessInformation', 'ContactNum', '(0998) 962 4697', '2025-10-22 20:43:31'),
(117, 1, 'BusinessInformation', 'Email', 'mamyresort128@gmail.com', '2025-10-22 20:43:31'),
(118, 1, 'BusinessInformation', 'Address', 'Sitio Colonia Gabihan, San Ildefonso, Bulacan', '2025-10-22 20:43:31'),
(119, 1, 'BusinessInformation', 'ShortDesc2', ' Welcome to Mamyr Resort and Events Place, where relaxation and unforgettable moments await you. Whether you\'re here for a peaceful retreat or a special celebration, we\'re dedicated to making your experience truly exceptional.', '2025-10-22 20:43:31'),
(120, 1, 'Rates and Hotel Rooms', 'HotelDesc', 'Mamyr Resort and Events Place is not only a venue for unforgettable celebrations but also a relaxing retreat, offering 11 air-conditioned hotel rooms for guests seeking comfort and convenience. Every booking at the hotel includes complimentary access to the resort\'s pool, allowing guests to unwind and enjoy their stay to the fullest. Whether you\'re here for a grand occasion or a quiet getaway, Mamyr Resort offers a beautiful and welcoming environment for all.', '2025-10-22 20:43:31'),
(121, 1, 'Events', 'EventTitle', 'EVENTS', '2025-10-22 20:43:31'),
(122, 1, 'Events', 'EventDesc', 'At Mamyr Resort and Events Place, we celebrate life’s most meaningful moments—weddings, birthdays, reunions, corporate events, and more—that can be celebrated in our Pavilion, which can occupy up to 350 guests, and our Mini Pavilion, perfect for more intimate gatherings of up to 50 guests. Whether grand or small, each event is made memorable in a beautiful and comfortable setting designed to suit your occasion.', '2025-10-22 20:43:31'),
(123, 1, 'Events', 'OurEventsTitle', 'Our Venues', '2025-10-22 20:43:31'),
(124, 1, 'Events', 'OurEventsDesc', 'Mamyr Resort and Events Place offers two exceptional venues: the spacious Main Function Hall for grand celebrations and the Mini Function Hall for intimate gatherings—both crafted to make every event truly memorable.', '2025-10-22 20:43:31'),
(125, 1, 'BookNowResort', 'ResortRules', 'Mahigpit na ipinagbabawal ang nasa impluwensiya ng alak o ipinagbabawal na gamot. , Mag shower muna bago mag swimming. , Ang mga lalaki ay dapat naka-satin shorts lamang. , Ang mga babae ay dapat nakasando o puting pang itaas at satin shorts lamang. , Bawal ang maong o six-pocket shorts, at colored shirt. , Bawal ang tsinelas sa loob ng pool. , Maglubog muna ng paa sa hugasan ng paa bago mag swimming. , Bawal ang anumang pagkain at inumin sa loob at paligid ng pool. , Bawal magtakbuhan, tulakan at kahit anong acrobatic stunts sa paligid ng pool upang maiwasan ang anumang aksidente. , Bawal ang mga bata sa malalim na bahagi ng pool. , Mga magulang, gabayang maigi ang inyong mga anak. , Hangga\'t maaari iwasang manakit ng inyong kapwa. , Gamitin lang ang cottage na inyong binayaran', '2025-12-08 00:02:06'),
(134, 1, 'TermsAndConditions', 'CustomerTerms', 'Welcome to Mamyr Resort and Events Place! By using our Resort Event Management System, you agree to abide by the terms and conditions outlined below. These terms apply to all bookings made for the resort, hotel, and event venues via this platform.\nPlease read these terms carefully before making any bookings.\n1. Booking & Reservation\n•	Eligibility: Users must be at least 18 years of age to book any services via our system.\n•	Booking Process: To make a booking, users must provide accurate details, including full name, contact information, payment details, and any additional requirements (e.g., room preferences, event specifications).\n•	Confirmation: A booking is considered confirmed once you receive an official booking confirmation email or notification. Any reservation made without this confirmation will not be considered valid.\n•	Booking Modifications: You may modify or cancel your booking through the system, provided such changes comply with the cancellation and modification policy.\n2. Payments & Charges\n•	Pricing: All pricing for resort accommodations, hotel rooms, and event venues are displayed clearly on the platform. Prices are subject to change based on seasonality, availability, or promotions.\n•	Payment Methods: We only accept certain payment methods, namely GCash and on-site cash payments. Down payments must be made before the time of booking unless otherwise stated.\n•	Refunds: Our business does not provide refunds for down payment upon cancellation. Users are encouraged to ensure that their booking information, as well as their schedules for their desired booking dates are accurately provided to avoid the need for cancellations.\n3. Check-in & Check-out\n•	Hotel & Resort: Early check-ins or late check-outs are subject to availability and may incur additional charges.\n•	Event Venue: Event venue access will be granted as per the agreed-upon event time. Additional charges may apply for extended event hours.\n4. Limitation of Liability\n•	Hotel/Resort Liability: Our liability for any loss, injury, or damage incurred during a stay or event is limited to the amount paid for the booking. We are not liable for any indirect or consequential damages.\n•	Event Liability: The resort is not responsible for any third-party event organizer’s actions or services. Any complaints regarding event services should be directed to the event organizer.\n5. Privacy & User Data Policy\nWe respect your privacy and are committed to protecting your personal data in compliance with the Data Privacy Act of 2012 (Republic Act No. 10173) and other relevant Philippine data protection laws. By using our platform, you agree to the collection, storage, and use of your data as outlined below.\n•	Types of Data Collected:\no	Personal Information: We collect your name, email address, phone number, and other personal details you provide during booking.\no	Payment Information: Payment details, such as GCash account numbers and billing information, are processed securely through third-party payment gateways.\no	Booking Data: We collect details of your bookings, such as accommodation type, check-in/check-out dates, event preferences, and any additional services requested.\n•	Use of Data:\no	We use your personal and booking information to process and manage your reservations, send booking confirmations, and provide customer support.\no	Payment details are used exclusively for processing payments and are never stored on our servers.\no	We may use your contact information to send promotional offers, newsletters, and updates about our services (you can opt-out at any time).\n•	Data Protection:\no	We implement security measures to protect your personal and payment information during transmission and storage.\no	We comply with the Data Privacy Act of 2012 and other applicable laws in the Philippines to ensure your data is handled with utmost care and confidentiality.\n•	Retention of Data: We retain your data only for as long as necessary to fulfill the purpose for which it was collected, including legal and accounting obligations. If you wish to delete your data, please contact us directly.\n•	Your Rights:\no	Access: You have the right to request a copy of your personal data.\no	Rectification: You can request corrections to any inaccuracies in your personal data.\no	Deletion: You can request the deletion of your personal data, subject to certain legal exceptions.\no	Opt-Out: You can opt out of marketing communications at any time by following the unsubscribe instructions in emails or contacting us directly.\nFor more information about how we handle your personal data, please refer to our full Privacy Policy available on our website.\n6. Modifications to Terms & Conditions\nWe reserve the right to modify these terms and conditions at any time. Any changes will be effective immediately upon posting on the platform. Users are encouraged to review these terms regularly.\n7. Dispute Resolution\nAny disputes arising from bookings or the use of our Resort Event Management System shall be resolved through binding arbitration in the jurisdiction of San Ildefonso, Bulacan, Philippines. In the event that arbitration is not possible, disputes will be subject to Philippine laws and resolved in the appropriate court.\n8. Governing Law\nThese terms and conditions shall be governed by and construed in accordance with the laws of the Philippines.\n9. Contact Information\nIf you have any questions about these terms and conditions, please contact us at:\n•	Email: mamyresort128@gmail.com\n•	Phone: (0998) 962 4697\n•	Address: Sitio Colonia Gabihan, San Ildefonso, Bulacan\n\n\n\n\nWelcome to Mamyr Resort and Events Place! By using our Resort Event Management System, you agree to abide by the terms and conditions outlined below. These terms apply to all bookings made for the resort, hotel, and event venues via this platform.\nPlease read these terms carefully before making any bookings.\n1. Booking & Reservation\n•	Eligibility: Users must be at least 18 years of age to book any services via our system.\n•	Booking Process: To make a booking, users must provide accurate details, including full name, contact information, payment details, and any additional requirements (e.g., room preferences, event specifications).\n•	Confirmation: A booking is considered confirmed once you receive an official booking confirmation email or notification. Any reservation made without this confirmation will not be considered valid.\n•	Booking Modifications: You may modify or cancel your booking through the system, provided such changes comply with the cancellation and modification policy.\n2. Payments & Charges\n•	Pricing: All pricing for resort accommodations, hotel rooms, and event venues are displayed clearly on the platform. Prices are subject to change based on seasonality, availability, or promotions.\n•	Payment Methods: We only accept certain payment methods, namely GCash and on-site cash payments. Down payments must be made before the time of booking unless otherwise stated.\n•	Refunds: Our business does not provide refunds for down payment upon cancellation. Users are encouraged to ensure that their booking information, as well as their schedules for their desired booking dates are accurately provided to avoid the need for cancellations.\n3. Check-in & Check-out\n•	Hotel & Resort: Early check-ins or late check-outs are subject to availability and may incur additional charges.\n•	Event Venue: Event venue access will be granted as per the agreed-upon event time. Additional charges may apply for extended event hours.\n4. Limitation of Liability\n•	Hotel/Resort Liability: Our liability for any loss, injury, or damage incurred during a stay or event is limited to the amount paid for the booking. We are not liable for any indirect or consequential damages.\n•	Event Liability: The resort is not responsible for any third-party event organizer’s actions or services. Any complaints regarding event services should be directed to the event organizer.\n5. Privacy & User Data Policy\nWe respect your privacy and are committed to protecting your personal data in compliance with the Data Privacy Act of 2012 (Republic Act No. 10173) and other relevant Philippine data protection laws. By using our platform, you agree to the collection, storage, and use of your data as outlined below.\n•	Types of Data Collected:\no	Personal Information: We collect your name, email address, phone number, and other personal details you provide during booking.\no	Payment Information: Payment details, such as GCash account numbers and billing information, are processed securely through third-party payment gateways.\no	Booking Data: We collect details of your bookings, such as accommodation type, check-in/check-out dates, event preferences, and any additional services requested.\n•	Use of Data:\no	We use your personal and booking information to process and manage your reservations, send booking confirmations, and provide customer support.\no	Payment details are used exclusively for processing payments and are never stored on our servers.\no	We may use your contact information to send promotional offers, newsletters, and updates about our services (you can opt-out at any time).\n•	Data Protection:\no	We implement security measures to protect your personal and payment information during transmission and storage.\no	We comply with the Data Privacy Act of 2012 and other applicable laws in the Philippines to ensure your data is handled with utmost care and confidentiality.\n•	Retention of Data: We retain your data only for as long as necessary to fulfill the purpose for which it was collected, including legal and accounting obligations. If you wish to delete your data, please contact us directly.\n•	Your Rights:\no	Access: You have the right to request a copy of your personal data.\no	Rectification: You can request corrections to any inaccuracies in your personal data.\no	Deletion: You can request the deletion of your personal data, subject to certain legal exceptions.\nFor more information about how we handle your personal data, please refer to our full Privacy Policy available on our website.\n6. Modifications to Terms & Conditions\nWe reserve the right to modify these terms and conditions at any time. Any changes will be effective immediately upon posting on the platform. Users are encouraged to review these terms regularly.\n7. Dispute Resolution\nAny disputes arising from bookings or the use of our Resort Event Management System shall be resolved through binding arbitration in the jurisdiction of San Ildefonso, Bulacan, Philippines. In the event that arbitration is not possible, disputes will be subject to Philippine laws and resolved in the appropriate court.\n8. Governing Law\nThese terms and conditions shall be governed by and construed in accordance with the laws of the Philippines.\n9. Contact Information\nIf you have any questions about these terms and conditions, please contact us at:\n•	Email: mamyresort128@gmail.com\n•	Phone: (0998) 962 4697\n•	Address: Sitio Colonia Gabihan, San Ildefonso, Bulacan\n10. Business Partner Terms\n• Eligibility: Business Partners must be at least 18 years of age to register and offer services via the platform.\n• Registration: Business Partners must complete a registration process and provide accurate business details, including the business name, contact information, services offered, and any additional requirements. Once approved, Business Partners will be granted access to manage and offer their services through the system.\n• Bookings & Reservations:\n•	Customer Interaction: Business Partners can list their services on the platform for customers to book. While Business Partners can view the bookings made for their services, they do not have the ability to approve or reject bookings.\n•	Booking Details: All bookings made through the platform will be reflected on the Business Partner’s page, and any relevant customer details will be provided for them to coordinate and prepare for the service being offered.\n•	Admin Communication: The Admin will contact the Business Partner directly once their services have been booked by a customer. This communication will include the booking details and any necessary information regarding the event or service.\n• Commission & Fees:\n•	Commission Disclosure: The commission rate applicable to Business Partners will be disclosed after the scheduled event between the Admin and the Business Partner. This will allow for transparent and mutually agreed-upon terms following the completion of the event.\n•	Payment Terms: Payments for bookings made through the platform will be processed directly through the platform’s payment system. After the event, the Admin will inform the Business Partner of their commission, and payments will be made according to the agreed-upon schedule, after deducting the commission fee.\n• Liability & Responsibilities:\n•	Service Delivery: Business Partners are fully responsible for delivering the services they offer to customers. They must ensure that services are provided as described, in a timely manner, and meet the standards expected by the customer.\n•	Customer Complaints: Any complaints or disputes regarding the services provided by the Business Partner should be resolved directly between the Business Partner and the customer. The resort is not responsible for the actions or services of Business Partners.\n•	Indemnity: Business Partners agree to indemnify and hold harmless Mamyr Resort from any claims, losses, or damages that arise from their services or the actions of their employees, contractors, or representatives.\n• Booking Modifications & Cancellations:\n•	Business Partners may request modifications or cancellations to bookings if necessary, but such changes will still be subject to the customer’s terms, as well as Mamyr Resort’s cancellation and modification policy.\n•	Business Partners should communicate directly with customers if any changes need to be made to the booking or service.\n• Promotions & Advertising: Business Partners may participate in promotional campaigns, discounts, or special offers on the platform. Any such offers or campaigns will be subject to mutual agreement and will be advertised on the platform.\n• Compliance with Laws: Business Partners are responsible for ensuring that their business and services comply with all applicable laws and regulations, including those related to safety, licensing, and tax obligations. Mamyr Resort is not responsible for the legality of the services offered by Business Partners.', '2025-12-01 23:42:42'),
(135, 1, 'TermsAndConditions', 'busPartnerTerms', 'Welcome to Mamyr Resort and Events Place! By using our Resort Event Management System, you agree to abide by the terms and conditions outlined below. These terms apply to all bookings made for the resort, hotel, and event venues via this platform.\nPlease read these terms carefully before making any bookings.\n1. Booking & Reservation\n•	Eligibility: Users must be at least 18 years of age to book any services via our system.\n•	Booking Process: To make a booking, users must provide accurate details, including full name, contact information, payment details, and any additional requirements (e.g., room preferences, event specifications).\n•	Confirmation: A booking is considered confirmed once you receive an official booking confirmation email or notification. Any reservation made without this confirmation will not be considered valid.\n•	Booking Modifications: You may modify or cancel your booking through the system, provided such changes comply with the cancellation and modification policy.\n2. Payments & Charges\n•	Pricing: All pricing for resort accommodations, hotel rooms, and event venues are displayed clearly on the platform. Prices are subject to change based on seasonality, availability, or promotions.\n•	Payment Methods: We only accept certain payment methods, namely GCash and on-site cash payments. Down payments must be made before the time of booking unless otherwise stated.\n•	Refunds: Our business does not provide refunds for down payment upon cancellation. Users are encouraged to ensure that their booking information, as well as their schedules for their desired booking dates are accurately provided to avoid the need for cancellations.\n3. Check-in & Check-out\n•	Hotel & Resort: Early check-ins or late check-outs are subject to availability and may incur additional charges.\n•	Event Venue: Event venue access will be granted as per the agreed-upon event time. Additional charges may apply for extended event hours.\n4. Limitation of Liability\n•	Hotel/Resort Liability: Our liability for any loss, injury, or damage incurred during a stay or event is limited to the amount paid for the booking. We are not liable for any indirect or consequential damages.\n•	Event Liability: The resort is not responsible for any third-party event organizer’s actions or services. Any complaints regarding event services should be directed to the event organizer.\n5. Privacy & User Data Policy\nWe respect your privacy and are committed to protecting your personal data in compliance with the Data Privacy Act of 2012 (Republic Act No. 10173) and other relevant Philippine data protection laws. By using our platform, you agree to the collection, storage, and use of your data as outlined below.\n•	Types of Data Collected:\no	Personal Information: We collect your name, email address, phone number, and other personal details you provide during booking.\no	Payment Information: Payment details, such as GCash account numbers and billing information, are processed securely through third-party payment gateways.\no	Booking Data: We collect details of your bookings, such as accommodation type, check-in/check-out dates, event preferences, and any additional services requested.\n•	Use of Data:\no	We use your personal and booking information to process and manage your reservations, send booking confirmations, and provide customer support.\no	Payment details are used exclusively for processing payments and are never stored on our servers.\no	We may use your contact information to send promotional offers, newsletters, and updates about our services (you can opt-out at any time).\n•	Data Protection:\no	We implement security measures to protect your personal and payment information during transmission and storage.\no	We comply with the Data Privacy Act of 2012 and other applicable laws in the Philippines to ensure your data is handled with utmost care and confidentiality.\n•	Retention of Data: We retain your data only for as long as necessary to fulfill the purpose for which it was collected, including legal and accounting obligations. If you wish to delete your data, please contact us directly.\n•	Your Rights:\no	Access: You have the right to request a copy of your personal data.\no	Rectification: You can request corrections to any inaccuracies in your personal data.\no	Deletion: You can request the deletion of your personal data, subject to certain legal exceptions.\nFor more information about how we handle your personal data, please refer to our full Privacy Policy available on our website.\n6. Modifications to Terms & Conditions\nWe reserve the right to modify these terms and conditions at any time. Any changes will be effective immediately upon posting on the platform. Users are encouraged to review these terms regularly.\n7. Dispute Resolution\nAny disputes arising from bookings or the use of our Resort Event Management System shall be resolved through binding arbitration in the jurisdiction of San Ildefonso, Bulacan, Philippines. In the event that arbitration is not possible, disputes will be subject to Philippine laws and resolved in the appropriate court.\n8. Governing Law\nThese terms and conditions shall be governed by and construed in accordance with the laws of the Philippines.\n9. Contact Information\nIf you have any questions about these terms and conditions, please contact us at:\n•	Email: mamyresort128@gmail.com\n•	Phone: (0998) 962 4697\n•	Address: Sitio Colonia Gabihan, San Ildefonso, Bulacan\n10. Business Partner Terms\n• Eligibility: Business Partners must be at least 18 years of age to register and offer services via the platform.\n• Registration: Business Partners must complete a registration process and provide accurate business details, including the business name, contact information, services offered, and any additional requirements. Once approved, Business Partners will be granted access to manage and offer their services through the system.\n• Bookings & Reservations:\n•	Customer Interaction: Business Partners can list their services on the platform for customers to book. While Business Partners can view the bookings made for their services, they do not have the ability to approve or reject bookings.\n•	Booking Details: All bookings made through the platform will be reflected on the Business Partner’s page, and any relevant customer details will be provided for them to coordinate and prepare for the service being offered.\n•	Admin Communication: The Admin will contact the Business Partner directly once their services have been booked by a customer. This communication will include the booking details and any necessary information regarding the event or service.\n• Commission & Fees:\n•	Commission Disclosure: The commission rate applicable to Business Partners will be disclosed after the scheduled event between the Admin and the Business Partner. This will allow for transparent and mutually agreed-upon terms following the completion of the event.\n•	Payment Terms: Payments for bookings made through the platform will be processed directly through the platform’s payment system. After the event, the Admin will inform the Business Partner of their commission, and payments will be made according to the agreed-upon schedule, after deducting the commission fee.\n• Liability & Responsibilities:\n•	Service Delivery: Business Partners are fully responsible for delivering the services they offer to customers. They must ensure that services are provided as described, in a timely manner, and meet the standards expected by the customer.\n•	Customer Complaints: Any complaints or disputes regarding the services provided by the Business Partner should be resolved directly between the Business Partner and the customer. The resort is not responsible for the actions or services of Business Partners.\n•	Indemnity: Business Partners agree to indemnify and hold harmless Mamyr Resort from any claims, losses, or damages that arise from their services or the actions of their employees, contractors, or representatives.\n• Booking Modifications & Cancellations:\n•	Business Partners may request modifications or cancellations to bookings if necessary, but such changes will still be subject to the customer’s terms, as well as Mamyr Resort’s cancellation and modification policy.\n•	Business Partners should communicate directly with customers if any changes need to be made to the booking or service.\n• Promotions & Advertising: Business Partners may participate in promotional campaigns, discounts, or special offers on the platform. Any such offers or campaigns will be subject to mutual agreement and will be advertised on the platform.\n• Compliance with Laws: Business Partners are responsible for ensuring that their business and services comply with all applicable laws and regulations, including those related to safety, licensing, and tax obligations. Mamyr Resort is not responsible for the legality of the services offered by Business Partners.', '2025-12-01 23:43:11'),
(152, 1, 'BookNowHotel', 'addPricing', 'If the maximum pax exceeded, extra guest is charged ₱250 per head', '2025-12-08 00:02:38'),
(153, 1, 'BookNowHotel', 'freePricing', 'Children 3 years old and below are free', '2025-12-08 00:02:38'),
(154, 1, 'BookNowHotel', 'addRequest', 'Any request for an additional hour of stay must be arranged directly with the resort.', '2025-12-08 00:02:38'),
(156, 1, 'BookNowEvent', 'dishSelect', 'Basic design for tables, chair & stage4 Dishes (Vegetables included)!w/ Rice & Drink/JuiceDessert', '2025-12-08 01:39:53'),
(157, 1, 'BookNowEvent', 'foodPreference', 'Kindly contact us first to confirm your food preference before adding it in the request field.', '2025-12-08 01:39:53'),
(158, 1, 'BookNowEvent', 'foodInclusions', 'Basic design for tables, chair & stage\n4 Dishes (Vegetables included)\nw/ Rice & Drink/Juice\nDessert', '2025-12-08 01:39:53');

-- --------------------------------------------------------

--
-- Table structure for table `websitecontentimage`
--

CREATE TABLE `websitecontentimage` (
  `WCImageID` int(11) NOT NULL,
  `contentID` int(11) NOT NULL,
  `imageData` varchar(500) DEFAULT NULL,
  `altText` varchar(255) DEFAULT NULL,
  `imageOrder` int(11) DEFAULT 1,
  `uploadedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `websitecontentimage`
--

INSERT INTO `websitecontentimage` (`WCImageID`, `contentID`, `imageData`, `altText`, `imageOrder`, `uploadedAt`) VALUES
(1, 1, 'poolPic2.jpg', 'Welcome Image 1', 1, '2025-10-22 20:43:31'),
(2, 1, 'hotel1.jpg', 'Welcome Image 2', 2, '2025-10-22 20:43:31'),
(3, 1, 'pav1.jpg', 'Welcome Image 3', 3, '2025-10-22 20:43:31'),
(4, 6, 'poolPic2.jpg', 'Book Now 1', 1, '2025-10-22 20:43:31'),
(5, 6, 'cottage4.jpg', 'Book Now 2', 2, '2025-10-22 20:43:31'),
(6, 6, 'hotel2.jpg', 'Book Now 3', 3, '2025-10-22 20:43:31'),
(7, 6, 'miniPav1.jpg', 'Book Now 4', 4, '2025-10-22 20:43:31'),
(8, 6, 'pav3.jpg', 'Book Now 5', 5, '2025-10-22 20:43:31'),
(9, 6, 'parking4.jpg', 'Book Now 5', 6, '2025-10-22 20:43:31'),
(10, 31, 'firstPic.jpg', 'About Image 1', 1, '2025-10-22 20:43:31'),
(11, 34, 'resort.png', 'Resort Logo', 1, '2025-10-22 20:43:31'),
(12, 36, 'events.png', 'Events Logo', 1, '2025-10-22 20:43:31'),
(13, 38, 'hotel.png', 'Hotel Logo', 1, '2025-10-22 20:43:31'),
(14, 41, 'aboutImage.jpg', 'About Image 2', 1, '2025-10-22 20:43:31'),
(15, 43, 'poolPic.jpg', 'About Image 3', 1, '2025-10-22 20:43:31'),
(16, 44, 'poolPic1.png', 'Pool Image 1', 1, '2025-10-22 20:43:31'),
(17, 44, 'poolPic2.jpg', 'Pool Image 2', 2, '2025-10-22 20:43:31'),
(18, 44, 'poolPic3.jpg', 'Pool Image 3', 3, '2025-10-22 20:43:31'),
(19, 44, 'poolPic4.jpeg', 'Pool Image 4', 4, '2025-10-22 20:43:31'),
(20, 44, 'poolPic5.jpg', 'Pool Image 5', 5, '2025-10-22 20:43:31'),
(21, 56, 'parking1.jpg', 'Parking Image 1', 1, '2025-10-22 20:43:31'),
(22, 56, 'parking2.jpg', 'Parking Image 2', 2, '2025-10-22 20:43:31'),
(23, 56, 'parking3.jpg', 'Parking Image 3', 3, '2025-10-22 20:43:31'),
(24, 56, 'parking4.jpg', 'Parking Image 4', 4, '2025-10-22 20:43:31'),
(25, 56, 'parking5.jpg', 'Parking Image 5', 5, '2025-10-22 20:43:31'),
(26, 46, 'cottage1.jpg', 'Cottage Image 1', 1, '2025-10-22 20:43:31'),
(27, 46, 'cottage2.jpg', 'Cottage Image 2', 2, '2025-10-22 20:43:31'),
(28, 46, 'cottage3.jpg', 'Cottage Image 3', 3, '2025-10-22 20:43:31'),
(29, 46, 'cottage4.jpg', 'Cottage Image 4', 4, '2025-10-22 20:43:31'),
(30, 46, 'cottage5.jpg', 'Cottage Image 5', 5, '2025-10-22 20:43:31'),
(31, 48, 'videoke1.jpg', 'Videoke Image 1', 1, '2025-10-22 20:43:31'),
(32, 48, 'videoke2.jpg', 'Videoke Image 2', 2, '2025-10-22 20:43:31'),
(33, 50, 'pav1.jpg', 'Pav Image 1', 1, '2025-10-22 20:43:31'),
(34, 50, 'pav2.jpg', 'Pav Image 2', 2, '2025-10-22 20:43:31'),
(35, 50, 'pav3.jpg', 'Pav Image 3', 3, '2025-10-22 20:43:31'),
(36, 50, 'pav4.jpg', 'Pav Image 4', 4, '2025-10-22 20:43:31'),
(37, 50, 'pav5.jpg', 'Pav Image 5', 5, '2025-10-22 20:43:31'),
(38, 50, 'pav6.jpg', 'Pav Image 6', 6, '2025-10-22 20:43:31'),
(39, 50, 'pav7.jpg', 'Pav Image 7', 7, '2025-10-22 20:43:31'),
(40, 50, 'pav8.jpg', 'Pav Image 8', 8, '2025-10-22 20:43:31'),
(41, 52, 'miniPav1.jpg', 'Mini Pav Image 1', 1, '2025-10-22 20:43:31'),
(42, 52, 'miniPav2.jpg', 'Mini Pav Image 2', 2, '2025-10-22 20:43:31'),
(43, 52, 'miniPav3.jpeg', 'Mini Pav Image 3', 3, '2025-10-22 20:43:31'),
(44, 52, 'miniPav4.jpeg', 'Mini Pav Image 4', 4, '2025-10-22 20:43:31'),
(45, 52, 'miniPav5.jpeg', 'Mini Pav Image 5', 5, '2025-10-22 20:43:31'),
(46, 54, 'hotel1.jpg', 'Hotel Pic 1', 1, '2025-10-22 20:43:31'),
(47, 54, 'hotel2.jpg', 'Hotel Pic 2', 2, '2025-10-22 20:43:31'),
(48, 54, 'hotel3.jpg', 'Hotel Pic 3', 3, '2025-10-22 20:43:31'),
(49, 54, 'hotel4.jpg', 'Hotel Pic 4', 4, '2025-10-22 20:43:31'),
(50, 54, 'hotel5.jpeg', 'Hotel Pic 5', 5, '2025-10-22 20:43:31'),
(51, 17, 'image.png', 'Blog Image 1', 1, '2025-10-22 20:43:31'),
(52, 21, 'img2.png', 'Blog Image 2', 1, '2025-10-22 20:43:31'),
(53, 25, 'img3.png', 'Blog Image 3', 1, '2025-10-22 20:43:31'),
(54, 29, 'img4.png', 'Blog Image 2', 1, '2025-10-22 20:43:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `additionalcharge`
--
ALTER TABLE `additionalcharge`
  ADD PRIMARY KEY (`additionalChargeID`),
  ADD KEY `bookingID` (`bookingID`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`),
  ADD UNIQUE KEY `userID` (`userID`);

--
-- Indexes for table `auditlog`
--
ALTER TABLE `auditlog`
  ADD PRIMARY KEY (`logID`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`bookingID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `bookingStatus` (`bookingStatus`),
  ADD KEY `customPackageID` (`customPackageID`);

--
-- Indexes for table `bookingservice`
--
ALTER TABLE `bookingservice`
  ADD PRIMARY KEY (`bookingServiceID`),
  ADD KEY `bookingID` (`bookingID`),
  ADD KEY `serviceID` (`serviceID`);

--
-- Indexes for table `booking_cancellation`
--
ALTER TABLE `booking_cancellation`
  ADD PRIMARY KEY (`cancellationID`),
  ADD KEY `bookingID` (`bookingID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `reasonID` (`reasonID`);

--
-- Indexes for table `booking_rejection`
--
ALTER TABLE `booking_rejection`
  ADD PRIMARY KEY (`rejectionID`),
  ADD KEY `bookingID` (`bookingID`),
  ADD KEY `adminID` (`adminID`),
  ADD KEY `reasonID` (`reasonID`);

--
-- Indexes for table `businesspartneravailedservice`
--
ALTER TABLE `businesspartneravailedservice`
  ADD PRIMARY KEY (`BPavailedService`),
  ADD KEY `partnershipServiceID` (`partnershipServiceID`),
  ADD KEY `bookingID` (`bookingID`),
  ADD KEY `approvalStatus` (`approvalStatus`);

--
-- Indexes for table `confirmedbooking`
--
ALTER TABLE `confirmedbooking`
  ADD PRIMARY KEY (`confirmedBookingID`),
  ADD KEY `bookingID` (`bookingID`),
  ADD KEY `paymentApprovalStatus` (`paymentApprovalStatus`),
  ADD KEY `confirmedbooking_ibfk_3` (`paymentStatus`),
  ADD KEY `confirmedbooking_ibfk_4` (`approvedBy`);

--
-- Indexes for table `custompackage`
--
ALTER TABLE `custompackage`
  ADD PRIMARY KEY (`customPackageID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `foodPricingPerHeadID` (`foodPricingPerHeadID`),
  ADD KEY `fk_eventTypeID` (`eventTypeID`);

--
-- Indexes for table `custompackageitem`
--
ALTER TABLE `custompackageitem`
  ADD PRIMARY KEY (`customPackageItemID`),
  ADD KEY `customPackageID` (`customPackageID`),
  ADD KEY `serviceID` (`serviceID`),
  ADD KEY `foodItemID` (`foodItemID`);

--
-- Indexes for table `entrancerate`
--
ALTER TABLE `entrancerate`
  ADD PRIMARY KEY (`entranceRateID`),
  ADD KEY `timeRangeID` (`timeRangeID`);

--
-- Indexes for table `entrancetimerange`
--
ALTER TABLE `entrancetimerange`
  ADD PRIMARY KEY (`timeRangeID`);

--
-- Indexes for table `eventcategory`
--
ALTER TABLE `eventcategory`
  ADD PRIMARY KEY (`categoryID`);

--
-- Indexes for table `menuitem`
--
ALTER TABLE `menuitem`
  ADD PRIMARY KEY (`foodItemID`),
  ADD KEY `availabilityID` (`availabilityID`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notificationID`),
  ADD KEY `bookingID` (`bookingID`),
  ADD KEY `partnershipID` (`partnershipID`),
  ADD KEY `senderID` (`senderID`),
  ADD KEY `notification_ibfk_receiver` (`receiverID`);

--
-- Indexes for table `partnership`
--
ALTER TABLE `partnership`
  ADD PRIMARY KEY (`partnershipID`),
  ADD UNIQUE KEY `businessEmail` (`businessEmail`),
  ADD KEY `userID` (`userID`),
  ADD KEY `partnerStatusID` (`partnerStatusID`);

--
-- Indexes for table `partnershipservice`
--
ALTER TABLE `partnershipservice`
  ADD PRIMARY KEY (`partnershipServiceID`),
  ADD KEY `partnershipID` (`partnershipID`),
  ADD KEY `PSAvailabilityID` (`PSAvailabilityID`),
  ADD KEY `partnerTypeID` (`partnerTypeID`);

--
-- Indexes for table `partnershiptype`
--
ALTER TABLE `partnershiptype`
  ADD PRIMARY KEY (`partnerTypeID`),
  ADD UNIQUE KEY `partnerType` (`partnerType`);

--
-- Indexes for table `partnership_partnertype`
--
ALTER TABLE `partnership_partnertype`
  ADD PRIMARY KEY (`pptID`),
  ADD KEY `partnerTypeID` (`partnerTypeID`);

--
-- Indexes for table `partnerstatus`
--
ALTER TABLE `partnerstatus`
  ADD PRIMARY KEY (`partnerStatusID`),
  ADD UNIQUE KEY `statusName` (`statusName`);

--
-- Indexes for table `partner_rejection`
--
ALTER TABLE `partner_rejection`
  ADD PRIMARY KEY (`rejectionID`),
  ADD KEY `partnershipID` (`partnershipID`),
  ADD KEY `adminID` (`adminID`),
  ADD KEY `reasonID` (`reasonID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `confirmedBookingID` (`confirmedBookingID`);

--
-- Indexes for table `paymentstatus`
--
ALTER TABLE `paymentstatus`
  ADD PRIMARY KEY (`paymentStatusID`),
  ADD UNIQUE KEY `statusName` (`statusName`);

--
-- Indexes for table `reason`
--
ALTER TABLE `reason`
  ADD PRIMARY KEY (`reasonID`);

--
-- Indexes for table `resortamenity`
--
ALTER TABLE `resortamenity`
  ADD PRIMARY KEY (`resortServiceID`),
  ADD KEY `RSAvailabilityID` (`RSAvailabilityID`),
  ADD KEY `RScategoryID` (`RScategoryID`);

--
-- Indexes for table `resortinfo`
--
ALTER TABLE `resortinfo`
  ADD PRIMARY KEY (`resortInfoID`);

--
-- Indexes for table `resortservicescategory`
--
ALTER TABLE `resortservicescategory`
  ADD PRIMARY KEY (`categoryID`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`serviceID`),
  ADD UNIQUE KEY `resortServiceID` (`resortServiceID`),
  ADD UNIQUE KEY `partnershipServiceID` (`partnershipServiceID`),
  ADD KEY `entranceRateID` (`entranceRateID`);

--
-- Indexes for table `serviceavailability`
--
ALTER TABLE `serviceavailability`
  ADD PRIMARY KEY (`availabilityID`),
  ADD UNIQUE KEY `availabilityName` (`availabilityName`);

--
-- Indexes for table `servicepricing`
--
ALTER TABLE `servicepricing`
  ADD PRIMARY KEY (`pricingID`);

--
-- Indexes for table `serviceunavailabledate`
--
ALTER TABLE `serviceunavailabledate`
  ADD PRIMARY KEY (`serviceUnavailableID`),
  ADD KEY `resortServiceID` (`resortServiceID`),
  ADD KEY `partnershipServiceID` (`partnershipServiceID`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`statusID`),
  ADD UNIQUE KEY `statusName` (`statusName`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `userRole` (`userRole`),
  ADD KEY `userStatusID` (`userStatusID`);

--
-- Indexes for table `userreview`
--
ALTER TABLE `userreview`
  ADD PRIMARY KEY (`userReviewID`),
  ADD KEY `bookingID` (`bookingID`);

--
-- Indexes for table `userstatus`
--
ALTER TABLE `userstatus`
  ADD PRIMARY KEY (`userStatusID`),
  ADD UNIQUE KEY `statusName` (`statusName`);

--
-- Indexes for table `usertype`
--
ALTER TABLE `usertype`
  ADD PRIMARY KEY (`userTypeID`),
  ADD UNIQUE KEY `typeName` (`typeName`);

--
-- Indexes for table `walkin_sales_summary`
--
ALTER TABLE `walkin_sales_summary`
  ADD PRIMARY KEY (`salesID`),
  ADD KEY `walkin_sales_summary_ibfk_1` (`createdBy`);

--
-- Indexes for table `websitecontent`
--
ALTER TABLE `websitecontent`
  ADD PRIMARY KEY (`contentID`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `websitecontentimage`
--
ALTER TABLE `websitecontentimage`
  ADD PRIMARY KEY (`WCImageID`),
  ADD KEY `contentID` (`contentID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `additionalcharge`
--
ALTER TABLE `additionalcharge`
  MODIFY `additionalChargeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auditlog`
--
ALTER TABLE `auditlog`
  MODIFY `logID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `bookingID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookingservice`
--
ALTER TABLE `bookingservice`
  MODIFY `bookingServiceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_cancellation`
--
ALTER TABLE `booking_cancellation`
  MODIFY `cancellationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_rejection`
--
ALTER TABLE `booking_rejection`
  MODIFY `rejectionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `businesspartneravailedservice`
--
ALTER TABLE `businesspartneravailedservice`
  MODIFY `BPavailedService` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `confirmedbooking`
--
ALTER TABLE `confirmedbooking`
  MODIFY `confirmedBookingID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custompackage`
--
ALTER TABLE `custompackage`
  MODIFY `customPackageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custompackageitem`
--
ALTER TABLE `custompackageitem`
  MODIFY `customPackageItemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entrancerate`
--
ALTER TABLE `entrancerate`
  MODIFY `entranceRateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `entrancetimerange`
--
ALTER TABLE `entrancetimerange`
  MODIFY `timeRangeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `eventcategory`
--
ALTER TABLE `eventcategory`
  MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `menuitem`
--
ALTER TABLE `menuitem`
  MODIFY `foodItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notificationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `partnership`
--
ALTER TABLE `partnership`
  MODIFY `partnershipID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `partnershipservice`
--
ALTER TABLE `partnershipservice`
  MODIFY `partnershipServiceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `partnershiptype`
--
ALTER TABLE `partnershiptype`
  MODIFY `partnerTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `partnership_partnertype`
--
ALTER TABLE `partnership_partnertype`
  MODIFY `pptID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `partnerstatus`
--
ALTER TABLE `partnerstatus`
  MODIFY `partnerStatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `partner_rejection`
--
ALTER TABLE `partner_rejection`
  MODIFY `rejectionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paymentstatus`
--
ALTER TABLE `paymentstatus`
  MODIFY `paymentStatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reason`
--
ALTER TABLE `reason`
  MODIFY `reasonID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `resortamenity`
--
ALTER TABLE `resortamenity`
  MODIFY `resortServiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `resortinfo`
--
ALTER TABLE `resortinfo`
  MODIFY `resortInfoID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `resortservicescategory`
--
ALTER TABLE `resortservicescategory`
  MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `serviceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `serviceavailability`
--
ALTER TABLE `serviceavailability`
  MODIFY `availabilityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `servicepricing`
--
ALTER TABLE `servicepricing`
  MODIFY `pricingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `serviceunavailabledate`
--
ALTER TABLE `serviceunavailabledate`
  MODIFY `serviceUnavailableID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `statusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `userreview`
--
ALTER TABLE `userreview`
  MODIFY `userReviewID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `userstatus`
--
ALTER TABLE `userstatus`
  MODIFY `userStatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `usertype`
--
ALTER TABLE `usertype`
  MODIFY `userTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `walkin_sales_summary`
--
ALTER TABLE `walkin_sales_summary`
  MODIFY `salesID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `websitecontent`
--
ALTER TABLE `websitecontent`
  MODIFY `contentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `websitecontentimage`
--
ALTER TABLE `websitecontentimage`
  MODIFY `WCImageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `additionalcharge`
--
ALTER TABLE `additionalcharge`
  ADD CONSTRAINT `additionalcharge_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`);

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `auditlog`
--
ALTER TABLE `auditlog`
  ADD CONSTRAINT `auditlog_ibfk_1` FOREIGN KEY (`adminID`) REFERENCES `admin` (`adminID`);

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`bookingStatus`) REFERENCES `status` (`statusID`),
  ADD CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`customPackageID`) REFERENCES `custompackage` (`customPackageID`);

--
-- Constraints for table `bookingservice`
--
ALTER TABLE `bookingservice`
  ADD CONSTRAINT `bookingservice_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`),
  ADD CONSTRAINT `bookingservice_ibfk_2` FOREIGN KEY (`serviceID`) REFERENCES `service` (`serviceID`);

--
-- Constraints for table `booking_cancellation`
--
ALTER TABLE `booking_cancellation`
  ADD CONSTRAINT `booking_cancellation_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`),
  ADD CONSTRAINT `booking_cancellation_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`),
  ADD CONSTRAINT `booking_cancellation_ibfk_3` FOREIGN KEY (`reasonID`) REFERENCES `reason` (`reasonID`);

--
-- Constraints for table `booking_rejection`
--
ALTER TABLE `booking_rejection`
  ADD CONSTRAINT `booking_rejection_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`),
  ADD CONSTRAINT `booking_rejection_ibfk_2` FOREIGN KEY (`adminID`) REFERENCES `admin` (`adminID`),
  ADD CONSTRAINT `booking_rejection_ibfk_3` FOREIGN KEY (`reasonID`) REFERENCES `reason` (`reasonID`);

--
-- Constraints for table `businesspartneravailedservice`
--
ALTER TABLE `businesspartneravailedservice`
  ADD CONSTRAINT `businesspartneravailedservice_ibfk_1` FOREIGN KEY (`partnershipServiceID`) REFERENCES `partnershipservice` (`partnershipServiceID`),
  ADD CONSTRAINT `businesspartneravailedservice_ibfk_2` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`),
  ADD CONSTRAINT `businesspartneravailedservice_ibfk_3` FOREIGN KEY (`approvalStatus`) REFERENCES `status` (`statusID`);

--
-- Constraints for table `confirmedbooking`
--
ALTER TABLE `confirmedbooking`
  ADD CONSTRAINT `confirmedbooking_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`),
  ADD CONSTRAINT `confirmedbooking_ibfk_2` FOREIGN KEY (`paymentApprovalStatus`) REFERENCES `status` (`statusID`),
  ADD CONSTRAINT `confirmedbooking_ibfk_3` FOREIGN KEY (`paymentStatus`) REFERENCES `paymentstatus` (`paymentStatusID`),
  ADD CONSTRAINT `confirmedbooking_ibfk_4` FOREIGN KEY (`approvedBy`) REFERENCES `admin` (`adminID`);

--
-- Constraints for table `custompackage`
--
ALTER TABLE `custompackage`
  ADD CONSTRAINT `custompackage_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`),
  ADD CONSTRAINT `custompackage_ibfk_2` FOREIGN KEY (`foodPricingPerHeadID`) REFERENCES `servicepricing` (`pricingID`),
  ADD CONSTRAINT `fk_eventTypeID` FOREIGN KEY (`eventTypeID`) REFERENCES `eventcategory` (`categoryID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `custompackageitem`
--
ALTER TABLE `custompackageitem`
  ADD CONSTRAINT `custompackageitem_ibfk_1` FOREIGN KEY (`customPackageID`) REFERENCES `custompackage` (`customPackageID`),
  ADD CONSTRAINT `custompackageitem_ibfk_2` FOREIGN KEY (`serviceID`) REFERENCES `service` (`serviceID`),
  ADD CONSTRAINT `custompackageitem_ibfk_3` FOREIGN KEY (`foodItemID`) REFERENCES `menuitem` (`foodItemID`);

--
-- Constraints for table `entrancerate`
--
ALTER TABLE `entrancerate`
  ADD CONSTRAINT `entrancerate_ibfk_1` FOREIGN KEY (`timeRangeID`) REFERENCES `entrancetimerange` (`timeRangeID`);

--
-- Constraints for table `menuitem`
--
ALTER TABLE `menuitem`
  ADD CONSTRAINT `menuitem_ibfk_1` FOREIGN KEY (`availabilityID`) REFERENCES `serviceavailability` (`availabilityID`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`),
  ADD CONSTRAINT `notification_ibfk_2` FOREIGN KEY (`partnershipID`) REFERENCES `partnership` (`partnershipID`),
  ADD CONSTRAINT `notification_ibfk_3` FOREIGN KEY (`senderID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `partnership`
--
ALTER TABLE `partnership`
  ADD CONSTRAINT `partnership_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`),
  ADD CONSTRAINT `partnership_ibfk_2` FOREIGN KEY (`partnerStatusID`) REFERENCES `partnerstatus` (`partnerStatusID`);

--
-- Constraints for table `partnershipservice`
--
ALTER TABLE `partnershipservice`
  ADD CONSTRAINT `partnershipservice_ibfk_1` FOREIGN KEY (`partnershipID`) REFERENCES `partnership` (`partnershipID`),
  ADD CONSTRAINT `partnershipservice_ibfk_2` FOREIGN KEY (`PSAvailabilityID`) REFERENCES `serviceavailability` (`availabilityID`),
  ADD CONSTRAINT `partnershipservice_ibfk_3` FOREIGN KEY (`partnerTypeID`) REFERENCES `partnership_partnertype` (`pptID`);

--
-- Constraints for table `partnership_partnertype`
--
ALTER TABLE `partnership_partnertype`
  ADD CONSTRAINT `partnership_partnertype_ibfk_1` FOREIGN KEY (`partnershipID`) REFERENCES `partnership` (`partnershipID`) ON DELETE CASCADE,
  ADD CONSTRAINT `partnership_partnertype_ibfk_2` FOREIGN KEY (`partnerTypeID`) REFERENCES `partnershiptype` (`partnerTypeID`);

--
-- Constraints for table `partner_rejection`
--
ALTER TABLE `partner_rejection`
  ADD CONSTRAINT `partner_rejection_ibfk_1` FOREIGN KEY (`partnershipID`) REFERENCES `partnership` (`partnershipID`),
  ADD CONSTRAINT `partner_rejection_ibfk_2` FOREIGN KEY (`adminID`) REFERENCES `admin` (`adminID`),
  ADD CONSTRAINT `partner_rejection_ibfk_3` FOREIGN KEY (`reasonID`) REFERENCES `reason` (`reasonID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`confirmedBookingID`) REFERENCES `confirmedbooking` (`confirmedBookingID`);

--
-- Constraints for table `resortamenity`
--
ALTER TABLE `resortamenity`
  ADD CONSTRAINT `resortamenity_ibfk_1` FOREIGN KEY (`RSAvailabilityID`) REFERENCES `serviceavailability` (`availabilityID`),
  ADD CONSTRAINT `resortamenity_ibfk_2` FOREIGN KEY (`RScategoryID`) REFERENCES `resortservicescategory` (`categoryID`);

--
-- Constraints for table `service`
--
ALTER TABLE `service`
  ADD CONSTRAINT `service_ibfk_1` FOREIGN KEY (`partnershipServiceID`) REFERENCES `partnershipservice` (`partnershipServiceID`),
  ADD CONSTRAINT `service_ibfk_2` FOREIGN KEY (`resortServiceID`) REFERENCES `resortamenity` (`resortServiceID`),
  ADD CONSTRAINT `service_ibfk_3` FOREIGN KEY (`entranceRateID`) REFERENCES `entrancerate` (`entranceRateID`);

--
-- Constraints for table `serviceunavailabledate`
--
ALTER TABLE `serviceunavailabledate`
  ADD CONSTRAINT `serviceunavailabledate_ibfk_1` FOREIGN KEY (`resortServiceID`) REFERENCES `resortamenity` (`resortServiceID`),
  ADD CONSTRAINT `serviceunavailabledate_ibfk_2` FOREIGN KEY (`partnershipServiceID`) REFERENCES `partnershipservice` (`partnershipServiceID`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`userRole`) REFERENCES `usertype` (`userTypeID`),
  ADD CONSTRAINT `user_ibfk_2` FOREIGN KEY (`userStatusID`) REFERENCES `userstatus` (`userStatusID`);

--
-- Constraints for table `userreview`
--
ALTER TABLE `userreview`
  ADD CONSTRAINT `userreview_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`);

--
-- Constraints for table `walkin_sales_summary`
--
ALTER TABLE `walkin_sales_summary`
  ADD CONSTRAINT `walkin_sales_summary_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `admin` (`adminID`);

--
-- Constraints for table `websitecontent`
--
ALTER TABLE `websitecontent`
  ADD CONSTRAINT `websitecontent_ibfk_1` FOREIGN KEY (`adminID`) REFERENCES `admin` (`adminID`);

--
-- Constraints for table `websitecontentimage`
--
ALTER TABLE `websitecontentimage`
  ADD CONSTRAINT `websitecontentimage_ibfk_1` FOREIGN KEY (`contentID`) REFERENCES `websitecontent` (`contentID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
