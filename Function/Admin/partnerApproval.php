<?php

require '../../Config/dbcon.php';
session_start();

//Approve Button is Click
if (isset($_POST['approveBtn'])) {
    $partnerID = mysqli_real_escape_string($conn, $_POST['partnerID']);
    $partnerStatus = mysqli_real_escape_string($conn, $_POST['partnerStatus']);
    date_default_timezone_set('Asia/Manila');
    $startDate = date('Y-m-d');

    $query = "SELECT * FROM partnerships 
    WHERE partnershipID = '$partnerID' AND status ='$partnerStatus'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $updateStatus = "UPDATE partnerships 
        SET status = 'Approved', startDate = '$startDate'
        WHERE partnershipID ='$partnerID'";
        $result = mysqli_query($conn, $updateStatus);
        if ($result) {
            $_SESSION['success'] = 'Request Approved Successfully';
            header('Location: ../../Pages/Admin/displayPartnership.php');
            exit();
        } else {
            $_SESSION['error'] = 'The request could not be approved. Please try again later.';
            header('Location: ../../Pages/Admin/displayPartnership.php');
            exit();
        }
    }
}


//Decline Button is Click
if (isset($_POST['declineBtn'])) {
    $partnerID = mysqli_real_escape_string($conn, $_POST['partnerID']);
    $partnerStatus = mysqli_real_escape_string($conn, $_POST['partnerStatus']);
    date_default_timezone_set('Asia/Manila');
    $startDate = date('d-m-Y');;

    $query = "SELECT * FROM partnerships 
    WHERE partnershipID = '$partnerID' AND status ='$partnerStatus'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $updateStatus = "UPDATE partnerships 
        SET status = 'Rejected'
        WHERE partnershipID ='$partnerID'";
        $result = mysqli_query($conn, $updateStatus);
        if ($result) {
            $_SESSION['success'] = 'The request has been declined successfully.';
            header('Location: ../../Pages/Admin/displayPartnership.php');
            exit();
        } else {
            $_SESSION['error'] = 'The request could not be declined. Please try again later.';
            header('Location: ../../Pages/Admin/displayPartnership.php');
            exit();
        }
    }
}
