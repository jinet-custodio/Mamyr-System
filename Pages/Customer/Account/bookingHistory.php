<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Customer/Account/bookingHistory.css" />
    <!-- DataTables Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/datatables.min.css" />
</head>

<body>

    <div class="sidebar">

        <div class="home">
            <a href="../dashboard.php"><img src="../../../Assets/Images/Icon/home2.png" alt="home icon"
                    class="homeIcon"></a>
        </div>
        <div class="sidebar-header">
            <h5>User Account</h5>
        </div>
        <ul class="list-group">
            <li>
                <a href="account.php" class="list-group-item ">
                    <img src="../../../Assets/Images/Icon/user.png" alt="Profile Information" class="sidebar-icon">
                    Profile Information
                </a>
            </li>

            <li>
                <a href="bookingHistory.php" class="list-group-item active" id="paymentBookingHist">
                    <img src="../../../Assets/Images/Icon/bookingHistory.png" alt="Booking History"
                        class="sidebar-icon">
                    Payment & Booking History
                </a>
            </li>

            <li>
                <a href="loginSecurity.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/login_security.png" alt="Login Security" class="sidebar-icon">
                    Login & Security
                </a>
            </li>


            <a href="deleteAccount.php" class="list-group-item">
                <img src="../../../Assets/Images/Icon/delete-user.png" alt="Delete Account" class="sidebar-icon">
                Delete Account
            </a>
            </li>
            <li>
                <button type="button" class="btn btn-outline-danger" id="logoutBtn"> <img
                        src="../../../Assets/Images/Icon/logout.png" alt="Log Out" class="sidebar-icon">
                    Logout</button>
            </li>
        </ul>
    </div>

    <div class="bookingHistContainer">

        <div class="titleContainer">
            <h2 class="title">Booking History</h2>
        </div>


        <div class="tableContainer">

            <table class=" table table-striped" id="bookingHistory">
                <thead>
                    <th scope="col">Check In Date</th>
                    <th scope="col">Check Out Date</th>
                    <th scope="col">Details</th>
                    <th scope="col">Status</th>
                    <th scope="col">Review</th>
                </thead>


                <tbody>
                    <tr>
                        <td>December 26, 2024</td>
                        <td>December 27, 2024</td>
                        <td><a href="#" class="fw-bold">Hotel Booking</a></td>
                        <td><a href="#" class="btn btn-success w-75">View</a></td>
                        <td><a href="" class="btn btn-outline-primary">Rate</a></td>
                    </tr>
                    <tr>
                        <td>January 26, 2025</td>
                        <td>January 26, 2025</td>
                        <td><a href="#" class="fw-bold">Event Booking</a></td>
                        <td><a href="#" class="btn btn-success w-75">View</a></td>
                        <td><a href="" class="btn btn-outline-primary">Rate</a></td>
                    </tr>

                    <tr>
                        <td>February 27, 2025</td>
                        <td>February 27, 2025</td>
                        <td><a href="#" class="fw-bold">Resort Booking</a></td>
                        <td><a href="#" class="btn btn-success w-75">View</a></td>
                        <td><a href="" class="btn btn-outline-primary">Rate</a></td>
                    </tr>

                    <tr>
                        <td>March 26, 2025</td>
                        <td>March 26, 2025</td>
                        <td><a href="#" class="fw-bold">Event Booking</a></td>
                        <td><a href="#" class="btn btn-success w-75">View</a></td>
                        <td><a href="" class="btn btn-outline-primary">Rate</a></td>
                    </tr>

                    <tr>
                        <td>April 26, 2025</td>
                        <td>April 26, 2025</td>
                        <td><a href="#" class="fw-bold">Resort Booking</a></td>
                        <td><a href="#" class="btn btn-success w-75">View</a></td>
                        <td><a href="" class="btn btn-outline-primary ">Rate</a></td>
                    </tr>
                </tbody>
            </table>


        </div>
    </div>



    </div>














    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- DataTables Link -->
    <script src="../../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
    $(document).ready(function() {
        $('#bookingHistory').DataTable();
    });
    </script>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Show -->
    <script>
    const params = new URLSearchParams(window.location.search);
    const paramsValue = params.get('action')
    const confirmationBtn = document.getElementById("confirmationBtn");
    const confirmationModal = document.getElementById("confirmationModal");
    const deleteModal = document.getElementById('deleteModal');
    const logoutBtn = document.getElementById('logoutBtn');

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
    });
    </script>


</body>



</html>