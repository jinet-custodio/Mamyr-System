<?php

require '../Config/dbcon.php';
session_start();

if (isset($_POST['submit_request'])) {
    $userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $userType = mysqli_real_escape_string($conn, $_POST['userType']);
    $middleInitial = mysqli_real_escape_string($conn, $_POST['middleInitial']);
    $businessEmail = mysqli_real_escape_string($conn, $_POST['emailAddress']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
    $companyName = mysqli_real_escape_string($conn, $_POST['companyName']);

    $partnerType = mysqli_real_escape_string($conn, $_POST['partnerType']);
    if ($partnerType === 'other') {
        $partnerType = mysqli_real_escape_string($conn, $_POST['other_input']);
    }

    $streetAddress = mysqli_real_escape_string($conn, $_POST['streetAddress']);
    $address2 = mysqli_real_escape_string($conn, $_POST['address2']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $zip = mysqli_real_escape_string($conn, $_POST['zip']);
    if ($address2 === '') {
        $partnerAddress = $streetAddress . ", " . $city . ", " . $province . ", " . $zip;
    } else {
        $partnerAddress = $streetAddress . " " . $address2 . ", " . $city . ", " . $province . ", " . $zip;
    }

    $documentLink = mysqli_real_escape_string($conn, $_POST['documentLink']);

    $query = "SELECT * from users WHERE userID = '$userID'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        if ($phoneNumber !== '' || $middleInitial !== '') {
            $updateQuery = "UPDATE users SET phoneNumber = '$phoneNumber', middleInitial = '$middleInitial' 
            WHERE userID =' $userID'";
            $result = mysqli_query($conn, $updateQuery);
            if ($result) {
                $_SESSION['success'] = "Updated successfully.";
            } else {
                $_SESSION['message'] = "Failed to update profile.";
            }
        }
        $userType = $data['userTypeID'];
        if ($userType == '1') {
            $insertQuery = "INSERT INTO 
            partnerships(userID, partnerAddress, companyName, partnerType, businessEmail, documentLink) 
            VALUES 
            ('$userID', '$partnerAddress', '$companyName', '$partnerType', '$businessEmail', '$documentLink')";
            $resultInsert = mysqli_query($conn, $insertQuery);
            if ($resultInsert) {
                $_SESSION['success'] = 'Partnership Request Sent Successfully';
                header("Location: ../Pages/Customer/partnerApplication.php");
                exit;
            } else {
                $_SESSION['message'] = 'Partnership Request Failed';
                header("Location: ../Pages/Customer/partnerApplication.php");
                exit;
            }
        } elseif ($userType == '2') {
            $_SESSION['message'] = 'You are already a partner of Mamyr Resort and Events Place. 
        You cannot file for another partnership.';
            header("Location: ../Pages/Customer/partnerApplication.php");
            exit;
        }
    } else {
        echo "<script>alert('Error1');</script>";
    }
} else {
    echo "<script>alert('Error');</script>";
}
