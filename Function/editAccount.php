<?php
require '../config/dbcon.php';
session_start();

// Enable error reporting (only for development)
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Reached before form processing"; // Debugging statement

if (isset($_POST['edit'])) {
    echo "Edit form detected"; // Debugging statement

    $firstName = "";
    $middleInitial = "";
    $lastName = "";

    $userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $userType = mysqli_real_escape_string($conn, $_POST['userType']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $nameParts = explode(" ", $name);
    $birthDate = mysqli_real_escape_string($conn, $_POST['birthDate']);
    $userAddress = mysqli_real_escape_string($conn, $_POST['userAddress']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);

    // Parse full name
    if (count($nameParts) == 1) {
        $firstName = $nameParts[0];
    } elseif (count($nameParts) == 2) {
        $firstName = $nameParts[0];
        $lastName = $nameParts[1];
    } elseif (count($nameParts) > 2) {
        $firstName = $nameParts[0];
        $lastName = array_pop($nameParts);
        $middleInitial = substr($nameParts[1], 0, 1);
    }

    // Check if an image was uploaded
    if (!empty($_FILES['userProfile']['tmp_name']) && $_FILES['userProfile']['error'] === 0) {
        echo "Image detected"; // Debugging statement
        $userProfile_name = $_FILES['userProfile']['name'];
        $userProfile_size = $_FILES['userProfile']['size'];
        $userProfile_tmp_name = $_FILES['userProfile']['tmp_name'];

        if ($userProfile_size > 2000000) {
            header("Location: ../Pages/account.php?message[]=Image%20is%20too%20large%20(max%202MB)");
            exit(0);
        }

        $imgData = file_get_contents($userProfile_tmp_name);

        $stmt = $conn->prepare("UPDATE users 
            SET firstName = ?, middleInitial = ?, lastName = ?, birthDate = ?, email = ?, phoneNumber = ?, userAddress = ?, userProfile = ? 
            WHERE userID = ?");

        $null = NULL; // Placeholder for BLOB
        $stmt->bind_param("sssssssbi", $firstName, $middleInitial, $lastName, $birthDate, $email, $phoneNumber, $userAddress, $null, $userID);
        $stmt->send_long_data(7, $imgData); // index 7 = userProfile
        $result = $stmt->execute();
    } else {
        // No image uploaded, just update other fields
        echo "No image uploaded, updating fields"; // Debugging statement
        $query = "UPDATE users 
            SET firstName='$firstName', middleInitial='$middleInitial', lastName='$lastName', birthDate='$birthDate', 
                email='$email', phoneNumber='$phoneNumber', userAddress='$userAddress' 
            WHERE userID='$userID'";
        $result = mysqli_query($conn, $query);
    }

    if ($result) {
        echo "Update successful"; // Debugging statement
        header("Location: ../Pages/account.php?id=$userID&role=$userType&message[]=Changes%20Applied!");
        exit(0);
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "No form submitted"; // Debugging statement
}
