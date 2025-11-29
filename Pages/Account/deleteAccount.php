<?php
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];


if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID, userRole FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $_SESSION['userRole'] = $user['userRole'];
    }

    if (!$user) {
        $_SESSION['error'] = 'Account no longer exists';
        session_unset();
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
}

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}


switch ($userRole) {
    case 1: //customer
        $role = "Customer";
        break;
    case 2:
        $role = "Business Partner";
        break;
    case 3:
        $role = "Admin";
        break;
    case 4:
        $role = "Partnership Applicant";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/deleteAccount.css" />
    <link rel="stylesheet" href="../../Assets/CSS/Account/account-sidebar.css" />

    <!-- Font Awesome Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


</head>

<body>

    <!-- Get User Info -->

    <?php
    $getUserInfo = $conn->prepare("SELECT * FROM user WHERE userID = ? AND userRole = ?");
    $getUserInfo->bind_param("ii", $userID, $userRole);
    $getUserInfo->execute();
    $getUserInfoResult = $getUserInfo->get_result();
    if ($getUserInfoResult->num_rows > 0) {
        $data =  $getUserInfoResult->fetch_assoc();
    }
    ?>
    <div class="wrapper d-flex">
        <aside class="sidebar" id="sidebar">
            <div class="d-flex justify-content-center" id="toggle-container">
                <button id="toggle-btn" type="button" class="btn toggle-button" style="display: none;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </button>
            </div>
            <div class="home">
                <?php if ($role === 'Customer' || $role === 'Partnership Applicant') { ?>
                    <a href="../Customer/dashboard.php">
                        <i class="bi bi-house homeIcon"></i>
                    </a>
                <?php } elseif ($role === 'Admin') { ?>
                    <a href="../Admin/adminDashboard.php">
                        <i class="bi bi-house homeIcon"></i>
                    </a>
                <?php } elseif ($role === 'Business Partner') { ?>
                    <a href="../BusinessPartner/bpDashboard.php">
                        <i class="bi bi-house homeIcon"></i>
                    </a>
                <?php } ?>

            </div>

            <div class="sidebar-header text-center">
                <h5 class="sidebar-text">User Account</h5>
                <?php
                $getProfile = $conn->prepare("SELECT firstName,userProfile, email FROM user WHERE userID = ? AND userRole = ?");
                $getProfile->bind_param("ii", $userID, $userRole);
                $getProfile->execute();
                $getProfileResult = $getProfile->get_result();
                if ($getProfileResult->num_rows > 0) {
                    $data = $getProfileResult->fetch_assoc();
                    $firstName = $data['firstName'];
                    $imageData = $data['userProfile'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_buffer($finfo, $imageData);
                    finfo_close($finfo);
                    $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
                ?>

                <div class="profileImage">
                    <img src="<?= htmlspecialchars($image) ?>"
                        alt=" <?= htmlspecialchars($data['firstName']) ?> Picture">
                </div>
            </div>
            <ul class="list-group sidebar-nav">
                <li>
                    <a href="account.php" class="list-group-item ">
                        <i class="bi bi-person sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
                    </a>
                </li>


                <?php if ($role === 'Customer' || $role === 'Partnership Applicant' || $role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bookingHistory.php" class="list-group-item" id="BookingHist">
                            <i class="bi bi-calendar2-check sidebar-icon"></i>
                            <span class="sidebar-text">Booking History</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="paymentHistory.php" class="list-group-item" id="paymentHist">
                            <i class="bi bi-credit-card-2-front sidebar-icon"></i>
                            <span class="sidebar-text">Payment</span>
                        </a>
                    </li>
                <?php } elseif ($role === 'Admin') { ?>
                    <li class="sidebar-item">
                        <a href="userManagement.php" class="list-group-item">
                            <i class="bi bi-person-gear sidebar-icon"></i>
                            <span class="sidebar-text">Manage Users</span>
                        </a>
                    </li>
                <?php } ?>
                <?php
                //* For business partner nav
                if ($role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bpBookings.php" class="list-group-item">
                            <i class="bi bi-calendar2-check sidebar-icon"></i>
                            <span class="sidebar-text">Bookings</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpServices.php" class="list-group-item">
                            <i class="bi bi-bell sidebar-icon"></i>
                            <span class="sidebar-text">Services</span>
                        </a>
                    </li>
                    <!-- <li class="sidebar-item">
                    <a href="bpSales.php" class="list-group-item">
                        <i class="bi bi-tags sidebar-icon"></i>
                        <span class="sidebar-text">Sales</span>
                    </a>
                </li> -->
                <?php } ?>
                <li>
                    <a href="loginSecurity.php" class="list-group-item">
                        <i class="bi bi-shield-check sidebar-icon"></i>
                        <span class="sidebar-text">Login & Security</span>
                    </a>
                </li>
                <li>
                    <a href="deleteAccount.php" class="list-group-item active">
                        <i class="bi bi-person-dash sidebar-icon"></i>
                        <span class="sidebar-text">Delete Account</span>
                    </a>
                </li>

            </ul>

            <div class="logout">
                <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn"
                    style="margin: 3vw auto;">
                    <i class="bi bi-box-arrow-right logout-icon"></i>
                    <span class="sidebar-text ms-2">Logout</span>
            </div>

        </aside>
        <!-- End Side Bar -->
        <main class="main-content" id="main-content">
            <div class="wrapper">
                <form action="../../Function/Account/deleteAccount.php" method="POST">
                    <div class="card">
                        <div class="header">
                            <h5 class="card-title">Account Deletion</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                Deleting your account is permanent.
                                When you delete your account, your main profile and everything else that you've added will
                                be
                                permanently deleted.
                                You won't be able to retrieve anything that you've added. All additional information, and
                                all of
                                your
                                messages will also be deleted.
                            </p>
                            <div class="delete-button">

                                <button type="submit" class="btn btn-danger" name="confirmationBtn">Delete
                                    Account</button>
                            </div>
                            <?php
                            if (isset($_SESSION['deleteAccountMessage'])) {
                                echo '<div class="message-container alert alert-danger text-center">' . htmlspecialchars($_SESSION['deleteAccountMessage']) . '</div>';
                                unset($_SESSION['deleteAccountMessage']);
                            }
                            ?>

                        </div>
                    </div>
                </form>
            </div>
        </main>

        <form action="../../Function/Account/deleteAccount.php" method="POST">
            <!-- Confirmation Modal -->
            <div class="modal fade" id="warningModal" aria-hidden="true"
                aria-labelledby="warningModalLabel" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="warningModalLabel">Account Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-center">
                                You have no active bookings or history associated with your account. Please note that account deletion is <strong>permanent</strong> and <strong>cannot be undone</strong>. Once deleted, all your account information will be removed from our system.
                            </p>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger"
                                data-bs-target="#confirmationModal" data-bs-toggle="modal"
                                data-bs-dismiss="modal">Proceed</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="confirmationModal" tabindex="-1"
                aria-labelledby="confirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="image w-100 text-center">
                            <img src="../../Assets/Images/Icon/warning.png" alt="warning icon"
                                class="warning-image">

                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="email"
                                value="<?= htmlspecialchars($data['email']) ?>">
                            <p class="modal-title text-center mb-2">Are you sure?</p>
                            <p class="modal-text text-center mb-2">You won't be able to revert this!</p>
                            <div class="button-container">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">No</button>
                                <button type="submit" class="btn btn-danger" name="yesDelete"
                                    id="yesDelete">Yes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <form action="../../Function/Account/deleteAccount.php" method="POST">
            <!-- Verification Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="w-100 text-center">
                                <h5 class="modal-title">This action cannot be undone</h5>
                                <input type="hidden" name="email"
                                    value="<?= htmlspecialchars($data['email']) ?>">
                            </div>
                            <button type="button" class="btn-close btn btn-danger ms-2"
                                data-bs-dismiss="modal" aria-label="Close">
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php
                            if (isset($_SESSION['modal-error'])) {
                                echo '<div class="message-container alert alert-danger text-center">' . htmlspecialchars($_SESSION['modal-error']) . '</div>';
                                unset($_SESSION['modal-error']);
                            }
                            ?>

                            <p class="modal-text text-center mb-2">Please enter the verification code
                            </p>

                            <div class="text-center">
                                <input type="text" name="enteredOTP" id="enteredOTP"
                                    class="form-control d-inline-block w-50 text-center"
                                    placeholder="6 digit security code" required>
                            </div>

                            <div class="button-container">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary"
                                    name="verifyCode">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <script>
        //Handle sidebar for responsiveness
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById('toggle-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const items = document.querySelectorAll('.list-group-item');
            const toggleCont = document.getElementById('toggle-container')

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');

                if (sidebar.classList.contains('collapsed')) {
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    });
                    toggleCont.style.justifyContent = "center"
                } else {
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    });
                    toggleCont.style.justifyContent = "flex-end"
                }
            });

            function handleResponsiveSidebar() {
                if (window.innerWidth <= 1024) {
                    sidebar.classList.add('collapsed');
                    toggleBtn.style.display = "flex";
                    mainContent.style.marginLeft = "16vw"
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    })

                } else {
                    toggleBtn.style.display = "none";
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    });
                    mainContent.style.marginLeft = "290px";
                    sidebar.classList.remove('collapsed');
                }
            }

            // Run on load and when window resizes
            handleResponsiveSidebar();
            window.addEventListener('resize', handleResponsiveSidebar);
        });
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Show -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramsValue = params.get('action')
        // const confirmationBtn = document.getElementById("confirmationBtn");
        const warningModal = document.getElementById("warningModal");
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
                    window.location.href = "../../Function/logout.php";
                }
            });
        });

        // confirmationBtn.addEventListener("click", function() {
        //     const myconfirmationModal = new bootstrap.Modal(confirmationModal);
        //     myconfirmationModal.show();
        // });

        if (paramsValue === 'success') {
            const myModal = new bootstrap.Modal(deleteModal);
            myModal.show();
        } else if (paramsValue === 'hasTransaction') {
            Swal.fire({
                icon: "info",
                title: "Account Deletion Unavailable",
                text: "It looks like your account has a booking linked to it, so we`re unable to delete it — even if the booking is completed or cancelled.",
                confirmButtonText: "Okay"
            });
        } else if (paramsValue === 'noTransaction') {
            const myModal = new bootstrap.Modal(warningModal);
            myModal.show();
        }

        if (paramsValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>
</body>

</html>