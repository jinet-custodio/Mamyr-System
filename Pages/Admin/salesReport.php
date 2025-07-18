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
    <title>Sales Report </title>


    <!-- CSS LINK -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/salesReport.css">

    <!-- BOOTSTRAP LINK -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
</head>

<body>
    <header class="header">
        <a href="adminDashboard.php" id="backToDashboard" class="backButton">
            <img src="../../Assets/Images/Icon/arrow.png" alt="back to dashboard" id="back-btn">
        </a>
        <div class="pagetitle">
            <img src="../../Assets/Images/Icon/Statistics.png" alt="" id="sales-logo">
            <h1>Sales Report</h1>
        </div>
    </header>
    <main>
        <div class="container-fluid">
            <!-- Temporary form action lang hehe para ma-print yung ininput ng user-->
            <form action="" method="POST">
                <div class="dateRange">
                    <label for="reportDate">Report Period: </label>
                    <div class="date-picker">
                        <div class="input-wrapper w-100">
                            <input type="text" name="reportDate" id="reportDate" placeholder="Click to enter date">
                            <i class="fa-solid fa-calendar" id="calendarIcon"></i>
                        </div>
                        <div class="error-message"></div>
                    </div>
                </div>
                <button type="submit" class="generateBtn btn btn-primary" name="generateReport" id="generateReport">
                    Generate Report
                </button>
            </form>


            <div class="container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer Name</th>
                            <th>Booking Type</th>
                            <th>Total Guest</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Payment Method</th>
                            <th>Total Cost</th>
                        </tr>

                    </thead>
                    <tbody>
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generateReport'])) {
                            $reportDate = $_POST['reportDate'];
                            $dates = preg_split('/\s*to\s*/', $reportDate);
                            $approvedStatusID = 2;

                            if (count($dates) === 2) {
                                $selectedStartDate = DateTime::createFromFormat('F d, Y', trim($dates[0]))->format('Y-m-d') . ' 00:00:00';
                                $selectedEndDate = DateTime::createFromFormat('F d, Y', trim($dates[1]))->format('Y-m-d') . ' 23:59:59';

                                $getReportData = $conn->prepare("SELECT LPAD(b.bookingID, 4, '0') AS formattedBookingID, 
                        b.bookingType, u.firstName, b.paxNum AS guest, 
                        b.startDate, b.endDate, 
                        b.paymentMethod, b.totalCost
                        FROM confirmedBookings cb
                        LEFT JOIN bookings b ON cb.bookingID = b.bookingID
                        LEFT JOIN users u ON b.userID = u.userID
                        WHERE cb.confirmedBookingStatus = ? AND b.startDate BETWEEN ? AND ?
                        ");

                                $getReportData->bind_param("iss", $approvedStatusID, $selectedStartDate, $selectedEndDate);
                                $getReportData->execute();
                                $getReportDataResult = $getReportData->get_result();
                                if ($getReportDataResult->num_rows > 0) {
                                    while ($row = $getReportDataResult->fetch_assoc()) {
                                        $formattedBookingID = $row['formattedBookingID'];
                                        $bookingType = $row['bookingType'];
                                        $firstName = $row['firstName'];
                                        $guest = $row['guest'];
                                        $startDate = $row['startDate'];
                                        $endDate = $row['endDate'];
                                        $paymentMethod = $row['paymentMethod'];
                                        $totalCost = $row['totalCost'];

                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($formattedBookingID) ?></td>
                                            <td><?= htmlspecialchars($firstName) ?></td>
                                            <td><?= htmlspecialchars($bookingType) ?></td>
                                            <td><?= htmlspecialchars($guest) ?></td>
                                            <td><?= htmlspecialchars($startDate) ?></td>
                                            <td><?= htmlspecialchars($endDate) ?></td>
                                            <td><?= htmlspecialchars($paymentMethod) ?></td>
                                            <td>₱<?= number_format($totalCost, 2) ?></td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="8" class="text-center no-data-text">No bookings found for selected dates</td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center no-data-text">Invalid Date Format</td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="8" class="text-center no-data-text">No data available</td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>

                <div class="button-container">
                    <form action="../../Function/Admin/generatePDF.php" method="POST">
                        <input type="hidden" name="selectedStartDate" id="selectedStartDate" value="<?= $selectedStartDate ?>">
                        <input type="hidden" name="selectedEndDate" id="selectedEndDate" value="<?= $selectedEndDate ?>">
                        <button type="submit" name="generatePDF" id="generatePDF" class="btn btn-primary w-100">Download PDF</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>


    <!-- Flatpickr Link -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr('#reportDate', {
            mode: "range",
            minDate: null,
            maxDate: "today",
            dateFormat: "F d, Y"
        });

        const calIcon = document.getElementById("calendarIcon");
        const reportDate = document.getElementById("reportDate");

        calIcon.addEventListener('click', function(event) {
            reportDate.click()
        })

        const generateReportBtn = document.getElementById("generateReport");
        const errorMessage = document.querySelector(".error-message");


        generateReportBtn.addEventListener("click", function(event) {
            const reportDateValue = reportDate.value.trim();

            if (reportDateValue === '') {
                event.preventDefault();
                errorMessage.innerHTML = 'Please choose the date range you want for the report';
            } else {
                errorMessage.innerHTML = '';
                errorMessage.style.border = "none";
            };
        });
    </script>

</body>

</html>