<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();

require '../../Function/Partner/sales.php';

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
    case 2:
        $role = "Business Partner";
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
    <title>Business Partner Sales - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/bpSales.css">
    <link rel="stylesheet" href="../../Assets/CSS/Account/account-sidebar.css" />
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
    $getData = $conn->prepare("SELECT u.firstName, u.lastName, u.middleInitial, u.userProfile, ut.typeName as roleName , p.partnershipID FROM user u
            INNER JOIN usertype ut ON u.userRole = ut.userTypeID
            LEFT JOIN partnership p ON u.userID = p.userID
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

        $partnershipID = $data['partnershipID'];
        $encodedPartnershipID = base64_encode($partnershipID ?? '');
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
                <div class="profileImage">
                    <img src="<?= htmlspecialchars($image) ?>"
                        alt="<?= htmlspecialchars($data['firstName']) ?> Picture">
                </div>
            </div>
            <ul class="list-group sidebar-nav">
                <li class="sidebar-item">
                    <a href="account.php" class="list-group-item">
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
                <?php if ($role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bpBookings.php" class="list-group-item">
                            <i class="bi bi-calendar-week sidebar-icon"></i>
                            <span class="sidebar-text">Bookings</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpServices.php" class="list-group-item">
                            <i class="bi bi-bell sidebar-icon"></i>
                            <span class="sidebar-text">Services</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpSales.php" class="list-group-item active">
                            <i class="bi bi-tags sidebar-icon"></i>
                            <span class="sidebar-text">Sales</span>
                        </a>
                    </li>
                <?php } ?>

                <li class="sidebar-item">
                    <a href="loginSecurity.php" class="list-group-item">
                        <i class="bi bi-shield-check sidebar-icon"></i>
                        <span class="sidebar-text">Login & Security</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="deleteAccount.php" class="list-group-item">
                        <i class="bi bi-person-dash sidebar-icon"></i>
                        <span class="sidebar-text">Delete Account</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn"
                        style="margin: 3vw auto;">
                        <i class="bi bi-box-arrow-right logout-icon"></i>
                        <span class="sidebar-text ms-2">Logout</span>
                    </button>
                </li>
            </ul>
        </aside> <!-- End Side Bar -->


        <main class="main-content" id="main-content">
            <div class="container">
                <h3 class="welcomeText" id="title">Sales</h3>
                <?php $totalSales = getSales($conn, $userID); ?>
                <div class="cardContainer">
                    <div class="card">
                        <div class="card-header fw-bold fs-5">Total Sales</div>
                        <div class="card-body">
                            <h2 class="totalSales">
                                <?= ($totalSales !== 0) ? number_format($totalSales, 2) : 'No sales to display' ?></h2>
                            <a href="../Admin/salesReport.php?id=<?= $encodedPartnershipID ?>"
                                class="btn btn-primary">Sales Report</a>
                        </div>
                    </div>

                </div>

                <div class="revenue-chart">
                    <canvas id="revenueBar"></canvas>
                </div>
            </div>
        </main>
    </div>

    <?php
    $getPartnershipID = $conn->prepare('SELECT partnershipID FROM `partnership` WHERE userID = ?');
    $getPartnershipID->bind_param('i', $userID);
    $getPartnershipID->execute();
    $result = $getPartnershipID->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $partnershipID = $data['partnershipID'];
    }
    ?>

    <?php
    $paymentStatusID = 3; //Fully Paid
    $paymentApprovalID = 5; //Done
    $getYearlySales = $conn->prepare("SELECT 
                    YEAR(b.startDate) AS year,
                    SUM(IFNULL(bs.bookingServicePrice, 0) + IFNULL(cpi.ServicePrice, 0)) AS yearlySales,
                    ps.partnershipID
                     
                    FROM booking b
                    LEFT JOIN  confirmedbooking cb ON b.bookingID = cb.bookingID
                    LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID
                    LEFT JOIN custompackageitem cpi ON b.customPackageID = cpi.customPackageID
                    LEFT JOIN service s ON (cpi.serviceID = s.serviceID  OR bs.serviceID = s.serviceID)
                    LEFT JOIN partnershipservice ps ON s.partnershipServiceID = ps.partnershipServiceID
                     
                    WHERE cb.paymentApprovalStatus = ?
                    AND cb.paymentStatus = ?
                    AND YEAR(b.startDate) = YEAR(CURDATE()) 
                    AND DATE(b.endDate) < CURDATE()
                    AND ps.partnershipID = ?
                    GROUP BY 
                     year
                    ORDER BY 
                     year
        ");
    $getYearlySales->bind_param("iii", $paymentApprovalID, $paymentStatusID,  $partnershipID);
    if (!$getYearlySales->execute()) {
        error_log("Failed executing monthly sales in a year. Error: " . $getYearlySales->error);
    }
    $sales = [];
    $years = [];
    $result = $getYearlySales->get_result();
    if ($result->num_rows > 0) {
        while ($data = $result->fetch_assoc()) {
            $sales[] = (float) $data['yearlySales'];
            $years[] = 'Year —' . $data['year'] ?? 'Year —' . DATE('Y');
        }
    } else {
        error_log("No data " . $getYearlySales->error);
    }
    ?>

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
    <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script>

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
                if (window.innerWidth <= 1240) {
                    sidebar.classList.add('collapsed');
                    toggleBtn.style.display = "flex";
                    mainContent.style.marginLeft = "15vw";
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

    <!-- Show  -->
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

    <!-- This is shown if no data to display -->
    <!-- <script src="../../Assets/JS/ChartNoData.js"></script> -->

    <script>
        const bar = document.getElementById("revenueBar").getContext('2d');

        const myBarChart = new Chart(bar, {
            type: 'bar',
            data: {
                labels: <?= json_encode($years) ?>,
                datasets: [{
                    label: "Yearly Sales Report",
                    data: <?= json_encode($sales) ?>,
                    backgroundColor: 'rgba(14, 194, 194, 1)',
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