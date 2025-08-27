<?php

require '../../../Config/dbcon.php';

header('Content-Type: application/json');

try {
    $getResortServices = $conn->prepare("SELECT * FROM resortamenties");

    if ($getResortServices === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if ($getResortServices->execute()) {
        $getResult = $getResortServices->get_result();

        if ($getResult->num_rows > 0) {
            $data = [];

            while ($row = $getResult->fetch_assoc()) {
                $data[] = $row;
            }

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } else {
            error_log('No data found.');
            echo json_encode([
                'success' => false,
                'message' => 'No data found. Please try again later.'
            ]);
        }
    } else {
        error_log("Execute error: " . $getResortServices->error);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while retrieving data from the database. Please try again later.'
        ]);
    }
} catch (Exception $e) {
    error_log("Exception caught: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unexpected server error. Please try again later.'
    ]);
}
