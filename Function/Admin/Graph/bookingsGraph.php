<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require '../../../Config/dbcon.php';
header('Content-Type: application/json');

if (isset($_GET['selectedFilter'])) {

    $selectedFilterValue = $_GET['selectedFilter'];

    switch ($selectedFilterValue) {
        case 'month':
            $filter = 'month';
            $sql = "SELECT 
                        b.bookingType,
                        MONTHNAME(b.startDate) AS month,
                        CONCAT('Week ', 
                            WEEK(b.startDate, 3) - WEEK(DATE_SUB(b.startDate, INTERVAL DAY(b.startDate)-1 DAY), 3) + 1
                        ) AS weekOfMonth,
                        COUNT(b.bookingID) AS totalBookingThisMonth
                    FROM 
                        confirmedbooking cb
                    LEFT JOIN 
                        booking b ON cb.bookingID = b.bookingID
                    LEFT JOIN 
                        businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID
                    -- LEFT JOIN 
                    --     payment p ON cb.confirmedBookingID = p.confirmedBookingID
                    WHERE 
                        cb.paymentStatus NOT IN (?) AND b.bookingStatus IN (?, ?, ?)  AND
                        MONTH(b.startDate) = MONTH(CURDATE()) 
                        AND YEAR(b.startDate) = YEAR(CURDATE())
                    GROUP BY 
                        b.bookingType, weekOfMonth
                    ORDER BY 
                        b.bookingType, weekOfMonth
            ";
            break;
        case 'w1':
        case 'w2':
        case 'w3':
        case 'w4':
        case 'w5':
            $filter = 'week';
            $weekNumber = intval(substr($selectedFilterValue, 1)) - 1;
            $sql = "SELECT 
                        b.bookingType,
                        CONCAT(
                            DATE_FORMAT(DATE(b.startDate - INTERVAL (DAY(b.startDate) - 1) % 7 DAY), '%b %e'), 
                            ' - ',
                            DATE_FORMAT(DATE(b.startDate - INTERVAL (DAY(b.startDate) - 1) % 7 DAY + INTERVAL 6 DAY), '%b %e'),
                            ' (Mon - Sun)'
                        ) AS weekLabel,
                        COUNT(CASE WHEN WEEKDAY(b.startDate) = 0 THEN b.bookingID END) AS Mon,
                        COUNT(CASE WHEN WEEKDAY(b.startDate) = 1 THEN b.bookingID END) AS Tue,
                        COUNT(CASE WHEN WEEKDAY(b.startDate) = 2 THEN b.bookingID END) AS Wed,
                        COUNT(CASE WHEN WEEKDAY(b.startDate) = 3 THEN b.bookingID END) AS Thu,
                        COUNT(CASE WHEN WEEKDAY(b.startDate) = 4 THEN b.bookingID END) AS Fri,
                        COUNT(CASE WHEN WEEKDAY(b.startDate) = 5 THEN b.bookingID END) AS Sat,
                        COUNT(CASE WHEN WEEKDAY(b.startDate) = 6 THEN b.bookingID END) AS Sun
                    FROM 
                        confirmedbooking cb
                    LEFT JOIN 
                        booking b ON cb.bookingID = b.bookingID
                    LEFT JOIN 
                        businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID
                    WHERE 
                        cb.paymentStatus NOT IN (?) AND b.bookingStatus IN (?, ?, ?)  AND
                        MONTH(b.startDate) = MONTH(CURDATE()) 
                        AND YEAR(b.startDate) = YEAR(CURDATE())
                        AND FLOOR((DAY(b.startDate) - 1) / 7) = ?
                    GROUP BY 
                        b.bookingType, YEAR(b.startDate), MONTH(b.startDate), FLOOR((DAY(b.startDate) - 1) / 7)
                    ORDER BY 
                        b.bookingType, MIN(b.startDate)
                    ";
            break;
    }


    $paymentIssue = 4;
    $approvedStatus = 2;
    $doneStatus = 6;
    $reservedID = 3;
    $getBookingsFiltered = $conn->prepare($sql);
    if (!$getBookingsFiltered) {
        error_log("Prepare failed: " . $conn->error);
    }
    // error_log("Executing query with weekNumber = $weekNumber * selectedValue = $selectedFilterValue");
    if ($filter === 'week') {
        $getBookingsFiltered->bind_param('iiiii', $paymentIssue, $approvedStatus, $doneStatus, $reservedID,  $weekNumber);
    } elseif ($filter === 'month') {
        $getBookingsFiltered->bind_param('iiii', $paymentIssue, $approvedStatus, $doneStatus, $reservedID);
    }

    // if ($filter === 'week') {
    //     $getBookingsFiltered->bind_param('i', $weekNumber);
    // }

    if (!$getBookingsFiltered->execute()) {
        error_log("Error: " . $getBookingsFiltered->error);
        echo json_encode([
            'success' => false,
            'message' => 'An error occured',
            'bookings' => []
        ]);
        exit;
    }

    $bookingsResult = $getBookingsFiltered->get_result();

    if ($bookingsResult->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'No Data',
            'bookings' => []
        ]);
        // error_log(print_r($bookings, true));
        exit;
    }

    $bookings = [];

    while ($row = $bookingsResult->fetch_assoc()) {
        $bookings[] = $row;
    }
    // error_log(print_r($bookings, true));

    echo json_encode([
        'success' => true,
        'bookings' => $bookings
    ]);
}
