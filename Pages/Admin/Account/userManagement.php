<?php
require '../../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../../../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../../../register.php?session=expired");
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
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Admin/Account/userManagement.css" />

    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/datatables.min.css">
</head>

<body>

    <!-- Get User Info -->

    <?php
    $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $userData = mysqli_fetch_assoc($result);
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
                <a href="userManagement.php" class="list-group-item active">
                    <img src="../../../Assets/Images/Icon/usermanagement.png" alt="" class="sidebar-icon">
                    Manage Users
                </a>
            </li>
            <!-- <li>
                <a href="" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/systempreferences.png" alt="" class="sidebar-icon">
                    System Preferences
                </a>
            </li> -->
            <!-- <li>
                <a href="revenue.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/revenue.png" alt="" class="sidebar-icon">
                    Revenue
                </a>
            </li> -->
            <li>
                <a href="deleteAccount.php" class="list-group-item">
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

    <div class="wrapper">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">User Management</h5>
                <a href="addAccount.php" class="btn btn-light add-button"><img src="../../../Assets/Images/Icon/addUser.png" alt=""> Add an Account</a>
            </div>
            <div class="card-body">
                <table class="table" id="usertable">
                    <thead>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Status</th>
                        <th scope="col">Date Created</th>
                        <th scope="col">Action</th>
                    </thead>
                    <tbody>
                        <?php
                        $selectUsers = "SELECT u.*, ut.typeName as roleName, stat.statusName as status
                            FROM users u
                            INNER JOIN usertypes ut ON u.userRole = ut.userTypeID
                            INNER JOIN userstatuses stat ON u.userStatusID = stat.userStatusID
                            WHERE u.userID != '$userID' AND u.userRole != '$userRole' AND u.userStatusID != '4'
                            ORDER BY u.userRole DESC";
                        $userResult = mysqli_query($conn, $selectUsers);
                        if (mysqli_num_rows($userResult) > 0) {
                            foreach ($userResult as $userData) {
                                $middleInitial = trim($userData['middleInitial']);
                                $name = ucfirst($userData['firstName']) . ($middleInitial ? " " . ucfirst($middleInitial) . "." : "") . " " . ucfirst($userData['lastName']);
                                $status =  $userData['status'];
                                $dataCreated = date("m-d-y", strtotime($userData['createdAt']));
                                if ($status === 'Verified') {
                                    // $class = 'badge rounded-pill bg-success text-light';
                                    $image = '../../../Assets/Images/Icon/greencircle.png';
                                } elseif ($status === 'Pending') {
                                    // $class = 'badge  rounded-pill bg-warning text-dark';
                                    $image = '../../../Assets/Images/Icon/yellowcircle.png';
                                }
                        ?>
                                <tr>
                                    <td><?= htmlspecialchars($name) ?></td>
                                    <td><?= htmlspecialchars($userData['email']) ?> </td>
                                    <td><?= htmlspecialchars(ucfirst($userData['status'])) ?><img src="<?= $image ?>" alt="" class="status-image"></td>
                                    <td><?= htmlspecialchars($dataCreated) ?> </td>
                                    <td>
                                        <div class="button-container">
                                            <form action="viewUser.php" method="POST">
                                                <input type="hidden" name="selectedUserID" value="<?= htmlspecialchars($userData['userID']) ?>">
                                                <button type="submit" class="btn btn-info" name="viewUser">View</button>
                                            </form>
                                            <button type="button" class="btn btn-danger deleteUserAccount" data-userid="<?= $userData['userID'] ?>">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            }
                        }
                        ?>

                    </tbody>
                </table>

                <!-- Confirmation Modal -->
                <form action="../../../Function/Admin/Account/deleteUserAccount.php" method="POST">
                    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="image w-100 text-center">
                                    <img src="../../../Assets/Images/Icon/warningRed.png" alt="warning icon" class="warning-image">
                                </div>
                                <input type="hidden" name="selectedUserID" value="<?= htmlspecialchars($userData['userID']) ?>">
                                <div class="modal-body">
                                    <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>">
                                    <p class="modal-title text-center mb-2">Are you sure?</p>
                                    <p class="modal-text text-center mb-2">Deleting this account will remove all of their data from the system. This action cannot be reverted.</p>
                                    <div class="button-container">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                        <button type="submit" class="btn btn-primary" name="yesDelete" id="yesDelete">Yes</button>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#usertable').DataTable({
                language: {
                    emptyTable: "No users found."
                }
            });
        });
    </script>


    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- For logout -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
                        window.location.href = "../../../Function/Admin/logout.php";
                    }
                });
            });
        });
    </script>


    <!-- For deleting user -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const deleteButtons = document.querySelectorAll(".deleteUserAccount");
            const confirmationModal = document.getElementById("confirmationModal");

            if (confirmationModal) {
                deleteButtons.forEach(button => {
                    button.addEventListener("click", function() {
                        const myconfirmationModal = new bootstrap.Modal(confirmationModal);
                        myconfirmationModal.show();
                    });
                });
            }
        });
    </script>



    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('status');

        if (paramValue === "deleted") {
            Swal.fire({
                title: "Confirmed",
                text: "The account has been deleted.",
                icon: "success"
            });
        } else if (paramValue === "failed") {
            Swal.fire({
                title: "Action Failed",
                text: "We were unable to delete the account. Please try again later.",
                icon: "error"
            });
        } else if (paramValue === "added") {
            Swal.fire({
                title: "Confirmed",
                text: "New Account Created Successfully.",
                icon: "success"
            });
        }

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>

</body>

</html>