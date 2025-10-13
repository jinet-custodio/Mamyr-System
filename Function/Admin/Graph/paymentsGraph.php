<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../../Config/dbcon.php';

header('Content-Type: application/json');

if (isset($_GET['selectedFiltered'])) {

    $selectedFilteredValue =  $_GET['selectedFiltered'];

    switch ($selectedFilteredValue) {
        case 'month':
            $filter = 'month';
            $sql = "SELECT
                    CASE 
                        WHEN (p.paymentStatus IS NULL) THEN 
                            IF(ps.statusName IS NULL, 'Unpaid', ps.statusName)
                        ELSE 
                            ps.statusName 
                    END AS paymentStatus,
                    MONTHNAME(b.startDate) as month,
                    CONCAT('week ', 
                        WEEK(b.startDate, 3) - WEEK(DATE_SUB(b.startDate, INTERVAL DAY(b.startDate)-1 DAY ), 3) + 1
                        ) AS weekOfTheMonth,
                    COUNT(b.bookingID)  AS paymentsThisMonth
                    FROM `confirmedbooking` cb 
                    LEFT JOIN booking b ON cb.bookingID = b.bookingID
                    LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                    LEFT JOIN paymentstatus ps ON p.paymentStatus = ps.paymentStatusID
                    WHERE 
                        -- b.bookingStatus IN (?,?) AND p.paymentStatus IN (?, ?) AND cb.paymentApprovalStatus = ? AND
                        MONTH(b.startDate) = MONTH(CURDATE()) 
                        AND YEAR(b.startDate) = YEAR(CURDATE()) 
                    GROUP BY
                        paymentStatus,  weekOfTheMonth
                    ORDER BY
                        paymentStatus, weekOfTheMonth";
            break;
        case 'w1':
        case 'w2':
        case 'w3':
        case 'w4':
        case 'w5':
            $filter = 'week';
            $weekNumber = substr($selectedFilteredValue, 1) - 1;
            $sql = "SELECT 
                    -- b.bookingType,
                    CASE 
                        WHEN (p.paymentStatus IS NULL) THEN 
                            IF(ps.statusName IS NULL, 'Unpaid', ps.statusName)
                        ELSE 
                            ps.statusName
                    END AS paymentStatus,
                    CONCAT(
                        DATE_FORMAT(DATE(b.startDate - INTERVAL (DAY(b.startDate) - 1) % 7 DAY), '%b %e'),
                        ' — ',
                        DATE_FORMAT(DATE(b.startDate - INTERVAL (DAY(b.startDate) - 1) % 7 DAY + INTERVAL 6 DAY), '%b %e'),
                        '(Mon - Sun)'
                    ) AS weekLabels,
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
                    LEFT JOIN 
                        payment p ON cb.confirmedBookingID = p.confirmedBookingID
                    LEFT JOIN 
                        paymentstatus ps ON p.paymentStatus = ps.paymentStatusID
                    WHERE 
                        -- b.bookingStatus IN (?,?) AND p.paymentStatus IN (?, ?) AND cb.paymentApprovalStatus = ? AND
                        MONTH(b.startDate) = MONTH(CURDATE()) 
                        AND YEAR(b.startDate) = YEAR(CURDATE())
                        AND FLOOR((DAY(b.startDate) - 1) / 7) = ?
                    GROUP BY 
                        paymentStatus, YEAR(b.startDate), MONTH(b.startDate), FLOOR((DAY(b.startDate) - 1) / 7)
                    ORDER BY 
                        paymentStatus, MIN(b.startDate)
                    ";
            break;
    }

    $approvedStatusID = $partiallyPaidID = 2;
    $reservedStatusID = $fullyPaid = 3;
    $doneStatus = 6;

    $getPaymentsFiltered = $conn->prepare($sql);
    if (!$getPaymentsFiltered) {
        error_log("Prepare failed: " . $conn->error);
    }

    // if ($filter === 'week') {
    //     $getPaymentsFiltered->bind_param('iiiiii',  $partiallyPaidID, $fullyPaid, $approvedStatusID, $doneStatus, $approvedStatusID, $weekNumber);
    // } elseif ($filter === 'month') {
    //     $getPaymentsFiltered->bind_param('iiiii', $partiallyPaidID, $fullyPaid, $approvedStatusID, $doneStatus, $approvedStatusID);
    // }

    if ($filter === 'week') {
        $getPaymentsFiltered->bind_param('i',   $weekNumber);
    }

    if (!$getPaymentsFiltered->execute()) {
        error_log("Error: " . $getPaymentsFiltered->error);
        echo json_encode([
            'success' => false,
            'message' => 'An error occured',
            'payments' => []
        ]);
        exit;
    }

    $paymentsResult = $getPaymentsFiltered->get_result();
    if ($paymentsResult->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'No Data',
            'payments' => []
        ]);
        // error_log(print_r($payments, true));
        exit;
    }

    $payments = [];

    while ($row = $paymentsResult->fetch_assoc()) {
        $payments[] = $row;
    }

    echo json_encode([
        'success' => true,
        'payments' => $payments
    ]);
}
