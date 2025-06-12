<?php
require '../../Config/dbcon.php';
session_start();

if (isset($_POST['editRoom'])) {
    echo "Edit form detected"; // Debugging statement

    $firstName = "";
    $middleInitial = "";
    $lastName = "";

    $roomID = mysqli_real_escape_string($conn, $_POST['roomID']);
    $roomName = mysqli_real_escape_string($conn, $_POST['roomName']);
    $roomStatus = mysqli_real_escape_string($conn, $_POST['roomStatus']);
    $roomRateRaw = mysqli_real_escape_string($conn, $_POST['roomRate']);      //resolve
    $roomCapacityRaw = mysqli_real_escape_string($conn, $_POST['roomCapacity']);
    $roomDescription = mysqli_real_escape_string($conn, $_POST['roomDescription']);

    //rate and capacity accepts string values, so this will filter only the number inputs of the user
    $roomRate = preg_replace('/[^0-9.]/', '', $roomRateRaw);
    $roomRate = floatval($roomRate);

    $roomCapacity = preg_replace('/\D/', '', $roomCapacityRaw);
    $roomCapacity = intval($roomCapacity);
    // Check if an image was uploaded
    if (!empty($_FILES['roomImage']['tmp_name']) && $_FILES['roomImage']['error'] === 0) {
        echo "Image detected"; // Debugging statement
        $roomImage_name = $_FILES['roomImage']['name'];
        $roomImage_size = $_FILES['roomImage']['size'];
        $roomImage_tmp_name = $_FILES['roomImage']['tmp_name'];

        if ($roomImage_size > 2000000) {
            header("Location: ../Pages/Admin/roomInfo.php?message[]=Image%20is%20too%20large%20(max%202MB)");
            exit(0);
        }

        $imgData = file_get_contents($roomImage_tmp_name);


        $stmt = $conn->prepare("UPDATE resortServices 
    SET RserviceName = ?, RSAvailabilityID = ?, RSprice = ?, RScapacity = ?, RSdescription = ?,RSimageData = ? 
    WHERE resortServiceID = ?");

        $null = NULL;
        $stmt->bind_param("sidisbi", $roomName, $roomStatus, $roomRate, $roomCapacity, $roomDescription, $null, $roomID);
        $stmt->send_long_data(5, $imgData);

        $result = $stmt->execute();
    } else {
        // No image uploaded, just update other fields
        echo "No image uploaded, updating fields"; // Debugging statement
        $query = "UPDATE resortServices 
            SET RserviceName='$roomName', RSAvailabilityID='$roomStatus', RSprice='$roomRate', RScapacity='$roomCapacity' 
            WHERE resortServiceID='$roomID'";
        $result = mysqli_query($conn, $query);
    }

    if ($result) {
        echo "Update successful"; // Debugging statement
        // Set data you want to POST back
        $roomID = htmlspecialchars($roomID, ENT_QUOTES, 'UTF-8');
        $actionType = "edit"; // or whatever context
        echo <<<HTML
    <form id="redirectForm" action="../../Pages/Admin/roomInfo.php" method="POST">
        <input type="hidden" name="roomID" value="$roomID">
        <input type="hidden" name="actionType" value="$actionType">
        <input type="hidden" name="status" value="success">
    </form>
    <script>
        document.getElementById("redirectForm").submit();
    </script>
HTML;
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
