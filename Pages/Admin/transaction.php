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
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/transaction.css" />

</head>

<body>
    <div class="topSection">
        <div class="dashTitleContainer">
            <a href="adminDashboard.php" class="dashboardTitle" id="dashboard"><img
                    src="../../Assets/images/MamyrLogo.png" alt="" class="logo"></a>
        </div>


        <div class="menus">
            <a href="#" class="notifs">
                <img src="../../Assets/Images/Icon/notification.png" alt="Notification Icon">
            </a>
            <a href="#" class="chat">
                <img src="../../Assets/Images/Icon/chat.png" alt="home icon">
            </a>
            <?php
            if ($userRole == 3) {
                $admin = "Admin";
            } else {
                $_SESSION['error'] = "Unauthorized Access!";
                session_destroy();
                header("Location: ../register.php");
                exit();
            }

            if ($admin === "Admin") {
                $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $firstName = $row['firstName'];
                    $profile = $row['userProfile'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_buffer($finfo, $profile);
                    finfo_close($finfo);
                    $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
                } else {
                    $firstName = 'None';
                }
            } else {
                $_SESSION['error'] = "Unauthorized Access!";
                session_destroy();
                header("Location: ../register.php");
                exit();
            }
            ?>
            <h5 class="adminTitle"><?= ucfirst($firstName) ?></h5>
            <a href="Account/account.php" class="admin">
                <img src="<?= htmlspecialchars($image) ?>" alt="home icon">
            </a>
        </div>
    </div>

    <nav class="navbar">

        <a class="nav-link" href="adminDashboard.php">
            <img src="../../Assets/Images/Icon/Dashboard.png" alt="Dashboard">
            <h5>Dashboard</h5>
        </a>

        <a class="nav-link" href="booking.php">
            <img src="../../Assets/Images/Icon/uim-schedule.png" alt="Bookings">
            <h5>Bookings</h5>
        </a>


        <a class="nav-link" href="roomList.php">
            <img src="../../Assets/Images/Icon/Hotel.png" alt="Rooms">
            <h5>Rooms</h5>
        </a>

        <a class="nav-link active" href="#">
            <img src="../../Assets/Images/Icon/Credit card.png" alt="Payments">
            <h5>Payments</h5>
        </a>


        <!-- <a class="nav-link" href="#">
                <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue">
                <h5>Revenue</h5>
            </a> -->


        <a class="nav-link" href="displayPartnership.php">
            <img src="../../Assets/Images/Icon/partnership.png" alt="Partnerships">
            <h5>Partnerships</h5>
        </a>

        <a class="nav-link" href="#">
            <img src="../../Assets/Images/Icon/Edit Button.png" alt="Edit Website">
            <h5>Edit Website</h5>
        </a>

        <a href="../../Function/Admin/logout.php" class="btn btn-danger">
            Log Out
        </a>

    </nav>

    <div class="card">
        <div class=" titleContainer">
            <h3 class="title">Transaction</h3>
        </div>


        <div class="tableContainer">
            <table class=" table table-striped" id="transaction">
                <thead>
                    <th scope="col">Guest</th>
                    <th scope="col">Total Payment</th>
                    <th scope="col">Balance Due</th>
                    <th scope="col">Payment Status</th>
                    <th scope="col">Payment Method</th>
                    <th scope="col">Action</th>
                </thead>

                <tbody>
                    <tr>
                        <td>Louise Bartolome</td>
                        <td>1,500 Php</td>
                        <td>0</td>
                        <td><span class="btn btn-success w-75" id="fullyPaidStatus">Fully Paid</span></td>
                        <td>Cash</td>
                        <td><button class="btn btn-warning w-75">View</button></td>
                    </tr>
                    <tr>
                        <td>Alliah Reyes</td>
                        <td>3,500 Php</td>
                        <td>3,500 Php</td>
                        <td><span class="btn btn-danger w-75" id="unpaidStatus">No Payment</span></td>
                        <td>Cash</td>
                        <td><button class="btn btn-warning w-75">View</button></td>
                    </tr>
                    <tr>
                        <td>Shan Ignacio</td>
                        <td>1,500 Php</td>
                        <td>0</td>
                        <td><span class="btn btn-primary w-75" id="partiallyPaidStatus">Partially Paid</span></td>
                        <td>Cash</td>
                        <td><button class="btn btn-warning w-75">View</button></td>
                    </tr>
                    <tr>
                        <td>Jeanette Custodio</td>
                        <td>1,500 Php</td>
                        <td>0</td>
                        <td><span class="btn btn-success w-75" id="fullyPaidStatus">Fully Paid</span></td>
                        <td>Cash</td>
                        <td><button class="btn btn-warning w-75">View</button></td>
                    </tr>
                    <tr>
                        <td>Jannine Correa</td>
                        <td>1,500 Php</td>
                        <td>0</td>
                        <td><span class="btn btn-success w-75" id="fullyPaidStatus">Fully Paid</span></td>
                        <td>Cash</td>
                        <td><button class="btn btn-warning w-75">View</button></td>
                    </tr>
                </tbody>
        </div>









        <!-- Jquery Link -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <!-- DataTables Link -->
        <script src="../../Assets/JS/datatables.min.js"></script>
        <!-- Table JS -->
        <script>
        $(document).ready(function() {
            $('#transaction').DataTable();
        });
        </script>

        <!-- Bootstrap Link -->
        <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
        </script>


</body>

</html>