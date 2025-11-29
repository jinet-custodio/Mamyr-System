<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../Config/dbcon.php';
session_start();
//for setting image paths in 'include' statements
$baseURL = '..';
require_once '../Function/Helpers/userFunctions.php';
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

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div class="container">
        <!-- Login Form -->
        <div class="form-box login">
            <form action="../Function/register.php" id="login-form" method="POST">
                <h1>Login</h1>
                <div class=" input-box">
                    <input type="text" class="form-control" id="login_email" name="login_email" autocomplete="username"
                        value="<?php echo isset($_SESSION['loginFormData']['email']) ? htmlspecialchars(trim($_SESSION['loginFormData']['email'])) : ''; ?>"
                        placeholder="Email" required>
                    <i class='bx bxs-envelope'></i>
                </div>
                <div class="input-box">
                    <input
                        type="password"
                        class="form-control"
                        id="login_password"
                        name="login_password"
                        placeholder="Password"
                        autocomplete="current-password"
                        required>

                    <i id="togglePassword" class='bx bxs-hide'></i>
                </div>
                <div class="forgot-link">
                    <a href="enterEmail.php">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary" id="login" name="login">Login</button>

                <div class="signUpSection">
                    <p>Don't have an account? <button type="button" class="signUpLink" data-bs-toggle="modal"
                            data-bs-target="#userType-modal">
                            Sign Up
                        </button></p>
                    <!-- <p>Don't have an account? <a href="userType.php" class="signUpLink">Sign Up
                        </a></p> -->


                </div>
                <div class="loginMessageBox">
                    <!-- (Show under Login Button) -->
                    <!-- <div class="errorMsg" style="display: none;">
                        <div class="login-error" id="passwordLValidation"></div>
                    </div> -->
                    <p class="errorMsg">
                        <!-- (Show under Login Button) -->
                        <?php
                        //Error Message 
                        if (isset($_SESSION['loginError'])) {
                            echo htmlspecialchars(strip_tags($_SESSION['loginError']));
                            unset($_SESSION['loginError']);
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
                <h1 id="signUpTitle">Sign Up</h1>
                <div class="fullName">
                    <div class="input-box">
                        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name" pattern="^[A-Za-zÀ-ÖØ-öø-ÿĀ-žḀ-ỹẀ-ẕ'.\- ]{2,100}$"
                            maxlength="30"
                            value="<?php echo isset($_SESSION['registerFormData']['firstName']) ? htmlspecialchars(trim($_SESSION['registerFormData']['firstName'])) : ''; ?>"
                            required>
                        <i class='bx bxs-user-circle'></i>
                    </div>
                    <div class="input-box">
                        <input type="text" class="form-control" id="middleInitial" name="middleInitial" maxlength="2" pattern="^[A-Za-zÀ-ÖØ-öø-ÿĀ-žḀ-ỹẀ-ẕ'.\- ]{2,100}$"
                            placeholder="M.I. (Optional)"
                            value="<?php echo isset($_SESSION['registerFormData']['middleInitial']) ? htmlspecialchars(trim($_SESSION['registerFormData']['middleInitial'])) : ''; ?>">
                        <i class='bx bxs-user-circle'></i>
                    </div>

                </div>
                <div class="userInfo">
                    <div class="input-box">
                        <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name" pattern="^[A-Za-zÀ-ÖØ-öø-ÿĀ-žḀ-ỹẀ-ẕ'.\- ]{2,100}$"
                            maxlength="30"
                            value="<?php echo isset($_SESSION['registerFormData']['lastName']) ? htmlspecialchars(trim($_SESSION['registerFormData']['lastName'])) : ''; ?>"
                            required>
                        <i class='bx bxs-user-circle'></i>
                    </div>
                    <div class="input-box">
                        <input type="text" class="form-control" id="userAddress" name="userAddress" maxlength="100"
                            placeholder="Address"
                            value="<?php echo isset($_SESSION['registerFormData']['userAddress']) ? htmlspecialchars(trim($_SESSION['registerFormData']['userAddress'])) : ''; ?>"
                            required>
                        <i class='bx bxs-user'></i>
                    </div>
                    <div class="input-box">
                        <input type="email" class="form-control" id="email" autocomplete="username" name="email" placeholder="Email"
                            value="<?php echo isset($_SESSION['registerFormData']['email']) ? htmlspecialchars(trim($_SESSION['registerFormData']['email'])) : ''; ?>"
                            required>
                        <input type="hidden" name="userRole" value="1"> <!-- 1 = customer -->
                        <input type="hidden" name="registerStatus" value="Customer">
                        <i class='bx bxs-envelope'></i>
                    </div>

                    <div class="passwordContainer">
                        <div class="input-box">
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Password"
                                autocomplete="new-password"
                                oninput="validateSignUpForm();"
                                required>
                            <i id="togglePassword1" class='bx bxs-hide'></i>
                        </div>
                        <div class="confirmErrorMsg" id="passwordValidation"></div>
                        <div class=" input-box">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password"
                                placeholder="Confirm Password" oninput="validateSignUpForm();" required>
                            <i id="togglePassword2" class='bx bxs-hide'></i>
                        </div>
                        <div class="confirmErrorMsg" id="passwordMatch"></div>
                    </div>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" id="password-strength" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <label for="terms" class="termsSection">
                    <input type="checkbox" id="terms-condition" name="terms" class="terms-checkbox" value=""
                        onchange="validateSignUpForm();"> I agree to the
                    <a href="#termsModal" class="termsLink" data-bs-toggle="modal" data-bs-target="#termsModal">Terms
                        and
                        Conditions</a>.
                </label><br>

                <div id="termsError"></div>
                <button type="submit" class="btn btn-primary" id="signUp" name="signUp" onclick="isValid(event);">Sign Up</button>
            </form>



            <!-- error message -->

            <div class="emailErrorMsg">
                <p>
                    <?php
                    if (isset($_SESSION['registerError'])) {
                        echo htmlspecialchars($_SESSION['registerError']);
                        unset($_SESSION['registerError']);
                    }
                    ?>
                </p>
            </div>

        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <div class="back-icon-container-login">
                    <a href="../index.php">
                        <img src="../Assets/Images/Icon/home.png" alt="Go back" class="backArrow">
                    </a>
                </div>
                <h1 class="welcome" id="welcomeLogin">Welcome to Mamyr Resort and Events Place!</h1>
                <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Logo" class="mamyrLogo">
                <!-- <p>Don't have an account?</p>
                <a href="userType.php" class="btn btn-outline-light signUpLink">Sign Up
                </a> -->
            </div>


            <div class="toggle-panel toggle-right">
                <h1 class="welcome" id="welcomeRegister">Welcome Back!</h1>
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
    <?php include '../Pages/Customer/loader.php'; ?>
    </div>


    <!-- User Type Modal -->
    <div class="modal fade" id="userType-modal" role="dialog" aria-labelledby="userType-modal-label">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" id="usertype-modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="userType-modal-label">I am signing up as:</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body user-category">

                    <a href="register.php?page=register" id="partner-link" class="categoryLink">
                        <div class="card category-card ">
                            <img class="card-img-top" src="../Assets/Images/UserTypePhotos/customer.png" alt="Partners">

                            <div class="category-body m-auto">
                                <h5 class="category-title m-auto">Customer</h5>
                                <p class="card-text">I am interested in making bookings and viewing the amenities of
                                    the resort.
                                </p>
                            </div>
                        </div>
                    </a>

                    <a href="busPartnerRegister.php" id="request-link" class="categoryLink">
                        <div class="card category-card ">
                            <img class="card-img-top" src="../Assets/Images/UserTypePhotos/businessPartner.png"
                                alt="Business Partner">

                            <div class="category-body m-auto">
                                <h5 class="category-title m-auto">Business Partner</h5>
                                <p class="card-text"> I want to request for a partnership to offer my services to
                                    customers of
                                    the resort.</p>
                            </div>
                        </div>
                    </a>

                </div>

            </div>
        </div>
    </div>
    <!-- User Type Modal -->
    <!-- terms and conditions modal -->

    <div class="modal fade"
        id="termsModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="exampleModalLabel"
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
                        <button type="button" class="btn btn-secondary" id="declineTermsBtn" onclick="declineTerms()">Decline</button>
                    </div>
                    <div class="acceptBtnContainer">
                        <button type="button" class="btn btn-primary" id="acceptTermsBtn"
                            onclick="AcceptTerms()">Accept</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- terms and conditions modal -->

    <!-- Bootstrap Link -->
    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>

    <!--Password Validation JS & terms and condition-->
    <script src="../Assets/JS/passwordValidation.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Loader function -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginBtn = document.getElementById('login');
            const loginEmail = document.getElementById('login_email');
            const loginPassword = document.getElementById('login_password');
            const loaderOverlay = document.getElementById('loaderOverlay');

            loginBtn.addEventListener('click', function(e) {
                const email = loginEmail.value.trim();
                const password = loginPassword.value.trim();

                // Basic validation
                if (email === '' || password === '') {
                    e.preventDefault();
                    Swal.fire({
                        text: "Please fill in the required information.",
                        icon: "info"
                    });
                    return;
                }

                // Check for valid email (simple validation)
                if (!email.includes('@')) {
                    e.preventDefault();
                    Swal.fire({
                        text: "Please enter a valid email adrress.",
                        icon: "info"
                    });
                    return;
                }

                // Show loader only if validation passes
                loaderOverlay.style.display = 'flex';
            });
        });
    </script>

    <!-- For password — weak, medium, strong -->
    <script>
        document.getElementById('password').addEventListener('input', function() {
            const password = document.getElementById("password").value;
            const weakPattern = /^.{0,5}$/;
            const mediumPattern = /^(?=.*[A-Za-z])(?=.*\d).{6,}$/;
            const strongPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;
            const passwordBar = document.getElementById("password-strength");
            // console.log(password);

            passwordBar.className = "progress-bar";
            let color = "";
            let number = "";
            let strength = 'too  weak';
            if (strongPattern.test(password)) {
                color = "bg-success";
                number = "100";
                strength = 'strong';
            } else if (mediumPattern.test(password)) {
                color = "bg-warning";
                number = "75";
                strength = 'moderate';
            } else if (weakPattern.test(password)) {
                color = "bg-danger";
                number = "50";
                strength = 'weak';
            } else {
                color = "bg-danger";
                number = "25";
                strength = 'too weak';
            }


            passwordBar.classList.add(color, `w-${number}`);
            passwordBar.setAttribute("aria-valuenow", number);
            passwordBar.textContent = strength;
        });
    </script>



    <script>
        const emailInputField = document.getElementById('email');

        const container = document.querySelector('.container');
        const registerBtn = document.querySelector('.register-btn');
        const loginBtn = document.querySelector('.login-btn');

        // registerBtn.addEventListener('click', () => {
        //     container.classList.add('active');
        // });

        emailInputField.addEventListener('change', () => {
            emailInputField.style.border = '1px solid rgb(237, 237, 237)';
        })

        loginBtn.addEventListener('click', () => {
            container.classList.remove('active');
        });

        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page');
        const action = urlParams.get('action');
        const session = urlParams.get('session');

        if (page === 'register') {
            container.classList.add('active');

            // 🔽 Remove `?page=register` from URL after activating the form
            const urlWithoutParam = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({}, document.title, urlWithoutParam);
        } else {
            container.classList.remove('active');
        }

        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        // Alert Message 
        if (session === 'expired') {
            Toast.fire({
                timer: 4000,
                position: "center",
                text: "Session Expired",
                icon: "warning"
            });
        }



        if (action === "deleted") {
            Toast.fire({
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
                icon: "warning",
                confirmButtonText: 'okay'
            }).then((result) => {
                emailInputField.style.border = '1px solid red';
            })
        } else if (action === "OTPFailed") {
            Swal.fire({
                title: "Oops",
                text: "We couldn’t send the OTP. Please try again.",
                icon: "warning"
            })
        } else if (action === "successVerification") {
            Toast.fire({
                text: "Verified Successfully",
                icon: "success"
            })
        } else if (action === 'partner-registered') {
            Toast.fire({
                text: "Partner has been successfully registered and verified.",
                icon: "success"
            })
        } else if (action === 'partnerApplicationFailed') {
            Swal.fire({
                title: "Server Error",
                text: "An error occurred during partner registration. Please try again later.",
                icon: "error",
                confirmButtonText: 'okay'
            });
        }

        if (urlParams) {
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