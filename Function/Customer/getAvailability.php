<?php
header('Content-Type: application/json');
include '../../Config/dbcon.php'; // adjust path

$dateTime = isset($_POST['dateTime']) ? $_POST['dateTime'] : null;

// Update this SQL to fetch your availability logic based on time.
// For now, it returns all rooms with current availability
$sql = "SELECT RServiceName, RSAvailabilityID, RSduration
        FROM resortAmenities
        WHERE RSCategoryID = 1";
$res = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = [
        'service' => trim($row['RServiceName']),
        'available' => ($row['RSAvailabilityID'] == 1),
        'duration' => trim($row['RSduration'])
    ];
}

echo json_encode(['rooms' => $data]);
