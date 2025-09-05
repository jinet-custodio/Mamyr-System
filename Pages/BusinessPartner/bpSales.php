<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];


if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Partner Sales - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- Bootstrap Link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/BusinessPartner/bpSales.css">
    <!-- DataTables Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css" />
    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

</head>

<body>
    <!-- Get the information to the database -->
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

    $getData = $conn->prepare("SELECT u.*, ut.typeName as roleName FROM user u
            INNER JOIN usertype ut ON u.userRole = ut.userTypeID
            WHERE u.userID = ? AND userRole = ?");
    $getData->bind_param("ii", $userID, $userRole);
    $getData->execute();
    $getDataResult = $getData->get_result();
    if ($getDataResult->num_rows > 0) {
        $data =  $getDataResult->fetch_assoc();
        $middleInitial = trim($data['middleInitial'] ?? '');
        $name = ucfirst($data['firstName'] ?? '') . " " .
            ucfirst($data['middleInitial'] ?? '') . " " .
            ucfirst($data['lastName'] ?? '');
        $profile = $data['userProfile'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $profile);
        finfo_close($finfo);
        $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
    }

    ?>
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="d-flex" id="toggle-container">
                <button id="toggle-btn" type="button" class="btn toggle-button" style="display: none;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </button>
            </div>
            <div class="home text-center">
                <?php if ($role === 'Customer') { ?>
                <a href="../Customer/dashboard.php">
                    <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                </a>
                <?php } elseif ($role === 'Admin') { ?>
                <a href="../Admin/adminDashboard.php">
                    <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                </a>
                <?php } elseif ($role === 'Business Partner') { ?>
                <a href="../Customer/dashboard.php">
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
                <?php if ($role === 'Business Partner') { ?>
                <li class="sidebar-item">
                    <a href="bpDashboard.php" class="list-group-item">
                        <i class="fa-solid fa-money-bill-trend-up sidebar-icon"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <?php } ?>
                <li class="sidebar-item">
                    <a href="../Account/account.php" class="list-group-item">
                        <i class="fa-regular fa-user sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
                    </a>
                </li>


                <?php if ($role === 'Customer' || $role === 'Business Partner') { ?>
                <li class="sidebar-item">
                    <a href="../Account/bookingHistory.php" class="list-group-item" id="paymentBookingHist">
                        <i class="fa-solid fa-table-list sidebar-icon"></i>
                        <span class="sidebar-text">Payment & Booking History</span>
                    </a>
                </li>
                <?php } elseif ($role === 'Admin') { ?>
                <li class="sidebar-item">
                    <a href="userManagement.php" class="list-group-item">
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
                    <a href="#" class="list-group-item active">
                        <i class="fa-solid fa-money-bill-trend-up sidebar-icon"></i>
                        <span class="sidebar-text">Revenue</span>
                    </a>
                </li>
                <?php } ?>

                <li class="sidebar-item">
                    <a href="../Account/loginSecurity.php" class="list-group-item">
                        <i class="fa-solid fa-user-shield sidebar-icon"></i>
                        <span class="sidebar-text">Login & Security</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="../Account/deleteAccount.php" class="list-group-item">
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


        <main class="main-content" id="main-content">
            <div class="container">
                <h3 class="welcomeText" id="title">Sales</h3>


                <div class="cardContainer">
                    <div class="card">
                        <div class="card-header fw-bold fs-5">Total Sales</div>
                        <div class="card-body">
                            <h2 class="totalSales">₱80,000</h2>
                            <a href="salesReport.php" class="btn btn-primary">Sales Report</a>
                        </div>
                    </div>

                </div>

                <div class="revenue-chart">
                    <canvas id="revenueBar"></canvas>
                </div>

                <!-- <div class="revenueBar">No data available.</div> -->
                <div class="revenue-chart">
                    <canvas id="revenueBar"></canvas>
                </div>




            </div>
        </main>



    </div>















    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- DataTables Link -->
    <script src="../../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
    $(document).ready(function() {
        $('#booking').DataTable({
            language: {
                emptyTable: "No Services"
            },
            columnDefs: [{
                width: '15%',
                target: 0

            }]
        });
    });
    </script>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    //Handle sidebar for responsiveness
    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.getElementById('toggle-btn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const items = document.querySelectorAll('.list-group-item');
        const toggleCont = document.getElementById('toggle-container');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');

            if (sidebar.classList.contains('collapsed')) {
                items.forEach(item => {
                    item.style.justifyContent = "center";
                });
                toggleCont.style.justifyContent = "center";
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
                toggleBtn.style.display = "flex";
                items.forEach(item => {
                    item.style.justifyContent = "center";
                })

            } else {
                toggleBtn.style.display = "none";
                items.forEach(item => {
                    item.style.justifyContent = "flex-start";
                })
                sidebar.classList.remove('collapsed');
            }
        }

        // Run on load and when window resizes
        handleResponsiveSidebar();
        window.addEventListener('resize', handleResponsiveSidebar);
    });
    </script>

    <!-- Show -->
    <script>
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutModal = document.getElementById('logoutModal');

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
    })
    </script>

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- <script src="path/to/chartjs/dist/chart.umd.js"></script> -->

    <script>
    Chart.register({
        id: 'noDataPlugin',
        beforeDraw(chart) {
            const dataset = '';
            const hasData = '';

            if (!hasData) {
                const ctx = chart.ctx;
                const {
                    width,
                    height
                } = chart;

                chart.clear();

                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.font = '20px Times New Roman';
                ctx.fillStyle = 'gray';
                ctx.fillText('No available data', width / 2, height / 2);
                ctx.restore();
            }
        }
    });


    const bar = document.getElementById("revenueBar").getContext('2d');

    const myBarChart = new Chart(bar, {
        type: 'bar',
        data: {
            labels: 'sales',
            datasets: [{
                label: 'Sales',
                data: '100',
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        },
        plugins: ['noDataPlugin']
    });
    </script>
</body>

</html>