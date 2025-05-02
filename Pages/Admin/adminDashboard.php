<?php
require '../../Config/dbcon.php';
session_start();
$userID = 1;
$_SESSION['userID'] =  $userID;
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/adminDashboard.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>

    <div class="topSection">
        <div class="dashTitleContainer">
            <a href="#" class="dashboardTitle" id="dashboard"><img src="../../Assets/images/MamyrLogo.png" alt=""
                    class="logo"></a>
        </div>

        <div class="menus">
            <a href="#" class="notifs">
                <img src="../../Assets/Images/Icon/notification.png" alt="Notification Icon">
            </a>
            <a href="#" class="chat">
                <img src="../../Assets/Images/Icon/chat.png" alt="home icon">
            </a>

            <h5 class="adminTitle">Mamyr Admin</h5>
            <a href="#" class="admin">
                <img src="../../Assets/Images/Icon/profile.png" alt="home icon">
            </a>
        </div>
    </div>

    <nav class="navbar d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">

            <a class="nav-link" href="../Pages/dashboard.php">
                <img src="../../Assets/Images/Icon/Dashboard.png" alt="Dashboard">
                <h5>Dashboard</h5>
            </a>

            <a class="nav-link" href="#">
                <img src="../../Assets/Images/Icon/uim-schedule.png" alt="Bookings">
                <h5>Bookings</h5>
            </a>


            <a class="nav-link" href="#">
                <img src="../../Assets/Images/Icon/Hotel.png" alt="Rooms">
                <h5>Rooms</h5>
            </a>



            <a class="nav-link" href="#">
                <img src="../../Assets/Images/Icon/Credit card.png" alt="Payments">
                <h5>Payments</h5>
            </a>


            <a class="nav-link" href="#">
                <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue">
                <h5>Revenue</h5>
            </a>


            <a class="nav-link" href="#">
                <img src="../../Assets/Images/Icon/Friend request.png" alt="Requests">
                <h5>Requests</h5>
            </a>

            <a class="nav-link" href="#">
                <img src="../../Assets/Images/Icon/Edit Button.png" alt="Edit Website">
                <h5>Edit Website</h5>
            </a>

        </div>

        <div class="logout-btn">
            <form class="container-fluid justify-content-start">
                <button class="btn btn-outline-primary me-2" type="submit" id="logOut" value="logOut" name="logOut">
                    Log Out
                </button>
            </form>
        </div>
    </nav>

    <div class="container-fluid" id="contentsCF">
        <div class="leftSection">

            <div class="bookingSummary">

                <div class="card" id="newBookings" style="width: 15rem; ">
                    <div class="card-header">
                        New Bookings
                    </div>

                </div>

                <div class="card" id="checkIn" style="width: 15rem; ">
                    <div class="card-header">
                        Check In
                    </div>

                </div>

                <div class="card" id="checkOut" style="width: 15rem; ">
                    <div class="card-header">
                        Check Out
                    </div>

                </div>

                <div class="card" id="revenue" style="width: 15rem; ">
                    <div class="card-header">
                        Revenue
                    </div>

                </div>

            </div>


            <div class="ReservationTrendsContainer">


                <div class="card" id="sched" style="width: 20rem; height: 30rem;">
                    <div class="card-header">
                        Upcoming Schedules
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">9:30 A.M - Executives' Meeting</li>
                        <li class="list-group-item">10:00 A.M - New Sale Record</li>
                        <li class="list-group-item">12:00 A.M - Shipment</li>
                        <li class="list-group-item">1:00 P.M - New Arrival Record</li>
                        <li class="list-group-item">2:00 P.M - New Policy Meeting</li>
                        <li class="list-group-item">2:30 P.M - Logistics Review</li>
                        <li class="list-group-item">4:00 P.M - Documentation</li>
                        <li class="list-group-item">4:30 P.M - Final Meeting</li>

                    </ul>
                </div>
            </div>
        </div>

        <div class="rightSection">


            <div class="card revenueCard" id="revenueGraphContainer">
                <div class="revenueTitleContainer">
                    <h5 class="mb-0 fw-bold revTitle" style="text-align: left;">REVENUE</h5>
                </div>

                <div class="graphImg">
                    <img src="../../Assets/Images/AdminImages/DashboardImages/graph.png" alt="" class="revenueGraph">
                </div>
            </div>
        </div>

    </div>
</body>

</html>