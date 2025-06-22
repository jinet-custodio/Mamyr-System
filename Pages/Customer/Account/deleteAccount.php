<?php
require '../../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../../register.php?session=expired");
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
    <title>Delete Account - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Customer/Account/deleteAccount.css" />

</head>

<body>

    <!-- Get User Info -->

    <?php
    $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
    }
    ?>
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
                <a href="account.php" class="list-group-item ">
                    <img src="../../../Assets/Images/Icon/user.png" alt="Profile Information" class="sidebar-icon">
                    Profile Information
                </a>
            </li>

            <li>
                <a href="bookingHistory.php" class="list-group-item">
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

    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h5 class="card-title">Account Deletion</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Deleting your account is permanent.
                    When you delete your account, your main profile and everything else that you've added will be
                    permanently deleted.
                    You won't be able to retrieve anything that you've added. All additional information, and all of
                    your
                    messages will also be deleted.
                </p>
                <div class="button-container">
                    <button type="button" class="btn btn-danger" name="confirmationBtn" id="confirmationBtn">Delete
                        Account</button>
                </div>
                <?php
                if (isset($_SESSION['deleteAccountMessage'])) {
                    echo '<div class="message-container alert alert-danger text-center">' . htmlspecialchars($_SESSION['deleteAccountMessage']) . '</div>';
                    unset($_SESSION['deleteAccountMessage']);
                }
                ?>

                <!-- Confirmation Modal -->
                <form action="../../../Function/Customer/Account/deleteAccount.php" method="POST">
                    <div class="modal fade" id="confirmationModal" tabindex="-1"
                        aria-labelledby="confirmationModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="image w-100 text-center">
                                    <img src="../../../Assets/Images/Icon/warning.png" alt="warning icon"
                                        class="warning-image">

                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>">
                                    <p class="modal-title text-center mb-2">Are you sure?</p>
                                    <p class="modal-text text-center mb-2">You won't be able to revert this!</p>
                                    <div class="button-container">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">No</button>
                                        <button type="submit" class="btn btn-primary" name="yesDelete"
                                            id="yesDelete">Yes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Verification Modal -->
                <form action="../../../Function/Customer/Account/deleteAccount.php" method="POST">
                    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div class="w-100 text-center">
                                        <h5 class="modal-title">This action cannot be undone</h5>
                                        <h6 class="modal-text" id="deleteModalLabel">
                                            This will permanently delete your current email <br>
                                            <strong><?= htmlspecialchars($data['email']) ?></strong>
                                        </h6>
                                        <input type="hidden" name="email"
                                            value="<?= htmlspecialchars($data['email']) ?>">
                                    </div>
                                    <button type="button" class="btn-close btn btn-danger ms-2" data-bs-dismiss="modal"
                                        aria-label="Close">
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <?php
                                    if (isset($_SESSION['modal-error'])) {
                                        echo '<div class="message-container alert alert-danger text-center">' . htmlspecialchars($_SESSION['modal-error']) . '</div>';
                                        unset($_SESSION['modal-error']);
                                    }
                                    ?>

                                    <input type="hidden" name="newEmail" value="<?= htmlspecialchars($newEmail) ?>">

                                    <p class="modal-text text-center mb-2">Please enter the verification code</p>

                                    <div class="text-center">
                                        <input type="text" name="enteredOTP" id="enteredOTP"
                                            class="form-control d-inline-block w-50 text-center"
                                            placeholder="6 digit security code" required>
                                    </div>

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
            </div>
        </div>
    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Show -->
    <script>
    const params = new URLSearchParams(window.location.search);
    const paramsValue = params.get('action')
    const confirmationBtn = document.getElementById("confirmationBtn");
    const confirmationModal = document.getElementById("confirmationModal");
    const deleteModal = document.getElementById('deleteModal');
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
                window.location.href = "../../../Function/logout.php";
            }
        });
    });

    confirmationBtn.addEventListener("click", function() {
        const myconfirmationModal = new bootstrap.Modal(confirmationModal);
        myconfirmationModal.show();
    });

    if (paramsValue === 'success') {
        const myModal = new bootstrap.Modal(deleteModal);
        myModal.show();
    };

    if (paramsValue) {
        const url = new URL(window.location);
        url.search = '';
        history.replaceState({}, document.title, url.toString());
    };
    </script>
</body>

</html>