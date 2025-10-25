<?php

require '../../Config/dbcon.php';


if (isset($_GET['selectedFilter']) && isset($_GET['id'])) {

    $selectedFilter = $_GET['selectedFilter'];
    $partnerID = (int) $_GET['id'];
    $filter = '';
    switch ($selectedFilter) {
        case 'month':
            $filter = 'month';
            $sql = "SELECT 
                         CASE 
                        WHEN (cb.paymentStatus IS NULL) THEN 
                            IF(stat.statusName IS NULL, 'Unpaid', stat.statusName)
                        ELSE 
                            stat.statusName
                        END AS paymentStatus,
                        MONTHNAME(b.startDate) AS month,
                        SUM(IFNULL(bs.bookingServicePrice, 0) + IFNULL(cpi.ServicePrice, 0)) AS monthlyRevenue,
                        CONCAT('Week ', 
                                WEEK(b.startDate, 3) - WEEK(DATE_SUB(b.startDate, INTERVAL DAY(b.startDate)-1 DAY ), 3) + 1
                            ) AS weekOfMonth,
                        ps.partnershipID, ps.partnershipServiceID
                    FROM booking b
                    LEFT JOIN  confirmedbooking cb ON b.bookingID = cb.bookingID
                    LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID
                    LEFT JOIN custompackageitem cpi ON b.customPackageID = cpi.customPackageID
                    LEFT JOIN service s ON (cpi.serviceID = s.serviceID  OR bs.serviceID = s.serviceID)
                    LEFT JOIN partnershipservice ps ON s.partnershipServiceID = ps.partnershipServiceID
                    LEFT JOIN businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID
                    LEFT JOIN paymentstatus stat ON cb.paymentStatus = stat.paymentStatusID
                    WHERE b.bookingStatus IN (?,?,?) 
                    AND cb.paymentStatus IN (?,?)
                    AND YEAR(b.startDate) = YEAR(CURDATE()) 
                    AND DATE(b.endDate) < CURDATE()
                    AND ps.partnershipID = ?
                    AND bpas.approvalStatus = ?
                    GROUP BY 
                        paymentStatus
                    ORDER BY 
                        paymentStatus
                    ";
            break;
        case 'w1':
        case 'w2':
        case 'w3':
        case 'w4':
        case 'w5':
            $filter = 'week';
            $weekNumber = substr($selectedFilter, 1) - 1;
            $sql = "SELECT 
                    CASE 
                        WHEN (cb.paymentStatus IS NULL) THEN 
                            IF(stat.statusName IS NULL, 'Unpaid', stat.statusName)
                        ELSE 
                            stat.statusName
                    END AS paymentStatus,
                    CONCAT(
                        DATE_FORMAT(DATE(b.startDate - INTERVAL (DAY(b.startDate) - 1) % 7 DAY), '%b %e'),
                        ' - ',
                        DATE_FORMAT(DATE(b.startDate - INTERVAL (DAY(b.startDate) - 1) % 7 DAY + INTERVAL 6 DAY), '%b %e'),
                        '(Mon - Sun )'
                    ) AS weeks,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 0 THEN IFNULL(bs.bookingServicePrice, 0) + IFNULL(cpi.ServicePrice, 0) ELSE 0 END) AS Mon,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 1 THEN IFNULL(bs.bookingServicePrice, 0) + IFNULL(cpi.ServicePrice, 0) ELSE 0 END) AS Tue,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 2 THEN IFNULL(bs.bookingServicePrice, 0) + IFNULL(cpi.ServicePrice, 0) ELSE 0 END) AS Wed,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 3 THEN IFNULL(bs.bookingServicePrice, 0) + IFNULL(cpi.ServicePrice, 0) ELSE 0 END) AS Thu,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 4 THEN IFNULL(bs.bookingServicePrice, 0) + IFNULL(cpi.ServicePrice, 0) ELSE 0 END) AS Fri,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 5 THEN IFNULL(bs.bookingServicePrice, 0) + IFNULL(cpi.ServicePrice, 0) ELSE 0 END) AS Sat,
                        SUM(CASE WHEN WEEKDAY(b.startDate) = 6 THEN IFNULL(bs.bookingServicePrice, 0) + IFNULL(cpi.ServicePrice, 0) ELSE 0 END) AS Sun
                    FROM booking b
                    LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                    LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID
                    LEFT JOIN custompackageitem cpi ON b.customPackageID = cpi.customPackageID
                    LEFT JOIN service s ON (cpi.serviceID = s.serviceID  OR bs.serviceID = s.serviceID)
                    LEFT JOIN partnershipservice ps ON s.partnershipServiceID = ps.partnershipServiceID
                    LEFT JOIN businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID
                    LEFT JOIN paymentstatus stat ON cb.paymentStatus = stat.paymentStatusID
                    WHERE b.bookingStatus IN (?,?,?)
                    AND cb.paymentStatus IN (?,?)
                    AND YEAR(b.startDate) = YEAR(CURDATE()) 
                    AND DATE(b.endDate) < CURDATE()
                    AND ps.partnershipID = ?
                    AND bpas.approvalStatus = ?
                    AND FLOOR((DAY(b.startDate) - 1 ) / 7) = ?
                    GROUP BY 
                        paymentStatus
                    ORDER BY 
                        paymentStatus
                    ";
            break;
    }

    $approvedStatus = $partiallyPaid = 2;
    $fullyPaid = $reserved = 3;
    $doneStatus = 6;
    $getPartnerSalesQuery = $conn->prepare($sql);
    if ($filter === 'month') {
        $getPartnerSalesQuery->bind_param('iiiiiii', $approvedStatus, $reserved, $doneStatus, $partiallyPaid, $fullyPaid, $partnerID, $approvedStatus);
    } else {
        $getPartnerSalesQuery->bind_param('iiiiiiii', $approvedStatus, $reserved, $doneStatus, $partiallyPaid, $fullyPaid, $partnerID, $approvedStatus, $weekNumber);
    }

    if (!$getPartnerSalesQuery->execute()) {
        error_log("Error: " . $getPaymentsFiltered->error);
        echo json_encode([
            'success' => false,
            'message' =>  error_log("Error: " . $getPaymentsFiltered->error),
            'sales' => []
        ]);
        exit;
    }
    $sales = [];
    $salesResult = $getPartnerSalesQuery->get_result();
    if ($salesResult->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'No Data',
            'sales' => []
        ]);
        exit;
    }

    while ($row = $salesResult->fetch_assoc()) {
        $sales[] = $row;
    }

    echo json_encode([
        'success' => true,
        'sales' => $sales
    ]);
}
