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

$bookings = "SELECT u.firstName, u.lastName, 
    ps.PBName, 
    rs.RScategoryID, rsc.categoryName AS serviceName 
    ec.categoryName AS eventName, 
    b.* 
    FROM bookings b
    INNER JOIN users u ON b.userID = u.userID
    LEFT JOIN statuses st ON st.statusID = b.bookingStatus
    LEFT JOIN allservices a ON b.packageServiceID = a.packageServiceID
    LEFT JOIN packages p ON a.packageID = p.packageID
    LEFT JOIN eventcategories ec ON p.PcategoryID = ec.categoryID
    LEFT JOIN services s ON a.serviceID = s.serviceID
    LEFT JOIN resortservices rs ON s.resortServiceID = rs.resortServiceID
    LEFT JOIN resortservicescategories rsc ON rsc.categoryID = rs.RScategoryID
    LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID
    WHERE b.userID = $userID";

$result = mysqli_query($conn, $bookings);

$events = [];

while ($row = $result->fetch_assoc()) {
    if (!empty($row['PBName'])) {
        $title = $row['PBName'];
    } elseif (!empty($row['eventName'])) {
        $title = $row['eventName'];
    } elseif (!empty($row['serviceName'])) {
        $title = $row['serviceName'];
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
