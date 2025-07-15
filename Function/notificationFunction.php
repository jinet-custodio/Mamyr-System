<?php

require '../Config//dbcon.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notificationID'])) {
    $notificationID = intval($_POST['notificationID']);

    $readNo = 1;
    $readQuery = $conn->prepare("UPDATE notifications SET is_read = ? WHERE notificationID = ?");
    $readQuery->bind_param("ii", $readNo, $notificationID);
    if ($readQuery->execute()) {
        if ($readQuery->affected_rows > 0) {
            echo "Marked as read";
        } else {
            echo "Already marked or invalid ID";
        }
    } else {
        echo "Query failed";
    }
}
