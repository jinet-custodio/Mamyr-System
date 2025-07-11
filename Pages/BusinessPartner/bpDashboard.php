<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Partner - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/BusinessPartner/bpDashboard.css">

</head>

<body>
    <!-- Side Bar -->
    <div class="sidebar">

        <div class="profileContainer">
            <a href="#"><img src="../../Assets/Images/defaultProfile.png" alt="home icon" class="profilePic"></a>
        </div>

        <ul class="list-group">
            <li>
                <a href="bpDashboard.php" class="list-group-item active">
                    <img src="../../Assets/Images/Icon/Dashboard.png" alt="Dashboard" class="sidebar-icon">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="#" class="list-group-item">
                    <img src="../../Assets/Images/Icon/booking.png" alt="Bookings" class="sidebar-icon">
                    Bookings
                </a>
            </li>

            <a href="deleteAccount.php" class="list-group-item">
                <img src="../../Assets/Images/Icon/services.png" alt="Services" class="sidebar-icon">
                Services
            </a>

            <li>
                <a href="#" class="list-group-item">
                    <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue" class="sidebar-icon">
                    Revenue
                </a>
            </li>



            </li>
            <li>
                <button type="button" class="btn btn-outline-danger" id="logoutBtn"> <img
                        src="../../../Assets/Images/Icon/logout.png" alt="Log Out" class="sidebar-icon">
                    Logout</button>
            </li>
        </ul>
    </div>

    <div class="container">
        <h3 class="welcomeText">Hello there, Partner!</h3>

        <div class="home">
            <a href="#"><img src="../../../Assets/Images/Icon/home2.png" alt="home icon" class="homeIcon"></a>
            <!-- this will lead to dashboard -->
        </div>

        <section>
            <div class="column1">
                <div class="card">
                    <div class="card-header fw-bold fs-5">Bookings</div>
                    <div class="card-body">
                        <h2 class="bookingNumber">8</h2>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header fw-bold fs-5">Approved</div>
                    <div class="card-body">
                        <h2 class="bookingNumber">5</h2>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header fw-bold fs-5">Pending</div>
                    <div class="card-body">
                        <h2 class="bookingNumber">3</h2>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header fw-bold fs-5">Revenue</div>
                    <div class="card-body">
                        <h2 class="bookingNumber">₱10,000</h2>
                    </div>
                </div>

            </div>

            <div class="card" id="salesPerformance">
                <div class="card-header fw-bold fs-5">Sales Performance</div>
                <div class="card-body" id="pieGraph">
                    <img src="../../Assets/Images/pieGraph.png" alt="Pie" class="pieGraph">
                </div>
            </div>

            <div class="card" id="revenue">
                <div class="card-header fw-bold fs-5">Revenue Overview</div>
                <div class="card-body" id="revenueGraphContainer">
                    <img src="../../Assets/Images/revenueGraph.png" alt="Pie" class="revenueGraph">
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-bold fs-5">Services</div>
                <div class="card-body">
                    <ul>
                        <li>Snacks</li>
                        <li>Photobooth</li>
                        <li>Videoke</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="#" class="btn btn-primary w-100">View All Services</a>
                </div>
            </div>


        </section>




    </div>


    <!-- End Side Bar -->





















    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutModal = document.getElementById('logoutModal');

    logoutBtn.addEventListener("click", function() {
        Swal.fire({
            title: "Are you sure you want to log out?",
            text: "You will need to log in again to access your account.",
            icon: "warning",
            showCancelButton: true,
            // confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, logout!",
            customClass: {
                title: 'swal-custom-title',
                htmlContainer: 'swal-custom-text'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "../../../Function/logout.php";
            }
        });
    })
    </script>
</body>

</html>