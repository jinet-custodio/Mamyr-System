<?php

require '../../Config/dbcon.php';
session_start();

//Approve Button is Click
if (isset($_POST['approveBtn'])) {
    $_SESSION['partnerID'] = mysqli_real_escape_string($conn, $_POST['partnerID']);
    $partnerStatus = mysqli_real_escape_string($conn, $_POST['partnerStatus']);
    $partnerUserID = mysqli_real_escape_string($conn, $_POST['partnerUserID']);
    date_default_timezone_set('Asia/Manila');
    $startDate = date('Y-m-d');

    $newPartnerStatus = 2;

    $partnerID = $_SESSION['partnerID'];

    $query = $conn->prepare("SELECT * FROM partnership
    WHERE partnershipID = ? AND partnerStatus = ?");
    $query->bind_param("ii", $partnerID, $partnerStatus);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $updateStatus = $conn->prepare("UPDATE partnership 
        SET partnerStatus = ?, startDate = ?
        WHERE partnershipID = ?");
        $updateStatus->bind_param("isi", $newPartnerStatus, $startDate, $partnerID);

        $newUserRole = 2;
        $updateRole = $conn->prepare("UPDATE users 
        SET userRole = ?
        WHERE userID = ?");
        $updateRole->bind_param("ii", $newUserRole, $partnerUserID);

        if ($updateStatus->execute() && $updateRole->execute()) {
            // $_SESSION['success-partnership'] = 'Request Approved Successfully';
            header('Location: ../../Pages/Admin/displayPartnership.php?action=approved');
            unset($_SESSION['partnerID']);
            exit();
        } else {
            $_SESSION['partnerID'] =  $partnerID;
            // $_SESSION['error-partnership'] = 'The request could not be approved. Please try again later.';
            header('Location: ../../Pages/Admin/partnership.php?container=4&action=failed1');
            exit();
        }
    } else {
        $_SESSION['partnerID'] =  $partnerID;
        // $_SESSION['error-partnership'] = 'The request could not be approved. Please try again later.';
        header('Location: ../../Pages/Admin/partnership.php?container=4&action=failed');
        exit();
    }
} else {
    $_SESSION['partnerID'] =  $partnerID;
    // $_SESSION['error-partnership'] = 'The request could not be approved. Please try again later.';
    header('Location: ../../Pages/Admin/partnership.php?container=4&action=failed');
    exit();
}


//Decline Button is Click
if (isset($_POST['declineBtn'])) {
    $_SESSION['partnerID'] = mysqli_real_escape_string($conn, $_POST['partnerID']);
    $partnerStatus = mysqli_real_escape_string($conn, $_POST['partnerStatus']);
    $rejectionReason = mysqli_real_escape_string($conn, $_POST['rejectionReason']);
    $partnerUserID = mysqli_real_escape_string($conn, $_POST['partnerUserID']);
    date_default_timezone_set('Asia/Manila');
    // $endDate = date('Y-m-d');

    $newPartnerStatus = 3;

    $partnerID = $_SESSION['partnerID'];

    $query = $conn->prepare("SELECT * FROM partnership 
    WHERE partnershipID = ? AND partnerStatus = ?");
    $query->bind_param("ii", $partnerID, $partnerStatus);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {

        if ($rejectionReason !== "") {
            $receiver = 'Partner';
            $message = $rejectionReason;
            $bookingID = Null;
        } else {
            //pakipalitan si gpt gumawa
            $receiver = 'Partner';
            $message = "Thank you for reaching out and considering our venue for your project. We’re currently being selective with partnerships to ensure alignment with our brand and guest experience. At this time, we won’t be moving forward with this opportunity, but we truly appreciate your interest and wish you all the best.";
            $bookingID = Null;
        }

        $updateStatus = $conn->prepare("UPDATE partnership 
        SET partnerStatus = ?
        WHERE partnershipID = ?");
        $updateStatus->bind_param("ii", $newPartnerStatus, $partnerID);

        $insertNotif = $conn->prepare("INSERT INTO notification(partnershipID, userID, message, bookingID, receiver)
        VALUES(?,?,?,?,?)");
        $insertNotif->bind_param("iisis", $partnerID,  $partnerUserID, $message, $bookingID, $receiver);


        if ($updateStatus->execute() && $insertNotif->execute()) {
            // $_SESSION['success-partnership'] = 'The request has been declined successfully.';
            header('Location: ../../Pages/Admin/displayPartnership.php?action=rejected');
            unset($_SESSION['partnerID']);
            exit();
        } else {
            // $_SESSION['error-partnership'] = 'The request could not be declined. Please try again later.';
            header('Location: ../../Pages/Admin/partnership.php?container=4&action=failed2');
            unset($_SESSION['partnerID']);
            exit();
        }
    }
}
