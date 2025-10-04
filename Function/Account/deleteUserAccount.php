<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';

session_start();

//* Di s`ya totally aalisin sa users pero deleted na yung magiging status n`ya

if (isset($_POST['yesDelete'])) {
    $selectedUserID = (int) $_POST['selectedUserID'];
    echo $selectedUserID;
    if ($selectedUserID !== "") {
        $selectedUserQuery = $conn->prepare("SELECT * FROM user WHERE userID = ?");
        $selectedUserQuery->bind_param('i', $selectedUserID);
        $selectedUserQuery->execute();
        $result = $selectedUserQuery->get_result();
        if ($result->num_rows > 0) {
            $storedData = $result->fetch_assoc();
            $isDeleted = true;
            $dateDeleted = date('Y-m-d');
            $anonymousEmail = 'deletedAt_' . bin2hex(random_bytes(4)) . '@gmail.com';
            $deletedID = 4;
            $firstName = 'Deleted';
            $lastName = 'User';
            $password = password_hash('deletedAccount', PASSWORD_DEFAULT);
            $deleteQuery = $conn->prepare("UPDATE user SET firstName = ?, lastName = ?, middleInitial = NULL, email = ?, phoneNumber = NULL, birthDate = NULL, isDeleted = ?, dateDeleted = ?, userStatusID = ?, password = ?  WHERE userID = ?");
            $deleteQuery->bind_param("sssisisi", $firstName, $lastName,  $anonymousEmail, $isDeleted, $dateDeleted, $deletedID, $password, $selectedUserID);
            if ($deleteQuery->execute()) {
                header("Location: ../../Pages/Account/userManagement.php?status=accountDeleted");
                exit;
            } else {
                header("Location: ../../Pages/Account/userManagement.php?status=failed");
                exit;
            }
            echo 'here3';
        }
        echo 'here2';
    } else {
        echo 'here1';
        header("Location: ../../Pages/Account/userManagement.php?status=null");
        exit;
    }
} else {
    echo 'here';
}
