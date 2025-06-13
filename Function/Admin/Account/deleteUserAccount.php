<?php

require '../../../Config/dbcon.php';

session_start();

//Di s`ya totally aalisin sa users pero deleted na yung magiging status n`ya
if (isset($_POST['yesDelete'])) {
    $selectedUserID = mysqli_real_escape_string($conn, $_POST['selectedUserID']);

    if ($selectedUserID !== "") {
        $selectQuery = "SELECT * FROM users WHERE userID = '$selectedUserID'";
        $result = mysqli_query($conn, $selectQuery);
        if (mysqli_num_rows($result) > 0) {
            $updateStatus = "UPDATE users SET
            userStatusID = '4' WHERE userID = '$selectedUserID'";
            $result = mysqli_query($conn, $updateStatus);
            if ($result) {
                header("Location: ../../../Pages/Admin/Account/userManagement.php?status=deleted");
                exit;
            } else {
                header("Location: ../../../Pages/Admin/Account/userManagement.php?status=failed");
                exit;
            }
        }
    } else {
        header("Location: ../../../Pages/Admin/Account/userManagement.php?status=null");
        exit;
    }
}
