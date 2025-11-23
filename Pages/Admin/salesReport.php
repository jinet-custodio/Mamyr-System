<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();

require_once '../../Function/Helpers/statusFunctions.php';
changeToDoneStatus($conn);

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
    case 3:
        $role = "Admin";
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
    <title>Sales Report </title>

    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS LINK -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/salesReport.css">

    <!-- BOOTSTRAP LINK -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
</head>

<body>
    <header class="header">

        <?php if ($userRole === 3) { ?>
            <a href="adminDashboard.php" id="backToDashboard" class="backButton">
                <img src="../../Assets/Images/Icon/arrowBtnBlack.png" alt="back to dashboard" id="back-btn">
            </a>
        <?php } elseif ($userRole === 2) { ?>
            <a href="../BusinessPartner/bpDashboard.php" id="backToDashboard" class="backButton">
                <img src="../../Assets/Images/Icon/arrowBtnBlack.png" alt="back to dashboard" id="back-btn">
            </a>
        <?php } ?>
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
                            <th>Booking Code</th>
                            <th>Customer Name</th>
                            <th>Booking Type</th>
                            <?php if ($userRole === 3) { ?>
                                <th>Total Guest</th>
                            <?php } elseif ($userRole === 2) { ?>
                                <th>Service Name</th>
                            <?php   } ?>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Payment Method</th>
                            <th>Total Cost</th>
                        </tr>

                    </thead>
                    <tbody>
                        <?php
                        $enableDownloadBtn = false;
                        $encodedPartnershipID = $_GET['id'] ?? 0;
                        $partnershipID = (int) base64_decode($encodedPartnershipID);

                        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generateReport'])) {
                            $reportDate = $_POST['reportDate'];
                            $reportDate = trim(preg_replace('/\s+/', ' ', $reportDate));
                            $dates = preg_split('/\s+to\s+/i', $reportDate);
                            $doneID = 6; //Done
                            $paymentStatusID = $reservedID = 3; //Fully Paid, Reserved
                            $approvedStatusID = $partiallyPaidID = 2; //approved, partially paid
                            if (count($dates) === 2) {
                                $selectedStartDate = DateTime::createFromFormat('F d, Y', trim($dates[0]))->format('Y-m-d') . ' 00:00:00';
                                $selectedEndDate = DateTime::createFromFormat('F d, Y', trim($dates[1]))->format('Y-m-d') . ' 23:59:59';

                                // print_r($reportDate);
                                // print_r($selectedStartDate);
                                // print_r($dates);

                                if ($userRole === 3) { //Admin
                                    $getReportData = $conn->prepare("SELECT LPAD(b.bookingID, 4, '0') AS formattedBookingID, b.bookingCode,
                                            b.bookingType, u.firstName, u.lastName, b.guestCount AS guest, 
                                            b.startDate, b.endDate, b.paymentMethod, b.totalCost, 
                                            cb.paymentApprovalStatus, cb.paymentStatus, 
                                            CASE WHEN bpas.bookingID IS NULL THEN cb.finalBill 
                                            ELSE cb.finalBill - bpas.price
                                            END AS confirmedFinalBill
                                            FROM confirmedbooking cb
                                            LEFT JOIN booking b ON cb.bookingID = b.bookingID
                                            -- LEFT JOIN payment p ON cb.confirmedBookingID = cb.confirmedBookingID
                                            LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID
                                            LEFT JOIN businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID
                                            LEFT JOIN user u ON b.userID = u.userID
                                            LEFT JOIN custompackage cp ON b.customPackageID = cp.customPackageID
                                            LEFT JOIN custompackageitem cpi ON cp.customPackageID = cpi.customPackageID 
                                            LEFT JOIN service s ON (bs.serviceID = s.serviceID OR cpi.serviceID = s.serviceID)
                                            LEFT JOIN partnershipservice ps  ON s.partnershipServiceID = ps.partnershipServiceID  
                                            WHERE cb.paymentApprovalStatus IN (?,?) AND b.bookingStatus IN (?,?,?) AND b.startDate BETWEEN ? AND ? 
                                            GROUP BY 
                                            b.bookingID, b.bookingType, u.firstName, u.lastName, b.guestCount, 
                                            b.startDate, b.endDate, b.paymentMethod, b.totalCost, 
                                            cb.paymentApprovalStatus, cb.paymentStatus, cb.finalBill
                                            ");
                                    $getReportData->bind_param("iiiiiss", $paymentStatusID, $partiallyPaidID, $reservedID,  $approvedStatusID, $doneID,  $selectedStartDate, $selectedEndDate);
                                } elseif ($userRole === 2) { //Partner
                                    $getReportData = $conn->prepare("SELECT LPAD(b.bookingID, 4, '0') AS formattedBookingID, b.bookingCode,
                                            cb.paymentApprovalStatus, cb.paymentStatus,
                                            b.bookingType, b.startDate, b.endDate, b.paymentMethod, cb.finalBill AS confirmedFinalBill,
                                            bs.serviceID, bs.bookingServicePrice,
                                            cp.customPackageID, cpi.customPackageID , cpi.serviceID,  
                                            s.serviceID, s.partnershipServiceID,
                                            ps.partnershipID, ps.PBName, ps.partnershipServiceID,	
                                            u.firstName, u.lastName

                                            FROM confirmedbooking cb
                                            LEFT JOIN booking b ON cb.bookingID = b.bookingID 
                                            -- LEFT JOIN payment p ON cb.confirmedBookingID = b.confirmedBookingID  
                                            LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID
                                            LEFT JOIN custompackage cp ON b.customPackageID = cp.customPackageID
                                            LEFT JOIN custompackageitem cpi ON cp.customPackageID = cpi.customPackageID 
                                            LEFT JOIN service s ON (bs.serviceID = s.serviceID OR cpi.serviceID = s.serviceID)
                                            LEFT JOIN partnershipservice ps  ON s.partnershipServiceID = ps.partnershipServiceID 
                                            LEFT JOIN businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID                     
                                            LEFT JOIN user u ON b.userID = u.userID

                                            WHERE cb.paymentApprovalStatus IN (?,?) AND b.startDate BETWEEN ? AND ?  AND ps.partnershipID = ?  AND bpas.approvalStatus = ?                        
                                            ");
                                    $getReportData->bind_param("iissii", $paymentStatusID, $partiallyPaidID, $selectedStartDate, $selectedEndDate, $partnershipID, $approvedStatusID);
                                }
                                $getReportData->execute();
                                $getReportDataResult = $getReportData->get_result();


                                if ($getReportDataResult->num_rows > 0) {
                                    $enableDownloadBtn = true;
                                    $_SESSION['reportData'] = [];
                                    while ($row = $getReportDataResult->fetch_assoc()) {
                                        $_SESSION['reportData'][] = $row;
                                        $bookingCode = $row['bookingCode'];
                                        $formattedBookingID = $row['formattedBookingID'];
                                        $bookingType = $row['bookingType'];
                                        $customerName = ucfirst($row['firstName']) . ' ' . ucfirst($row['lastName']);
                                        $guest = $row['guest'] ?? 0;
                                        $rawStartDate = $row['startDate'] ?? null;
                                        $rawEndDate = $row['endDate'] ?? null;
                                        $startDate = date('Y-m-d h:i A', strtotime($rawStartDate));
                                        $endDate = date('Y-m-d h:i A', strtotime($rawEndDate));
                                        $paymentMethod = $row['paymentMethod'];
                                        $totalCost = $row['confirmedFinalBill'];
                                        $partnerServiceName = $row['PBName'] ?? null;
                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($bookingCode) ?></td>
                                            <td><?= htmlspecialchars($customerName) ?></td>
                                            <td><?= htmlspecialchars($bookingType) ?></td>
                                            <?php if ($userRole === 3) { ?>
                                                <td><?= htmlspecialchars($guest) ?></td>
                                            <?php } elseif ($userRole === 2) { ?>
                                                <td><?= htmlspecialchars($partnerServiceName) ?></td>
                                            <?php   } ?>
                                            <td><?= $startDate ?></td>
                                            <td><?= $endDate ?></td>
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
                <?php if ($role === 'Admin'): ?>
                    <hr class="separator-line">

                    <h5 class="bulkBooking-title">Bulk Booking Report</h5>
                    <table class="table table-striped bulkBooking-table">
                        <thead>
                            <tr>
                                <th colspan="2">Date Period</th>
                                <th>Type of Booking</th>
                                <th>Total Booking/s</th>
                                <th>Total Sale/s</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generateReport'])) {
                                $reportDate = $_POST['reportDate'];
                                $reportDate = trim(preg_replace('/\s+/', ' ', $reportDate));
                                $dates = preg_split('/\s+to\s+/i', $reportDate);
                                $doneID = 6; //Done
                                $paymentStatusID = $reservedID = 3; //Fully Paid, Reserved
                                $approvedStatusID = $partiallyPaidID = 2; //approved, partially paid
                                if (count($dates) === 2) {
                                    $selectedStartDate = DateTime::createFromFormat('F d, Y', trim($dates[0]))->format('Y-m-d') . ' 00:00:00';
                                    $selectedEndDate = DateTime::createFromFormat('F d, Y', trim($dates[1]))->format('Y-m-d') . ' 23:59:59';


                                    $dataQuery = $conn->prepare("SELECT `startDate`, `endDate`, `bookingType`, `bookingCount`, `salesAmount` FROM `walkin_sales_summary` WHERE startDate BETWEEN ? AND ?");
                                    $dataQuery->bind_param("ss", $selectedStartDate, $selectedEndDate);
                                    $dataQuery->execute();
                                    $result = $dataQuery->get_result();

                                    if ($result->num_rows > 0) {

                                        $_SESSION['bulkData'] = [];
                                        while ($row = $result->fetch_assoc()) {
                                            $_SESSION['bulkData'][] = $row;

                                            $startDate = $row['startDate'] ?? null;
                                            $endDate = $row['endDate'] ?? null;
                                            $bookingType = $row['bookingType'] ?? null;
                                            $bookingCount = intval($row['bookingCount'] ?? null);
                                            $salesAmount = floatval($row['salesAmount'] ?? null);

                                            if (date('F', strtotime($startDate)) === date('F', strtotime($endDate))) {
                                                $datePeriod = date("M. d ", strtotime($startDate)) . ' to ' . date("d, Y", strtotime($endDate));
                                            } else {
                                                $datePeriod = date("M. d, Y", strtotime($startDate)) . ' to ' . date("M. d, Y", strtotime($endDate));
                                            }

                            ?>
                                            <tr>
                                                <td colspan="2"><?= $datePeriod ?></td>
                                                <td><?= $bookingType ?> </td>
                                                <td><?= $bookingCount ?></td>
                                                <td>₱<?= number_format($salesAmount, 2) ?></td>
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
                <?php endif;  ?>

                <div class="button-container">
                    <form action="../../Function/Admin/generatePDF.php" method="POST" target="_blank">
                        <input type="hidden" name="partnershipID" id="partnershipID" value="<?= $partnershipID ?>">
                        <input type="hidden" name="selectedStartDate" id="selectedStartDate"
                            value="<?= $selectedStartDate ?>">
                        <input type="hidden" name="selectedEndDate" id="selectedEndDate"
                            value="<?= $selectedEndDate ?>">
                        <button type="submit" name="generatePDF" id="generatePDF" class="btn btn-primary"
                            <?= $enableDownloadBtn ? '' : 'disabled' ?>>Download PDF</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap Link -->
    <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script>


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