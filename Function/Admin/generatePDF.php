<?php
require '../../Config/dbcon.php';
require_once __DIR__ . '/../../vendor/autoload.php';
session_start();
date_default_timezone_set('Asia/Manila'); //Set default time zone 
$mpdf = new \Mpdf\Mpdf();
$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);
if (isset($_POST['generatePDF'])) {



    $selectedStartDate  = mysqli_real_escape_string($conn, $_POST['selectedStartDate']);
    $selectedEndDate  = mysqli_real_escape_string($conn, $_POST['selectedEndDate']);
    $dateToday = date("l, F d, Y (g:i A)");


    $startDateFormatted = date('Y-m-d 00:00:00', strtotime($selectedStartDate));
    $endDateFormatted = date('Y-m-d 23:59:59', strtotime($selectedEndDate));

    $getProfile = $conn->prepare("SELECT firstName, middleInitial, lastName FROM users WHERE userID = ? AND userRole = ?");
    $getProfile->bind_param("ii", $userID, $userRole);
    $getProfile->execute();
    $getProfileResult = $getProfile->get_result();

    if ($getProfileResult->num_rows > 0) {
        $data = $getProfileResult->fetch_assoc();
        $middleInitial = trim($data['middleInitial']);
        $name = ucfirst($data['firstName']) . " " . ucfirst($middleInitial) . " " . ucfirst($data['lastName']);
    } else {
        $name = "Unknown User";
    }


    ob_start();
?>

    <!DOCTYPE html>
    <html>

    <head>
        <title>Sales Report - Mamyr Resort and Events Place</title>
        <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">

        <style>
            .logo {
                height: 35px;
                margin-top: -5px;

            }

            .header-title {
                font-size: 20px;
                margin-top: -55px;

            }

            .headerTextContainer {
                margin-top: 37px;
            }

            h4 {
                font-family: "Poppins Light";
                font-size: 12px;
                margin-top: -30px;
            }

            .section-title {
                text-align: center;
                font-size: 18px;
            }

            hr {
                width: 95%;
                height: 2px;
                margin-top: -15px;
                background-color: black;
            }

            p {
                font-size: 12px;
            }

            .request {
                text-align: right;
            }

            .table {
                width: 100%;
                margin-top: 75px;
                border: 1px solid black;
                border-collapse: collapse;
                text-align: center;
                font-size: 12px;

            }

            tr tr,
            td,
            th {
                border: 1px solid black;
                padding: 10px;

            }

            .no-data-text {
                font-weight: bold;
                color: red;
            }

            .signatories {
                margin-top: 70px;

            }
        </style>
    </head>

    <body>

        <header>
            <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
            <h1 class="header-title" style="text-align: center;">Mamyr Resort & Events Place</h2>

                <div class="headerTextContainer">
                    <h4 style="text-align:center;">Gabihan, San Ildefonso, Bulacan <br> mamyresort@gmail.com | (0998) 962
                        4697
                    </h4>

                </div>
        </header>
        <hr>
        <main>
            <div class="background-image">

                <section class="contents">
                    <h2 class="section-title">Sales Report</h2>
                    <p style="text-align: left; margin-top: 40px"><strong>Report Generated:</strong> <?= $dateToday ?></p>
                    <p style="text-align: left;"><strong>Date Range:</strong>
                        <?= date("F d, Y", strtotime($selectedStartDate)) ?> to
                        <?= date("F d, Y", strtotime($selectedEndDate)) ?></p>

                </section>
                <p class="request" style="text-align: right; margin-top: -60px"><strong>Requested By:</strong>
                    <?= htmlspecialchars($name) ?></p>

                <section class="contents">
                    <table class="table">
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
                            $totalBookings = 0;
                            $totalCost = 0;
                            $approvedStatusID = 2;

                            $getReportData = $conn->prepare("SELECT LPAD(b.bookingID, 4, '0') AS formattedBookingID, 
                    b.bookingType, u.firstName, b.paxNum AS guest, 
                    b.startDate, b.endDate, 
                    b.paymentMethod, b.totalCost
                    FROM confirmedBookings cb
                    LEFT JOIN bookings b ON cb.bookingID = b.bookingID
                    LEFT JOIN users u ON b.userID = u.userID
                    WHERE cb.confirmedBookingStatus = ? AND b.startDate BETWEEN ? AND ?");

                            $getReportData->bind_param("iss", $approvedStatusID, $startDateFormatted, $endDateFormatted);
                            $getReportData->execute();
                            $getReportDataResult = $getReportData->get_result();

                            if ($getReportDataResult->num_rows > 0) {
                                while ($row = $getReportDataResult->fetch_assoc()) {
                                    $totalBookings++;
                                    $totalCost += $row['totalCost'];
                            ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['formattedBookingID']) ?></td>
                                        <td><?= htmlspecialchars($row['firstName']) ?></td>
                                        <td><?= htmlspecialchars($row['bookingType']) ?></td>
                                        <td><?= htmlspecialchars($row['guest']) ?></td>
                                        <td><?= date('F d, Y', strtotime($row['startDate'])) ?></td>
                                        <td><?= date('F d, Y', strtotime($row['endDate'])) ?></td>
                                        <td><?= htmlspecialchars($row['paymentMethod']) ?></td>
                                        <td>₱<?= number_format($row['totalCost'], 2) ?></td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="8" class="no-data-text">No bookings found for selected dates</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </section>

                <section class="contents">
                    <h2 class="section-title" style="margin-top: 50px;">Report Summary</h2>
                    <p style="text-align: left; margin-top: 20px;"><strong>Total Bookings:</strong> <?= $totalBookings ?>
                    </p>
                    <p style="text-align: left;"><strong>Total Cost:</strong> ₱<?= number_format($totalCost, 2) ?></p>
                </section>

                <section class="signatories">
                    <p style="text-align: left; margin-left:60px;"><strong>Signed By:</strong> ________________________</p>
                </section>
                <p style="text-align:right; margin-top: -25px; margin-right:60px;"><strong>Submitted By:</strong>
                    ______________________</p>
            </div>
        </main>

    </body>

    </html>

<?php

    $html = ob_get_clean();

    $mpdf->WriteHTML($html);
    $mpdf->Output('Sales_Report.pdf', 'I');
    exit;
}
