<?php

require '../../Config/dbcon.php';

header('Content-Type: application/json');

$categories = [];
$halls = [];

if (isset($_GET['startDate']) && isset($_GET['endDate'])) {

    // Date & Time
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];


    // Get Event Categories
    $getEventCategoryQuery = $conn->prepare("SELECT * FROM eventcategory");
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
    $getEventHallQuery = $conn->prepare("SELECT ra.* FROM resortamenity  ra
            WHERE ra.RScategoryID = ?
            AND NOT EXISTS (
                    SELECT 1 FROM serviceunavailabledate sud
                    WHERE sud.resortServiceID = ra.resortServiceID
                    AND (? < sud.unavailableEndDate AND ? > sud.unavailableStartDate)
                )
            ");
    if (!$getEventHallQuery) {
        echo json_encode(['error' => $conn->error]);
        exit();
    }
    $getEventHallQuery->bind_param("iss", $eventHallID,  $startDate,  $endDate);
    $getEventHallQuery->execute();
    $getEventHallResult = $getEventHallQuery->get_result();

    if ($getEventHallResult->num_rows > 0) {
        while ($row = $getEventHallResult->fetch_assoc()) {
            $halls[] = $row;
        }
    }
    $getEventHallQuery->close();

    echo json_encode([
        'Categories' => $categories,
        'Halls' => $halls
    ]);
} else {
    echo json_encode([
        'error' => 'Fill Required Data'
    ]);
    exit();
}
