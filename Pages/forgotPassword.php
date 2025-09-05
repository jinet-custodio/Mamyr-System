<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../Config/dbcon.php';

if (isset($_SESSION['email'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    $_SESSION['email'] = $email;
} else {
    error_log("No Email in Session");
    $_SESSION['loginError'] = "An error occurred. Please try again.";
    header("Location: register.php");
    exit;
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
    <link rel="stylesheet" href="../Assets/CSS/forgotPassword.css">

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../assets/css/bootstrap.min.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">



    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <div class="container">

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
                        value="<?= htmlspecialchars($email) ?>" required>
                </div>
                <div class="input-box">
                    <input type="password" class="form-control" id="newPassword" name="newPassword"
                        placeholder="New Password" oninput="changePasswordValidation();" required>
                    <i id="togglePassword1" class='bx bxs-hide'></i>
                </div>
                <div class="errorMsg" id="passwordValidation"> </div>
                <div class="input-box">
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                        placeholder="Confirm Password" oninput="changePasswordValidation();" required>
                    <i id="togglePassword2" class='bx bxs-hide'></i>
                </div>
                <div class="errorMsg" id="passwordMatch"></div>

                <button type="submit" class="btn btn-primary" id="changePassword" name="changePassword" disabled>Change Password</button>
            </form>
        </div>
    </div>


    <!-- Bootstrap Script -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>


    <!-- Password Validation Script -->
    <script src="../Assets/JS/passwordValidation.js"></script>


    <!-- Password show/hide(yung eye) JS -->
    <script>
        const passwordField1 = document.getElementById('newPassword');
        const passwordField2 = document.getElementById('confirmPassword');
        const togglePassword1 = document.getElementById('togglePassword1');
        const togglePassword2 = document.getElementById('togglePassword2');

        function togglePasswordVisibility(passwordField, toggleIcon) {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('bxs-hide');
                toggleIcon.classList.add('bx-show-alt');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('bx-show-alt');
                toggleIcon.classList.add('bxs-hide');
            }
        }

        togglePassword1.addEventListener('click', () => {
            togglePasswordVisibility(passwordField1, togglePassword1);
        });

        togglePassword2.addEventListener('click', () => {
            togglePasswordVisibility(passwordField2, togglePassword2);
        });
    </script>

</body>

</html>