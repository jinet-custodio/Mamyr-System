<?php
require '../../../Config/dbcon.php';


// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo "No data received.";
    exit;
}

$update = $conn->prepare("
    UPDATE resortinfo
    SET resortInfoDetail = ?
    WHERE resortInfoName = ?
");

foreach ($data as $key => $value) {
    $update->bind_param("ss", $value, $key);
    $update->execute();
}

echo "success";
