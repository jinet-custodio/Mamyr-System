<?php

date_default_timezone_set('Asia/Manila');


//? Function for removing otps
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


//? Function for generating code
function generateCode($length)
{
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= random_int(0, 9);
    }
    return $otp;
}

//? Function for getting user role name
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

//? Function for getting user status name
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


//? Function for adding a user in users table with role "Admin" to the admin table
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
        $counter = 0;
        while ($row = $adminQueryResult->fetch_assoc()) {
            $storedUserID = intval($row['userID']);
            $firstName = ucfirst($row['firstName']);
            $middleInitial = ucfirst($row['middleInitial'] ?? '') . '.' ?? ' ';
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
                $counter++;
                $insertAdminQuery->close();
            }
            $result->free();
            $selectUsers->close();
        }
    }
    return $counter;

    $adminQueryResult->free();
    $getAdminQuery->close();
}
