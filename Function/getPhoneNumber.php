<?php

require '../Config/dbcon.php';

session_start();

$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);

if (isset($_POST['submitPhoneNumber'])) {

    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);

    $checkPhoneNumber = $conn->prepare("SELECT phoneNumber FROM users WHERE userID = ? AND userRole = ?");
    $checkPhoneNumber->bind_param("ii", $userID, $userRole);
    $checkPhoneNumber->execute();
    $checkPhoneNumberResult = $checkPhoneNumber->get_result();
    if ($checkPhoneNumberResult->num_rows > 0) {
        $row = $checkPhoneNumberResult->fetch_assoc();
        $storedNumber = $row['phoneNumber'];


        $updatePhoneNumber = $conn->prepare("UPDATE users SET phoneNumber = ? WHERE userID = ? AND userRole = ?");
        $updatePhoneNumber->bind_param("sii", $phoneNumber, $userID, $userRole);
        if ($updatePhoneNumber->execute()) {
            header("Location: ../Pages/Customer/bookNow.php?action=bookNow");
        }
    } else {
        header("Location: ../Pages/Customer/bookNow.php?action=bookNow");
    }
}
