<?php
function getNotification($conn, $userID, $receiver)
{
    $getNotifications = $conn->prepare("SELECT * FROM notification WHERE (receiverID = ? OR receiver = ?) AND is_read = 0");
    $getNotifications->bind_param("is", $userID, $receiver);
    $getNotifications->execute();
    $result = $getNotifications->get_result();

    $notificationsArray = [];
    $color = [];
    $notificationIDs = [];
    $counter = 0;

    if ($result->num_rows > 0) {
        while ($notification = $result->fetch_assoc()) {
            $notificationIDs[] = $notification['notificationID'];
            $notificationsArray[] = $notification['message'];
            $counter++;

            if ($notification['is_read'] == 0) {
                $color[] = "rgba(247, 213, 176, 0.5)";
            } else {
                $color[] = "white";
            }
        }
    }

    return [
        'count' => $counter,
        'messages' => $notificationsArray,
        'colors' => $color,
        'ids' => $notificationIDs,
    ];
}
