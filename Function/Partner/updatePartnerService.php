<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../Config/dbcon.php';

$data = json_decode(file_get_contents('php://input'), true);

$serviceID = intval($data['id']);
$serviceName = ucfirst($data['name']);
$rawPrice = $data['price'];
$servicePrice = str_replace(['₱', ','], '', $rawPrice);
$serviceCapacity = intval($data['capacity']);
$serviceDuration = $data['duration'];
$serviceDesc = $data['descriptions'] ?? 'N/A';
// $serviceImage = $data['image'];
$serviceAvailabilityName = ucwords($data['availability']);
try {
    $getAvailabilityIDQuery = $conn->prepare('SELECT `availabilityID` FROM `serviceavailability` WHERE `availabilityName` = ?');
    $getAvailabilityIDQuery->bind_param('s', $serviceAvailabilityName);
    $getAvailabilityIDQuery->execute();
    $result = $getAvailabilityIDQuery->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $availabilityID = $row['availabilityID'];
    }

    $updatePartnerServiceQuery = $conn->prepare("UPDATE `partnershipservice` SET `PBName`= ?,`PBPrice`=?,`PBDescription`= ? ,`PBcapacity`= ?,`PBduration`= ? ,`PSAvailabilityID`= ? WHERE `partnershipServiceID`= ?");
    $updatePartnerServiceQuery->bind_param('sdsssii', $serviceName, $servicePrice, $serviceDesc, $serviceCapacity, $serviceDuration, $availabilityID, $serviceID);
    if (!$updatePartnerServiceQuery->execute()) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed updating the service information'
        ]);
        throw new Exception("Error updating the service information: " . $updateMenuItem->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Service information updated successfully.'
    ]);
} catch (Exception $e) {
    error_log('Exception Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occured!'
    ]);
}
