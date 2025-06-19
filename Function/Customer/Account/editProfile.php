<?php

require '../../../Config/dbcon.php';

session_start();

//Change Profile Picture
if (isset($_POST['changePfpBtn'])) {
    $userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $userRole = mysqli_real_escape_string($conn, $_POST['userRole']);
    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['size'] > 0) {
        $imageData = file_get_contents($_FILES['profilePic']['tmp_name']);
        $imageData = mysqli_real_escape_string($conn, $imageData);
    } else {
        $defaultImage = '../../../Assets/Images/defaultProfile.png';
        if (file_exists($defaultImage)) {
            $imageData = file_get_contents('../../../Assets/Images/defaultProfile.png');
            $imageData = mysqli_real_escape_string($conn, $imageData);
        } else {
            $imageData = NULL;
        }
    }

    if ($imageData !== NULL) {
        $query = "UPDATE users SET userProfile = '$imageData' WHERE userID = '$userID' AND userRole = '$userRole'";

        if (mysqli_query($conn, $query)) {
            header("Location: ../../../Pages/Customer/Account/account.php?message=success-image");
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        header("Location: ../../../Pages/Customer/Account/account.php?message=error-image");
        exit;
    }
} elseif (isset($_POST['cancelPfp'])) {
    header("Location: ../../../Pages/Customer/Account/account.php");
    exit;
}
