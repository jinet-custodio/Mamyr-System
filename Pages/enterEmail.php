<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../Config/dbcon.php';
//for setting image paths in 'include' statements
$baseURL = '..';

if (isset($_SESSION['email'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    $_SESSION['email'] = $email;
}

if (isset($_SESSION['action'])) {
    $_SESSION['action'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="shortcut icon" href="../Assets/Images/Icon/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="../Assets/CSS/enterEmail.css">
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <div class="container">
        <div class="back-icon-container">
            <a href="../Pages/register.php">
                <img src="../Assets/Images/Icon/arrowBtnBlue.png" alt="Go back" class="backArrow">
            </a>
        </div>
        <div class="form-box forgotPassword">
            <form action="../Function/forgotPassword.php" method="POST">
                <h1 class="container-title">Forgot Password</h1>
                <div class="errorMessageBox">
                    <div class="errorMsg">
                        <?php
                        if (isset($_SESSION['error'])) {
                            echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']);
                        }
                        ?>
                    </div>
                </div>
                <div class="input-box">
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo !empty($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '' ?>"
                        placeholder="Enter your email" required>
                </div>
                <button type="submit" class="btn btn-primary" id="verify_email" name="verify_email">Verify Email</button>

            </form>
        </div>
    </div>

    <?php include 'Customer/loader.php'; ?>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sweetalert Message -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const action = params.get('action');
        const time = params.get('time');

        const email = document.getElementById('email');

        switch (action) {
            case 'hasOTP':
                Swal.fire({
                    icon: 'info',
                    title: 'Wait a minute',
                    html: `An OTP was sent to <strong> ${email.value} </strong> a few minutes ago. Please wait <strong> ${time} minute/s </strong> before requesting another one. You can still enter the OTP you received earlier.`,
                    showCancelButton: true,
                    cancelButtonText: 'Cancel',
                    confirmButtonText: 'Okay',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'verify_email.php';
                    } else {
                        Swal.close();
                    }
                })
                break;
        }
    </script>

    <!-- Script for loader -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const emailInput = document.getElementById('email');
            const submitBtn = document.getElementById('verify_email');

            // Disable the button initially
            submitBtn.disabled = true;

            // Function to validate email
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Listen for input changes
            emailInput.addEventListener('input', function() {
                if (isValidEmail(emailInput.value)) {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
            });

            // Checks if the email box is not empty before triggering loader
            form.addEventListener('submit', function(e) {
                if (!isValidEmail(emailInput.value)) {
                    e.preventDefault();
                    loaderOverlay.style.display = 'none';
                    return;
                }
                loaderOverlay.style.display = 'flex';
            });

        });
    </script>

</body>

</html>