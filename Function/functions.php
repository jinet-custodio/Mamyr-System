<?php

date_default_timezone_set('Asia/Manila');

function resetExpiredOTPs($conn)
{
    $query = "UPDATE users SET userOTP = NULL, OTP_expiration_at = NULL 
              WHERE OTP_expiration_at IS NOT NULL AND OTP_expiration_at < NOW() - INTERVAL 5 MINUTE";

    $otpResetQuery = $conn->prepare($query);

    $otpResetQuery->execute();
    if (!$otpResetQuery->execute()) {
        echo "Error updating OTPs: " . $conn->error;
    }
    $otpResetQuery->close();
}

function changeToExpiredStatus($conn)
{
    date_default_timezone_set('Asia/Manila');

    $dateNow = date('Y-m-d H:i:s');
    $pendingStatusID = 1;
    $expiredStatusID = 6;

    // Select all bookings that have ended and are still pending
    $selectBookings = $conn->prepare("SELECT bookingID FROM bookings WHERE endDate < ? AND bookingStatus = ?");
    $selectBookings->bind_param("si", $dateNow, $pendingStatusID);
    $selectBookings->execute();
    $result = $selectBookings->get_result();

    if ($result->num_rows > 0) {
        $updateQuery = $conn->prepare("UPDATE bookings SET bookingStatus = ? WHERE bookingID = ?");
        $count = 0;
        while ($row = $result->fetch_assoc()) {
            $bookingID = (int)$row['bookingID'];
            $updateQuery->bind_param("ii", $expiredStatusID, $bookingID);
            if ($updateQuery->execute()) {
                $count += $updateQuery->affected_rows;
            }
        }

        $updateQuery->close();
        echo $count . " booking(s) marked as expired.";
    }

    $selectBookings->close();
}

function changeToDoneStatus($conn)
{
    date_default_timezone_set('Asia/Manila');

    $dateNow = date('Y-m-d H:i:s');
    $approvedStatusID = 2;
    $doneStatusID = 5;
    $fullyPaidID = 3;

    //Select all confirmed bookings that have ended and is fully paid
    $selectConfirmedBookings = $conn->prepare("SELECT cb.*, b.endDate, b.bookingID FROM confirmedBookings cb 
                            JOIN bookings b ON cb.bookingID = b.bookingID WHERE B.endDate < ? AND paymentStatus = ? AND paymentApprovalStatus = ?");
    $selectConfirmedBookings->bind_param("sii", $dateNow, $fullyPaidID, $approvedStatusID);
    $selectConfirmedBookings->execute();
    $result = $selectConfirmedBookings->get_result();
    if ($result->num_rows > 0) {
        $updateQuery = $conn->prepare("UPDATE confirmedBookings SET paymentApprovalStatus = ? WHERE bookingID = ?");
        $counter = 0;
        while ($row = $result->fetch_assoc()) {
            $bookingID = (int)$row['bookingID'];
            $updateQuery->bind_param("ii", $doneStatusID, $bookingID);
            if ($updateQuery->execute()) {
                $counter += $updateQuery->affected_rows;
            }
        }
        $updateQuery->close();
        echo $counter . " booking(s) marked as done.";
    }

    $selectConfirmedBookings->close();
}

function getStatuses($conn, $statusID)
{

    $getStatus = $conn->prepare("SELECT * FROM statuses WHERE statusID = ?");
    $getStatus->bind_param("i", $statusID);
    $getStatus->execute();
    $getStatusResult = $getStatus->get_result();
    if ($getStatusResult->num_rows > 0) {
        $row = $getStatusResult->fetch_assoc();
        return [
            'statusID' => $row['statusID'],
            'statusName' => $row['statusName']
        ];
    } else {
        return NULL;
    }
}

function getPaymentStatus($conn, $paymentStatusID)
{

    $getPaymentStatus = $conn->prepare("SELECT * FROM bookingPaymentStatus WHERE paymentStatusID = ?");
    $getPaymentStatus->bind_param("i", $paymentStatusID);
    $getPaymentStatus->execute();
    $getPaymentStatusResult = $getPaymentStatus->get_result();
    if ($getPaymentStatusResult->num_rows > 0) {
        $row = $getPaymentStatusResult->fetch_assoc();
        return [
            'paymentStatusID' => $row['paymentStatusID'],
            'paymentStatusName' => $row['statusName']
        ];
    } else {
        return NULL;
    }
}


function addToAdminTable($conn)
{
    $adminID = 3;
    $position = 'Administrator';

    // Fetch users with userRole = 3
    $getAdminQuery = $conn->prepare("SELECT userID FROM users WHERE userRole = ?");
    $getAdminQuery->bind_param('i', $adminID);
    $getAdminQuery->execute();
    $adminQueryResult = $getAdminQuery->get_result();

    if ($adminQueryResult->num_rows > 0) {
        while ($row = $adminQueryResult->fetch_assoc()) {
            $storedUserID = intval($row['userID']);


            $selectUsers = $conn->prepare("SELECT userID FROM admins WHERE userID = ?");
            $selectUsers->bind_param('i', $storedUserID);
            $selectUsers->execute();
            $result = $selectUsers->get_result();

            if ($result->num_rows < 1) {

                $insertAdminQuery = $conn->prepare("INSERT INTO admins (userID, position) VALUES (?, ?)");
                $insertAdminQuery->bind_param('is', $storedUserID, $position);
                if (!$insertAdminQuery->execute()) {
                    echo "Error inserting admin: " . $insertAdminQuery->error;
                }
                $insertAdminQuery->close();
            }
            $result->free();
            $selectUsers->close();
        }
    }


    $adminQueryResult->free();
    $getAdminQuery->close();
}


function autoChangeStatus($conn)
{
    $occupiedStatusID = 2;
    $availableStatusID = 1;


    $fetchUnavailableServiceDatesQuery = $conn->prepare("SELECT * FROM serviceunavailabledates WHERE unavailableStartDate <= NOW() AND unavailableEndDate >=  NOW()");
    // $fetchUnavailableServiceDatesQuery->bind_param('ss', $today, $today);
    $fetchUnavailableServiceDatesQuery->execute();
    $result = $fetchUnavailableServiceDatesQuery->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $resortServiceID = intval($row['resortServiceID']);
            $partnershipServiceID = intval($row['partnershipServiceID']);

            if ($resortServiceID  > 0) {
                $updateStatus = $conn->prepare("UPDATE resortAmenities SET RSAvailabilityID = ? WHERE resortServiceID = ? AND RSAvailabilityID = ?");
                $updateStatus->bind_param('iii', $occupiedStatusID, $resortServiceID, $availableStatusID);
                $updateStatus->execute();
                $updateStatus->close();
            }

            if ($partnershipServiceID > 0) {
                $updateStatus = $conn->prepare("UPDATE partnershipServices SET PSAvailabilityID = ? WHERE partnershipServiceID = ? AND PSAvailabilityID = ?");
                $updateStatus->bind_param('iii', $occupiedStatusID, $partnershipServiceID, $availableStatusID);
                $updateStatus->execute();
                $updateStatus->close();
            }
        }
    }


    $fetchUnavailableServiceDatesQuery = $conn->prepare("SELECT * FROM serviceunavailabledates WHERE unavailableStartDate > NOW() OR unavailableEndDate < NOW()");
    $fetchUnavailableServiceDatesQuery->execute();
    $result = $fetchUnavailableServiceDatesQuery->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $resortServiceID = intval($row['resortServiceID']);
            $partnershipServiceID = intval($row['partnershipServiceID']);

            if (!empty($resortServiceID)) {
                $updateStatus = $conn->prepare("UPDATE resortAmenities SET RSAvailabilityID = ? WHERE resortServiceID = ? AND RSAvailabilityID = ?");
                $updateStatus->bind_param('iii', $availableStatusID, $resortServiceID, $occupiedStatusID);
                $updateStatus->execute();
                $updateStatus->close();
            }

            if (!empty($partnershipServiceID)) {

                $updateStatus = $conn->prepare("UPDATE partnershipServices SET PSAvailabilityID = ? WHERE partnershipServiceID = ? AND PSAvailabilityID = ?");
                $updateStatus->bind_param('iii', $availableStatusID, $partnershipServiceID, $occupiedStatusID);
                $updateStatus->execute();
                $updateStatus->close();
            }
        }
    }
    $result->free();
    $fetchUnavailableServiceDatesQuery->close();
}
