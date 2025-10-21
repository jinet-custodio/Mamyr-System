<?php

require '../../../Config/dbcon.php';

header('Content-type: application/json');


if (isset($_GET['selectedPeriod']) && isset($_GET['selectedStatus'])) {

    $selectedStatus = $_GET['selectedStatus'];
    $selectedPeriod = $_GET['selectedPeriod'];


    switch ($selectedPeriod) {
        case 'month':
            $periodFilter = 'month';
            switch ($selectedStatus) {
                case 'all':
                    $statusFilter = 'all';
                    $sql = "SELECT 
                                bookingType,
                                COUNT(bookingID) AS totalBookings
                            FROM 
                                booking
                            WHERE
                                MONTH(startDate) = MONTH(CURDATE())
                                and YEAR(startDate) = YEAR(CURDATE())
                            GROUP BY 
                                bookingType
                            ";
                    break;
                case 1: //Pending
                case 2: //Approved
                case 3: //Reserved
                case 4: //Cancelled
                case 5: //Rejected
                case 6: //Done
                case 7: //Expired
                    $statusFilter = '';
                    $statusID = intval($selectedStatus);
                    $sql = "SELECT 
                                bookingType,
                                COUNT(bookingID) AS totalBookings
                            FROM 
                                booking
                            WHERE
                                bookingStatus = ?
                                AND MONTH(startDate) = MONTH(CURDATE())
                                AND YEAR(startDate) = YEAR(CURDATE())
                            GROUP BY 
                                bookingType
                            ";
                    break;
            }
            break;
        case 'w1':
        case 'w2':
        case 'w3':
        case 'w4':
        case 'w5':
            $periodFilter = 'week';
            $weekNumber = intval(substr($selectedPeriod, 1)) - 1;
            switch ($selectedStatus) {
                case 'all':
                    $statusFilter = 'all';
                    $sql = "SELECT 
                                bookingType,
                                COUNT(bookingID) AS totalBookings
                            FROM 
                                booking
                            WHERE
                                MONTH(startDate) = MONTH(CURDATE())
                                and YEAR(startDate) = YEAR(CURDATE())
                                AND FLOOR((DAY(startDate) - 1) / 7) = ?
                            GROUP BY 
                                bookingType
                            ";
                    break;
                case 1: //Pending
                case 2; //Approved
                case 3: //Reserved
                case 4: //Cancelled
                case 5: //Rejected
                case 6: //Done
                case 7: //Expired
                    $statusFilter = '';
                    $statusID = intval($selectedStatus);
                    $sql = "SELECT 
                                bookingType,
                                COUNT(bookingID) AS totalBookings
                            FROM 
                                booking
                            WHERE
                                bookingStatus = ?
                                AND MONTH(startDate) = MONTH(CURDATE())
                                AND YEAR(startDate) = YEAR(CURDATE())
                                AND FLOOR((DAY(startDate) - 1) / 7) = ?
                            GROUP BY 
                                bookingType
                            ";
                    break;
            }
            break;
    }

    $getTotalBookings = $conn->prepare($sql);

    if ($periodFilter === 'month') {
        if ($statusFilter !== 'all') {
            $getTotalBookings->bind_param('i', $statusID);
        }
    } else {
        if ($statusFilter === 'all') {
            $getTotalBookings->bind_param('i', $weekNumber);
        } else {
            $getTotalBookings->bind_param('ii',  $statusID, $weekNumber);
        }
    }

    if (!$getTotalBookings->execute()) {
        $errorInfo = $getTotalBookings->errorInfo();
        echo json_encode([
            'success' => false,
            'message' => $errorInfo
        ]);
    }
    $totalBookings = [];
    $result = $getTotalBookings->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'result' => $totalBookings
        ]);
        exit;
    }

    while ($row = $result->fetch_assoc()) {
        $totalBookings[] = $row;
    }
    echo json_encode([
        'success' => true,
        'result' => $totalBookings
    ]);
}
