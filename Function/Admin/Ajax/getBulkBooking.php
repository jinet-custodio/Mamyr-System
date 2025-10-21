<?php

require '../../../Config/dbcon.php';

$getBulkBookings = $conn->prepare("SELECT `startDate`, `endDate`, `bookingType`, `bookingCount`, `salesAmount` FROM `walkin_sales_summary` ORDER BY createdAt DESC");

if (!$getBulkBookings->execute()) {
    $errorInfo = $getBulkBookings->errorInfo();
    echo json_encode([
        'success' => false,
        'message' => $errorInfo
    ]);
}

$bulkBookings = [];
$result  = $getBulkBookings->get_result();

if ($result->num_rows === 0) {
    $bulkBookings = [];
}

while ($row = $result->fetch_assoc()) {
    $startDate = date('M. d, Y', strtotime($row['startDate']));
    $endDate = date('M. d, Y', strtotime($row['endDate']));
    $bulkBookings[] = [
        'salesAmount' => '₱' . number_format($row['salesAmount'] ?? 0, 2),
        'bookingType' => $row['bookingType'] . ' Booking',
        'bookingCount' => $row['bookingCount'],
        'startDate' => $startDate,
        'endDate' => $endDate

    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Fetching Successfull',
    'bulkBookings' => $bulkBookings
]);
