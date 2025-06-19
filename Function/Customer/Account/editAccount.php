<?php


require '../../../Config/dbcon.php';

session_start();

//Change Account Details
if (isset($_POST['saveChanges'])) {
    $userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $userRole = mysqli_real_escape_string($conn, $_POST['userRole']);
    $fullName = mysqli_real_escape_string($conn, $_POST['fullName']);
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $birthDate = date('Y-m-d', strtotime($birthday));
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $nameParts = explode(" ", trim($fullName));
    $numParts = count($nameParts);

    if ($numParts <= 2) {
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';
    } elseif ($numParts == 3) {
        $firstName = $nameParts[0] . ' ' . $nameParts[1];
        $lastName = $nameParts[2];
    } elseif ($numParts >= 4) {
        // First 2 words = first name
        $firstName = $nameParts[0] . ' ' . $nameParts[1];

        // Last word = last name
        $lastName = $nameParts[$numParts - 1];

        // Middle parts between index 2 and numParts - 1
        $middleInitials = [];
        for ($i = 2; $i < $numParts - 1; $i++) {
            $middle = trim($nameParts[$i]);
            if (substr($middle, -1) === '.' && strlen($middle) <= 3) {
                $middleInitials[] = $middle;
            } else {
                // Also add any middle names that are not initials
                $middleInitials[] = $middle;
            }
        }

        $middleInitial = implode(' ', $middleInitials);
        if ($middleInitial === "") {
            $middleInitial = NULL;
        }
    }

    $query = "UPDATE users SET 
    firstName = '$firstName', 
    middleInitial = '$middleInitial',  
    lastName = '$lastName',
    userAddress ='$address',
    phoneNumber = '$phoneNumber', 
    birthDate = '$birthDate' 
    WHERE userID ='$userID' AND userRole = '$userRole'
    ";
    $result = mysqli_query($conn, $query);
    if ($result) {
        header("Location: ../../../Pages/Customer/Account/account.php?message=success-change");
        exit;
    } else {
        header("Location: ../../../Pages/Customer/Account/account.php?message=error-change");
        exit;
    }
} elseif (isset($_POST['cancelChanges'])) {
    header("Location: ../../../Pages/Customer/Account/account.php");
    exit;
} else {
    header("Location: ../../../Pages/Customer/Account/account.php");
    exit;
}
