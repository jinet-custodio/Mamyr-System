<?php

ini_set('display_errors', 0); // Don't display errors to browser
ini_set('display_startup_errors', 0);
error_reporting(0);

require '../../Config/dbcon.php';

// Optional: Set the correct content type
header('Content-Type: application/json');


if (isset($_GET['selectedFilter'])) {

    $selectedFilterValue = $_GET['selectedFilter'];

    switch ($selectedFilterValue) {
        case 'month':
            $filter = 'month';
            $sql = "SELECT 
                        MONTHNAME(b.startDate) AS month,
                        CONCAT('Week ', CEIL(DAY(b.startDate) / 7)) AS weekOfMonth,
                        SUM(
                            CASE 
                                WHEN bpas.bookingID IS NULL THEN cb.finalBill
                                ELSE cb.finalBill - bpas.price
                            END
                        ) AS totalSalesThisWeek
                    FROM 
                        confirmedbooking cb
                    LEFT JOIN 
                        booking b ON cb.bookingID = b.bookingID
                    LEFT JOIN 
                        businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID
                    WHERE 
                        cb.paymentStatus IN (?, ?) AND cb.paymentApprovalStatus IN (?, ?) AND
                        MONTH(b.startDate) = MONTH(CURDATE()) 
                        AND YEAR(b.startDate) = YEAR(CURDATE())
                    GROUP BY 
                        CEIL(DAY(b.startDate) / 7)
                    ORDER BY 
                        CEIL(DAY(b.startDate) / 7)
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
                        CONCAT(
                            DATE_FORMAT(DATE(b.startDate - INTERVAL (DAY(b.startDate) - 1) % 7 DAY), '%b %e'), 
                            ' - ',
                            DATE_FORMAT(DATE(b.startDate - INTERVAL (DAY(b.startDate) - 1) % 7 DAY + INTERVAL 6 DAY), '%b %e'),
                            ' (Mon - Sun)'
                        ) AS weekLabel,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 0 THEN CASE WHEN bpas.bookingID IS NULL THEN cb.finalBill ELSE cb.finalBill - bpas.price END  ELSE 0 END) AS Mon,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 1 THEN CASE WHEN bpas.bookingID IS NULL THEN cb.finalBill ELSE cb.finalBill - bpas.price END  ELSE 0 END) AS Tue,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 2 THEN CASE WHEN bpas.bookingID IS NULL THEN cb.finalBill ELSE cb.finalBill - bpas.price END  ELSE 0 END) AS Wed,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 3 THEN CASE WHEN bpas.bookingID IS NULL THEN cb.finalBill ELSE cb.finalBill - bpas.price END  ELSE 0 END) AS Thu,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 4 THEN CASE WHEN bpas.bookingID IS NULL THEN cb.finalBill ELSE cb.finalBill - bpas.price END  ELSE 0 END) AS Fri,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 5 THEN CASE WHEN bpas.bookingID IS NULL THEN cb.finalBill ELSE cb.finalBill - bpas.price END  ELSE 0 END) AS Sat,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 6 THEN CASE WHEN bpas.bookingID IS NULL THEN cb.finalBill ELSE cb.finalBill - bpas.price END  ELSE 0 END) AS Sun
                    FROM 
                        confirmedbooking cb
                    LEFT JOIN 
                        booking b ON cb.bookingID = b.bookingID
                    LEFT JOIN 
                        businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID
                    WHERE 
                        cb.paymentStatus IN (?, ?) AND cb.paymentApprovalStatus IN (?, ?) AND
                        MONTH(b.startDate) = MONTH(CURDATE()) 
                        AND YEAR(b.startDate) = YEAR(CURDATE())
                        AND FLOOR((DAY(b.startDate) - 1) / 7) = ?
                    GROUP BY 
                        YEAR(b.startDate), MONTH(b.startDate), FLOOR((DAY(b.startDate) - 1) / 7)
                    ORDER BY 
                        MIN(b.startDate)
                    ";
            break;
    }


    $partiallyPaid = 2;
    $fullyPaid = 3;
    $approvedStatus = 2;
    $doneStatus = 5;
    $getSalesFiltered = $conn->prepare($sql);
    if (!$getSalesFiltered) {
        error_log("Prepare failed: " . $conn->error);
    }
    if ($filter === 'week') {
        $getSalesFiltered->bind_param('iiiii',  $partiallyPaid, $fullyPaid, $approvedStatus, $doneStatus, $weekNumber);
    } elseif ($filter === 'month') {
        $getSalesFiltered->bind_param('iiii',  $partiallyPaid, $fullyPaid, $approvedStatus, $doneStatus);
    }

    if (!$getSalesFiltered->execute()) {
        echo json_encode([
            'success' => false,
            'message' => 'An error occured',
            'sales' => []
        ]);
        exit;
    }

    $salesResult = $getSalesFiltered->get_result();

    if ($salesResult->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'No Data',
            'sales' => []
        ]);
        // error_log(print_r($sales, true));
        exit;
    }

    $sales = [];

    while ($row = $salesResult->fetch_assoc()) {
        $sales[] = $row;
    }
    // error_log(print_r($sales, true));

    echo json_encode([
        'success' => true,
        'sales' => $sales
    ]);
}
