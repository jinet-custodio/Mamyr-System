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

</body>

</html>