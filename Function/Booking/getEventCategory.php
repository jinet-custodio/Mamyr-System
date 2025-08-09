<?php

require '../../Config/dbcon.php';

header('Content-Type: application/json');

$categories = [];
$halls = [];

// Get Event Categories
$getEventCategoryQuery = $conn->prepare("SELECT * FROM eventCategories");
$getEventCategoryQuery->execute();
$getEventCategoryResult = $getEventCategoryQuery->get_result();

if ($getEventCategoryResult->num_rows > 0) {
    while ($row = $getEventCategoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}
$getEventCategoryQuery->close();

// Get Event Halls
$eventHallID = 4;
$getEventHallQuery = $conn->prepare("SELECT * FROM resortAmenities WHERE RScategoryID = ?");
$getEventHallQuery->bind_param("i", $eventHallID);
$getEventHallQuery->execute();
$getEventHallResult = $getEventHallQuery->get_result();

if ($getEventHallResult->num_rows > 0) {
    while ($row = $getEventHallResult->fetch_assoc()) {
        $halls[] = $row;
    }
}
$getEventHallQuery->close();

// Output a single JSON response
echo json_encode([
    'Categories' => $categories,
    'Halls' => $halls
]);
