<?php

require '../../Config/dbcon.php';

header('Content-Type: application/json');

if (isset($_GET['id']) && isset($_GET['receiver'])) {

    $userID = (int) $_GET['id'];
    $receiver = strtolower(trim($_GET['receiver']));
    $isRead = 1;

    if ($receiver === 'admin') {

        $markAsRead = $conn->prepare("
            UPDATE notification 
            SET is_read = ? 
            WHERE receiver = ?
        ");
        $markAsRead->bind_param('is', $isRead, $receiver);
    } else {

        $markAsRead = $conn->prepare("
            UPDATE notification 
            SET is_read = ? 
            WHERE receiverID = ?
        ");
        $markAsRead->bind_param('ii', $isRead, $userID);
    }

    if (!$markAsRead->execute()) {
        error_log("Mark as read failed: " . $markAsRead->error);

        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
        exit();
    }

    $markAsRead->close();

    echo json_encode([
        'success' => true,
        'message' => 'All notifications marked as read'
    ]);
    exit();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Missing parameters'
    ]);
    exit();
}
