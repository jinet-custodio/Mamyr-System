<?php
require '../../Config/dbcon.php';
session_start();

if (isset($_POST['editRoom'])) {
    $firstName = "";
    $middleInitial = "";
    $lastName = "";

    $roomID = intval($_POST['roomID']);
    $roomName = mysqli_real_escape_string($conn, $_POST['roomName']);
    $roomStatus = mysqli_real_escape_string($conn, $_POST['roomStatus']);
    $roomRateRaw = mysqli_real_escape_string($conn, $_POST['roomRate']);
    $roomCapacityRaw = mysqli_real_escape_string($conn, $_POST['roomCapacity']);
    $roomMaxCapacityRaw = mysqli_real_escape_string($conn, $_POST['roomMaxCapacity']);
    $roomDescription = mysqli_real_escape_string($conn, $_POST['roomDescription']);
    $roomDuration = mysqli_real_escape_string($conn, $_POST['roomDuration']);

    //rate and capacity accepts string values, so this will filter only the number inputs of the user
    $roomRate = preg_replace('/[^0-9.]/', '', $roomRateRaw);
    $roomRate = floatval($roomRate);

    $roomCapacity = preg_replace('/\D/', '', $roomCapacityRaw);
    $roomCapacity = intval($roomCapacity);

    $roomMaxCapacity = preg_replace('/\D/', '', $roomMaxCapacityRaw);
    $roomMaxCapacity = intval($roomCapacity);

    $servicePath = __DIR__ . '../../../Assets/Images/Services/Hotel/';

    if (!is_dir($servicePath)) {
        mkdir($servicePath, 0755, true);
    }
    $updateImage = false;
    $_SESSION['roomInfoFormData'] = $_POST;
    if (!empty($_FILES['roomImage']) && $_FILES['roomImage']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['roomImage']['size'] < 64000000) {
            $randomNumber = rand(11, 99);

            $roomImageName = 'Hotel_' . $randomNumber . '_' . $_FILES['roomImage']['name'];
            $roomImagePath = $_FILES['roomImage']['tmp_name'];
            $imagePath = $servicePath . $roomImageName;

            if (!move_uploaded_file($roomImagePath, $imagePath)) {
                error_log("Failed to move uploaded file to: $imagePath");
                $_SESSION['actionType'] = 'edit';
                $_SESSION['roomID'] = $roomID;
                header("Location: ../../Pages/Admin/roomInfo.php?action=imageMoveFailed");
                exit();
            } else {
                move_uploaded_file($roomImagePath, $imagePath);
                $updateImage = true;
            }
        } else {
            $_SESSION['actionType'] = 'edit';
            $_SESSION['roomID'] = $roomID;
            header("Location: ../../Pages/Admin/roomInfo.php?action=exceedImageLimitSize");
            exit();
        }
    }

    if ($updateImage) {
        $updateHotelQuery = $conn->prepare("UPDATE `resortamenity` SET `RServiceName`= ?,`RSprice`= ?,`RScapacity`= ?,`RSmaxCapacity`= ?,`RSduration`= ?,`RSdescription`=?,`RSimageData`= ?,`RSAvailabilityID`= ? WHERE resortServiceID = ?");
        $updateHotelQuery->bind_param("sdiisssii", $roomName, $roomRate, $roomCapacity, $roomMaxCapacity, $roomDuration, $roomDescription, $roomImageName, $roomStatus, $roomID);
    } else {
        $updateHotelQuery = $conn->prepare("UPDATE `resortamenity` SET `RServiceName`= ?,`RSprice`= ?,`RScapacity`= ?,`RSmaxCapacity`= ?,`RSduration`= ?,`RSdescription`=?,`RSAvailabilityID`= ? WHERE resortServiceID = ?");
        $updateHotelQuery->bind_param("sdiissii", $roomName, $roomRate, $roomCapacity, $roomMaxCapacity, $roomDuration, $roomDescription,  $roomStatus, $roomID);
    }

    if ($updateHotelQuery->execute()) {
        unset($_SESSION['actionType']);
        unset($_SESSION['roomID']);
        header("Location: ../../Pages/Admin/roomList.php?action=roomUpdated");
        exit();
    } else {
        $_SESSION['actionType'] = 'edit';
        $_SESSION['roomID'] = $roomID;
        error_log("Error: " . $updateHotelQuery->error);

        header("Location: ../../Pages/Admin/roomInfo.php?action=updateFailed");
        exit();
    }
    exit;
} else {
    error_log("Error: " . mysqli_error($conn));
}
