<?php

require '../../Config/dbcon.php';
session_start();

if (isset($_POST['eventBook'])) {
    $eventType = mysqli_real_escape_string($conn, $_POST['eventType']);
    $eventVenue = mysqli_real_escape_string($conn, $_POST['eventVenue']);
    $eventDate = mysqli_real_escape_string($conn, $_POST['eventDate']);
    $eventPax = mysqli_real_escape_string($conn, $_POST['eventPax']);
    $eventPackage = mysqli_real_escape_string($conn, $_POST['eventPackage']);
    $additionalNotes = mysqli_real_escape_string($conn, $_POST['additionalNotes']);


    if ($eventType) {
        echo "Selected Venue: " . htmlspecialchars($eventType);
    } else {
        echo "No venue selected.";
    }
}
