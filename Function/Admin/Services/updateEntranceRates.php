<?php

require '../../../Config/dbcon.php';
session_start();

date_default_timezone_set('Asia/Manila');


//Get the data

$data = json_decode(file_get_contents('php://input'), true);


$entranceRateID = intval($data['id']);
$tourType = $data['tourType'];
$timeRange = $data['time'];
$visitorType = $data['visitorType'];
$price = floatval($data['price']);
$timeRangeID = intval($data['timeRangeID']);
$availability = $data['availability'];

$conn->begin_transaction();

try {

    $updateTimeRange = $conn->prepare("UPDATE `entrancetimerange` SET `session_type`= ?,`time_range`= ? WHERE timeRangeID = ?");
    $updateTimeRange->bind_param('ssi', $tourType, $timeRange, $timeRangeID);
    if ($updateTimeRange->execute()) {
        $updateEntranceRate = $conn->prepare("UPDATE `entrancerate` SET `sessionType`= ?,`timeRangeID`= ?,`ERcategory`= ?,`ERprice`= ?, `availability` = ? WHERE entranceRateID = ?");
        $updateEntranceRate->bind_param("sisdsi", $tourType, $timeRangeID, $visitorType, $price, $availability, $entranceRateID);
        // error_log("SQL Query: " . $updateEntranceRate->error);
        if ($updateEntranceRate->execute()) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Resort Entrance Rates Updated Successfully'
            ]);
        } else {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Update Failed'
            ]);
            error_log('Error:' . $updateTimeRange->error);
            exit();
        }
    } else {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Update Failed'
        ]);
        error_log('Error:' . $updateTimeRange->error);
        exit();
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'An error occured'
    ]);
}
