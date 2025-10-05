<?php
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

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

unset($_SESSION['account-form']);
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
        href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/userManagement.css" />

    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">

    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
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

    $getUserInfo = $conn->prepare("SELECT * FROM user WHERE userID = ? AND userRole = ?");
    $getUserInfo->bind_param("ii", $userID, $userRole);
    $getUserInfo->execute();
    $getUserInfoResult = $getUserInfo->get_result();
    if ($getUserInfoResult->num_rows > 0) {
        $data =  $getUserInfoResult->fetch_assoc();


        $imageData = $data['userProfile'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);
        $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }
    ?>

    <div class="wrapper d-flex">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="d-flex justify-content-center" id="toggle-container">
                <button id="toggle-btn" type="button" class="btn toggle-button" style="display: none;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </button>
            </div>
            <div class="home">
                <?php if ($role === 'Customer') { ?>
                    <a href="../Customer/dashboard.php">
                        <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                    </a>
                <?php } elseif ($role === 'Admin') { ?>
                    <a href="../Admin/adminDashboard.php">
                        <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                    </a>
                <?php } elseif ($role === 'Business Partner') { ?>
                    <a href="../BusinessPartner/bpDashboard.php">
                        <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                    </a>
                <?php } ?>
            </div>

            <div class="sidebar-header text-center">
                <h5 class="sidebar-text">User Account</h5>
                <div class="profileImage">
                    <img src="<?= htmlspecialchars($image) ?>"
                        alt="<?= htmlspecialchars($data['firstName']) ?> Picture">
                </div>
            </div>
            <ul class="list-group sidebar-nav">
                <li class="sidebar-item">
                    <a href="account.php" class="list-group-item ">
                        <i class="fa-solid fa-user sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
                    </a>
                </li>

                <?php if ($role === 'Customer' || $role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bookingHistory.php" class="list-group-item" id="paymentBookingHist">
                            <i class="fa-solid fa-table-list sidebar-icon"></i>
                            <span class="sidebar-text">Payment & Booking History</span>
                        </a>
                    </li>
                <?php } elseif ($role === 'Admin') { ?>
                    <li class="sidebar-item">
                        <a href="userManagement.php" class="list-group-item active">
                            <i class="fa-solid fa-people-roof sidebar-icon"></i>
                            <span class="sidebar-text">Manage Users</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bpBookings.php" class="list-group-item">
                            <i class="fa-regular fa-calendar-days sidebar-icon"></i>
                            <span class="sidebar-text">Bookings</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpServices.php" class="list-group-item">
                            <i class="fa-solid fa-bell-concierge sidebar-icon"></i>
                            <span class="sidebar-text">Services</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpSales.php" class="list-group-item">
                            <i class="fa-solid fa-money-bill-trend-up sidebar-icon"></i>
                            <span class="sidebar-text">Sales</span>
                        </a>
                    </li>
                <?php } ?>

                <li class="sidebar-item">
                    <a href="loginSecurity.php" class="list-group-item">
                        <i class="fa-solid fa-user-shield sidebar-icon"></i>
                        <span class="sidebar-text">Login & Security</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="deleteAccount.php" class="list-group-item">
                        <i class="fa-solid fa-user-slash sidebar-icon"></i>
                        <span class="sidebar-text">Delete Account</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn"
                        style="margin: 3vw auto;">
                        <i class="fa-solid fa-arrow-right-from-bracket sidebar-icon"></i>
                        <span class="sidebar-text ms-2">Logout</span>
                    </button>
                </li>
            </ul>
        </aside> <!-- End Side Bar -->
        <!-- End Side Bar -->
        <!-- Customer Information Container -->
        <main class="main-content" id="main-content">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">User Management</h5>
                    <a href="addAccount.php" class="btn btn-light add-button"><img src="../../Assets/Images/Icon/addUser.png" alt=""> Add an Account</a>
                </div>
                <div class="card-body">
                    <table class="table" id="usertable">
                        <thead>
                            <th scope="col">Name</th>
                            <th scope="col" class="emailCol">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date Created</th>
                            <th scope="col">Action</th>
                        </thead>
                        <tbody>
                            <?php

                            $selectUsers = $conn->prepare("SELECT u.*, ut.typeName as roleName, stat.statusName as status
                            FROM user u
                            INNER JOIN usertype ut ON u.userRole = ut.userTypeID
                            INNER JOIN userstatus stat ON u.userStatusID = stat.userStatusID
                            WHERE u.userID != ? AND  u.userStatusID != ?
                            ORDER BY u.userRole DESC");
                            $selectUsers->bind_param("ii", $userID, $userStatusID);
                            $selectUsers->execute();
                            $selectUsersResult = $selectUsers->get_result();
                            if ($selectUsersResult->num_rows > 0) {
                                $users = $selectUsersResult->fetch_all(MYSQLI_ASSOC);
                                foreach ($users as $userData) {
                                    $middleInitial = trim($userData['middleInitial']);
                                    $name = ucfirst($userData['firstName']) . ($middleInitial ? " " . ucfirst($middleInitial) . "." : "") . " " . ucfirst($userData['lastName']);
                                    $status =  $userData['status'];
                                    $role = $userData['roleName'];
                                    $dataCreated = date("F d, Y", strtotime($userData['createdAt']));
                                    if ($status === 'Verified') {
                                        $image = '../../Assets/Images/Icon/greencircle.png';
                                    } elseif ($status === 'Pending') {
                                        $image = '../../Assets/Images/Icon/yellowcircle.png';
                                    }
                            ?>
                                    <tr>
                                        <td><?= htmlspecialchars($name) ?></td>
                                        <td class="emailCol"><?= htmlspecialchars($userData['email']) ?> </td>
                                        <td class="user-role"><?= htmlspecialchars($role) ?></td>
                                        <td class="statusText">
                                            <span class="status-label"><?= htmlspecialchars(ucfirst($userData['status'])) ?></span>
                                            <img src="<?= $image ?>" alt="" class="status-image">
                                        </td>
                                        <td><?= htmlspecialchars($dataCreated) ?> </td>
                                        <td>
                                            <div class="button-container">
                                                <form action="viewUser.php" method="POST" id="viewForm">
                                                    <input type="hidden" name="selectedUserID" value="<?= htmlspecialchars($userData['userID']) ?>">
                                                    <button type="submit" class="btn btn-info viewBtn" name="viewUser">View</button>
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
                    <form action="../../Function/Account/deleteUserAccount.php" method="POST">
                        <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="image w-100 text-center">
                                        <img src="../../Assets/Images/Icon/warningRed.png" alt="warning icon" class="warning-image">
                                    </div>
                                    <input type="hidden" name="selectedUserID" value="<?= htmlspecialchars($userData['userID']) ?>">
                                    <div class="modal-body">
                                        <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>">
                                        <p class="modal-title text-center mb-2">Are you sure?</p>
                                        <p class="modal-text text-center mb-2">Deleting this account will remove all of their data from the system. This action cannot be reverted.</p>
                                        <div class="button-container modal-buttons">
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
        </main>
    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#usertable').DataTable({
                language: {
                    emptyTable: "No users found."
                },
                columnDefs: [{
                        targets: 0,
                        width: "15%"
                    },
                    {
                        targets: 1,
                        width: "23%"
                    },
                    {
                        targets: 2,
                        width: "13%"
                    },
                    {
                        targets: 3,
                        width: "15%"
                    },
                    {
                        targets: 4,
                        width: "17%"
                    },
                    {
                        targets: 5,
                        width: "17%"
                    }
                ]
            });
        });
    </script>
    <script>
        //Handle sidebar for responsiveness
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById('toggle-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const items = document.querySelectorAll('.list-group-item');
            const toggleCont = document.getElementById('toggle-container');
            const viewBtns = document.querySelectorAll('.viewBtn');
            const deleteBtns = document.querySelectorAll('.deleteUserAccount');
            const roles = document.querySelectorAll('.user-role');
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
                if (window.innerWidth <= 600) {
                    sidebar.classList.add('collapsed');
                    viewBtns.forEach(viewBtn => {
                        viewBtn.innerHTML = '<i class="fa-regular fa-eye"></i>';
                    })
                    deleteBtns.forEach(deleteBtn => {
                        deleteBtn.innerHTML = '<i class="fa-solid fa-user-xmark"></i>';
                    })
                    roles.forEach(role => {
                        // console.log(role.innerHTML);
                        if (role.innerHTML == "Customer") {
                            role.innerHTML = '<i class="fa-solid fa-user status-icon"></i>';
                        } else if (role.innerHTML == "Partner") {
                            role.innerHTML = '<i class="fa-solid fa-handshake status-icon"></i>';
                        } else if (role.innerHTML == "Admin") {
                            role.innerHTML = '<i class="fa-solid fa-user-tie status-icon"></i>';
                        } else if (role.innerHTML == "PartnerRequest") {
                            role.innerHTML = '<i class="fa-solid fa-hourglass-half status-icon"></i>';
                        }
                    })
                } else {
                    toggleBtn.style.display = "none";
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    })
                    sidebar.classList.remove('collapsed');
                    viewBtns.forEach(viewBtn => {
                        viewBtn.innerHTML = 'View';
                    })
                    deleteBtns.forEach(deleteBtn => {
                        deleteBtn.innerHTML = 'Delete';
                    })
                    roles.forEach(role => {
                        // console.log(role.innerHTML);
                        if (role.innerHTML == '<i class="fa-solid fa-user status-icon"></i>') {
                            role.innerHTML = 'Customer';
                        } else if (role.innerHTML == '<i class="fa-solid fa-handshake status-icon"></i>') {
                            role.innerHTML = 'Partner';
                        } else if (role.innerHTML == '<i class="fa-solid fa-user-tie status-icon"></i>') {
                            role.innerHTML = 'Admin';
                        } else if (role.innerHTML == '<i class="fa-solid fa-hourglass-half status-icon"></i>') {
                            role.innerHTML = 'PartnerRequest';
                        }
                    })
                }
                //change the text into icons when the screen width shrinks to below 1024px
                if (window.innerWidth <= 1024) {
                    sidebar.classList.add('collapsed');
                    toggleBtn.style.display = "flex";
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    })

                }
            }

            // Run on load and when window resizes
            handleResponsiveSidebar();
            window.addEventListener('resize', handleResponsiveSidebar);
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
                        window.location.href = "../../Function/Admin/logout.php";
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