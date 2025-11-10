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
$fetchUserBookingQuery = $conn->prepare("SELECT 
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
    $date = $row['startDate'];
    $type = $row['bookingType'];

    if (isset($eventsByDate[$date])) {
        continue;
    }

    $color = 'rgb(255, 153, 153)';

    if ($type == 'Hotel') {
        $color = '#f7c42cff'; // Yellow
    } elseif ($type == 'Resort') {
        $color = 'rgba(148, 217, 245, 1)'; // Blue
    }

    $eventsByDate[$date] = [
        'title' => $type . ' #' . $row['bookingID'],
        'start' => $date,
        'allDay' => true,
        'backgroundColor' => $color,
        'opacity' => '1',
        'type' => $row['bookingType']
    ];
}

echo json_encode(array_values($eventsByDate));
