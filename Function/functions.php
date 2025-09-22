<?php

date_default_timezone_set('Asia/Manila');

//*Function for removing otps
function resetExpiredOTPs($conn)
{
    $query = "UPDATE user
              SET userOTP = NULL, OTP_expiration_at = NULL 
              WHERE OTP_expiration_at IS NOT NULL 
                AND OTP_expiration_at < NOW() - INTERVAL 5 MINUTE";

    $otpReset = $conn->prepare($query);

    if (!$otpReset) {
        error_log("Failed to prepare statement: " . $conn->error);
        return false;
    }

    if (!$otpReset->execute()) {
        error_log("Failed to execute statement: " . $otpReset->error);
        $otpReset->close();
        return false;
    }

    $affectedRows = $otpReset->affected_rows;
    $otpReset->close();

    return $affectedRows;
}


//*Function for changing the status into expired when still pending and passed the booking date
function changeToExpiredStatus($conn)
{
    date_default_timezone_set('Asia/Manila');

    $dateNow = date('Y-m-d H:i:s');
    $pendingStatusID = 1;
    $expiredStatusID = 6;

    // Select all bookings that have ended and are still pending
    $selectBookings = $conn->prepare("SELECT bookingID FROM booking WHERE endDate < ? AND bookingStatus = ?");
    $selectBookings->bind_param("si", $dateNow, $pendingStatusID);
    $selectBookings->execute();
    $result = $selectBookings->get_result();

    if ($result->num_rows > 0) {
        $updateQuery = $conn->prepare("UPDATE booking SET bookingStatus = ? WHERE bookingID = ?");
        $count = 0;
        while ($row = $result->fetch_assoc()) {
            $bookingID = (int)$row['bookingID'];
            $updateQuery->bind_param("ii", $expiredStatusID, $bookingID);
            if ($updateQuery->execute()) {
                $count += $updateQuery->affected_rows;
            }
        }

        $updateQuery->close();
        return $count . " booking(s) marked as expired.";
    }
    $result->free();
    $selectBookings->close();
}

//*Function for all the fully paid and finished event status will changed to done.
function changeToDoneStatus($conn)
{
    date_default_timezone_set('Asia/Manila');

    $dateNow = date('Y-m-d H:i:s');
    $approvedStatusID = 2;
    $doneStatusID = 5;
    $fullyPaidID = 3;

    //Select all confirmed bookings that have ended and is fully paid
    $selectConfirmedBookings = $conn->prepare("SELECT cb.*, b.endDate, b.bookingID FROM confirmedbooking cb 
                            JOIN booking b ON cb.bookingID = b.bookingID WHERE b.endDate < ? AND paymentStatus = ? AND paymentApprovalStatus = ?");
    $selectConfirmedBookings->bind_param("sii", $dateNow, $fullyPaidID, $approvedStatusID);
    $selectConfirmedBookings->execute();
    $result = $selectConfirmedBookings->get_result();
    if ($result->num_rows > 0) {
        $updateQuery = $conn->prepare("UPDATE confirmedbooking SET paymentApprovalStatus = ? WHERE bookingID = ?");
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

//*Func for getting statuses
function getStatuses($conn, $statusID)
{

    $getStatus = $conn->prepare("SELECT * FROM status WHERE statusID = ?");
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

//*Function for getting the status of payments
function getPaymentStatus($conn, $paymentStatusID)
{

    $getPaymentStatus = $conn->prepare("SELECT * FROM bookingpaymentstatus WHERE paymentStatusID = ?");
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

//Function for adding a user in users table with role "Admin" to the admin table 
function addToAdminTable($conn)
{
    $adminID = 3;
    $position = 'Administrator';

    // Fetch users with userRole = 3
    $getAdminQuery = $conn->prepare("SELECT userID, firstName, middleInitial, lastName FROM user WHERE userRole = ?");
    $getAdminQuery->bind_param('i', $adminID);
    $getAdminQuery->execute();
    $adminQueryResult = $getAdminQuery->get_result();

    if ($adminQueryResult->num_rows > 0) {
        while ($row = $adminQueryResult->fetch_assoc()) {
            $storedUserID = intval($row['userID']);
            $firstName = ucfirst($row['firstName']);
            $middleInitial = ucfirst($row['middleInitial']) . '.' ?? ' ';
            $lastName = ucfirst($row['lastName']);
            $fullName = $firstName . ' ' . $middleInitial . ' ' . $lastName;

            $selectUsers = $conn->prepare("SELECT userID FROM admin WHERE userID = ?");
            $selectUsers->bind_param('i', $storedUserID);
            $selectUsers->execute();
            $result = $selectUsers->get_result();

            if ($result->num_rows < 1) {

                $insertAdminQuery = $conn->prepare("INSERT INTO admin(userID, position, fullName) VALUES (?, ?,?)");
                $insertAdminQuery->bind_param('iss', $storedUserID, $position, $fullName);
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

//Function for everyday status change in resortamenities
function autoChangeStatus($conn)
{
    $occupiedStatusID = 2;
    $availableStatusID = 1;

    // Set status to OCCUPIED
    $fetchUnavailableServiceDatesQuery = $conn->prepare("SELECT * FROM serviceunavailabledate 
        WHERE unavailableStartDate <= NOW() AND unavailableEndDate >= NOW()
    ");
    $fetchUnavailableServiceDatesQuery->execute();
    $result = $fetchUnavailableServiceDatesQuery->get_result();

    while ($row = $result->fetch_assoc()) {
        $resortServiceID = intval($row['resortServiceID']);
        $partnershipServiceID = intval($row['partnershipServiceID']);

        if ($resortServiceID > 0) {

            $updateStatus = $conn->prepare("UPDATE resortamenity 
                SET RSAvailabilityID = ? 
                WHERE resortServiceID = ? AND RSAvailabilityID = ?
            ");
            $updateStatus->bind_param('iii', $occupiedStatusID, $resortServiceID, $availableStatusID);
            $updateStatus->execute();
            $updateStatus->close();

            $fetchInfo = $conn->prepare("SELECT RServiceName, RScategoryID FROM resortamenity WHERE resortServiceID = ?");
            $fetchInfo->bind_param('i', $resortServiceID);
            $fetchInfo->execute();
            $infoResult = $fetchInfo->get_result()->fetch_assoc();
            $serviceName = $infoResult['RServiceName'];
            $hotelCategoryID = $infoResult['RScategoryID'];
            $fetchInfo->close();

            $updateRelated = $conn->prepare("UPDATE resortamenity 
                SET RSAvailabilityID = ? 
                WHERE RServiceName = ? AND RScategoryID = ? AND resortServiceID != ? AND RSAvailabilityID = ?
            ");
            $updateRelated->bind_param('isiii', $occupiedStatusID, $serviceName, $hotelCategoryID, $resortServiceID, $availableStatusID);
            $updateRelated->execute();
            $updateRelated->close();
        }

        if ($partnershipServiceID > 0) {
            $updateStatus = $conn->prepare("UPDATE partnershipservice 
                SET PSAvailabilityID = ? 
                WHERE partnershipServiceID = ? AND PSAvailabilityID = ?
            ");
            $updateStatus->bind_param('iii', $occupiedStatusID, $partnershipServiceID, $availableStatusID);
            $updateStatus->execute();
            $updateStatus->close();
        }
    }

    //Set status to AVAILABLE 
    $fetchUnavailableServiceDatesQuery = $conn->prepare("SELECT * FROM serviceunavailabledate 
        WHERE unavailableStartDate > NOW() OR unavailableEndDate < NOW() ");
    $fetchUnavailableServiceDatesQuery->execute();
    $result = $fetchUnavailableServiceDatesQuery->get_result();

    while ($row = $result->fetch_assoc()) {
        $resortServiceID = intval($row['resortServiceID']);
        $partnershipServiceID = intval($row['partnershipServiceID']);

        if (!empty($resortServiceID)) {
            $updateStatus = $conn->prepare("UPDATE resortamenity 
                SET RSAvailabilityID = ? 
                WHERE resortServiceID = ? AND RSAvailabilityID = ?
            ");
            $updateStatus->bind_param('iii', $availableStatusID, $resortServiceID, $occupiedStatusID);
            $updateStatus->execute();
            $updateStatus->close();

            $fetchInfo = $conn->prepare("SELECT RServiceName, RScategoryID FROM resortamenity WHERE resortServiceID = ?");
            $fetchInfo->bind_param('i', $resortServiceID);
            $fetchInfo->execute();
            $infoResult = $fetchInfo->get_result()->fetch_assoc();
            $serviceName = $infoResult['RServiceName'];
            $hotelCategoryID = $infoResult['RScategoryID'];
            $fetchInfo->close();

            $updateRelated = $conn->prepare("UPDATE resortamenity
                SET RSAvailabilityID = ? 
                WHERE RServiceName = ? AND RScategoryID = ? AND resortServiceID != ? AND RSAvailabilityID = ?
            ");
            $updateRelated->bind_param('isiii', $availableStatusID, $serviceName, $hotelCategoryID, $resortServiceID, $occupiedStatusID);
            $updateRelated->execute();
            $updateRelated->close();
        }

        if (!empty($partnershipServiceID)) {

            $updateStatus = $conn->prepare("UPDATE partnershipservice 
                SET PSAvailabilityID = ? 
                WHERE partnershipServiceID = ? AND PSAvailabilityID = ?
            ");
            $updateStatus->bind_param('iii', $availableStatusID, $partnershipServiceID, $occupiedStatusID);
            $updateStatus->execute();
            $updateStatus->close();
        }
    }

    $result->free();
    $fetchUnavailableServiceDatesQuery->close();
}


//Function for generating otps
function generateOTP($length)
{
    $otp = str_pad(random_int(100000, 999999), $length, '0', STR_PAD_LEFT);
    return $otp;
}

//Function for getting user role name
function getUserStatus($conn, $userStatusID)
{

    $getStatus = $conn->prepare("SELECT * FROM userstatus WHERE userStatusID = ?");
    $getStatus->bind_param("i", $userStatusID);
    $getStatus->execute();
    $getStatusResult = $getStatus->get_result();
    if ($getStatusResult->num_rows > 0) {
        $row = $getStatusResult->fetch_assoc();
        return [
            'userStatusID' => $row['userStatusID'],
            'userStatusName' => $row['statusName']
        ];
    } else {
        return NULL;
    }
}

//Function for getting user status name
function getUserRole($conn, $roleID)
{

    $getStatus = $conn->prepare("SELECT * FROM usertype WHERE userTypeID = ?");
    $getStatus->bind_param("i", $roleID);
    $getStatus->execute();
    $getStatusResult = $getStatus->get_result();
    if ($getStatusResult->num_rows > 0) {
        $row = $getStatusResult->fetch_assoc();
        return [
            'userTypeID' => $row['userTypeID'],
            'userTypeName' => $row['typeName']
        ];
    } else {
        return NULL;
    }
}

//Function for getting availability status
function getAvailabilityStatus($conn, $availabilityID)
{
    $query = $conn->prepare("SELECT * FROM `serviceavailability` WHERE availabilityID = ?");
    $query->bind_param("i", $availabilityID);
    if (!$query->execute()) {
        error_log("Failed executing query:" . $query->error);
    }

    $result = $query->get_result();
    if (!$result->num_rows === 0) {
        return null;
    }
    $row = $result->fetch_assoc();
    return [
        'availabilityID' => $row['availabilityID'],
        'availabilityName' => $row['availabilityName']
    ];
}
