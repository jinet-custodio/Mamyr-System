<?php

require '../../Config/dbcon.php';


if (isset($_GET['id'])) {
    $userID = (int) $_GET['id'];
    $isRead = true;
    $markAsRead = $conn->prepare("UPDATE notification SET is_read = ? WHERE receiverID = ?");
    $markAsRead->bind_param('ii', $isRead, $userID);

    if (!$markAsRead->execute()) {
        error_log("Execution of marking all as read failed. " . $markAsRead->error);
        echo json_encode([
            'success' => false,
            'message' => 'Error'
        ]);
        exit();
    }

    $markAsRead->close();

    echo json_encode([
        'success' => true,
        'message' => 'All notification marked as read'
    ]);
    exit();
}
