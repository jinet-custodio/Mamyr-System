<?php

require '../Config/dbcon.php';

session_start();

$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);

if (isset($_POST['submitPhoneNumber'])) {
    $page = mysqli_real_escape_string($conn, $_POST['page']) ?? 'bookNow.php';
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);

    $checkPhoneNumber = $conn->prepare("SELECT phoneNumber FROM user WHERE userID = ? AND userRole = ?");
    $checkPhoneNumber->bind_param("ii", $userID, $userRole);
    $checkPhoneNumber->execute();
    $checkPhoneNumberResult = $checkPhoneNumber->get_result();
    if ($checkPhoneNumberResult->num_rows > 0) {
        $row = $checkPhoneNumberResult->fetch_assoc();
        $storedNumber = $row['phoneNumber'];


        $updatePhoneNumber = $conn->prepare("UPDATE user SET phoneNumber = ? WHERE userID = ? AND userRole = ?");
        $updatePhoneNumber->bind_param("sii", $phoneNumber, $userID, $userRole);
        if ($updatePhoneNumber->execute()) {
            header("Location: ../Pages/Customer/$page?action=phoneAdded");
        }
    } else {
        header("Location: ../Pages/Customer/$page?action=errorAdding");
    }
}
