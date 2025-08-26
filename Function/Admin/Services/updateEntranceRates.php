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

$conn->begin_transaction();

try {

    $updateTimeRange = $conn->prepare("UPDATE `entrancetimeranges` SET `session_type`= ?,`time_range`= ? WHERE timeRangeID = ?");
    $updateTimeRange->bind_param('ssi', $tourType, $timeRange, $timeRangeID);
    if ($updateTimeRange->execute()) {
        $updateEntranceRate = $conn->prepare("UPDATE `entrancerates` SET `sessionType`= ?,`timeRangeID`= ?,`ERcategory`= ?,`ERprice`= ? WHERE entranceRateID = ?");
        $updateEntranceRate->bind_param("sisdi", $tourType, $timeRangeID, $visitorType, $price, $entranceRateID);
        if ($updateEntranceRate->execute()) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Updated Successfully'
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
        'message' => 'Update Failed'
    ]);
}
