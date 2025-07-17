<?php
require '../../Config/dbcon.php';
require_once __DIR__ . '/../../vendor/autoload.php';
session_start();
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
        <title>Sales Report</title>
    </head>

    <body>

        <header>
            <h1 class="header-title">Mamyr Resort & Event Place</h1>
            <h3 style="text-align:center;">Gabihan, San Ildefonso, Bulacan</h3>
            <h4 style="text-align:center;">mamyrResort@gmail.com | 0999-999-9999</h4>
        </header>

        <main>
            <section>
                <h2 class="section-title">Sales Report</h2>
                <p><strong>Report Generated:</strong> <?= $dateToday ?></p>
                <p><strong>Date Range:</strong> <?= date("F d, Y", strtotime($selectedStartDate)) ?> to <?= date("F d, Y", strtotime($selectedEndDate)) ?></p>
            </section>

            <section>
                <p><strong>Requested By:</strong> <?= htmlspecialchars($name) ?></p>
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

            <section>
                <h2 class="section-title">Summary</h2>
                <p><strong>Monthly Total Bookings:</strong> <?= $totalBookings ?></p>
                <p><strong>Total Cost:</strong> ₱<?= number_format($totalCost, 2) ?></p>
            </section>

            <section>
                <p><strong>Signed By:</strong> ________________________</p>
                <p><strong>Submitted By:</strong> ______________________</p>
            </section>
        </main>

    </body>

    </html>

<?php

    $html = ob_get_clean();

    $mpdf->WriteHTML($html);
    $mpdf->Output('Sales_Report.pdf', 'I');
    exit;
}
