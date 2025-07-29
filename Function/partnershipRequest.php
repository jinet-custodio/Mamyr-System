<?php

require '../Config/dbcon.php';
session_start();



if (isset($_POST['submit_request'])) {
    $userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $userRole = mysqli_real_escape_string($conn, $_POST['userRole']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $middleInitial = mysqli_real_escape_string($conn, $_POST['middleInitial']) ?? NULL;
    $businessEmail = mysqli_real_escape_string($conn, $_POST['businessEmail']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
    $companyName = mysqli_real_escape_string($conn, $_POST['companyName']);
    $partnerType = mysqli_real_escape_string($conn, $_POST['partnerType']);

    $streetAddress = mysqli_real_escape_string($conn, $_POST['streetAddress']);
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $zip = mysqli_real_escape_string($conn, $_POST['zip']);
    if ($barangay === '') {
        $partnerAddress = $barangay . ", " . $city . ", " . $province . ", " . $zip;
    } else {
        $partnerAddress = $streetAddress . " " . $barangay . ", " . $city . ", " . $province . ", " . $zip;
    }

    $proofLink = mysqli_real_escape_string($conn, $_POST['proofLink']);

    //Get the partner information based on the business Email
    $partnerQuery = $conn->prepare("SELECT * from partnerships WHERE businessEmail = ?");
    $partnerQuery->bind_param("s", $businessEmail);
    $partnerQuery->execute();
    $partnerResult = $partnerQuery->get_result();
    $_SESSION['partnerData'] = $_POST;
    if ($partnerResult->num_rows > 0) {
        header("Location: ../Pages/partnerApplication.php?result=emailExist");
        $partnerResult->close();
    } else {
        //Get the users information based on the userID
        // $partnerQuery = $conn->prepare("SELECT * from users WHERE userID = ?");
        // $partnerQuery->bind_param("i", $userID);
        // $partnerQuery->execute();
        // $partnerResult = $partnerQuery->get_result();
        // if ($partnerResult->num_rows > 0) {
        //     $data = $partnerResult->fetch_assoc();


        $updateQuery = $conn->prepare("UPDATE users SET firstName = ?, middleInitial = ?, lastName = ?, phoneNumber = ? WHERE userID = ?");
        $updateQuery->bind_param("ssssi", $firstName, $middleInitial, $lastName, $phoneNumber, $userID);
        if ($updateQuery->execute()) {
            $_SESSION['success'] = "Profile updated successfully.";
        } else {
            $_SESSION['message'] = "Failed to update profile.";
        }
        //Select partnerType ID
        $partnerTypeQuery = $conn->prepare("SELECT * FROM partnershipTypes WHERE partnerType = ?");
        $partnerTypeQuery->bind_param("s", $partnerType);
        $partnerTypeQuery->execute();
        $partnerTypeResult = $partnerTypeQuery->get_result();
        if ($partnerTypeResult->num_rows > 0) {
            $data = $partnerTypeResult->fetch_assoc();
            $partnerTypeID = $data['partnerTypeID'];
        }

        if ($userRole ==  1) {
            $insertQuery = $conn->prepare("INSERT INTO partnerships(userID, partnerAddress, companyName, partnerType, businessEmail, documentLink) VALUES (?,?,?,?,?,?)");
            $insertQuery->bind_param("ississ", $userID, $partnerAddress, $companyName, $partnerTypeID, $businessEmail, $proofLink);
            if ($insertQuery->execute()) {
                $_SESSION['success'] = 'Partnership Request Sent Successfully';
                header("Location: ../Pages/Customer/partnerApplication.php");
                exit;
            } else {
                $_SESSION['message'] = 'Partnership Request Failed';
                header("Location: ../Pages/Customer/partnerApplication.php");
                exit;
            }
        } elseif ($userTypeID == 2) {
            $_SESSION['message'] = 'You are already a partner of Mamyr Resort and Events Place. 
                You cannot file for another partnership.';
            header("Location: ../Pages/Customer/partnerApplication.php");
            exit;
        }
    }
}
