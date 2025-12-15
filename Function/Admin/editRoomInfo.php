<?php
require '../../Config/dbcon.php';
session_start();

if (!isset($_POST['editRoom'])) {
    error_log("Invalid access to editRoom");
    exit();
}


$roomID = intval($_POST['roomID']);
$roomName = trim($_POST['roomName']);
$roomStatus = intval($_POST['roomStatus']);
$roomDescription = trim($_POST['roomDescription']);
$roomDuration = trim($_POST['roomDuration']);

$roomRateRaw = $_POST['roomRate'];
$roomRate = floatval(preg_replace('/[^0-9.]/', '', $roomRateRaw));

$roomCapacity = intval(preg_replace('/\D/', '', $_POST['roomCapacity']));
$roomMaxCapacity = intval(preg_replace('/\D/', '', $_POST['roomMaxCapacity']));


$servicePath = __DIR__ . '../../../Assets/Images/Services/Hotel/';

if (!is_dir($servicePath)) {
    mkdir($servicePath, 0755, true);
}

$updateImage = false;
$roomImageName = null;

$_SESSION['roomInfoFormData'] = $_POST;

if (!empty($_FILES['roomImage']) && $_FILES['roomImage']['error'] === UPLOAD_ERR_OK) {

    if ($_FILES['roomImage']['size'] > 64000000) {
        $_SESSION['actionType'] = 'edit';
        $_SESSION['roomID'] = $roomID;
        header("Location: ../../Pages/Admin/roomInfo.php?action=exceedImageLimitSize");
        exit();
    }

    $randomNumber = rand(11, 99);
    $roomImageName = 'Hotel_' . $randomNumber . '_' . basename($_FILES['roomImage']['name']);
    $imageTmpPath = $_FILES['roomImage']['tmp_name'];
    $imageFullPath = $servicePath . $roomImageName;

    if (!move_uploaded_file($imageTmpPath, $imageFullPath)) {
        error_log("Failed to move uploaded file.");
        $_SESSION['actionType'] = 'edit';
        $_SESSION['roomID'] = $roomID;
        header("Location: ../../Pages/Admin/roomInfo.php?action=imageMoveFailed");
        exit();
    }

    $updateImage = true;
}

$conn->begin_transaction();

try {

    if ($updateImage) {
        $updateRoomQuery = $conn->prepare("
            UPDATE resortamenity
            SET 
                RServiceName = ?,
                RSprice = ?,
                RScapacity = ?,
                RSmaxCapacity = ?,
                RSduration = ?,
                RSdescription = ?,
                RSimageData = ?
            WHERE resortServiceID = ?
        ");

        $updateRoomQuery->bind_param(
            "sdiisssi",
            $roomName,
            $roomRate,
            $roomCapacity,
            $roomMaxCapacity,
            $roomDuration,
            $roomDescription,
            $roomImageName,
            $roomID
        );
    } else {
        $updateRoomQuery = $conn->prepare("
            UPDATE resortamenity
            SET 
                RServiceName = ?,
                RSprice = ?,
                RScapacity = ?,
                RSmaxCapacity = ?,
                RSduration = ?,
                RSdescription = ?
            WHERE resortServiceID = ?
        ");

        $updateRoomQuery->bind_param(
            "sdiissi",
            $roomName,
            $roomRate,
            $roomCapacity,
            $roomMaxCapacity,
            $roomDuration,
            $roomDescription,
            $roomID
        );
    }

    $updateRoomQuery->execute();


    $updateAvailabilityQuery = $conn->prepare("
        UPDATE resortamenity
        SET RSAvailabilityID = ?
        WHERE RServiceName = ?
    ");

    $updateAvailabilityQuery->bind_param(
        "is",
        $roomStatus,
        $roomName
    );

    $updateAvailabilityQuery->execute();

    $conn->commit();

    unset($_SESSION['actionType'], $_SESSION['roomID'], $_SESSION['roomInfoFormData']);

    header("Location: ../../Pages/Admin/roomList.php?action=roomUpdated");
    exit();
} catch (Exception $e) {

    $conn->rollback();

    $_SESSION['actionType'] = 'edit';
    $_SESSION['roomID'] = $roomID;

    error_log("Room update failed: " . $e->getMessage());

    header("Location: ../../Pages/Admin/roomInfo.php?action=updateFailed");
    exit();
}
