<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS Links -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/auditLogs.css">
    <link rel="stylesheet" href="../../Assets/CSS/Admin/sidebar.css">
    <!-- CSS Links -->
    <!-- Bootstrap Links -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
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
    <main>
        <div id="sidebar" class="collapse show sidebar-custom">
            <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo"
                id="sbLogo">
            <ul class="nav flex-column">
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="adminDashboard.php">
                        <i class="bi bi-speedometer2"></i> <span id="linkText">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="booking.php">
                        <i class="bi bi-calendar-week"></i><span id="linkText"> Bookings</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="reviews.php">
                        <i class="bi bi-list-stars"></i> <span id="linkText">Reviews</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="roomList.php">
                        <i class="bi bi-door-open"></i> <span id="linkText">Rooms</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="services.php">
                        <i class="bi bi-bell"></i> <span id="linkText">Services</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="transaction.php">
                        <i class="bi bi-credit-card-2-front"></i> <span id="linkText">Payments</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="displayPartnership.php">
                        <i class="bi bi-people"></i> <span id="linkText">Partnerships</span>
                    </a>
                </li>
                <li class="nav-item" id="navLI">
                    <a class="nav-link" href="editWebsite/editWebsite.php">
                        <i class="bi bi-pencil-square"></i> <span id="linkText">Edit Website</span>
                    </a>
                </li>
                <li class="nav-item active" id="navLI">
                    <a class="nav-link" href="auditLogs.php">
                        <i class="bi bi-clock-history"></i> <span id="linkText">Audit Logs</span>
                    </a>
                </li>
            </ul>


            <section>
                <a href="../Account/account.php" class="profileContainer" id="pfpContainer">
                    <img src=" ../../Assets/Images/defaultProfile.png" alt="Admin Profile"
                        class="rounded-circle profilePic">
                    <h5 class="admin-name" id="adminName">Diane Dela Cruz</h5>
                </a>
            </section>

            <section class="btn btn-outline-danger logOutContainer">
                <a href="../../Function/Admin/logout.php" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    <h5>Log Out</h5>
                </a>
            </section>
        </div>

        <section class="auditLog-container">
            <section class="notification-toggler-container">
                <div class="sbToggle-container">
                    <button class="toggle-button" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar"
                        aria-controls="sidebar">
                        <i class="bi bi-layout-sidebar"></i>
                    </button>
                </div>
                <div class="notification-container position-relative">
                    <button type="button" class="btn position-relative" data-bs-toggle="modal"
                        data-bs-target="#notificationModal">
                        <i class="bi bi-bell" id="notification-icon"></i>
                        <?php if (!empty($counter)): ?>
                        <?= htmlspecialchars($counter) ?>
                        </span>
                        <?php endif; ?>
                    </button>
                </div>
            </section>

            <section class="page-title-container">
                <h5 class="page-title">Audit Logs</h5>
            </section>

            <div class="auditLog-table">
                <div class="card">

                    <table class="table table-striped display nowrap" id="auditLogTable">
                        <thead>
                            <th scope="col">Log ID</th>
                            <th scope="col">Admin Id</th>
                            <th scope="col">Action</th>
                            <th scope="col">Target</th>
                            <th scope="col">Details</th>
                            <th scope="col">Time Stamp</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>001</td>
                                <td>002</td>
                                <td><span class="badge bg-warning text-capitalize">Update</span></td>
                                <td>Resort</td>
                                <td>Resort Services</td>
                                <td>2025-10-09 14:30:00</td>
                            </tr>

                            <tr>
                                <td>001</td>
                                <td>002</td>
                                <td><span class="badge bg-success text-capitalize">Create</span></td>
                                <td>Resort</td>
                                <td>Resort Services</td>
                                <td>2025-10-09 14:30:00</td>
                            </tr>

                            <tr>
                                <td>001</td>
                                <td>002</td>
                                <td><span class="badge bg-danger text-capitalize">Delete</span></td>
                                <td>Resort</td>
                                <td>Resort Services</td>
                                <td>2025-10-09 14:30:00</td>
                            </tr>

                            <tr>
                                <td>001</td>
                                <td>002</td>
                                <td><span class="badge bg-info text-capitalize">Approved</span></td>
                                <td>Resort</td>
                                <td>Resort Services</td>
                                <td>2025-10-09 14:30:00</td>
                            </tr>

                            <tr>
                                <td>001</td>
                                <td>002</td>
                                <td><span class="badge bg-red text-capitalize">Rejected</span></td>
                                <td>Resort</td>
                                <td>Resort Services</td>
                                <td>2025-10-09 14:30:00</td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>



        </section>
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="../../Assets/JS/datatables.min.js"></script>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>


    <!-- Table JS -->
    <script>
    $('#auditLogTable').DataTable({
        // responsive: false,
        // scrollX: true,
        // columnDefs: [{
        //         width: '10%',
        //         targets: 0
        //     },
        //     {
        //         width: '15%',
        //         targets: 1
        //     },
        //     {
        //         width: '15%',
        //         targets: 2
        //     },
        //     {
        //         width: '15%',
        //         targets: 3
        //     },
        //     {
        //         width: '15%',
        //         targets: 4
        //     },
        //     {
        //         width: '10%',
        //         targets: 5
        //     },
        //     {
        //         width: '10%',
        //         targets: 6
        //     },
        // ],
    });
    </script>



    <script src="../../Assets/JS/adminNavbar.js"></script>
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


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->

</body>

</html>