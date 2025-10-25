<?php
ini_set('log_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);


require '../../Config/dbcon.php';
session_start();

function getMessageReceiver($userRoleID)
{
    switch ($userRoleID) {
        case 1:
            $receiver = 'Customer';
            break;
        case 2:
            $receiver = 'Partner';
            break;
        case 3:
            $receiver = 'Admin';
            break;
        default:
            $receiver = 'Customer';
    }
    return $receiver;
}

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

// print_r('ID: '  . $userID);
// print_r('Role: ' . $userRole);

if (isset($_POST['approveBtn'])) {
    $bookingID = (int) $_POST['bookingID'];
    $guestID = (int) $_POST['guestID'];
    $guestRoleID = (int) $_POST['guestRole'];
    $eventType = mysqli_real_escape_string($conn, $_POST['eventType']);

    $rawEventDate = mysqli_real_escape_string($conn, $_POST['eventDate']);
    $eventDuration = mysqli_real_escape_string($conn, $_POST['eventDuration']);

    $timeRangeClean = preg_replace('/\(.+\)/', '',  $eventDuration);
    list($startTime, $endTime) = array_map('trim', explode('-', $timeRangeClean));

    $startDateTime = new DateTime("$rawEventDate $startTime");
    $endDateTime = new DateTime("$rawEventDate $endTime");

    $unavailableStartDate = $startDateTime->format('Y-m-d H:i:s');
    $unavailableEndDate = $endDateTime->format('Y-m-d H:i:s');

    try {
        $approveStatusID = 2;
        $updateStatusBooking = $conn->prepare("UPDATE `businesspartneravailedservice` SET `approvalStatus`= ? WHERE `bookingID`= ?");
        $updateStatusBooking->bind_param("ii", $approveStatusID, $bookingID);

        if (!$updateStatusBooking->execute()) {
            throw new Exception("Failed executing updating status query. Error => " . $updateStatusBooking->error);
        }


        $selectApproved = $conn->prepare("SELECT partnershipServiceID FROM businesspartneravailedservice WHERE bookingID = ? AND approvalStatus = ?");
        $selectApproved->bind_param("ii", $bookingID, $approveStatusID);
        $selectApproved->execute();
        $result = $selectApproved->get_result();

        if ($row = $result->fetch_assoc()) {
            $partnershipServiceID = $row['partnershipServiceID'];
        }


        $insertIntoUnavailable = $conn->prepare("INSERT INTO `serviceunavailabledate`(`partnershipServiceID`, `unavailableStartDate`, `unavailableEndDate`) VALUES (?,?,?)");
        $insertIntoUnavailable->bind_param('iss', $partnershipServiceID, $unavailableStartDate, $unavailableEndDate);

        if (!$insertIntoUnavailable->execute()) {
            throw new Exception("Failed inserting to unavailable date query. Error => " . $insertIntoUnavailable->error);
        }

        $receiver = getMessageReceiver($guestRoleID);
        $message = "Good news! Your additional service has been approved for your upcoming " . $eventType;
        $notificationQuery = $conn->prepare("INSERT INTO `notification`(`bookingID`, `senderID`, `message`, `receiver`, `receiverID`) VALUES (?,?,?,?,?)");
        $notificationQuery->bind_param("iissi", $bookingID, $userID, $message, $receiver, $guestID);

        if (!$notificationQuery->execute()) {
            throw new Exception("Failed inserting notification query. Error => " . $notificationQuery->error);
        }
        header('Location: ../../../../Pages/Account/bpBookings.php?action=approve-success');
    } catch (Exception $e) {
        error_log("Error-> " . $e->getMessage());
        header('Location: ../../../../Pages/Account/bpBookings.php?action=approve-failed');
    }
} elseif (isset($_POST['rejectBtn'])) {
    $bookingID = (int) $_POST['bookingID'];
    $guestID = (int) $_POST['guestID'];
    $guestRoleID = (int) $_POST['guestRole'];
    $eventType = mysqli_real_escape_string($conn, $_POST['eventType']);
    try {
        $rejectStatusID = 5;
        $updateStatusBooking = $conn->prepare("UPDATE `businesspartneravailedservice` SET `approvalStatus`= ? WHERE `bookingID`= ?");
        $updateStatusBooking->bind_param("ii", $rejectStatusID, $bookingID);

        if (!$updateStatusBooking->execute()) {
            throw new Exception("Failed executing updating status query. Error => " . $updateStatusBooking->error);
        }

        $receiver = getMessageReceiver($guestRoleID);
        $message = "We're sorry to inform you that your additional service has been rejected for your upcoming " . $eventType;
        $notificationQuery = $conn->prepare("INSERT INTO `notification`(`bookingID`, `senderID`, `message`, `receiver`, `receiverID`) VALUES (?,?,?,?,?)");
        $notificationQuery->bind_param("iissi", $bookingID, $userID, $message, $receiver, $guestID);

        if (!$notificationQuery->execute()) {
            throw new Exception("Failed inserting notification query. Error => " . $notificationQuery->error);
        }
        header('Location: ../../../../Pages/Account/bpBookings.php?action=reject-success');
    } catch (Exception $e) {
        error_log("Error-> " . $e->getMessage());
        header('Location: ../../../../Pages/Account/bpBookings.php?action=reject-failed');
    }
}
