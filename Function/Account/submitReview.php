<?php
require '../../Config/dbcon.php';
session_start();


$bookingID = intval($_POST['bookingID']);
$bookingType = $_POST['bookingType'];
$reviewRating = floatval($_POST['reviewRating']);
$reviewComment = $_POST['reviewComment'];

$stmt = $conn->prepare("INSERT INTO userreview (bookingID, bookingType, reviewRating, reviewComment) VALUES (?, ?, ?, ?) LIMIT 1");

if ($stmt) {
    $stmt->bind_param("isds", $bookingID, $bookingType, $reviewRating, $reviewComment);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        http_response_code(500);
        echo "Error executing statement: " . $stmt->error;
        error_log("Error executing statement: " . $stmt->error);
    }


    $stmt->close();
} else {
    http_response_code(500);
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
