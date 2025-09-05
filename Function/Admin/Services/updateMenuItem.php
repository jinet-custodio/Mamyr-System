<?php

require '../../../Config/dbcon.php';
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$foodName = $data['name'];
$foodID = intval($data['id']);
$foodPrice = floatval($data['price']);
$foodCategory = strtoupper($data['category']);
$foodAvailability = intval($data['availability']);


try {
    $updateMenuItem = $conn->prepare("UPDATE `menuitem` SET `foodName`= ?,`foodPrice`= ?,`foodCategory`= ?,`availabilityID`= ? WHERE `foodItemID`= ? ");
    $updateMenuItem->bind_param("sdsii", $foodName, $foodPrice, $foodCategory, $foodAvailability, $foodID);

    if (!$updateMenuItem->execute()) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed updating the menu'
        ]);
        error_log("Error updating the menu: " . $updateMenuItem->error);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Update Successful!'
        ]);
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occured!'
    ]);
}
