<?php

require '../../../Config/dbcon.php';
session_start();

date_default_timezone_set('Asia/Manila');


//Get the data

$data = json_decode(file_get_contents('php://input'), true);

$serviceID = intval($data['id']);
$serviceName = $data['name'];
$servicePrice = floatval($data['price']);
$serviceCapacity = intval($data['capacity']);
$serviceMaxCapacity = intval($data['maxCapacity']);
$serviceDuration = $data['duration'];
$serviceDesc = $data['description'];
// $serviceImage = $data['image'];
$serviceAvailability = intval($data['availability']);

$conn->begin_transaction();

try {
    $updateServiceQuery = $conn->prepare("UPDATE resortamenity SET
    RServiceName = ?, RSprice = ?, RScapacity = ?, RSmaxCapacity = ?, RSdescription = ?, RSAvailabilityID = ?, RSduration = ? WHERE resortServiceID = ?
    ");
    $updateServiceQuery->bind_param("sdiisisi", $serviceName, $servicePrice, $serviceCapacity, $serviceMaxCapacity, $serviceDesc,  $serviceAvailability, $serviceDuration, $serviceID);
    if ($updateServiceQuery->execute()) {
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Updated Successfully'
        ]);
    } else {
        error_log("Error Updating" . $updateServiceQuery->error);
        $conn->rollback();
        echo json_encode([
            'success' => false,
        ]);
    }
} catch (Exception $e) {
    error_log("Error Updating" . $updateServiceQuery->error);
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to Update Service'
    ]);
}
