<?php
require '../../Config/dbcon.php';

session_start();

$bookingID = intval($_POST['bookingID']);
$bookingType = $conn->real_escape_string($_POST['bookingType']);
$reviewRating = floatval($_POST['reviewRating']);
$reviewComment = $conn->real_escape_string($_POST['reviewComment']);

$sql = "INSERT INTO userreview (bookingID, bookingType, reviewRating, reviewComment)
        VALUES ('$bookingID', '$bookingType', '$reviewRating', '$reviewComment')";

if ($conn->query($sql) === TRUE) {
    echo "Success";
} else {
    http_response_code(500);
    echo "Error: " . $conn->error;
}

$conn->close();
