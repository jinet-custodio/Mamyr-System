<?php

require '../Config/dbcon.php';
session_start();

require_once '../Function/functions.php';
resetExpiredOTPs($conn);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up</title>
    <link rel="shortcut icon" href="../Assets/Images/Icon/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="../Assets/CSS/index.css">

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">


    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div class="container">
        <!-- Login Form -->
        <div class="form-box login">
            <form action="../Function/register.php" id="login-form" method="POST">
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
                <button type="submit" class="btn btn-primary" id="login" name="login" disabled>Login</button>

                <div class="loginMessageBox">
                    <div class="errorMsg">
                        <!-- (Show under Login Button) -->
                        <div class="login-error" id="passwordLValidation"></div>
                    </div>
                    <p class="errorMsg">
                        <!-- (Show under Login Button) -->
                        <?php
                        //Error Message 
                        if (isset($_SESSION['error'])) {
                            echo htmlspecialchars(strip_tags($_SESSION['error']));
                            unset($_SESSION['error']);
                        }
                        //Alert Message 
                        if (isset($_GET['session']) && $_GET['session'] === 'expired') {
                            echo '<div class="alert alert-warning" >Session Expired</div>';
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

        <div class="form-box register" id="register">
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
                        <input type="hidden" name="userRole" value="1"> <!-- 1 = customer -->
                        <input type="hidden" name="registerStatus" value="customer">

                        <i class='bx bxs-envelope'></i>
                    </div>

                    <div class="passwordContainer">
                        <div class="input-box">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Password" oninput="validateSignUpForm();" required>
                            <i id="togglePassword1" class='bx bxs-hide'></i>
                        </div>
                        <div class="confirmErrorMsg" id="passwordValidation"></div>
                        <div class=" input-box">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                placeholder="Confirm Password" oninput="validateSignUpForm();" required>
                            <i id="togglePassword2" class='bx bxs-hide'></i>
                        </div>
                        <div class="confirmErrorMsg" id="passwordMatch"></div>
                    </div>
                </div>

                <label for="terms">
                    <input type="checkbox" id="terms" name="terms" class="terms-checkbox" value="1"
                        onchange="validateSignUpForm();"> I agree to the
                    <a href="#termsModal" class="termsLink" data-bs-toggle="modal" data-bs-target="#termsModal">Terms
                        and
                        Conditions</a>.
                </label><br>

                <div id="termsError"></div>
                <button type="submit" class="btn btn-primary" id="signUp" name="signUp" disabled>Sign Up</button>
            </form>








            <!-- error message -->



            <div class="emailErrorMsg">
                <p>
                    <?php
                    if (isset($_SESSION['email-message'])) {
                        echo htmlspecialchars($_SESSION['email-message']);
                        unset($_SESSION['email-message']); // aalisin after ma display
                    }
                    ?>
                </p>
            </div>

        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1 class="welcome">Welcome to Mamyr!</h1>
                <p>Don't have an account?</p>
                <a href="userType.php" class="btn btn-outline-light signUpLink">Sign Up
                </a>

                <div class="back-icon-container-login">
                    <a href="../index.php">
                        <img src="../Assets/Images/Icon/home.png" alt="Go back" class="backArrow">
                    </a>
                </div>
            </div>


            <div class="toggle-panel toggle-right">
                <h1 class="welcome">Welcome Back!</h1>
                <p>Already have an account?</p>
                <button class="btn btn-outline-light login-btn">Login</button>

                <div class="back-icon-container-signup">
                    <a href="../index.php">
                        <img src="../Assets/Images/Icon/home.png" alt="Go back" class="backArrow">
                    </a>
                </div>

            </div>
        </div>
    </div>
    <div id="loaderOverlay" style="display: none;">
        <div class="loader" id="loader"></div>
    </div>


    <!-- terms and conditions modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Terms and Conditions</h5>

                </div>
                <div class="modal-body">
                    <p class="termsDescription text-center">Welcome to Mamyr Resort and Events Place! By using our
                        Resort Event Management System, you agree to abide by the terms and conditions outlined below.
                        These terms apply to all bookings made for the resort, hotel, and event venues via this
                        platform.
                        <br><strong> Please read these terms carefully before making any bookings.</strong>
                    </p>
                    <h6>1. Booking & Reservation</h6>
                    <ul>
                        <li><strong>Eligibility:</strong> Users must be at least 18 years of age to book any
                            services via our system.</li>
                        <li><strong>Booking Process:</strong> Users must provide accurate details, including
                            full name, contact information, payment details, and any additional requirements
                            (e.g., room preferences, event specifications).</li>
                        <li><strong>Confirmation:</strong> A booking is considered confirmed once you receive an
                            official booking confirmation email or notification. Any reservation made without
                            this confirmation will not be considered valid.</li>
                        <li><strong>Booking Modifications:</strong> You may modify or cancel your booking
                            through the system, provided such changes comply with the cancellation and
                            modification policy.</li>
                    </ul>
                    <h6>2. Payments & Charges</h6>
                    <ul>
                        <li><strong>Pricing:</strong> All pricing for resort accommodations, hotel rooms, and
                            event venues are displayed clearly on the platform. Prices are subject to change
                            based on seasonality, availability, or promotions.</li>
                        <li><strong>Payment Methods:</strong> We only accept certain payment methods, namely
                            GCash and on-site cash payments. Down payments must be made before the time of
                            booking unless otherwise stated.</li>
                        <li><strong>Refunds:</strong> Our business does not provide refunds for down payment
                            upon cancellation. Users are encouraged to ensure that their booking information, as
                            well as their schedules for their desired booking dates are accurately provided to
                            avoid the need for cancellations.</li>
                    </ul>
                    <h6>3. Check-in & Check-out</h6>
                    <ul>
                        <li><strong>Hotel & Resort:</strong> Early check-ins or late check-outs are subject to
                            availability and may incur additional charges.</li>
                        <li><strong>Event Venue:</strong> Event venue access will be granted as per the
                            agreed-upon event time. Additional charges may apply for extended event hours.</li>
                    </ul>
                    <h6>4. Limitation of Liability</h6>
                    <ul>
                        <li><strong>Hotel/Resort Liability:</strong> Our liability for any loss, injury, or
                            damage incurred during a stay or event is limited to the amount paid for the
                            booking. We are not liable for any indirect or consequential damages.</li>
                        <li><strong>Event Liability:</strong> The resort is not responsible for any third-party
                            event organizer’s actions or services. Any complaints regarding event services
                            should be directed to the event organizer.</li>
                    </ul>
                    <h6>5. Privacy & User Data Policy</h6>
                    <ul>
                        <li><strong>Types of Data Collected:</strong> Personal Information, Payment Information,
                            Booking Data.</li>
                        <li><strong>Use of Data:</strong> Your personal and booking information is used to
                            process and manage your reservations, send confirmations, and provide customer
                            support.</li>
                        <li><strong>Data Protection:</strong> We implement security measures to protect your
                            personal and payment information.</li>
                        <li><strong>Retention of Data:</strong> We retain your data only for as long as
                            necessary to fulfill the purpose for which it was collected.</li>
                        <li><strong>Your Rights:</strong> You have the right to access, rectify, delete, or
                            opt-out of marketing communications regarding your personal data.</li>
                    </ul>
                    <h6>6. Modifications to Terms & Conditions</h6>
                    <p>We reserve the right to modify these terms and conditions at any time. Changes will be
                        effective immediately upon posting.</p>
                    <h6>7. Dispute Resolution</h6>
                    <p>Any disputes arising from bookings will be resolved through arbitration in San Ildefonso,
                        Bulacan, Philippines.</p>
                    <h6>8. Governing Law</h6>
                    <p>These terms shall be governed by the laws of the Philippines.</p>
                    <h6>9. Contact Information</h6>
                    <ul>
                        <li>Email: <a href="mailto:mamyresort128@gmail.com">mamyresort128@gmail.com</a></li>
                        <li>Phone: (0998) 962 4697</li>
                        <li>Address: Sitio Colonia Gabihan, San Ildefonso, Bulacan</li>
                    </ul>

                </div>
                <div class="modal-footer">
                    <div class="declineBtnContainer">
                        <button type="button" class="btn btn-secondary" id="declineBtn" data-bs-dismiss="modal"
                            aria-label="Close">Decline</button>
                    </div>
                    <div class="acceptBtnContainer">
                        <button type="button" class="btn btn-primary" id="acceptBtn">Accept</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- terms and conditions modal -->

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>



    <!--Password Validation JS -->
    <script src="../Assets/JS/passwordValidation.js"></script>

    <!-- Check if user agree to the terms and condition -->
    <!-- <script src="../Assets/JS/checkbox.js"></script> -->


    <!-- Password Match JS-->
    <!-- <script src="../Assets/JS/checkPasswordMatch.js"></script> -->
    <!-- Register Password Validation JS -->
    <!-- <script src="../Assets/JS/checkPassword.js"></script> -->

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Loader function -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const signUpBtn = document.getElementById('signUp');
            const loginBtn = document.getElementById('login');
            const loginEmail = document.getElementById('login_email');
            const loginPassword = document.getElementById('login_password');
            const loader = document.getElementById('loader');


            // Click event on the button
            signUpBtn.addEventListener('click', function(e) {
                document.getElementById('loaderOverlay').style.display = 'flex';
            });
            loginBtn.addEventListener('click', function(e) {
                document.getElementById('loaderOverlay').style.display = 'flex';
            });
        });
    </script>


    <script>
        const container = document.querySelector('.container');
        const registerBtn = document.querySelector('.register-btn');
        const loginBtn = document.querySelector('.login-btn');

        // registerBtn.addEventListener('click', () => {
        //     container.classList.add('active');
        // });

        loginBtn.addEventListener('click', () => {
            container.classList.remove('active');
        });

        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page');
        const action = urlParams.get('action');

        if (page === 'register') {
            container.classList.add('active');

            // 🔽 Remove `?page=register` from URL after activating the form
            const urlWithoutParam = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({}, document.title, urlWithoutParam);
        } else {
            container.classList.remove('active');
        }

        if (action === "deleted") {
            Swal.fire({
                title: "Success",
                text: "Your account has been deleted successfully.",
                icon: "success"
            });
        } else if (action === "unauthorized") {
            Swal.fire({
                title: "Oops",
                text: "You are not authorized to access this page.",
                icon: "warning"
            })
        } else if (action === "notVerified") {
            Swal.fire({
                title: "Oops",
                text: "User not verified. Please verify your account.",
                icon: "warning"
            })
        } else if (action === "emailExist") {
            Swal.fire({
                title: "Oops",
                text: "An account with this email already exists.",
                icon: "warning"
            })
        } else if (action === "OTPFailed") {
            Swal.fire({
                title: "Oops",
                text: "We couldn’t send the OTP. Please try again.",
                icon: "warning"
            })
        } else if (action === "successVerification") {
            Swal.fire({
                title: "Verified Successfully",
                text: "Your account has been verified. You may now log in to your account.",
                icon: "success"
            })
        }
        if (page || action) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>

    <!-- Eye icon of password show and hide -->
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
</body>

</html>