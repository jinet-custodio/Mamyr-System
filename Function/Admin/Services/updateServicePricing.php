<?php

require '../../../Config/dbcon.php';
header("Content-Type: application/json");
date_default_timezone_set('Asia/Manila');

$data = json_decode(file_get_contents('php://input'), true);


$id = (int) $data['pricingID'];
$pricingType = htmlspecialchars($data['pricingType']);
$servicePrice = (float) $data['servicePrice'];
$chargeType = htmlspecialchars($data['chargeType']);
$ageGroup = htmlspecialchars($data['ageGroup']);
$notes = htmlentities($data['notes'] ?? 'N/A');

try {


    $updateServicePricingQuery = $conn->prepare("UPDATE `servicepricing` SET `pricingType`= ?,`price`=?,`chargeType`=?,`ageGroup`=?,`notes`=? WHERE `pricingID`= ?");
    $updateServicePricingQuery->bind_param("sdsssi", $pricingType, $servicePrice, $chargeType, $ageGroup, $notes, $id);

    if (!$updateServicePricingQuery->execute()) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed updating the pricing'
        ]);
        throw new Exception("Error updating the pricing: " . $updateMenuItem->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Service pricing updated successfully.'
    ]);
} catch (Exception $e) {
    error_log('Exception Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occured!'
    ]);
}
