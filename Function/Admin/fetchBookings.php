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

$fetchUserBookingQuery = $conn->prepare("
    SELECT 
        cb.bookingID,
        b.startDate,
        b.endDate,
        b.bookingType,
        s.resortServiceID,
        s.entranceRateID,
        cp.customPackageID,
        cp.eventTypeID,
        ec.categoryID,
        ec.categoryName,
        s.partnershipServiceID
    FROM confirmedbooking cb
    INNER JOIN booking b ON cb.bookingID = b.bookingID
    LEFT JOIN custompackage cp ON b.customPackageID = cp.customPackageID
    LEFT JOIN eventcategory ec ON cp.eventTypeID = ec.categoryID
    LEFT JOIN bookingservice bs ON bs.bookingID = b.bookingID
    LEFT JOIN service s ON bs.serviceID = s.serviceID
    WHERE cb.paymentStatus = ? OR cb.paymentStatus = ?
");
$fetchUserBookingQuery->bind_param("ii", $fullyPaid, $partiallyPaid);
$fetchUserBookingQuery->execute();
$result = $fetchUserBookingQuery->get_result();

$eventsByDate = [];

while ($row = $result->fetch_assoc()) {
    $bookingID = $row['bookingID'];
    $startdate = $row['startDate'];
    $enddate = $row['endDate'];
    $type = $row['bookingType'];
    $categoryName = $row['categoryName'] ?? '';

    if (isset($eventsByDate[$bookingID])) {
        continue;
    }

    // Default color
    $color = '#dc3545';

    // Set color based on booking type
    if ($type === 'Hotel') {
        $color = '#ffc107';
    } elseif ($type === 'Resort') {
        $color = '#5dccf5';
    }

    // Add category if Event
    $title = $type;
    if (strtolower($type) === 'event' && !empty($categoryName)) {
        $title .= ' - ' . $categoryName;
    }

    $start = date('c', strtotime($startdate));
    $end   = date('c', strtotime($enddate));


    $eventsByDate[$bookingID] = [
        'title' => $title,
        'start' => $start,
        'end' => $end,
        'allDay' => false,
        'backgroundColor' => $color,
        'opacity' => '1'
    ];
}


if (empty($eventsByDate)) {
    echo json_encode([
        [
            'title' => 'No Bookings Found',
            'start' => date('Y-m-d'),
            'allDay' => true,
            'backgroundColor' => '#6c757d',
            'opacity' => '0.6'
        ]
    ]);
} else {
    echo json_encode(array_values($eventsByDate));
}
