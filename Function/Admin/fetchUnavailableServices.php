<?php
session_start();
header('Content-Type: application/json');
require '../../Config/dbcon.php';

// Query unavailable dates with joined service names
$query = $conn->prepare("
    SELECT 
        sud.unavailableStartDate,
        sud.unavailableEndDate,
        ra.RServiceName,
        ps.PBName
    FROM serviceunavailabledate sud
    LEFT JOIN resortamenity ra ON sud.resortServiceID = ra.resortServiceID
    LEFT JOIN partnershipservice ps ON sud.partnershipServiceID = ps.partnershipServiceID
");

$query->execute();
$result = $query->get_result();

$unavailableEvents = [];

while ($row = $result->fetch_assoc()) {
    $title = $row['RServiceName'] ?? $row['PBName'] ?? 'Unavailable Service';
    $start = $row['unavailableStartDate'];
    $end = $row['unavailableEndDate'];

    $unavailableEvents[] = [
        'title' => $title . ' in Use',
        'start' => $start,
        'end'   => $end,
        'allDay' => false,
        'backgroundColor' => '#6c757d',  // Grey
        'borderColor' => '#6c757d',
        'opacity' => '0.7'
    ];
}

// Return fallback if no unavailable dates found
if (empty($unavailableEvents)) {
    echo json_encode([
        [
            'title' => 'No services marked as unavailable.',
            'start' => date('Y-m-d'),
            'allDay' => true,
            'backgroundColor' => '#e0e0e0',
            'borderColor' => '#e0e0e0',
            'opacity' => '0.5'
        ]
    ]);
} else {
    echo json_encode($unavailableEvents);
}
