<?php
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}
require '../../Function/notification.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/reviews.css">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/sidebar.css">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <aside id="sidebar">
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo">
        <ul class="nav flex-column">
            <li class="nav-item">
                <i class="bi bi-speedometer2"></i>
                <a class="nav-link" href="adminDashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-calendar-week"></i>
                <a class="nav-link" href="booking.php">Bookings</a>
            </li>
            <li class="nav-item active">
                <i class="bi bi-list-stars"></i>
                <a class="nav-link" href="reviews.php">Reviews</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-door-open"></i>
                <a class="nav-link" href="roomList.php">Rooms</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-bell"></i>
                <a class="nav-link" href="services.php">Services</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-credit-card-2-front"></i>
                <a class="nav-link" href="transaction.php">Payments</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-people"></i>
                <a class="nav-link" href="displayPartnership.php">Partnerships</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-pencil-square"></i>
                <a class="nav-link" href="editWebsite/editWebsite.php">Edit Website</a>
            </li>
            <li class="nav-item">
                <i class="bi bi-clock-history"></i>
                <a class="nav-link" href="auditLogs.php">Audit Logs</a>
            </li>
        </ul>
        <section class="profileContainer">
            <img src="../../Assets/Images/defaultProfile.png" alt="Admin Profile" class="rounded-circle profilePic">
            <h5 class="admin-name">Diane Dela Cruz</h5>

        </section>

        <section class="btn btn-outline-danger logOutContainer">
            <a href="../../Function/Admin/logout.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i>
                <h5>Log Out</h5>
            </a>
        </section>
    </aside>
    <main>
        <!-- Booking-container -->
        <section class="booking-container">
            <section class="notification-container">
                <i class="bi bi-bell" id="notification-icon"></i>
            </section>

            <section class="page-title-container">
                <h5 class="page-title">Reviews</h5>
            </section>



            <section class="container filter-section">
                <select class="form-select monthSelection" id="filterSelect">
                    <option value="" disabled>Select a Time Range</option>
                    <option value="1" selected>Last 30 days</option>
                    <option value="2">Last 3 months</option>
                    <option value="3">Last 6 months</option>
                    <option value="4">This year</option>
                    <option value="5">This quarter</option>
                    <option value="6">Older</option>
                </select>
            </section>

            <section class="container cardContainer" id="reviewsContainer">
            </section>


        </section>
    </main>


    <!-- Notification Modal -->
    <?php include '../notificationModal.php' ?>

    <!-- Bootstrap Link -->
    <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Notification Ajax -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const badge = document.querySelector('.notification-container .badge');

            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function() {
                    const notificationID = this.dataset.id;

                    fetch('../../Function/notificationFunction.php', {
                            method: 'POST',
                            headers: {
                                'Content-type': 'application/x-www-form-urlencoded'
                            },
                            body: 'notificationID=' + encodeURIComponent(notificationID)
                        })
                        .then(response => response.text())
                        .then(data => {

                            this.style.transition = 'background-color 0.3s ease';
                            this.style.backgroundColor = 'white';


                            if (badge) {
                                let currentCount = parseInt(badge.textContent, 10);

                                if (currentCount > 1) {
                                    badge.textContent = currentCount - 1;
                                } else {
                                    badge.remove();
                                }
                            }
                        });
                });
            });
        });
    </script>

    <script>
        function fetchReviews(filterValue) {
            fetch('../../Function/Admin/fetchReviews.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'filter=' + encodeURIComponent(filterValue)
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('reviewsContainer').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching reviews:', error);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const selectBox = document.getElementById('filterSelect');

            fetchReviews(1); // "Last 30 days"

            selectBox.addEventListener('change', function() {
                fetchReviews(this.value);
            });
        });
    </script>


    <!-- Responsive Navbar -->
    <script src="../../Assets/JS/adminNavbar.js"></script>
</body>

</html>