<?php

require '../Config/dbcon.php';
session_start();
require '../Function/OTPdeletion.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up</title>
    <link rel="shortcut icon" href="../Assets/Images/Icon/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="../Assets/CSS/index.css">
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div class="container">
        <!-- Login Form -->
        <div class="form-box login">
            <form action="../Function/register.php" method="POST">
                <h1>Login</h1>
                <div class="input-box">
                    <input type="text" class="form-control" id="login_email" name="login_email"
                        value="<?php echo isset($_SESSION['formData']['email']) ? htmlspecialchars(trim($_SESSION['formData']['email'])) : ''; ?>"
                        placeholder="Email" required>
                    <i class='bx bxs-envelope'></i>
                </div>
                <div class="input-box">
                    <input type="password" class="form-control" id="login_password" name="login_password"
                        oninput="checkLoginPassword();" placeholder="Password" required>
                    <i id="togglePassword" class='bx bxs-hide'></i>
                </div>
                <div class="forgot-link">
                    <a href="../Pages/enterEmail.php">Forgot Password?</a>
                </div>
                <button type="submit" class="btn" id="login" name="login">Login</button>

                <div class="errorMessageBox">
                    <div class="errorMsg">
                        <div class="login-error" id="passwordLValidation"></div>
                    </div>
                    <p class="errorMsg">
                        <?php
                        if (isset($_SESSION['error'])) {
                            echo htmlspecialchars(strip_tags($_SESSION['error']));
                            unset($_SESSION['error']);
                        }

                        if (isset($_GET['session']) && $_GET['session'] === 'expired') {
                            echo '<div class="alert alert-warning" style="margin-top: 1rem;">Session Expired</div>';
                        }

                        ?>
                    </p>
                    <p class="successMsg">
                        <?php
                        if (isset($_SESSION['success'])) {
                            echo htmlspecialchars(strip_tags($_SESSION['success']));
                            unset($_SESSION['success']);
                        }
                        ?>
                    </p>
                </div>
            </form>
        </div>
        <div class="form-box register">
            <form action="../Function/register.php" method="POST">
                <h1>Sign Up</h1>
                <div class="fullName">
                    <div class="input-box">
                        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name"
                            value="<?php echo isset($_SESSION['formData']['firstName']) ? htmlspecialchars(trim($_SESSION['formData']['firstName'])) : ''; ?>"
                            required>
                        <i class='bx bxs-user-circle'></i>
                    </div>
                    <div class="input-box">
                        <input type="text" class="form-control" id="middleInitial" name="middleInitial"
                            placeholder="M.I. (Optional)"
                            value="<?php echo isset($_SESSION['formData']['middleInitial']) ? htmlspecialchars(trim($_SESSION['formData']['middleInitial'])) : ''; ?>">
                        <i class='bx bxs-user-circle'></i>
                    </div>

                </div>
                <div class="userInfo">
                    <div class="input-box">
                        <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name"
                            value="<?php echo isset($_SESSION['formData']['lastName']) ? htmlspecialchars(trim($_SESSION['formData']['lastName'])) : ''; ?>"
                            required>
                        <i class='bx bxs-user-circle'></i>
                    </div>
                    <div class="input-box">
                        <input type="text" class="form-control" id="userAddress" name="userAddress"
                            placeholder="Address"
                            value="<?php echo isset($_SESSION['formData']['userAddress']) ? htmlspecialchars(trim($_SESSION['formData']['userAddress'])) : ''; ?>"
                            required>
                        <i class='bx bxs-user'></i>
                    </div>
                    <div class="input-box">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email"
                            value="<?php echo isset($_SESSION['formData']['email']) ? htmlspecialchars(trim($_SESSION['formData']['email'])) : ''; ?>"
                            required>
                        <i class='bx bxs-envelope'></i>
                    </div>

                    <div class="passwordContainer">
                        <div class="input-box">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Password" oninput="checkPassword();" required>
                            <i id="togglePassword1" class='bx bxs-hide'></i>
                        </div>
                        <div class=" input-box">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                placeholder="Confirm Password" oninput="checkPasswordMatch();" required>
                            <i id="togglePassword2" class='bx bxs-hide'></i>
                        </div>
                    </div>
                </div>

                <label for="terms">
                    <input type="checkbox" id="terms" name="terms" class="terms-checkbox" value="1"
                        onchange="checkBox()"> I agree to the
                    <a href="#" id="open-modal">Terms and Conditions</a>.
                </label><br>
                <button type="submit" class="btn" id="signUp" name="signUp" disabled>Sign Up</button>
            </form>

            <!-- error message -->

            <div class="errorContainer ">
                <div class="userInfoError">
                    <div class="errorMsg">
                        <p class="emailErrorMsg">
                            <?php
                            if (isset($_SESSION['email-message'])) {
                                echo htmlspecialchars($_SESSION['email-message']);
                                unset($_SESSION['email-message']); // aalisin after ma display
                            }
                            ?>
                        </p>
                    </div>

                    <div class="passwordContainerError">
                        <div class="errorMsg">
                            <div class="confirmErrorMsg" id="passwordValidation"></div>
                        </div>
                        <div class="errorMsg">
                            <div class="confirmErrorMsg1" id="passwordMatch"></div>
                        </div>
                        <div class="errorMsg">
                            <div class="confirmErrorMsg text-center" id="termsError"></div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1 class="welcome">Welcome to Mamyr!</h1>
                <p>Don't have an account?</p>
                <button class="btn register-btn">Sign Up</button>

                <div class="back-icon-container-login">
                    <a href="../index.php">
                        <img src="../Assets/Images/Icon/home.png" alt="Go back" class="backArrow">

                    </a>
                </div>

            </div>


            <div class="toggle-panel toggle-right">
                <h1 class="welcome">Welcome Back!</h1>
                <p>Already have an account?</p>
                <button class="btn login-btn">Login</button>

                <div class="back-icon-container-signup">
                    <a href="../index.php">
                        <img src="../Assets/Images/Icon/home.png" alt="Go back" class="backArrow">

                    </a>
                </div>

            </div>
        </div>
    </div>
    <script src="../Assets/JS/checkbox.js"></script>
    <script src="../Assets/JS/checkPasswordMatch.js"></script>
    <script src="../Assets/JS/checkPassword.js"></script>
    <script src="../Assets/JS/checkLoginPassword.js"></script>
    <script>
        const container = document.querySelector('.container');
        const registerBtn = document.querySelector('.register-btn');
        const loginBtn = document.querySelector('.login-btn');

        registerBtn.addEventListener('click', () => {
            container.classList.add('active');
        })

        loginBtn.addEventListener('click', () => {
            container.classList.remove('active');
        })

        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page');

        if (page === 'register') {
            container.classList.add('active');
        } else {
            container.classList.remove('active');
        }
    </script>
    <script>
        const passwordField = document.getElementById('login_password');
        const passwordField1 = document.getElementById('password');
        const passwordField2 = document.getElementById('confirm_password');
        const togglePassword = document.getElementById('togglePassword');
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

        togglePassword.addEventListener('click', () => {
            togglePasswordVisibility(passwordField, togglePassword);
        });

        togglePassword1.addEventListener('click', () => {
            togglePasswordVisibility(passwordField1, togglePassword1);
        });

        togglePassword2.addEventListener('click', () => {
            togglePasswordVisibility(passwordField2, togglePassword2);
        });
    </script>

    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
</body>

</html>