<?php
header('Content-Type: application/json');
include '../../Config/dbcon.php';

$dateTime = isset($_POST['dateTime']) ? $_POST['dateTime'] : null;
if (!$dateTime) {
    echo json_encode(['rooms' => []]);
    exit;
}

$sql = "
SELECT r.RServiceName,
       r.resortServiceID,
       r.RSAvailabilityID,
       CASE
           -- Check dynamic unavailable dates first
           WHEN EXISTS (
               SELECT 1
               FROM serviceunavailabledate s
               WHERE s.resortServiceID = r.resortServiceID
                 AND s.status != 'cancelled'
                 AND '$dateTime' BETWEEN s.unavailableStartDate AND s.unavailableEndDate
           ) THEN 0
           -- Check static status (e.g., Maintenance, Occupied, Private)
           WHEN r.RSAvailabilityID != 1 THEN 0
           ELSE 1
       END AS isAvailable
FROM resortamenity r
WHERE r.RScategoryID = 1
ORDER BY CAST(REGEXP_SUBSTR(r.RServiceName, '\\d+') AS UNSIGNED);
";

$res = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = [
        'service' => $row['RServiceName'],
        'available' => (bool)$row['isAvailable'],
        'RSAvailabilityID' => (int)$row['RSAvailabilityID']
    ];
}

echo json_encode(['rooms' => $data]);
