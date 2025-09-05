<?php

require '../../Config/dbcon.php';

session_start();


if (isset($_POST['createAccount'])) {

    $role = mysqli_real_escape_string($conn, $_POST['roleSelect']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $middleInitial = mysqli_real_escape_string($conn, $_POST['middleInitial']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    $userStatusID = 2;
    if (isset($_FILES['profile-image']) && $_FILES['profile-image']['size'] > 0) {
        $image_data = file_get_contents($_FILES['profile-image']['tmp_name']);
    } else {
        $defaultImage = '../../Assets/Images/defaultProfile.png';
        $image_data = file_exists($defaultImage) ? file_get_contents($defaultImage) : NULL;
    }


    $birthday = $birthday !== "" ? $birthday : NULL;
    $middleInitial = $middleInitial !== "" ? $middleInitial : NULL;


    if ($role === "admin") {
        $userRole = 3;
    } elseif ($role === "customer") {
        $userRole = 1;
    } elseif ($role === "partner") {
        $userRole = 2;
    } else {
        header("Location: ../../Pages/Account/addAccount.php?status=invalidRole");
        exit;
    }

    $addUser = $conn->prepare("INSERT INTO user(firstName, middleInitial, lastName, email, userAddress, phoneNumber, birthdate, userProfile, password, userRole, userStatusID) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $addUser->bind_param("sssssssssii", $firstName, $middleInitial, $lastName, $email, $address, $phoneNumber, $birthday, $image_data, $hashPassword, $userRole, $userStatusID);
    if ($addUser->execute()) {
        header("Location: ../../Pages/Account/userManagement.php?status=added");
        exit;
    } else {
        echo "Error: " . $addUser->error;
    }
    $addUser->close();
}
