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

$bookings = "SELECT u.firstName, u.lastName, ps.PBName, rs.category, ec.categoryName, b.* 
    FROM bookings b
    INNER JOIN users u ON b.userID = u.userID
    LEFT JOIN packages p ON b.packageID = p.packageID
    LEFT JOIN eventcategories ec ON p.categoryID = ec.categoryID
    LEFT JOIN services s ON b.serviceID = s.serviceID
    LEFT JOIN resortservices rs ON s.resortServiceID = rs.resortServiceID
    LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID
    WHERE b.userID = $userID";

$result = mysqli_query($conn, $bookings);

$events = [];

while ($row = $result->fetch_assoc()) {
    if (!empty($row['PBName'])) {
        $title = $row['PBName'];
    } elseif (!empty($row['categoryName'])) {
        $title = $row['categoryName'];
    } elseif (!empty($row['category'])) {
        $title = $row['category'];
    } else {
        $title = "Booking";
    }

    $events[] = [
        'title' => $title,
        'start' => $row['startDate'],
        'description' => $row['firstName'] . ' ' . $row['lastName'],
        'allDay' => true,
        'display' => 'background',
        'backgroundColor' => '#28a745',
        'opacity' => '1'
    ];
}

echo json_encode($events);
