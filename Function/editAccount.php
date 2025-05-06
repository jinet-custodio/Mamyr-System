<?php
require '../config/dbcon.php';
session_start();

if (isset($_POST['edit'])) {
    echo "Edit form detected"; // Debugging statement

    $firstName = "";
    $middleInitial = "";
    $lastName = "";

    $userID = mysqli_real_escape_string($conn, $_POST['userID']);
    $userType = mysqli_real_escape_string($conn, $_POST['userType']);
    $fullName = mysqli_real_escape_string($conn, $_POST['name']);
    $birthDate = mysqli_real_escape_string($conn, $_POST['birthDate']);
    $userAddress = mysqli_real_escape_string($conn, $_POST['userAddress']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);

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

        // Last 2 words = last name
        $lastName = $nameParts[$numParts - 2] . ' ' . $nameParts[$numParts - 1];

        // Middle parts between index 2 and numParts - 2
        $middleInitials = [];
        for ($i = 2; $i < $numParts - 2; $i++) {
            $middle = trim($nameParts[$i]);
            if (substr($middle, -1) === '.' && strlen($middle) <= 3) { // e.g., C.
                $middleInitials[] = $middle;
            }
        }

        $middleInitial = implode(' ', $middleInitials);
    }

    echo "Parsed:<br>";
    echo "First: $firstName<br>";
    echo "Middle: $middleInitial<br>";
    echo "Last: $lastName<br>";
    // Check if an image was uploaded
    if (!empty($_FILES['userProfile']['tmp_name']) && $_FILES['userProfile']['error'] === 0) {
        echo "Image detected"; // Debugging statement
        $userProfile_name = $_FILES['userProfile']['name'];
        $userProfile_size = $_FILES['userProfile']['size'];
        $userProfile_tmp_name = $_FILES['userProfile']['tmp_name'];

        if ($userProfile_size > 2000000) {
            header("Location: ../Pages/Customer/account.php?message[]=Image%20is%20too%20large%20(max%202MB)");
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
        header("Location: ../Pages/Customer/account.php?");
        exit(0);
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "No form submitted"; // Debugging statement
}
