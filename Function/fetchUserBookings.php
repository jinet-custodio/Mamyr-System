<?php
session_start();
header('Content-Type: application/json');
require '../Config/dbcon.php';

if (!isset($_SESSION['userID'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userID = $_SESSION['userID'];

$sql = "
    SELECT 
        cb.bookingID,
        b.startDate,
        b.bookingType,
        u.firstName,
        u.lastName,
        s.resortServiceID,
        s.entranceRateID,
        cp.customPackageID,
        s.partnershipServiceID
    FROM confirmedbookings cb
    INNER JOIN bookings b ON cb.bookingID = b.bookingID
    INNER JOIN users u ON b.userID = u.userID
    LEFT JOIN custompackages cp ON b.customPackageID = cp.customPackageID
    LEFT JOIN bookingservices bs ON bs.bookingID = b.bookingID
    LEFT JOIN services s ON bs.serviceID = s.serviceID
    WHERE cb.paymentStatus = 2 AND u.userID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$eventsByDate = [];

while ($row = $result->fetch_assoc()) {
    $date = $row['startDate'];
    $type = $row['bookingType'];
    
    if (isset($eventsByDate[$date])) {
        continue;
    }

    $color = '#dc3545'; 

    if ($type == 'Hotel') {
        $color = '#ffc107'; // Yellow
    } elseif ($type == 'Resort') {
        $color = '#5dccf5'; // Blue
    }

    $eventsByDate[$date] = [
        'title' => $type . ' #' . $row['bookingID'],
        'start' => $date,
        'allDay' => true,
        'backgroundColor' => $color,
        'opacity' => '1'
    ];
}

echo json_encode(array_values($eventsByDate));
