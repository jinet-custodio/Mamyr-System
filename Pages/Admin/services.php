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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/services.css" />

</head>

<body id="servicesBody">
    <div class="topSection">
        <div class="dashTitleContainer">
            <a href="adminDashboard.php" class="dashboardTitle" id="dashboard"><img
                    src="../../Assets/images/MamyrLogo.png" alt="" class="logo"></a>
        </div>

        <div class="menus">
            <!-- Get notification -->
            <?php

            $receiver = 'Admin';
            $getNotifications = $conn->prepare("SELECT * FROM notifications WHERE receiver = ? AND is_read = 0");
            $getNotifications->bind_param("s", $receiver);
            $getNotifications->execute();
            $getNotificationsResult = $getNotifications->get_result();
            if ($getNotificationsResult->num_rows > 0) {
                $counter = 0;
                $notificationsArray = [];
                $color = [];
                $notificationIDs = [];
                while ($notifications = $getNotificationsResult->fetch_assoc()) {
                    $is_readValue = $notifications['is_read'];
                    $notificationIDs[] = $notifications['notificationID'];
                    if ($is_readValue === 0) {
                        $notificationsArray[] = $notifications['message'];
                        $counter++;
                        $color[] = "rgb(247, 213, 176, .5)";
                    } elseif ($is_readValue === 1) {
                        $notificationsArray[] = $notifications['message'];
                        $counter++;
                        $color[] = "white";
                    }
                }
            }
            ?>

            <div class="notification-container position-relative">
                <button type="button" class="btn position-relative" data-bs-toggle="modal"
                    data-bs-target="#notificationModal">
                    <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                    <?php if (!empty($counter)): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= htmlspecialchars($counter) ?>
                    </span>
                    <?php endif; ?>
                </button>
            </div>

            <a href="#" class="chat">
                <img src="../../Assets/Images/Icon/chat.png" alt="home icon">
            </a>
            <?php
            if ($userRole == 3) {
                $admin = "Admin";
            } else {
                $_SESSION['error'] = "Unauthorized Access!";
                session_destroy();
                header("Location: ../register.php");
                exit();
            }

            if ($admin === "Admin") {
                $getProfile = $conn->prepare("SELECT firstName,userProfile FROM users WHERE userID = ? AND userRole = ?");
                $getProfile->bind_param("ii", $userID, $userRole);
                $getProfile->execute();
                $getProfileResult = $getProfile->get_result();
                if ($getProfileResult->num_rows > 0) {
                    $data = $getProfileResult->fetch_assoc();
                    $firstName = $data['firstName'];
                    $imageData = $data['userProfile'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_buffer($finfo, $imageData);
                    finfo_close($finfo);
                    $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            } else {
                $_SESSION['error'] = "Unauthorized Access!";
                session_destroy();
                header("Location: ../register.php");
                exit();
            }
            ?>
            <h5 class="adminTitle"><?= ucfirst($firstName) ?></h5>
            <a href="../Account/account.php" class="admin">
                <img src="<?= htmlspecialchars($image) ?>" alt="home icon">
            </a>
        </div>
    </div>

    <nav class="navbar">

        <a class="nav-link " href="adminDashboard.php">
            <img src="../../Assets/Images/Icon/Dashboard.png" alt="Dashboard">
            <h5>Dashboard</h5>
        </a>

        <a class="nav-link" href="booking.php">
            <img src="../../Assets/Images/Icon/uim-schedule.png" alt="Bookings">
            <h5>Bookings</h5>
        </a>


        <a class="nav-link" href="roomList.php">
            <img src="../../Assets/Images/Icon/Hotel.png" alt="Rooms">
            <h5>Rooms</h5>
        </a>

        <a class="nav-link active" href="services.php">
            <img src="../../Assets/Images/Icon/servicesAdminNav.png" alt="Services">
            <h5>Services</h5>
        </a>


        <!-- <a href="revenue.php" class="nav-link">
            <img src="../../../Assets/Images/Icon/revenue.png" alt="" class="sidebar-icon">
            <h5>Revenue</h5>
        </a> -->


        <a class="nav-link" href="transaction.php">
            <img src="../../Assets/Images/Icon/Credit card.png" alt="Payments">
            <h5>Payments</h5>
        </a>


        <a class="nav-link" href="revenue.php">
            <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue">
            <h5>Revenue</h5>
        </a>


        <a class="nav-link" href="displayPartnership.php">
            <img src="../../Assets/Images/Icon/partnership.png" alt="Partnerships">
            <h5>Partnerships</h5>
        </a>

        <a class="nav-link" href="editWebsite/editWebsite.php">
            <img src="../../Assets/Images/Icon/Edit Button.png" alt="Edit Website">
            <h5>Edit Website</h5>
        </a>

        <a href="../../Function/Admin/logout.php" class="btn btn-danger">
            Log Out
        </a>

    </nav>
    <div class="container-fluid">

        <div class="headerContainer">
            <div class="backArrowContainer" id="backArrowContainer" style="display: none;">
                <img src="../../Assets/Images/icon/back-button.png" alt="Back Arrow" class="backArrow">
            </div>
            <h2 class="header text-center" id="headerText">Services</h2>
        </div>

        <section id="serviceCategories">
            <a href="#" id="resort-link" class="categoryLink">
                <div class="card category-card resort-category">
                    <img class="card-img-top" src="../../Assets/images/amenities/poolPics/poolPic3.jpg"
                        alt="Wedding Event">

                    <div class="category-body">
                        <h5 class="category-title">RESORT</h5>
                    </div>
                </div>
            </a>

            <a href="#" id="hotel-link" class="categoryLink">
                <div class="card category-card hotel-category">
                    <img class="card-img-top" src="../../Assets/images/amenities/hotelPics/hotel1.jpg"
                        alt="Wedding Event">
                    <div class="category-body">
                        <h5 class="category-title">HOTEL</h5>
                    </div>
                </div>
            </a>

            <a href="#" id="event-link" class="categoryLink" onclick="eventCategory()">
                <div class="card category-card event-category">
                    <img class="card-img-top" src="../../Assets/images/amenities/pavilionPics/pav4.jpg"
                        alt="Wedding Event">
                    <div class="category-body">
                        <h5 class="category-title">EVENT</h5>
                    </div>
                </div>
            </a>

            <a href="#" id="catering-link" class="categoryLink">
                <div class="card category-card event-category">
                    <img class="card-img-top" src="../../Assets/images//BookNowPhotos/foodCoverImg2.jpg"
                        alt="Wedding Event">
                    <div class="category-body">
                        <h5 class="category-title">CATERING</h5>
                    </div>
                </div>
            </a>
        </section>

        <div class="resortContainer" id="resortContainer" style="display: none;">
            <button class="btn btn-primary" id="addResortServiceBtn" onclick="addService()">Add a Service</button>
            <table class=" table table-striped" id="resortServices">
                <thead>
                    <th scope="col">Service Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Capacity</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Description</th>
                    <th scope="col">Image</th>
                    <th scope="col">Availability</th>
                    <th scope="col">Action</th>

                </thead>

                <tbody>
                    <tr>
                        <td><input type="text" class="form-control" id="resortServiceName"></td>
                        <td><input type="text" class="form-control" id="resortServicePrice"></td>
                        <td><input type="text" class="form-control" id="resortServiceCapacity"></td>
                        <td><input type="text" class="form-control" id="resortServiceDuration"></td>
                        <td><input type="text" class="form-control" id="resortServiceDesc"></td>
                        <td><input type="text" class="form-control" id="resortServiceImage"></td>
                        <td> <select id="resortAvailability" name="resortAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>
                        </td>
                        <td class="buttonContainer">
                            <button class="btn btn-primary" id="editResortService" onclick="edit()">Edit</button>
                            <button class="btn btn-danger deleteBtn" id="deleteResortService">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="saveBtnContainer" id="saveBtnResortContainer">
                <button type="submit " class="btn btn-success" id="saveChanges" onclick="saveButton()">Save</button>
            </div>
        </div>

        <div class="hotelContainer" id="hotelContainer" style="display: none;">
            <button class="btn btn-primary" id="addHotelServiceBtn" onclick="addService()">Add a Service</button>
            <table class=" table table-striped" id="hotelServices">
                <thead>
                    <th scope="col">Service Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Capacity</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Description</th>
                    <th scope="col">Image</th>
                    <th scope="col">Availability</th>
                    <th scope="col">Action</th>

                </thead>

                <tbody>
                    <tr>
                        <td><input type="text" class="form-control" id="hotelServiceName"></td>
                        <td><input type="text" class="form-control" id="hotelServicePrice"></td>
                        <td><input type="text" class="form-control" id="hotelServiceCapacity"></td>
                        <td><input type="text" class="form-control" id="hotelServiceDuration"></td>
                        <td><input type="text" class="form-control" id="hotelServiceDesc"></td>
                        <td><input type="text" class="form-control" id="hotelServiceImage"></td>
                        <td> <select id="hotelAvailability" name="hotelAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>
                        </td>
                        <td class="buttonContainer">
                            <button class="btn btn-primary" id="editHotelService" onclick="edit()">Edit</button>
                            <button class="btn btn-danger deleteBtn" id="deleteHotelService">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="saveBtnContainer" id="saveBtnHotelContainer">
                <button type="submit" class="btn btn-success" id="saveChanges" onclick="saveButton()">Save</button>
            </div>
        </div>

        <div class="eventContainer" id="eventContainer" style="display: none;">
            <button class="btn btn-primary" id="addEventServiceBtn" onclick="addService()">Add a Service</button>
            <table class=" table table-striped" id="eventServices">
                <thead>
                    <th scope="col">Service Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Capacity</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Description</th>
                    <th scope="col">Image</th>
                    <th scope="col">Availability</th>
                    <th scope="col">Action</th>

                </thead>

                <tbody>
                    <tr>
                        <td><input type="text" class="form-control" id="eventServiceName"></td>
                        <td><input type="text" class="form-control" id="eventServicePrice"></td>
                        <td><input type="text" class="form-control" id="eventServiceCapacity"></td>
                        <td><input type="text" class="form-control" id="eventServiceDuration"></td>
                        <td><input type="text" class="form-control" id="eventServiceDesc"></td>
                        <td><input type="text" class="form-control" id="eventServiceImage"></td>
                        <td> <select id="eventAvailability" name="eventAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>
                        </td>
                        <td class="buttonContainer">
                            <button class="btn btn-primary" id="editEventService" onclick="edit()">Edit</button>
                            <button class="btn btn-danger deleteBtn" id="deleteEventService">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="saveBtnContainer" id="saveBtnEventContainer">
                <button type="submit " class="btn btn-success" id="saveChanges" onclick="saveButton()">Save</button>
            </div>
        </div>

        <div class="cateringContainer" id="cateringContainer" style="display: none;">
            <button class="btn btn-primary" id="addCateringServiceBtn" onclick="addService()">Add a Service</button>
            <table class=" table table-striped" id="cateringServices">
                <thead>
                    <th scope="col">Food Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Category</th>
                    <th scope="col">Availability</th>
                    <th scope="col">Action</th>

                </thead>

                <tbody>
                    <tr>
                        <td><input type="text" class="form-control" id="foodName"></td>
                        <td><input type="text" class="form-control" id="foodPrice"></td>
                        <td><input type="text" class="form-control" id="foodCategory"></td>


                        <td> <select id="foodAvailability" name="foodAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="unavailable" id="unavailable">Unavailable</option>

                            </select>
                        </td>
                        <td class="buttonContainer">
                            <button class="btn btn-primary" id="editCateringService" onclick="edit()">Edit</button>
                            <button class="btn btn-danger deleteBtn" id="deleteCateringService">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="saveBtnContainer" id="saveBtnCateringContainer">
                <button type="submit " class="btn btn-success" id="saveChanges" onclick="saveButton()">Save</button>
            </div>
        </div>





    </div>

    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0">
                    <?php if (!empty($notificationsArray)): ?>
                    <ul class="list-group list-group-flush ">
                        <?php foreach ($notificationsArray as $index => $message):
                                $bgColor = $color[$index];
                                $notificationID = $notificationIDs[$index];
                            ?>
                        <li class="list-group-item mb-2 notification-item"
                            data-id="<?= htmlspecialchars($notificationID) ?>"
                            style="background-color: <?= htmlspecialchars($bgColor) ?>; border: 1px solid rgb(84, 87, 92, .5)">
                            <?= htmlspecialchars($message) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="p-3 text-muted">No new notifications.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
    </script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
    $(document).ready(function() {
        $('#resortServices').DataTable({
            language: {
                emptyTable: "No Services"
            }
        });
        $('#hotelServices').DataTable({
            language: {
                emptyTable: "No Services"
            }
        });
        $('#eventServices').DataTable({
            language: {
                emptyTable: "No Services"
            }
        });
        $('#cateringServices').DataTable({
            language: {
                emptyTable: "No Services"
            }
        });
    });
    </script>

    <script>
    const saveBtnResortContainer = document.getElementById("saveBtnResortContainer")
    const saveBtnHotelContainer = document.getElementById("saveBtnHotelContainer")
    const saveBtnEventContainer = document.getElementById("saveBtnEventContainer")
    const saveBtnCateringContainer = document.getElementById("saveBtnCateringContainer")

    saveBtnResortContainer.style.display = "none"
    saveBtnHotelContainer.style.display = "none"
    saveBtnEventContainer.style.display = "none"
    saveBtnCateringContainer.style.display = "none"


    function addService() {
        if (saveBtnResortContainer.style.display == "none") {
            saveBtnResortContainer.style.display = "flex";
            saveBtnHotelContainer.style.display = "none";
            saveBtnEventContainer.style.display = "none";
            saveBtnCateringContainer.style.display = "none";

        } else if (saveBtnHotelContainer.style.display == "none") {
            saveBtnHotelContainer.style.display = "flex";
            saveBtnResortContainer.style.display = "none";
            saveBtnEventContainer.style.display = "none";
            saveBtnCateringContainer.style.display = "none";

        } else if (saveBtnEventContainer.style.display == "none") {
            saveBtnEventContainer.style.display = "flex";
            saveBtnResortContainer.style.display = "none";
            saveBtnHotelContainer.style.display = "none";
            saveBtnCateringContainer.style.display = "none";
        } else if (saveBtnCateringContainer.style.display == "none") {
            saveBtnCateringContainer.style.display = "flex";
            saveBtnResortContainer.style.display = "none";
            saveBtnHotelContainer.style.display = "none";
            saveBtnEventContainer.style.display = "none";
        } else {
            saveBtnContainer.style.display = "flex"
        }
    }

    function edit() {
        if (saveBtnResortContainer.style.display == "none" || saveBtnHotelContainer.style.display == "none" ||
            saveBtnEventContainer.style.display == "none" || saveBtnCateringContainer.style.display == "none") {
            saveBtnResortContainer.style.display = "flex";
            saveBtnHotelContainer.style.display = "flex";
            saveBtnEventContainer.style.display = "flex";
            saveBtnCateringContainer.style.display = "flex";

        } else {
            saveBtnContainer.style.display = "flex"
        }
    }
    </script>

    <script>
    $(document).ready(function() {
        const table = $('#resortServices').DataTable();

        $(addResortServiceBtn).on('click', function() {
            const newResortData = [
                '<input type="text" class="form-control" id="resortServiceName">',
                '<input type="text" class="form-control" id="resortServicePrice">',
                '<input type="text" class="form-control" id="resortServiceCapacity">',
                '<input type="text" class="form-control" id="resortServiceDuration">',
                '<input type="text" class="form-control" id="resortServiceDesc">',
                '<input type="text" class="form-control" id="resortServiceImage">',

                `<select id="resortAvailability" name="resortAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>`,

                `<td class="buttonContainer">
                            <button class="btn btn-primary"  id="editResortService" onclick="edit()">Edit</button>
                            <button class="btn btn-danger deleteBtn"  id="deleteResortService">Delete</button>
                        </td>`,

            ];
            table.row.add(newResortData).draw(false);

        });

    });
    </script>

    <script>
    $(document).ready(function() {
        const table = $('#hotelServices').DataTable();

        $(addHotelServiceBtn).on('click', function() {
            const newHotelData = [
                '<input type="text" class="form-control" id="hotelServiceName">',
                '<input type="text" class="form-control" id="hotelServicePrice">',
                '<input type="text" class="form-control" id="hotelServiceCapacity">',
                '<input type="text" class="form-control" id="hotelServiceDuration">',
                '<input type="text" class="form-control" id="hotelServiceDesc">',
                '<input type="text" class="form-control" id="hotelServiceImage">',

                `<select id="hotelAvailability" name="hotelAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>`,

                `<td class="buttonContainer">
                            <button class="btn btn-primary"  id="editHotelService" onclick="edit()">Edit</button>
                            <button class="btn btn-danger deleteBtn"  id="deleteHotelService">Delete</button>
                        </td>`,

            ];
            table.row.add(newHotelData).draw(false);

        });

    });
    </script>

    <script>
    $(document).ready(function() {
        const table = $('#eventServices').DataTable();

        $(addEventServiceBtn).on('click', function() {
            const newEventData = [
                '<input type="text" class="form-control" id="EventServiceName">',
                '<input type="text" class="form-control" id="EventServicePrice">',
                '<input type="text" class="form-control" id="EventServiceCapacity">',
                '<input type="text" class="form-control" id="EventServiceDuration">',
                '<input type="text" class="form-control" id="EventServiceDesc">',
                '<input type="text" class="form-control" id="EventServiceImage">',

                `<select id="EventAvailability" name="EventAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>`,

                `<td class="buttonContainer">
                            <button class="btn btn-primary"  id="editEventService" onclick="edit()">Edit</button>
                            <button class="btn btn-danger deleteBtn"  id="deleteEventService" >Delete</button>
                        </td>`,

            ];
            table.row.add(newEventData).draw(false);

        });

    });
    </script>

    <script>
    $(document).ready(function() {
        const table = $('#cateringServices').DataTable();

        $(addCateringServiceBtn).on('click', function() {
            const newCateringData = [
                '<input type="text" class="form-control" id="foodName">',
                '<input type="text" class="form-control" id="foodPrice">',
                '<input type="text" class="form-control" id="foodCategory">',


                ` <td> <select id="foodAvailability" name="foodAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="unavailable" id="unavailable">Unavailable</option>

                            </select>
                        </td>`,

                `<td class="buttonContainer">
                            <button class="btn btn-primary"  id="editCateringService" onclick="edit()">Edit</button>
                            <button class="btn btn-danger deleteBtn"  id="deleteCateringService">Delete</button>
                        </td>`,

            ];
            table.row.add(newCateringData).draw(false);

        });

    });
    </script>




    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const serviceCategories = document.getElementById("serviceCategories");
        const resortLink = document.getElementById("resort-link");
        const hotelLink = document.getElementById("hotel-link");
        const eventLink = document.getElementById("event-link");
        const cateringLink = document.getElementById("catering-link");
        const resortContainer = document.getElementById("resortContainer");
        const hotelContainer = document.getElementById("hotelContainer");
        const eventContainer = document.getElementById("eventContainer");
        const cateringContainer = document.getElementById("cateringContainer");
        const backButton = document.getElementById("backArrowContainer");

        backButton.addEventListener("click", function() {
            backButton.style.display = "none";
            resortContainer.style.display = "none";
            hotelContainer.style.display = "none";
            eventContainer.style.display = "none";
            cateringContainer.style.display = "none";
            document.getElementById("headerText").innerHTML = "Services";
            serviceCategories.style.display = "flex";
            document.body.setAttribute("style", "background-color: #a1c8c7")
        })

        resortLink.addEventListener("click", function() {
            serviceCategories.style.display = "none";
            backButton.style.display = "block";
            resortContainer.style.display = "block";
            document.getElementById("headerText").innerHTML = "Resort";
            document.body.setAttribute("style", "background-color: whitesmoke;");
        });

        hotelLink.addEventListener("click", function() {
            serviceCategories.style.display = "none";
            backButton.style.display = "block";
            hotelContainer.style.display = "block";
            document.getElementById("headerText").innerHTML = "Hotel"
            document.body.setAttribute("style", "background-color: whitesmoke;");
        })

        eventLink.addEventListener("click", function() {
            serviceCategories.style.display = "none";
            backButton.style.display = "block";
            eventContainer.style.display = "block";
            document.getElementById("headerText").innerHTML = "Event"
            document.body.setAttribute("style", "background-color: whitesmoke;");
        })

        cateringLink.addEventListener("click", function() {
            serviceCategories.style.display = "none";
            backButton.style.display = "block";
            cateringContainer.style.display = "block";
            document.getElementById("headerText").innerHTML = "Catering"
            document.body.setAttribute("style", "background-color: whitesmoke;");
        })
    });
    </script>
</body>

</html>