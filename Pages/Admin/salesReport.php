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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportRange = $_POST['reportDate'] ?? '';

    // Split the string by " to "
    $dates = explode(" to ", $reportRange);

    $startDate = $dates[0] ?? null;
    $endDate = $dates[1] ?? null;

    echo "Start Date: " . htmlspecialchars($startDate) . "<br>";
    echo "End Date: " . htmlspecialchars($endDate);
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report </title>
    <link rel="stylesheet" href="../../Assets/CSS/Admin/salesReport.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <a href="#" class="navbar-brand">
            <img src="../../Assets/Images/Icon/Statistics.png" alt="" id="sales-logo"> Sales Report
        </a>
    </nav>
    <main>
        <div class="container-fluid">
            <!-- Temporary form action lang hehe para ma-print yung ininput ng user-->
            <form action="salesReport.php" method="POST">
                <div class="dateRange">
                    <label for="reportDate">Report Period: </label>
                    <input type="text" name="reportDate" id="reportDate" placeholder="Click to enter date">
                    <i class="fa-solid fa-calendar" id="calendarIcon" style="margin-left: -2vw;">
                    </i>
                </div>
                <div class="salesSummary">
                    <h2>Sales Summary:</h2>
                    <div class="report">

                    </div>
                    <div class="generateBtn">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr('#reportDate', {
            mode: "range",
            minDate: "today",
            dateFormat: "Y-m-d"
        });

        const calIcon = document.getElementById("calendarIcon");
        const reportDate = document.getElementById("reportDate");

        calIcon.addEventListener('click', function(event) {
            reportDate.click()
        })
    </script>
</body>

</html>