<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID, userRole FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $_SESSION['userRole'] = $user['userRole'];
    }

    if (!$user) {
        $_SESSION['error'] = 'Account no longer exists';
        session_unset();
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
}

//php for unsetting the roomID variable every time the user leaves the page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsetRoomID'])) {
    unset($_SESSION['roomID']);
    exit(); // No need to render the rest of the page
}

if (isset($_SESSION['actionType'])) {
    $actionType = $_SESSION['actionType'];
} elseif (isset($_POST['actionType'])) {
    $actionType = mysqli_real_escape_string($conn, $_POST['actionType']);
}

if (isset($_SESSION['roomID'])) {
    $_POST['roomID'] = $_SESSION['roomID'];
}



$message = '';
$status = '';

if (isset($_SESSION['error'])) {
    $message = htmlspecialchars(strip_tags($_SESSION['error']));
    $status = 'error';
    unset($_SESSION['error']);
} elseif (isset($_SESSION['success'])) {
    $message = htmlspecialchars(strip_tags($_SESSION['success']));
    $status = 'success';
    unset($_SESSION['success']);
}

switch ($userRole) {
    case 3:
        $role = "Admin";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}


//php for unsetting the roomID variable every time the user leaves the page

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/roomInfo.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="title">
        <a href="roomList.php"><img src="../../Assets/Images/Icon/undo2.png" alt="Back Button"></a>
        <div class="adminTitle">
            Room Information
        </div>
    </div>
    <?php
    $roomID = mysqli_real_escape_string($conn, $_POST['roomID']);
    $actionType = $actionType;
    $availabilityOptions = [];
    $availabilityQuery = $conn->prepare("SELECT availabilityID, availabilityName FROM serviceavailability");
    $availabilityQuery->execute();
    $availabilityQueryResult = $availabilityQuery->get_result();
    while ($row =  $availabilityQueryResult->fetch_assoc()) {
        $availabilityOptions[] = $row;
    }
    $hotelCategoryID = 1;
    $getRoomStatus = $conn->prepare("SELECT rs.*, 
    sa.availabilityName AS roomStatus 
    FROM resortamenity rs 
    LEFT JOIN serviceavailability sa ON rs.RSAvailabilityID = sa.availabilityID 
    WHERE RScategoryID = ? AND resortServiceID = ?");
    $getRoomStatus->bind_param("ii", $hotelCategoryID, $roomID);
    $getRoomStatus->execute();
    $getRoomStatusResult = $getRoomStatus->get_result();
    if ($getRoomStatusResult->num_rows > 0) {
        $roomInfo = $getRoomStatusResult->fetch_array();
    ?>
        <form action="../../Function/Admin/editRoomInfo.php" method="POST" enctype="multipart/form-data"
            class="information">

            <div class="bookInfobox">
                <div class="left-col">
                    <?php
                    // $roomID = mysqli_real_escape_string($conn, $_POST['roomID']);
                    // $_SESSION['roomID'] = $roomID;
                    // $actionType = mysqli_real_escape_string($conn, $_POST['actionType']);

                    $userQuery = $conn->prepare("SELECT 
                                b.*, bs.*, 
                                u.firstName, u.middleInitial, u.lastName, 
                                rs.*, 
                                s.resortServiceID 
                                FROM booking b 
                                LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID                                                                                                          
                                LEFT JOIN service s ON bs.serviceID = s.serviceID
                                LEFT JOIN resortamenity rs ON rs.resortServiceID = s.resortServiceID 
                                LEFT JOIN user u ON u.userID = b.userID 
                                WHERE 
                                rs.RScategoryID = ?
                                AND rs.resortServiceID = ?
                                AND NOW() BETWEEN b.startDate AND b.endDate");
                    $userQuery->bind_param("ii", $hotelCategoryID, $roomID);
                    $userQuery->execute();
                    $userQueryResult = $userQuery->get_result();
                    if ($userQueryResult->num_rows > 0) {
                        $rentor = $userQueryResult->fetch_array();
                        $rentorName = $rentor['firstName'] . " " . $rentor['middleInitial'] . " " .  $rentor['lastName'];
                    ?>
                        <div class="info" id="rentorRow">
                            <label for="rentorName"> Rentor: </label>
                            <input type="text" name="rentorName" class="rentorName form-control" id="rentorName"
                                value="<?= htmlspecialchars($rentorName) ?>" disabled>
                        </div>
                    <?php
                    }
                    ?>
                    <div class="info">
                        <label for="roomName"> Room Name: </label>
                        <input type="text" name="roomName" class="roomName form-control " id="roomName"
                            value="<?= $roomInfo['RServiceName'] ?>">
                    </div>
                    <div class="info">
                        <label for="roomStatus">Status:</label>
                        <select name="roomStatus" id="roomStatus" class="form-control roomStatus">
                            <?php foreach ($availabilityOptions as $option): ?>
                                <option value="<?= $option['availabilityID'] ?>"
                                    <?= ($roomInfo['roomStatus'] === $option['availabilityName']) ? 'selected' : '' ?>>
                                    <?= $option['availabilityName'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="roomID" value="<?= $roomID ?> ">
                    </div>

                    <div class="info">
                        <label for="roomRate"> Rate: </label>
                        <input type="text" name="roomRate" class="roomRate form-control" id="roomRate"
                            value="<?= "₱ " . $roomInfo['RSprice'] ?>">
                    </div>
                    <div class="info">
                        <label for="roomCapacity"> Capacity: </label>
                        <input type="text" name="roomCapacity" class="roomCapacity form-control" id="roomCapacity"
                            value="<?= $roomInfo['RScapacity'] . " pax" ?>">
                    </div>
                    <div class="info">
                        <label for="roomMaxCapacity">Max Capacity: </label>
                        <input type="text" name="roomMaxCapacity" class="roomMaxCapacity form-control" id="roomMaxCapacity"
                            value="<?= $roomInfo['RSmaxCapacity'] . " pax" ?>">
                    </div>
                    <div class="info">
                        <label for="roomDuration">Duration: </label>
                        <input type="text" name="roomDuration" class="roomDuration form-control" id="roomDuration"
                            value="<?= $roomInfo['RSduration'] ?>">
                    </div>
                    <!-- <div class="end">
                        <label for="others"> Others: </label>
                        <input type="text" name="others" style="padding: 0.5vw; font-size: 1.5vw;"
                            class="others form-control" id="others">
                    </div> -->
                </div>
                <!-- <input type="text" name="roomImage" class="roomImage" id="roomImage"> -->
                <?php
                $imgSrc = '../../Assets/Images/Services/Hotel/';
                if (!empty($roomInfo['RSimageData'])) {
                    $image = $imgSrc . $roomInfo['RSimageData'];
                } else {
                    $image = '../../Assets/Image/no-picture.jpg';
                }
                ?>


                <div class="right-col">
                    <div class="end" id="image">
                        <div class="room-image-wrapper">
                            <img src="<?= $image ?>" alt="Room Image" class="room-preview room-image" id="roomImage">
                            <div class="image-overlay" id="image-overlay">Change Image</div>
                            <input type="file" name="roomImage" class="roomImageInput">
                        </div>
                    </div>
                    <div class="end">
                        <label for="roomDescription"> Description: </label>
                        <textarea rows="4" name="roomDescription" class="roomDescription form-control" id="roomDescription"
                            style="padding: 0.5vw; font-size: 1.5vw;"><?= $roomInfo['RSdescription'] ?>
                        </textarea>
                    </div>
                </div>
            </div>
            <div class="buttons" id="buttons">
                <a href="roomList.php" class="cancelBtn btn btn-danger" type="button">Cancel</a>
                <button class="saveBtn btn btn-primary" type="submit" name="editRoom"> Save</button>
            </div>
        </form>
    <?php
    }
    ?>



    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>


    <!-- checks whether the action chosen is view or edit, disables or enables input boxes depending on the result -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const rentorRow = document.getElementById("rentorRow");
            const leftCol = document.querySelector(".left-col");
            const rightCol = document.querySelector(".right-col");
            const actionType = "<?= $actionType ?>";
            console.log(actionType);
            if (!rentorRow) {
                leftCol.style.gridTemplateRows = "repeat(4, 1fr)";
            }

            const inputs = document.querySelectorAll(
                ".left-col input, .left-col select, .left-col textarea, .right-col input, .right-col textarea");
            const overlay = document.querySelector(".image-overlay");
            const rentorName = document.getElementById("rentorName")
            const btns = document.querySelector(".buttons");
            inputs.forEach(input => {
                if (actionType === "view") {
                    input.disabled = true;
                    overlay.style.display = "none";
                    btns.style.display = "none";
                } else {
                    input.disabled = false;
                    rentorName.disabled = true;
                }
            })
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const fileInput = document.querySelector(".roomImageInput");
            const previewImage = document.getElementById("roomImage");

            fileInput.addEventListener("change", function() {
                const file = fileInput.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>


</body>

</html>