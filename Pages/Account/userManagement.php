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
        href="../../Assets/Images/Icon/favicon.png" />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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
            </ul>
            <div class="logout">
                <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn" style="margin: 3vw auto;">
                    <i class="fa-solid fa-arrow-right-from-bracket sidebar-icon"></i>
                    <span class="sidebar-text ms-2">Logout</span>

            </div>
        </aside>
        <!-- End Side Bar -->

        <!-- Customer Information Container -->
        <main class="main-content" id="main-content">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">User Management</h5>
                    <a href="addAccount.php" class="btn btn-light add-button"><img src="../../Assets/Images/Icon/addUser.png" alt=""> Add an Account</a>
                </div>
                <div class="card-body">
                    <table class="table table-hover" id="usertable">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col" class="emailCol">Email</th>
                                <th scope="col">Role</th>
                                <th scope="col">Status</th>
                                <th scope="col">Date Created</th>
                                <th scope="col">Action</th>
                            </tr>

                        </thead>
                        <tbody id="user-table-body"> </tbody>
                    </table>

                    <!-- Confirmation Modal -->
                    <form action="../../Function/Account/deleteUserAccount.php" method="POST">
                        <div class="modal fade " id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="image w-100 text-center">
                                        <i class="fa-solid fa-circle-exclamation" style="color: #b61b1b;"></i>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="selectedUserID" value="">
                                        <p class="modal-title text-center mb-2">Are you sure?</p>
                                        <p class="modal-text text-center mb-2">Deleting this account will remove all user access from the system. Their booking records will be retained for historical or reporting purposes, but personal identifiers (such as their name) will be removed. This action is permanent and cannot be undone.</p>
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
                        width: "18%"
                    },
                    {
                        targets: 1,
                        width: "25%"
                    },
                    {
                        targets: 2,
                        width: "10%"
                    },
                    {
                        targets: 3,
                        width: "10%"
                    },
                    {
                        targets: 4,
                        width: "15%"
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
        function getRoleBadge(role) {
            let colorClass = "";
            switch (role.toLowerCase()) {
                case "admin":
                    colorClass = "badge bg-danger";
                    break;
                case "customer":
                    colorClass = "badge bg-primary";
                    break;
                case "business partner":
                case "partner":
                    colorClass = "badge bg-success";
                    break;
                case "applicant":
                    colorClass = "badge bg-warning ";
                    break;
                default:
                    colorClass = "badge bg-secondary";
            }
            return `<span class="${colorClass} text-capitalize">${role}</span>`;
        }

        function getStatusBadge(status) {
            let colorClass = "";
            switch (status.toLowerCase()) {
                case "verified":
                case "active":
                    colorClass = "badge bg-success";
                    break;
                case "pending":
                    colorClass = "badge bg-warning ";
                    break;
                default:
                    colorClass = "badge bg-light text-muted";
            }
            return `<span class="${colorClass} text-capitalize">${status}</span>`;
        }

        fetch("../../Function/Admin/Ajax/getUsers.php")
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || "Failed to load user data.");
                    return;
                }

                const users = data.users;
                const table = $('#usertable').DataTable();
                table.clear();

                users.forEach(user => {
                    table.row.add([
                        user.name,
                        user.email,
                        getRoleBadge(user.role),
                        getStatusBadge(user.status),
                        user.date,
                        `<div class="button-container">
                            <form action="viewUser.php" method="POST" id="viewForm">
                                <input type="hidden" name="selectedUserID" value="${user.userID}">
                                <button type="submit" class="btn btn-info viewBtn" name="viewUser">View</button>
                            </form>
                            <button 
                                type="button" 
                                class="btn btn-danger deleteUserAccount"
                                data-bs-toggle="modal" 
                                data-bs-target="#confirmationModal"
                                data-userid="${user.userID}" >
                                Delete
                            </button>
                        </div>`
                    ]);
                });


                table.draw();

            }).catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Failed to load data from the server.'
                })
            });


        const deleteBtns = document.querySelectorAll('.deleteUserAccount');

        document.addEventListener('click', function(e) {
            const target = e.target;
            if (target.classList.contains('deleteUserAccount')) {
                const userId = target.getAttribute("data-userid");
                const confirmationModal = document.getElementById("confirmationModal");
                if (!confirmationModal) return;

                const userIdInput = confirmationModal.querySelector('input[name="selectedUserID"]');
                if (userIdInput) userIdInput.value = userId;
            }
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

    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('action');

        if (paramValue === "accountDeleted") {
            Swal.fire({
                position: 'top-right',
                title: "Confirmed",
                text: "The account has been deleted.",
                icon: "success",
                showConfirmButton: false,
                timer: 1500
            });
        } else if (paramValue === "failed") {
            Swal.fire({
                title: "Action Failed",
                text: "We were unable to delete the account. Please try again later.",
                icon: "error"
            });
        } else if (paramValue === "userCreated") {
            Swal.fire({
                position: 'top-right',
                title: "Confirmed",
                text: "New Account Created Successfully.",
                icon: "success",
                showConfirmButton: false,
                timer: 1500
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