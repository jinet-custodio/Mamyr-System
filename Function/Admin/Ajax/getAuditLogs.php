<?php

require '../../../Config/dbcon.php';

$sql = "SELECT logID, adminID, action, target, logDetails, timestamp FROM auditlog ORDER BY logID DESC";
$result = $conn->query($sql);

$data = array();

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['data' => $data]);

$conn->close();
