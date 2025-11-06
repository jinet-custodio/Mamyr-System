<?php

require '../../Config/dbcon.php';

if (isset($_GET['id']) && isset($_GET['role'])) {

    $userID = (int) $_GET['id'];
    $receiver = htmlspecialchars($_GET['role']);

    if (strtolower($receiver) === 'admin') {
        $getNotifications = $conn->prepare("SELECT * FROM notification 
            WHERE receiverID IS NULL 
            AND receiver = ? 
            AND is_read = 0
            ORDER BY created_at DESC
        ");
        $getNotifications->bind_param("s", $receiver);
    } else {
        $getNotifications = $conn->prepare("SELECT * FROM notification 
            WHERE receiverID = ? 
            AND receiver = ? 
            AND is_read = 0 
            ORDER BY created_at DESC
        ");
        $getNotifications->bind_param("is", $userID, $receiver);
    }

    $getNotifications->execute();
    $result = $getNotifications->get_result();
    $notifications = [];
    $counter = 0;
    if ($result->num_rows > 0) {
        while ($notification = $result->fetch_assoc()) {
            $date = date('M. d, Y • g:i A', strtotime($notification['created_at']));
            $notifications[] = [
                'notificationID' => $notification['notificationID'],
                'message' => $notification['message'],
                'createdAt' => $date
            ];
            $counter++;
        }
    }

    echo json_encode([
        'notifications' => $notifications,
        'count' => $counter
    ]);
}
