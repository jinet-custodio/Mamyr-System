<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Security - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Customer/Account/loginSecurity.css" />
</head>

<body>
    <!-- Side Bar -->
    <div class="sidebar">

        <div class="home">
            <a href="../dashboard.php"><img src="../../../Assets/Images/Icon/home2.png" alt="home icon"
                    class="homeIcon"></a>
        </div>
        <div class="sidebar-header">
            <h5>User Account</h5>
        </div>
        <ul class="list-group">
            <li>
                <a href="new_account.php" class="list-group-item ">
                    <img src="../../../Assets/Images/Icon/user.png" alt="Profile Information" class="sidebar-icon">
                    Profile Information
                </a>
            </li>

            <li>
                <a href="userManagement.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/bookingHistory.png" alt="Booking History"
                        class="sidebar-icon">
                    Booking History
                </a>
            </li>

            <li>
                <a href="loginSecurity.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/login_security.png" alt="Login Security" class="sidebar-icon">
                    Login & Security
                </a>
            </li>


            <a href="deleteAccount.php" class="list-group-item active">
                <img src="../../../Assets/Images/Icon/delete-user.png" alt="Delete Account" class="sidebar-icon">
                Delete Account
            </a>
            </li>
            <li>
                <button type="button" class="btn btn-outline-danger" id="logoutBtn"> <img
                        src="../../../Assets/Images/Icon/logout.png" alt="Log Out" class="sidebar-icon">
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
                    <input type="email" name="email" id="email" value="louisebartolome.basc@gmail.com" disabled>
                    <button type="button" class="btn btn-primary" id="changeEmailBtn" data-toggle="modal"
                        data-target="#emailModal">Change</button>
                </div>
                <div class="input-box">
                    <label for="password">Password</label>
                    <input type="password" name="showPassword" id="showPassword" value="*********" disabled>
                    <button type="button" class="btn btn-primary" id="changePasswordBtn">Change</button>
                </div>


            </div>
            <!-- Email Change Modal -->
            <form action="#" method="POST">
                <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-center" id="emailModalLabel">Your current email is <br>
                                    <strong>louisebartolome.basc@gmail.com</strong>
                                </h5>
                                <!-- <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>"> -->
                                <button type="button" class="btn-close btn btn-danger" data-bs-dismiss="modal"
                                    aria-label="Close"> </button>
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
            <form action="#" method="POST">
                <div class="modal fade" id="email2Modal" tabindex="-1" aria-labelledby="email2ModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-center" id="email2ModalLabel">Your current email is <br>
                                    <strong>louisebartolome.basc@gmail.com</strong>
                                </h5>
                                <!-- <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>"> -->
                                <button type="button" class="btn-close btn btn-danger" data-bs-dismiss="modal"
                                    aria-label="Close">
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- <?php
                                if (isset($_SESSION['modal-error'])) {
                                    echo '<div class="message-container alert alert-danger">' . htmlspecialchars($_SESSION['modal-error']) . '</div>';
                                    unset($_SESSION['modal-error']);
                                }
                                ?> -->
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
            <form action="../../../Function/Admin/Account/loginSecurity.php" method="POST">
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
            <form action="../../../Function/Admin/Account/loginSecurity.php" method="POST">
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
                                            oninput="checkPasswordModal()" required>
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
                                            oninput="checkPassMatchModal()" required>
                                        <i id="togglePassword4" class='bx bxs-hide'></i>
                                    </div>
                                    <div class="errorMsg">
                                        <div class="confirmErrorMsg" id="passwordMatch"></div>
                                    </div>
                                </div>
                                <div class="button-container">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" name="changePassword">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>


    <!-- Password Validation -->
    <script src="../../../Assets/JS/checkPassword.js"></script>
    <script src="../../../Assets/JS/checkPasswordMatch.js"></script>


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


</body>

</html>