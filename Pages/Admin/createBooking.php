<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();

require_once '../../Function/Helpers/statusFunctions.php';
require_once '../../Function/Helpers/userFunctions.php';
addToAdminTable($conn);
changeToDoneStatus($conn);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID, userRole FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $_SESSION['userRole'] = $user['userRole'];
    }

    if (!$user) {
        $_SESSION['error'] = 'Account no longer exists';
        session_unset();
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
}
if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

switch ($userRole) {
    case 3:
        $role = "Admin";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/createBooking.css">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
        <div class="backBtnContainer">
            <a href="booking.php"><img src="../../Assets/Images/Icon/arrowBtnBlack.png" alt="Back Button" class="backButton">
            </a>
        </div>

    </nav>

    <?php
    $getAdminData = $conn->prepare("SELECT adminID FROM admin WHERE userID = ?");
    $getAdminData->bind_param('i', $userID);
    if (!$getAdminData->execute()) {
        error_log('Failed getting user data: userID' . $userID);
    }

    $result = $getAdminData->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $adminID = $data['adminID'];
    } else {
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
    ?>

    <div class="titleContainer">
        <h1 class="title" id="title">Add Booking</h1>
    </div>

    <form action="../../Function/Admin/addBulkBooking.php" method="POST">

        <input type="hidden" name="adminID" value="<?= $adminID ?>">

        <main class="container-fluid">
            <!-- <h5 class="bsTitle">Booking Summary</h5> -->
            <section class="topSection">
                <section class="leftSide">
                    <div class="infoLabelContainer">
                        <label for="repPeriod">Date Period: </label>
                        <!-- <label for="bookingCount">Number of Bookings: </label> -->
                        <label for="bookingType">Type of Booking:</label>
                    </div>

                    <div class="infoInput">
                        <section class="date-picker">
                            <div class="input-wrapper w-100">
                                <input type="text" name="repPeriod" class="form-control" id="repPeriod"
                                    placeholder="Click to enter date" required>
                                <i class="fa-solid fa-calendar" id="calendarIcon"></i>
                            </div>
                        </section>

                        <!-- <input type="number" class="form-control" id="bookingCount" name="bookingCount" min='1' required> -->

                        <select class="form-select" aria-label="typeOfBooking" id="bookingType" name="bookingType" required>
                            <option selected disabled>Booking Type</option>
                            <option value="resort">Resort</option>
                            <option value="hotel">Hotel</option>
                            <option value="event">Event</option>
                        </select>
                    </div>


                </section>

                <section class="rightSide">


                    <div class="bsContainer">
                        <div class="bsLabelContainer">
                            <label for="totalBooking">Total Bookings: </label>
                            <label for="totalSales">Total Sales: </label>
                        </div>

                        <div class="bsInput">
                            <input type="text" class="form-control" id="totalBooking" name="totalBooking" placeholder="5">
                            <input type="text" class="form-control" id="totalSales" name="totalSales" placeholder="23000.00">
                        </div>
                    </div>

                    <!-- <div class="bsButtons">
                        <button class="btn btn-primary"><i class="bi bi-pencil-square"></i> Edit</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-floppy"></i> Save</button>
                    </div> -->

                </section>
            </section>

            <section class="button-container">
                <!-- <button class="btn btn-primary"><i class="bi bi-pencil-square"></i> Edit</button> -->
                <button type="submit" class="btn btn-success" id="addBooking" name="addBulkBooking"><i class="bi bi-plus-lg"></i> Add Booking</button>
            </section>
        </main>


        <section class="container-fluid tableContainer">
            <table class="table table-striped" id="addedBookings">
                <thead>
                    <th scope="col">Start Date</th>
                    <th scope="col">End Date</th>
                    <th scope="col">Type of Booking</th>
                    <th scope="col">Number of Bookings</th>
                    <th scope="col">Total Sales</th>

                </thead>
                <tbody>
                    <!-- <tr>
                        <td>September 14, 2025</td>
                        <td>September 30, 2025</td>
                        <td>Resort Booking</td>
                        <td>₱15,000</td>
                    </tr>

                    <tr>
                        <td>September 16, 2025</td>
                        <td>September 25, 2025</td>
                        <td>Hotel Booking</td>
                        <td>₱35,000</td>
                    </tr>

                    <tr>
                        <td>September 1, 2025</td>
                        <td>September 30, 2025</td>
                        <td>Event Booking</td>
                        <td>₱100,000</td>
                    </tr> -->
                </tbody>
            </table>

        </section>



    </form>




    <!-- Bootstrap Link -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#addedBookings').DataTable({
                // columnDefs: [{
                //         width: '10%',
                //         targets: 0
                //     },
                //     {
                //         width: '15%',
                //         targets: 2
                //     },
                //     {
                //         width: '15%',
                //         targets: 4
                //     },
                // ],
            });
        });
    </script>

    <!-- Fetch Added Bookings -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch(`../../Function/Admin/Ajax/getBulkBooking.php`)
                .then(result => {
                    if (!result.ok) throw new Error("Network Error");
                    return result.json();
                }).then((data) => {
                    if (!data.success) {
                        Swal.fire({
                            text: data.message ||
                                "Server error (500): Something went wrong on our end. Please try again later.",
                            title: "Error",
                            icon: "error",
                        });
                    };

                    const bookings = data.bulkBookings;
                    const table = $('#addedBookings').DataTable();
                    table.clear();

                    bookings.forEach(info => {
                        table.row.add([
                            info.startDate,
                            info.endDate,
                            info.bookingType,
                            info.bookingCount,
                            info.salesAmount
                        ]);
                    });
                    table.draw();
                }).catch((error) => {
                    Swal.fire({
                        text: error.message || 'An error occured',
                        title: 'Error',
                        icon: 'error'
                    });
                })
        });
    </script>


    <!-- Flatpickr Link -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr('#repPeriod', {
            mode: "range",
            minDate: null,
            maxDate: "today",
            dateFormat: "M. d, Y"
        });
    </script>
</body>

</html>