<?php

require '../../Config/dbcon.php';

session_start();

//Change Profile Picture
if (isset($_POST['changePfpBtn'])) {
    $userRole = (int) $_SESSION['userRole'];
    $userID = (int) $_SESSION['userID'];
    $imageMaxSize = 5 * 1024 * 1024; // 5 MB max
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    $_SESSION['userID'] = $userID;
    $_SESSION['userRole'] = $userRole;
    if (isset($_FILES['profilePic']) && is_uploaded_file($_FILES['profilePic']['tmp_name'])) {
        $imageData = file_get_contents($_FILES['profilePic']['tmp_name']);
        $imageName = $_FILES['profilePic']['name'];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $imageSize - $_FILES['profilePic']['size'];
        if (!in_array($imageExt, $allowedExt)) {
            header("Location: ../../../../Pages/Account/account.php?message=extNotAllowed");
            exit();
        }

        if ($imageSize > $imageMaxSize) {
            header("Location: ../../../../Pages/Account/account.php?message=sizeExceed");
            exit();
        }
    } else {
        header("Location: ../../../../Pages/Account/account.php?message=noUploadedImage");
        exit();
    }

    if ($imageData !== NULL) {
        $query = $conn->prepare("UPDATE user SET userProfile = ? WHERE userID = ? AND userRole = ?");
        $query->bind_param("sii", $imageData, $userID, $userRole);

        if ($query->execute()) {
            header("Location: ../../Pages/Account/account.php?message=success-image");
            exit;
        } else {
            error_log("Error: " . mysqli_error($conn));
        }
    } else {
        header("Location: ../../Pages/Account/account.php?message=error-image");
        exit;
    }
} elseif (isset($_POST['cancelPfp'])) {
    header("Location: ../../Pages/Account/account.php");
    exit;
}
