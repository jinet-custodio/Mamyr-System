<?php
session_start();
require '../Config/dbcon.php';
if (isset($_SESSION['formData']['email'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['formData']['email']);
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
                    <div class="errorMsg" id="passwordValidation"></div>
                    <div class="errorMsg" id="passwordMatch"></div>
                </div>
                <div class="input-box">
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?= $email ?>" required>
                </div>
                <div class="input-box">
                    <input type="password" class="form-control" id="newPassword" name="newPassword"
                        placeholder="New Password" oninput="checkPasswordModal()" required>
                    <i id="togglePassword1" class='bx bxs-hide'></i>
                </div>
                <div class="input-box">
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                        placeholder="Confirm Password" oninput="checkPasswordMatchModal()" required>
                    <i id="togglePassword2" class='bx bxs-hide'></i>
                </div>

                <button type="submit" class="btn" id="changePassword" name="changePassword">Change Password</button>
            </form>
        </div>
    </div>


    <script src="../Assets/JS/checkPasswordMatch.js"></script>
    <script src="../Assets/JS/checkPassword.js"></script>

    <script>
        const passwordField1 = document.getElementById('password');
        const passwordField2 = document.getElementById('confirm_password');
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