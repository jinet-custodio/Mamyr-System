<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../Config/dbcon.php';

if (isset($_POST['addBulkBooking'])) {
    $dateRange = mysqli_real_escape_string($conn,  $_POST['repPeriod']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $totalBookings = intval($_POST['totalBooking']);
    $totalSales = floatval($_POST['totalSales']);

    list($firstDate, $lastDate) = explode(" to ", $dateRange);

    $startDateObj = new DateTime($firstDate);
    $endDateObj = new DateTime($lastDate);

    $startDate = $startDateObj->format('Y-m-d');
    $endDate = $endDateObj->format('Y-m-d');

    error_log(print_r($_POST, true));
    error_log($startDate);
    error_log($endDate);
    $_SESSION['createBookingForm'] = $_POST;
    if (empty($dateRange) && empty($bookingType) && empty($totalBookings) && empty($totalSales)) {
        header('Location: ../../Pages/Admin/createBooking.php?action=fieldAllRequiredInfo');
        exit();
    }

    try {
        $insertSalesQuery = $conn->prepare("INSERT INTO `walkin_sales_summary`(`startDate`, `endDate`, `bookingType`, `bookingCount`, `salesAmount`) VALUES (?,?,?,?,?)");
        $insertSalesQuery->bind_param('sssid', $startDate, $endDate, $bookingType, $totalBookings, $totalSales);

        if (!$insertSalesQuery->execute()) {
            throw new Exception('Error executing the insertion of booking with sales query');
        }

        unset($_SESSION['createBookingForm']);
        header('Location: ../../Pages/Admin/createBooking.php?action=added-success');
        exit();
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
        header('Location: ../../Pages/Admin/createBooking.php?action=error');
        exit();
    }
}
