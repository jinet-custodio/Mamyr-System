<?php
session_start();
header('Content-Type: application/json');
require '../../Config/dbcon.php';

if (!isset($_SESSION['userID'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userID = intval($_SESSION['userID']);

$partiallyPaid = 2;
$fullyPaid = 3;
$fetchUserBookingQuery = $conn->prepare("SELECT 
                                    cb.bookingID,
                                    b.startDate,
                                    b.endDate,
                                    b.bookingType,
                                    s.resortServiceID,
                                    s.entranceRateID,
                                    cp.customPackageID,
                                    s.partnershipServiceID
                                FROM confirmedbooking cb
                                INNER JOIN booking b ON cb.bookingID = b.bookingID
                                LEFT JOIN custompackage cp ON b.customPackageID = cp.customPackageID
                                LEFT JOIN bookingservice bs ON bs.bookingID = b.bookingID
                                LEFT JOIN service s ON bs.serviceID = s.serviceID
                                WHERE cb.paymentStatus = ? OR cb.paymentStatus = ?
                            ");
$fetchUserBookingQuery->bind_param("ii", $fullyPaid, $partiallyPaid);
$fetchUserBookingQuery->execute();
$result = $fetchUserBookingQuery->get_result();
$eventsByDate = [];

while ($row = $result->fetch_assoc()) {
    $startdate = $row['startDate'];
    $enddate = $row['endDate'];
    $type = $row['bookingType'];

    if (isset($eventsByDate[$startdate])) {
        continue;
    }

    $color = '#dc3545';

    if ($type == 'Hotel') {
        $color = '#ffc107'; // Yellow
    } elseif ($type == 'Resort') {
        $color = '#5dccf5'; // Blue
    }

    $formattedEndDate = null;
    if (!empty($endDate)) {
        $formattedEndDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
    }

    $eventsByDate[$startdate] = [
        'title' => $type,
        'start' => $startdate,
        'end' => $enddate,
        'allDay' => true,
        'backgroundColor' => $color,
        'opacity' => '1'
    ];
}

// After processing results
if (empty($eventsByDate)) {
    echo json_encode([
        [
            'title' => 'No Bookings Found',
            'start' => date('Y-m-d'), // today's date
            'allDay' => true,
            'backgroundColor' => '#6c757d', // Grey color
            'opacity' => '0.6'
        ]
    ]);
} else {
    echo json_encode(array_values($eventsByDate));
}
