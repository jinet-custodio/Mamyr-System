<?php
session_start();
require '../Config/dbcon.php';
if (isset($_SESSION['formData']['email'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['formData']['email']);
    // echo "Logged-in email: " . htmlspecialchars($email);
} else {
    echo 'No email in session';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/forgotPassword.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <div class="container">

        <div class="form-box forgotPassword">
            <form action="../Function/changePassword.php" method="POST">
                <h1>Forgot Password</h1>
                <div class="input-box">
                    <input type="hidden" class="form-control" id="email" name="email" value="<?= $email ?>" required>
                </div>
                <div class="input-box">
                    <input type="password" class="form-control" id="password" name="newPassword"
                        placeholder="New Password" oninput="checkPassword()" required>
                    <i class='bx bxs-low-vision'></i>
                </div>
                <div class="input-box">
                    <input type="password" class="form-control" id="confirm_password" name="confirmPassword"
                        placeholder="Confirm Password" oninput="checkPasswordMatch()" required>
                    <i class='bx bxs-low-vision'></i>
                </div>

                <button type="submit" class="btn" id="changePassword" name="changePassword">Change Password</button>

                <div class="errorMessageBox">
                    <div class="errorMsg">
                        <?php
                        if (isset($_SESSION['error'])) {
                            echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']);
                        }
                        ?>
                    </div>
                    <div class="errorMsg" id="passwordValidation"></div>
                    <div class="errorMsg" id="passwordMatch"></div>
                </div>
            </form>
        </div>
    </div>
    <script src="../Assets/JS/checkPasswordMatch.js"></script>
    <script src="../Assets/JS/checkPassword.js"></script>
</body>

</html>