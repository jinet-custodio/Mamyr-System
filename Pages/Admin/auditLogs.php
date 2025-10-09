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
        <div id="sidebar">
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
                <li class="nav-item">
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
                <li class="nav-item active">
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
        </div>


        <section class="auditLog-container">
            <section class="notification-container">
                <i class="bi bi-bell" id="notification-icon"></i>
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