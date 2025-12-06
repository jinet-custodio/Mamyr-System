<?php
session_start();
header('Content-Type: application/json');
require '../Config/dbcon.php';

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
        b.bookingType
    FROM confirmedbooking cb
    INNER JOIN booking b ON cb.bookingID = b.bookingID
    WHERE cb.paymentStatus = ? OR cb.paymentStatus = ?
");

$fetchUserBookingQuery->bind_param("ii", $fullyPaid, $partiallyPaid);
$fetchUserBookingQuery->execute();
$result = $fetchUserBookingQuery->get_result();

$eventsByDate = [];

while ($row = $result->fetch_assoc()) {

    $date = $row['startDate'];
    $dayOnly = substr($date, 0, 10);
    $type = $row['bookingType'];

    if (isset($eventsByDate[$dayOnly])) {
        continue;
    }

    $color = 'rgb(255, 153, 153)';

    if ($type === 'Hotel') {
        $color = '#f7c42c';
    } elseif ($type === 'Resort') {
        $color = 'rgba(148, 217, 245, 1)';
    }

    $eventsByDate[$dayOnly] = [
        'title' => $type . ' #' . $row['bookingID'],
        'start' => $dayOnly,
        'allDay' => true,
        'backgroundColor' => $color,
        'opacity' => '1',
        'type' => $type
    ];
}

$unavailQuery = $conn->prepare("
    SELECT 
        sud.unavailableStartDate AS startDate,
        sud.unavailableEndDate AS endDate,
        ra.RServiceName AS service
    FROM serviceunavailabledate sud
    JOIN resortamenity ra ON ra.resortServiceID = sud.resortServiceID
    WHERE sud.status IN ('confirmed', 'hold')
");

$unavailQuery->execute();
$unavailResult = $unavailQuery->get_result();

while ($row = $unavailResult->fetch_assoc()) {

    $startDate = substr($row['startDate'], 0, 10);
    $endDate   = substr($row['endDate'], 0, 10);

    // Expand multi-day ranges
    $current = strtotime($startDate);
    $end     = strtotime($endDate);

    while ($current <= $end) {

        $day = date("Y-m-d", $current);

        if (!isset($eventsByDate[$day])) {
            $eventsByDate[$day] = [
                'title' => 'Unavailable - ' . $row['service'],
                'start' => $day,
                'allDay' => true,
                'backgroundColor' => '#7f8c8d', // Gray
                'opacity' => '1',
                'type' => 'unavailable'
            ];
        }

        $current = strtotime("+1 day", $current);
    }
}

echo json_encode(array_values($eventsByDate));
