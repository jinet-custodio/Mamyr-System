<?php
require '../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../register.php?session=expired");
    exit();
}

$_SESSION['last_activity'] = time();
$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Security - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/loginSecurity.css" />

    <!-- Boxicon Link -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <!-- Get User Info -->

    <?php
    if ($userRole == 1) {
        $role = "Customer";
    } elseif ($userRole == 2) {
        $role = "Business Partner";
    } elseif ($userRole == 3) {
        $role = "Admin";
    } else {
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
    }


    $getUserInfo = $conn->prepare("SELECT * FROM users WHERE userID = ? AND userRole = ?");
    $getUserInfo->bind_param("ii", $userID, $userRole);
    $getUserInfo->execute();
    $getUserInfoResult = $getUserInfo->get_result();
    if ($getUserInfoResult->num_rows > 0) {
        $data =  $getUserInfoResult->fetch_assoc();
    }
    ?>
    <!-- Side Bar -->
    <div class="sidebar">

        <div class="home">
            <?php if ($role === 'Customer') { ?>
                <a href="../Customer/dashboard.php">
                    <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                </a>
            <?php } elseif ($role === 'Admin') { ?>
                <a href="../Admin/adminDashboard.php">
                    <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                </a>
            <?php } ?>
        </div>

        <div class="sidebar-header">
            <h5>User Account</h5>

            <?php
            $getProfile = $conn->prepare("SELECT firstName,userProfile FROM users WHERE userID = ? AND userRole = ?");
            $getProfile->bind_param("ii", $userID, $userRole);
            $getProfile->execute();
            $getProfileResult = $getProfile->get_result();
            if ($getProfileResult->num_rows > 0) {
                $profile = $getProfileResult->fetch_assoc();
                $firstName = $profile['firstName'];
                $imageData = $profile['userProfile'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $imageData);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            ?>

            <div class="profileImage">
                <img src="<?= htmlspecialchars($image) ?>" alt=" <?= htmlspecialchars($data['firstName']) ?> Picture">
            </div>
        </div>
        <ul class="list-group">
            <li>
                <a href="account.php" class="list-group-item ">
                    <img src="../../Assets/Images/Icon/user.png" alt="Profile Information" class="sidebar-icon">
                    Profile Information
                </a>
            </li>

            <li>
                <a href="loginSecurity.php" class="list-group-item active">
                    <img src="../../Assets/Images/Icon/login_security.png" alt="Login Security" class="sidebar-icon">
                    Login & Security
                </a>
            </li>

            <?php if ($role === 'Customer' || $role === 'Business Partner') { ?>
                <li>
                    <a href="bookingHistory.php" class="list-group-item" id="paymentBookingHist">
                        <img src="../../Assets/Images/Icon/bookingHistory.png" alt="Booking History"
                            class="sidebar-icon">
                        Payment & Booking History
                    </a>
                </li>
            <?php } elseif ($role === 'Admin') { ?>
                <li>
                    <a href="userManagement.php" class="list-group-item">
                        <img src="../../Assets/Images/Icon/usermanagement.png" alt="" class="sidebar-icon">
                        Manage Users
                    </a>
                </li>
            <?php } ?>

            <li>
                <a href="deleteAccount.php" class="list-group-item">
                    <img src="../../Assets/Images/Icon/delete-user.png" alt="Delete Account" class="sidebar-icon">
                    Delete Account
                </a>
            </li>
            <li>
                <button type="button" class="btn btn-outline-danger" id="logoutBtn"> <img
                        src="../../Assets/Images/Icon/logout.png" alt="Log Out" class="sidebar-icon">
                    Logout</button>
            </li>
        </ul>
    </div>
    <!-- End Side Bar -->

    <div class="card">
        <h5 class="card-header">
            Login & security settings

        </h5>
        <div class="card-body">
            <p class="card-text">Your account credentials are used to securely access your resort account, manage
                reservations, view transaction history,
                and receive important notifications regarding services, promotions, and exclusive offers.</p>
            <div class="form-container">
                <div class="input-box">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?= $data['email'] ?>" disabled>
                    <button type="button" class="btn btn-primary" id="changeEmailBtn" data-toggle="modal"
                        data-target="#emailModal">Change</button>
                </div>
                <div class="input-box">
                    <label for="password">Password</label>
                    <input type="password" name="showPassword" id="showPassword" value="*********" disabled>
                    <button type="button" class="btn btn-primary" id="changePasswordBtn">Change</button>
                </div>

                <?php
                if (isset($_SESSION['email-change'])) {
                    echo '<div class="message-container alert alert-danger">' . htmlspecialchars($_SESSION['email-change']) . '</div>';
                    unset($_SESSION['email-change']);
                }
                ?>

            </div>
            <!-- Email Change Modal -->
            <form action="../../Function/Account/loginSecurity.php" method="POST">
                <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-center" id="emailModalLabel">Your current email is <br>
                                    <strong><strong><?= htmlspecialchars($data['email']) ?></strong>
                                </h5>
                                <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>">
                                <div class="closeButtonContainer">
                                    <button type="button" class="btn-close btn btn-danger" data-bs-dismiss="modal"
                                        aria-label="Close"> </button>
                                </div>

                            </div>
                            <div class="modal-body">
                                <p class="modal-text">Please enter your password</p>
                                <input type="password" name="passwordEntered" id="passwordEntered" required>
                                <i id="togglePassword" class='bx bxs-hide'></i>
                                <div class="button-container">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary"
                                        name="validatePassword">Continue</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Email Change Modal -->
            <form action="../../Function/Account/loginSecurity.php" method="POST">
                <div class="modal fade" id="email2Modal" tabindex="-1" aria-labelledby="email2ModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-center" id="email2ModalLabel">Your current email is <br>
                                    <strong><strong><?= htmlspecialchars($data['email']) ?></strong>
                                </h5>
                                <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>">
                                <button type="button" class="btn-close btn btn-danger" data-bs-dismiss="modal"
                                    aria-label="Close">
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php
                                if (isset($_SESSION['modal-error'])) {
                                    echo '<div class="message-container alert alert-danger">' . htmlspecialchars($_SESSION['modal-error']) . '</div>';
                                    unset($_SESSION['modal-error']);
                                }
                                ?>
                                <p class="modal-text">Please enter your new email</p>
                                <input type="email" name="newEmail" id="newEmail" placeholder="Email" required>
                                <div class="button-container">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" name="verifyEmail">Verify</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Email Change Modal -->
            <form action="../../Function/Account/loginSecurity.php" method="POST">
                <div class="modal fade" id="email3Modal" tabindex="-1" aria-labelledby="email3ModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-center" id="email3ModalLabel">Your current email is <br>
                                    <strong><?= htmlspecialchars($data['email']) ?></strong>
                                </h5>
                                <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>">
                                <button type="button" class="btn-close btn btn-danger" data-bs-dismiss="modal"
                                    aria-label="Close">
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php
                                $newEmail = $_SESSION['newEmail'];
                                if (isset($_SESSION['modal-error'])) {
                                    echo '<div class="message-container alert alert-danger">' . htmlspecialchars($_SESSION['modal-error']) . '</div>';
                                    unset($_SESSION['modal-error']);
                                }
                                ?>
                                <input type="hidden" name="newEmail" value="<?= htmlspecialchars($newEmail) ?>">
                                <p class="modal-text">Please enter the verification code</p>
                                <input type="text" name="enteredOTP" id="enteredOTP" placeholder="6 digit security code"
                                    required>
                                <div class="button-container">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" name="verifyCode">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Password Change Modal -->
            <form action="../../Function/Account/loginSecurity.php" method="POST">
                <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="passwordLabel">Change Password</h5>
                                <p class="modal-text">Password must contain at least 1 letter, 1 number, and 1 symbol.
                                    Minimun length is 8 characters.</p>
                            </div>

                            <div class="modal-body">
                                <?php
                                if (isset($_SESSION['password-error'])) {
                                    echo '<div class="message-container alert alert-danger">' . htmlspecialchars($_SESSION['password-error']) . '</div>';
                                    unset($_SESSION['password-error']);
                                }
                                ?>

                                <div class="input-container">
                                    <label for="currentPassword">Current Password</label>
                                    <div class="password">
                                        <input type="password" name="currentPassword" id="currentPassword" required>
                                        <i id="togglePassword2" class='bx bxs-hide'></i>
                                    </div>
                                </div>
                                <div class="input-container">
                                    <label for="newPassword">New Password</label>
                                    <div class="password">
                                        <input type="password" name="newPassword" id="newPassword"
                                            oninput="changePasswordValidation();" required>
                                        <i id="togglePassword3" class='bx bxs-hide'></i>
                                    </div>
                                    <div class="errorMsg">
                                        <div class="confirmErrorMsg" id="passwordValidation"></div>
                                    </div>
                                </div>
                                <div class="input-container">
                                    <label for="confirmPassword">Confirm Password</label>
                                    <div class="password">
                                        <input type="password" name="confirmPassword" id="confirmPassword"
                                            oninput="changePasswordValidation();" required>
                                        <i id="togglePassword4" class='bx bxs-hide'></i>
                                    </div>
                                    <div class="errorMsg">
                                        <div class="confirmErrorMsg" id="passwordMatch"></div>
                                    </div>
                                </div>
                                <div class="button-container">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" id="changePassword" name="changePassword" disabled>Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>


    <!-- Password Validation -->
    <script src="../../Assets/JS/passwordValidation.js"></script>


    <script>
        //Show Modal
        document.addEventListener("DOMContentLoaded", function() {
            const changeEmailBtn = document.getElementById("changeEmailBtn");
            const changePasswordBtn = document.getElementById("changePasswordBtn");
            const emailModal = document.getElementById("emailModal");
            const passwordModal = document.getElementById("passwordModal");

            changeEmailBtn.addEventListener("click", function() {
                const myEmailModal = new bootstrap.Modal(emailModal);
                myEmailModal.show();
            });
            changePasswordBtn.addEventListener("click", function() {
                const myPasswordModal = new bootstrap.Modal(passwordModal);
                myPasswordModal.show();
            });
        });
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('step');
        const email2Modal = document.getElementById("email2Modal");
        const email3Modal = document.getElementById("email3Modal");
        const passwordModal = document.getElementById("passwordModal");

        if (paramValue === '2') {
            const myEmail2Modal = new bootstrap.Modal(email2Modal);
            myEmail2Modal.show();
            if (paramValue) {
                const url = new URL(window.location);
                url.search = '';
                history.replaceState({}, document.title, url.toString());
            };
        } else if (paramValue === '3') {
            const myEmail3Modal = new bootstrap.Modal(email3Modal);
            myEmail3Modal.show();
            if (paramValue) {
                const url = new URL(window.location);
                url.search = '';
                history.replaceState({}, document.title, url.toString());
            };
        } else if (paramValue === 'success') {
            Swal.fire({
                title: "Success",
                text: "Your email has been updated successfully.",
                icon: "success"
            });
        } else if (paramValue === 'success-password') {
            Swal.fire({
                title: "Success",
                text: "Your password has been updated successfully.",
                icon: "success"
            });
        } else if (paramValue === '4') {
            const myPasswordModal = new bootstrap.Modal(passwordModal);
            myPasswordModal.show();
            if (paramValue) {
                const url = new URL(window.location);
                url.search = '';
                history.replaceState({}, document.title, url.toString());
            };
        }
        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>

    <!-- Eye icon of password show and hide -->
    <script>
        const passwordField = document.getElementById('passwordEntered');
        const passwordField1 = document.getElementById('currentPassword');
        const passwordField2 = document.getElementById('newPassword');
        const passwordField3 = document.getElementById('confirmPassword');
        const togglePassword = document.getElementById('togglePassword');
        const togglePassword1 = document.getElementById('togglePassword2');
        const togglePassword2 = document.getElementById('togglePassword3');
        const togglePassword3 = document.getElementById('togglePassword4');

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

        togglePassword3.addEventListener('click', () => {
            togglePasswordVisibility(passwordField3, togglePassword3);
        });
    </script>

    <script>
        const logoutBtn = document.getElementById('logoutBtn');

        logoutBtn.addEventListener("click", function() {
            Swal.fire({
                title: "Are you sure you want to log out?",
                text: "You will need to log in again to access your account.",
                icon: "warning",
                showCancelButton: true,
                // confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, logout!",
                customClass: {
                    title: 'swal-custom-title',
                    htmlContainer: 'swal-custom-text'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../../Function/logout.php";
                }
            });
        })
    </script>


</body>

</html>