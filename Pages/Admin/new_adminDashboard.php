<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS Links -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/new_adminDashboard.css">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/sidebar.css">
    <!-- CSS Links -->
    <!-- Bootstrap Links -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Links -->
    <!-- Icon Links -->
    <link rel="stylesheet" href="https://cdn.hugeicons.com/font/hgi-stroke.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Icon Links -->
</head>

<body>
    <div id="sidebar">
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo">
        <ul class="nav flex-column">
            <li class="nav-item active">
                <i class="bi bi-speedometer2"></i>
                <a class="nav-link" href="#">Dashboard</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-calendar-week"></i>
                <a class="nav-link" href="#">Bookings</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-list-stars"></i>
                <a class="nav-link" href="#">Reviews</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-door-open"></i>
                <a class="nav-link" href="#">Rooms</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-bell"></i>
                <a class="nav-link" href="#">Services</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-credit-card-2-front"></i>
                <a class="nav-link" href="#">Payments</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-people"></i>
                <a class="nav-link" href="#">Partnerships</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-pencil-square"></i>
                <a class="nav-link" href="#">Edit Website</a>
            </li>
        </ul>

        <section class="profileContainer">
            <img src="../../Assets/Images/defaultProfile.png" alt="Admin Profile" class="rounded-circle profilePic">
            <h5 class="admin-name">Diane Dela Cruz</h5>

        </section>

        <section class="btn btn-outline-danger logOutContainer">
            <button class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i>
                <h5>Log Out</h5>
            </button>
        </section>
    </div>

    <main class="dashboard-container">

        <section class="notification-container">
            <i class="bi bi-bell" id="notification-icon"></i>
        </section>

        <section class="container topSection">

            <div class="card customer-card">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-people"></i>
                        <h6 class="header-text">Customers</h6>
                    </div>

                    <div class="data-container customer">
                        <h5 class="card-data" id="customer-data">300</h5>
                        <div class="spanContainer">
                            <span class="status"><i class="bi bi-arrow-up-short"></i>
                                <h6 id="customer-status">78.8%</h6>
                            </span>
                            <h6 class="span-caption">vs last month</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card total-bookings">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-calendar-check"></i>
                        <h6 class="header-text">Total Bookings</h6>
                    </div>

                    <div class="data-container ">
                        <h5 class="card-data" id="totalBooking-data">30</h5>
                    </div>
                </div>
            </div>

            <div class="card total-bookings">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-tags"></i>
                        <h6 class="header-text">Sales</h6>
                    </div>

                    <div class="data-container">
                        <h5 class="card-data" id="sales-data">100,000 php</h5>
                    </div>
                </div>
            </div>

            <div class="card mostUsedSrvice-card">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-bell"></i>
                        <h6 class="header-text">Most Used Service</h6>
                    </div>

                    <div class="data-container">
                        <h5 class="card-data" id="muService-data">Resort</h5>
                    </div>
                </div>
            </div>

            <div class="card occupancy-card">
                <div class="card-body">
                    <div class="header">
                        <i class="bi bi-people"></i>
                        <h6 class="header-text">Current Occupancy</h6>
                    </div>

                    <div class="data-container">
                        <h5 class="card-data" id="occupancy-data">50</h5>
                    </div>
                </div>
            </div>
        </section>

        <section class="container bottomSection">
            <div class="card graph-card">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <i class="bi bi-calendar-check"></i>
                        <h6 class="graph-header-text">Bookings</h6>

                        <div class="filter-btn-container">
                            <div class="filter-select-wrapper">
                                <select class="filter-select">
                                    <option selected disabled>Filters</option>
                                    <option value="#"></option>
                                </select>
                                <i class="bi bi-funnel"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bookings-chart">
                        <img src="../../Assets/Images/adminTemporary/bookingsGraph.jpg" alt="Bookings Graph"
                            class="graph" id="bookingsBar">
                        <!-- <canvas class="graph" id="bookingsBar"></canvas> -->
                    </div>
                </div>
            </div>

            <div class="calendar-rating-container">

                <div class="card ratings-card">
                    <div class="card-body graph-card-body">
                        <div class="graph-header">
                            <i class="bi bi-star"></i>
                            <h6 class="graph-header-text">Ratings</h6>
                        </div>

                        <div class="rating-categories">
                            <!-- Resort -->
                            <div class="rating-row">
                                <div class="rating-label">Resort</div>
                                <div class="rating-bar">
                                    <div class="progress">
                                        <div class="progress-bar" id="resort-bar" role="progressbar" style="width: 88%;"
                                            aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="rating-value" id="resort-rating-value">4.4</div>
                            </div>

                            <!-- Hotel -->
                            <div class="rating-row">
                                <div class="rating-label">Hotel</div>
                                <div class="rating-bar">
                                    <div class="progress">
                                        <div class="progress-bar" id="hotel-bar" role="progressbar" style="width: 92%;"
                                            aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="rating-value" id="hotel-rating-value">4.6</div>
                            </div>

                            <!-- Event -->
                            <div class="rating-row">
                                <div class="rating-label">Event</div>
                                <div class="rating-bar">
                                    <div class="progress">
                                        <div class="progress-bar" id="event-bar" role="progressbar" style="width: 95%;"
                                            aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="rating-value" id="event">4.8</div>
                            </div>

                            <!-- Overall Rating (Optional) -->
                            <div class="overall-rating">
                                <div class="overall-rating-label">
                                    <h6 class="overall-rating-label">Overall Rating</h6>
                                    <h4 class="overall-rating-value">4.6</h4>
                                </div>
                                <div class="overall-rating-stars">
                                    <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i>
                                    <i class="bi bi-star-fill" id="overall-rating"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card calendar-card">
                    <div id="calendar"></div>
                </div>


            </div>


            <div class="card graph-card">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <i class="bi bi-tags"></i>
                        <h6 class="graph-header-text">Sales</h6>

                        <div class="filter-btn-container">
                            <div class="filter-select-wrapper">
                                <select class="filter-select">
                                    <option selected disabled>Filters</option>
                                    <option value="#"></option>
                                </select>
                                <i class="bi bi-funnel"></i>
                            </div>
                        </div>
                    </div>

                    <div class="sales-chart">
                        <img src="../../Assets/Images/adminTemporary/bookingsGraph.jpg" alt="Bookings Graph"
                            class="graph" id="salesBar">
                        <!-- <canvas class="graph" id="salesBar"></canvas> -->
                        <a href="#" class="btn btn-primary gen-rep-btn" id="gen-rep">Generate Sales Report</a>
                    </div>

                </div>
            </div>

            <div class="card graph-card">
                <div class="card-body graph-card-body">
                    <div class="graph-header">
                        <i class="bi bi-receipt-cutoff"></i>
                        <h6 class="graph-header-text">Payments</h6>

                        <div class="filter-btn-container">
                            <div class="filter-select-wrapper">
                                <select class="filter-select">
                                    <option selected disabled>Filters</option>
                                    <option value="#"></option>
                                </select>
                                <i class="bi bi-funnel"></i>
                            </div>
                        </div>
                    </div>

                    <div class="payments-chart">
                        <img src="../../Assets/Images/adminTemporary/bookingsGraph.jpg" alt="Bookings Graph"
                            class="graph" id="paymentsBar">
                        <!-- <canvas class="graph" id="paymentsBar"></canvas> -->

                    </div>

                </div>
            </div>
        </section>


    </main>












    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Display if no available data -->
    <script src="../../Assets/JS/ChartNoData.js"> </script>

    <!-- full calendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth'

        });
        calendar.render();
    });
    </script>
</body>

</html>