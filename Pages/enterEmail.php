<?php
session_start();
require '../Config/dbcon.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/enterEmail.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <div class="container">
        <div class="back-icon-container">
            <a href="../Pages/register.php">
                <img src="../Assets/Images/Icon/arrow.png" alt="Go back" class="backArrow">
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
                    <input type="email" class="form-control" id="email" name="email"
                        placeholder="Enter your email" required>
                </div>
                <button type="submit" class="btn" id="verify_email" name="verify_email">Verify Email</button>

            </form>
        </div>
    </div>
    <!-- Div for loader -->
    <div id="loaderOverlay" style="display: none;">
        <div class="loader"></div>
    </div>

    <!-- Script for loader -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loaderOverlay = document.getElementById('loaderOverlay');
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

        function hideLoader() {
            const overlay = document.getElementById('loaderOverlay');
            if (overlay) overlay.style.display = 'none';
        }

        // Hide loader on normal load
        window.addEventListener('load', hideLoader);

        // Hide loader on back/forward navigation (from browser cache)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                hideLoader();
            }
        });
    </script>

</body>

</html>