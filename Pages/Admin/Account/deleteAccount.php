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
    <title>Mamyr Resort and Events Place</title>
    <link
        rel="icon"
        type="image/x-icon"
        href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" />

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Admin/Account/deleteAccount.css" />

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
        <div class="sidebar-header">
            <h5>Account Settings</h5>
        </div>
        <ul class="list-group">
            <li>
                <a href="account.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/user.png" alt="" class="sidebar-icon">
                    Profile Information
                </a>
            </li>
            <li>
                <a href="loginSecurity.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/login_security.png" alt="" class="sidebar-icon">
                    Login & Security
                </a>
            </li>
            <li>
                <a href="userManagement.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/usermanagement.png" alt="" class="sidebar-icon">
                    User Management
                </a>
            </li>
            <!-- <li>
                <a href="" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/systempreferences.png" alt="" class="sidebar-icon">
                    System Preferences
                </a>
            </li> -->
            <li>
                <a href="" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/revenue.png" alt="" class="sidebar-icon">
                    Revenue
                </a>
            </li>
            <li>
                <a href="deleteAccount.php" class="list-group-item  active">
                    <img src="../../../Assets/Images/Icon/delete-user.png" alt="" class="sidebar-icon">
                    Delete Account
                </a>
            </li>
            <li>
                <button type="button" class="btn btn-outline-danger" id="logoutBtn"> <img src="../../../Assets/Images/Icon/logout.png" alt="" class="sidebar-icon">
                    Logout</button>
            </li>
        </ul>
    </div>
    <!-- End Side Bar -->

    <a href="../adminDashboard.php" class="home-button btn btn-primary"><img src="../../../Assets/Images/Icon/home2.png" alt=""></a>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Account Deletion</h5>
        </div>
        <div class="card-body">
            <p class="card-text">You can permanently delete your account here. This action cannot be undone.</p>
            <!-- <div class="input-container">
                <label for="password">Enter your password:</label>
                <input type="password" name="passwordEntered" id="passwordEntered">
            </div> -->
            <div class="button-container">
                <button type="button" class="btn btn-danger" name="deleteAccount" id="deleteAccount">Delete Account</button>
            </div>

            <!-- Email Change Modal -->
            <form action="../../../Function/Admin/Account/loginSecurity.php" method="POST">
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-center" id="deleteModalLabel">Your current email is <br> <strong><?= htmlspecialchars($data['email']) ?></strong></h5>
                                <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>">
                                <button type="button" class="btn-close btn btn-danger" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php
                                if (isset($_SESSION['modal-error'])) {
                                    echo '<div class="message-container alert alert-danger">' . htmlspecialchars($_SESSION['modal-error']) . '</div>';
                                    unset($_SESSION['modal-error']);
                                }
                                ?>
                                <input type="hidden" name="newEmail" value="<?= htmlspecialchars($newEmail) ?>">
                                <p class="modal-text">Please enter the verification code</p>
                                <input type="text" name="enteredOTP" id="enteredOTP" placeholder="6 digit security code" required>
                                <div class="button-container">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" name="verifyCode">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Change Modal -->
    <!-- <form action="../../../Function/Admin/logout.php" method="POST">
        <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center" id="logoutModalLabel">Your current email is <br> <strong><?= htmlspecialchars($data['email']) ?></strong></h5>
                        <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>">
                        <button type="button" class="btn-close btn btn-danger" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php
                        if (isset($_SESSION['modal-error'])) {
                            echo '<div class="message-container alert alert-danger">' . htmlspecialchars($_SESSION['modal-error']) . '</div>';
                            unset($_SESSION['modal-error']);
                        }
                        ?>
                        <input type="hidden" name="newEmail" value="<?= htmlspecialchars($newEmail) ?>">
                        <p class="modal-text">Please enter the verification code</p>
                        <input type="text" name="enteredOTP" id="enteredOTP" placeholder="6 digit security code" required>
                        <div class="button-container">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="verifyCode">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form> -->


    <!-- Bootstrap Link -->
    <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const deleteAccBtn = document.getElementById("deleteAccount");
        const deleteModal = document.getElementById('deleteModal');
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutModal = document.getElementById('logoutModal');

        deleteAccBtn.addEventListener("click", function() {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    const myModal = new bootstrap.Modal(deleteModal);
                    myModal.show();
                }
            });
        });

        logoutBtn.addEventListener("click", function() {
            Swal.fire({
                title: "Are you sure you want to log out?",
                text: "You will need to log in again to access your account.",
                icon: "warning",
                showCancelButton: true,
                // confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, logout!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../../../Function/Admin/logout.php";
                }
            });
        })
    </script>
</body>

</html>