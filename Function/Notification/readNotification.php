<?php

require '../../Config/dbcon.php';


if (isset($_GET['id'])) {
    $notificationID = (int) $_GET['id'];
    $isRead = true;
    $markAsRead = $conn->prepare("UPDATE notification SET is_read = ? WHERE notificationID = ?");
    $markAsRead->bind_param('ii', $isRead,  $notificationID);

    if (!$markAsRead->execute()) {
        error_log("Execution of marking a notification as read failed. " . $markAsRead->error);
        echo json_encode([
            'success' => false,
            'message' => 'Error'
        ]);
        exit();
    }

    $markAsRead->close();

    echo json_encode([
        'success' => true,
        'message' => 'A notification marked as read'
    ]);
    exit();
}
