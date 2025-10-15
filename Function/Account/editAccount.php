<?php


require '../../Config/dbcon.php';

session_start();

//Change Account Details
if (isset($_POST['saveChanges'])) {
    $userRole = (int) $_SESSION['userRole'];
    $userID = (int) $_SESSION['userID'];
    $partnershipID = (int) $_POST['partnershipID'] ?? 0;
    $fullName = mysqli_real_escape_string($conn, $_POST['fullName']);
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    // $birthDate = date('Y-m-d', strtotime($birthday));
    // var_dump($birthday);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $companyName = !empty($_POST['companyName'])
        ? mysqli_real_escape_string($conn, $_POST['companyName'])
        : '';

    $businessEmail = !empty($_POST['businessEmail'])
        ? mysqli_real_escape_string($conn, $_POST['businessEmail'])
        : '';

    $partnerAddress = !empty($_POST['partnerAddress'])
        ? mysqli_real_escape_string($conn, $_POST['partnerAddress'])
        : '';

    $nameParts = explode(" ", trim($fullName));
    $numParts = count($nameParts);

    $fullName = preg_replace('/\s+/', ' ', trim($fullName));
    $nameParts = explode(' ', $fullName);
    $numParts = count($nameParts);

    $firstName = '';
    $middleInitial = null;
    $lastName = '';

    if ($numParts === 1) {
        $firstName = $nameParts[0];
    } elseif ($numParts === 2) {
        $firstName = $nameParts[0];
        $lastName = $nameParts[1];
    } elseif ($numParts >= 3) {

        $lastName = array_pop($nameParts);


        $possibleMiddle = end($nameParts);
        if (preg_match('/^[A-Z]\.$/i', $possibleMiddle)) {
            $middleInitial = array_pop($nameParts);
        }

        $firstName = implode(' ', $nameParts);
    }


    if (!empty($birthday) && strtotime($birthday)) {
        $birthDate = date('Y-m-d', strtotime($birthday));
    } else {
        $birthDate = NULL;
    }



    $updateUser = $conn->prepare("UPDATE user SET 
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


        if (!empty($partnershipID)) {
            $updatePartner = $conn->prepare("UPDATE `partnership` SET `companyName`=?,`businessEmail`=?,`partnerAddress`=? WHERE `partnershipID`= ? AND `userID`= ?");
            $updatePartner->bind_param("sssii", $companyName, $businessEmail, $partnerAddress, $partnershipID, $userID);

            if (!$updatePartner->execute()) {
                header("Location: ../../Pages/Account/account.php?message=partner-failed");
                exit;
            }
        }

        header("Location: ../../Pages/Account/account.php?message=success-change");
        exit;
    } else {
        error_log("User update failed: " . $updateUser->error);
        // $_SESSION['update-error'] = "Failed to update account details.";
        header("Location: ../../Pages/Account/account.php?message=error-change");
        exit;
    }
} else {
    header("Location: ../../Pages/Account/account.php");
    exit;
}
