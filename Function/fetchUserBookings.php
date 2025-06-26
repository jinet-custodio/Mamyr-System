<?php
session_start();
header('Content-Type: application/json');
require '../Config/dbcon.php';

if (!isset($_SESSION['userID'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userID = 2;

$sql = "
    SELECT 
        cb.bookingID,
        b.startDate,
        u.firstName,
        u.lastName,
        s.resortServiceID,
        s.entranceRateID,
        s.partnershipServiceID
    FROM confirmedbookings cb
    INNER JOIN bookings b ON cb.bookingID = b.bookingID
    INNER JOIN users u ON b.userID = u.userID
    LEFT JOIN bookingsservices bs ON bs.bookingID = b.bookingID
    LEFT JOIN services s ON bs.serviceID = s.serviceID
    WHERE u.userID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    // Default values
    // $title = 'Event';
    $color = '#dc3545'; // Red

    // Determine simplified label and color
    if (!empty($row['resortServiceID'])) {
        // $title = 'Resort/Hotel';
        $color = '#ffc107'; // Yellow
    } elseif (!empty($row['entranceRateID'])) {
        // $title = 'Resort Entrance';
        $color = '#007bff'; // Blue
    }

    $events[] = [
        'start' => $row['startDate'],
        'description' => $row['firstName'] . ' ' . $row['lastName'],
        'allDay' => true,
        'display' => 'background',
        'backgroundColor' => $color,
        'opacity' => '1'
    ];
}

echo json_encode($events);
