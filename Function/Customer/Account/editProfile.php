<?php

require '../../../Config/dbcon.php';

session_start();

//Change Profile Picture
if (isset($_POST['changePfpBtn'])) {
    $userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $userRole = mysqli_real_escape_string($conn, $_POST['userRole']);
    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['size'] > 0) {
        $imageData = file_get_contents($_FILES['profilePic']['tmp_name']);
    } else {
        $defaultImage = '../../../Assets/Images/defaultProfile.png';
        if (file_exists($defaultImage)) {
            $imageData = file_get_contents('../../../Assets/Images/defaultProfile.png');
        } else {
            $imageData = NULL;
        }
    }

    if ($imageData !== NULL) {
        $query = $conn->prepare("UPDATE users SET userProfile = ? WHERE userID = ? AND userRole = ?");
        $query->bind_param("sii", $imageData, $userID, $userRole);

        if ($query->execute()) {
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
