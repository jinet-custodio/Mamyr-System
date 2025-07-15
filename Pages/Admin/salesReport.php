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
        <div class="container-fluid" style="border: 1px solid red;">
            <!-- Temporary form action lang hehe para ma-print yung ininput ng user-->
            <form action="salesReport.php" method="POST">
                <div class="dateRange">
                    <label for="reportDate">Report Period: </label>
                    <div class="date-picker">
                        <div class="input-wrapper w-100">
                            <input type="text" name="reportDate" id="reportDate" placeholder="Click to enter date">
                            <i class="fa-solid fa-calendar" id="calendarIcon"></i>
                        </div>
                    </div>
                </div>
                <button type="button" class="generateBtn btn btn-outline-primary" name="generateReport" id="generateReport">
                    Generate Report
                </button>
            </form>


            <div class="table">

                <?php

                ?>
                <table>
                    <thead>

                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>




    </main>


    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr('#reportDate', {
            mode: "range",
            minDate: null,
            dateFormat: "m-d-y"
        });

        const calIcon = document.getElementById("calendarIcon");
        const reportDate = document.getElementById("reportDate");

        calIcon.addEventListener('click', function(event) {
            reportDate.click()
        })


        const generateReportBtn = document.getElementById("generateReport");

        generateReportBtn.addEventListener("click", function() {
            const reportDateValue = reportDate.value.trim();

            if (reportDateValue.includes(" to ")) {
                const [firstDate, lastDate] = reportDateValue.split(" to ");
                console.log("First date:", firstDate);
                console.log("Last date:", lastDate);
            } else {
                console.log("Only one date selected:", reportDateValue);
            }

            
        })
    </script>

</body>

</html>