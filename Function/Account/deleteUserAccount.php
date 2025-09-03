<?php

require '../../Config/dbcon.php';

session_start();

//Di s`ya totally aalisin sa users pero deleted na yung magiging status n`ya
if (isset($_POST['yesDelete'])) {
    $selectedUserID = (int) $_POST['selectedUserID'];

    if ($selectedUserID !== "") {
        $selectedUserQuery = $conn->prepare("SELECT * FROM user WHERE userID = ?");
        $selectedUserQuery->bind_param('i', $selectedUserID);
        $selectedUserQuery->execute();
        $result = $selectedUserQuery->get_result();
        if ($result->num_rows > 0) {
            $storedData = $result->fetch_assoc();
            $deletedID = 4;
            $deleteQuery = $conn->prepare("UPDATE user SET userStatusID = ? WHERE userID = ?");
            $deleteQuery->bind_param("iis", $deletedID, $selectedUserID);
            if ($deleteQuery->execute()) {
                header("Location: ../../Pages/Account/userManagement.php?status=deleted");
                exit;
            } else {
                header("Location: ../../Pages/Account/userManagement.php?status=failed");
                exit;
            }
        }
    } else {
        header("Location: ../../Pages/Account/userManagement.php?status=null");
        exit;
    }
}
