<?php


require '../../../Config/dbcon.php';

session_start();

//Change Account Details
if (isset($_POST['saveChanges'])) {
    $userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $userRole = mysqli_real_escape_string($conn, $_POST['userRole']);
    $fullName = mysqli_real_escape_string($conn, $_POST['fullName']);
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $nameParts = array_values(array_filter(explode(" ", trim($fullName))));
    $numParts = count($nameParts);
    $firstName = '';
    $middleInitial = NULL;
    $lastName = '';

    if ($numParts == 2) {
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';
    } elseif ($numParts == 3) {
        $firstName = $nameParts[0];
        $lastName = $nameParts[2];

        if (substr($nameParts[1], -1) === '.' && strlen($nameParts[1]) <= 3) {
            $middleInitial = $nameParts[1];
        } else {
            $middleInitial = $nameParts[1];
        }
    } else {
        $firstName = $nameParts[0] . ' ' . $nameParts[1];
        $lastName = $nameParts[$numParts - 1];

        $middleInitials = [];
        for ($i = 2; $i < $numParts - 1; $i++) {
            $middle = trim($nameParts[$i]);
            if (substr($middle, -1) === '.' && strlen($middle) <= 3) {
                $middleInitials[] = $middle;
            } else {
                $middleInitials[] = $middle;
            }
        }

        $middleInitial = implode(' ', $middleInitials);
        if ($middleInitial === "") {
            $middleInitial = NULL;
        }
    }

    if (!empty($birthday) && strtotime($birthday)) {
        $birthDate = date('Y-m-d', strtotime($birthday));
    } else {
        $birthDate = NULL;
    }

    $updateUser = $conn->prepare("UPDATE users SET 
    firstName = ?, 
    middleInitial = ?,  
    lastName = ?,
    userAddress =?,
    phoneNumber = ?, 
    birthDate = ?   
    WHERE userID =? AND userRole = ?");

    $updateUser->bind_param(
        "ssssssii",
        $firstName,
        $middleInitial,
        $lastName,
        $address,
        $phoneNumber,
        $birthDate,
        $userID,
        $userRole
    );
    if ($updateUser->execute()) {
        header("Location: ../../../Pages/Customer/Account/account.php?message=success-change");
        exit;
    } else {
        header("Location: ../../../Pages/Customer/Account/account.php?message=error-change");
        exit;
    }
} else {
    header("Location: ../../../Pages/Customer/Account/account.php");
    exit;
}
