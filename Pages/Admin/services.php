<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
        integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body id="servicesBody">
    <div class="topSection">
        <div class="dashTitleContainer">
            <a href="adminDashboard.php" class="dashboardTitle" id="dashboard"><img
                    src="../../Assets/Images/MamyrLogo.png" alt="" class="logo"></a>
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
            <button type="button" id="resort-link" class="categoryLink">
                <div class="card category-card resort-category">
                    <img class="card-img-top" src="../../Assets/Images/amenities/poolPics/poolPic2.jpg" alt="Resort">

                    <div class="category-body">
                        <h5 class="category-title">RESORT</h5>
                    </div>
                </div>
            </button>

            <button type="button" id="resortRates-link" class="categoryLink">
                <div class="card category-card resort-category">
                    <img class="card-img-top" src="../../Assets/Images/amenities/poolPics/poolPic3.jpg"
                        alt="Resort Rates">

                    <div class="category-body">
                        <h5 class="category-title">RESORT RATES</h5>
                    </div>
                </div>
            </button>

            <button type="button" id="event-link" class="categoryLink">
                <div class="card category-card event-category">
                    <img class="card-img-top" src="../../Assets/Images/amenities/pavilionPics/pav4.jpg" alt="Event">
                    <div class="category-body">
                        <h5 class="category-title">EVENT</h5>
                    </div>
                </div>
            </button>

            <button type="button" id="catering-link" class="categoryLink">
                <div class="card category-card event-category">
                    <img class="card-img-top" src="../../Assets/Images/BookNowPhotos/foodCoverImg2.jpg" alt="Catering">
                    <div class="category-body">
                        <h5 class="category-title">CATERING</h5>
                    </div>
                </div>
            </button>
        </section>

        <!-- For Resort -->
        <div class="resortContainer" id="resortContainer" style="display: none;">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal"
                id="addResortServiceBtn">Add a Service</button>
            <table class=" table table-striped" id="resortServices">
                <thead>
                    <th scope="col">Service Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Capacity</th>
                    <th scope="col">Max Capacity</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Description</th>
                    <th scope="col">Image</th>
                    <th scope="col">Availability</th>
                    <th scope="col">Action</th>
                </thead>

                <tbody>
                    <?php
                    $hotelCategoryID = 1;
                    $getResortServices = $conn->prepare("SELECT * FROM resortamenities WHERE RScategoryID !=  ?");

                    if ($getResortServices === false) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }

                    $getResortServices->bind_param('i', $hotelCategoryID);

                    if ($getResortServices->execute()) {
                        $getResult = $getResortServices->get_result();

                        if ($getResult->num_rows > 0) {
                            while ($row = $getResult->fetch_assoc()) {
                                $serviceID = $row['resortServiceID'];
                                $serviceName = $row['RServiceName'];
                                $servicePrice = $row['RSprice'];
                                $serviceCapacity = $row['RScapacity'];
                                $serviceMaxCapacity = $row['RSmaxCapacity'];
                                $serviceDuration = $row['RSduration'];
                                $serviceDesc = $row['RSdescription'];
                                $serviceImageName = $row['RSimageData'];
                                $serviceAvailability = $row['RSAvailabilityID'];

                    ?>
                                <tr class="resortdata">
                                    <input type="hidden" class="form-control resortServiceID" name="resortServiceID"
                                        value="<?= htmlspecialchars($serviceID) ?>" readonly>
                                    <td><input type="text" class="form-control resortServiceName" name="resortServiceName"
                                            value="<?= htmlspecialchars($serviceName) ?>" readonly></td>
                                    <td><input type="text" class="form-control resortServicePrice" name="resortServicePrice"
                                            value="<?= htmlspecialchars($servicePrice) ?>" readonly></td>
                                    <td><input type="text" class="form-control resortServiceCapacity" name="resortServiceCapacity"
                                            value="<?= htmlspecialchars($serviceCapacity) ?>" readonly></td>
                                    <td><input type="text" class="form-control resortServiceMaxCapacity"
                                            name="resortServiceMaxCapacity" value="<?= htmlspecialchars($serviceMaxCapacity) ?>"
                                            readonly></td>
                                    <td><input type="text" class="form-control resortServiceDuration" name="resortServiceDuration"
                                            value="<?= htmlspecialchars($serviceDuration) ?>" readonly></td>
                                    <td><textarea name="serviceDesc" readonly
                                            class="form-control"><?= htmlspecialchars($serviceDesc) ?></textarea></td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control resortServiceImage"
                                                value="<?= htmlspecialchars($serviceImageName) ?>" name="resortServiceImage"
                                                readonly>
                                            <button class="btn btn-outline-secondary editImageBtn" disabled type="button"><i
                                                    class="fa-solid fa-camera"></i></button>
                                        </div>
                                        <input type="file" class="form-control resortServiceImagePicker"
                                            name="resortServiceImagePicker" hidden>
                                    </td>
                                    <td>
                                        <select name="resortAvailability" class="form-select resortAvailability" disabled>
                                            <option value="" disabled <?= $serviceAvailability == "" ? "selected" : "" ?>>Select
                                                Availability</option>
                                            <option value="1" <?= $serviceAvailability == "1" ? "selected" : "" ?>>Available
                                            </option>
                                            <option value="2" <?= $serviceAvailability == "2" ? "selected" : "" ?>>Occupied</option>
                                            <option value="3" <?= $serviceAvailability == "3" ? "selected" : "" ?>>Maintenance
                                            </option>
                                            <option value="4" <?= $serviceAvailability == "4" ? "selected" : "" ?>>Private</option>
                                            <option value="5" <?= $serviceAvailability == "5" ? "selected" : "" ?>>Not Available
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="buttonContainer">
                                            <button class="btn btn-primary editBtn editResortService"
                                                onclick="editResortService(this)" id="editPrimary" data-label="Edit"><i
                                                    class="fa-solid fa-pen-to-square"></i>Edit</button>
                                            <button class="btn btn-danger cancelBtn cancelResortService" disabled id="cancelDanger"
                                                onclick="cancelResortService(this)"><i
                                                    class="fa-solid fa-delete-left"></i>Cancel</button>
                                        </div>
                                    </td>
                                </tr>
                    <?php
                            }
                            $getResult->free();
                            $getResortServices->close();
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- For Resort Rates -->
        <div class="resortRatesContainer" id="resortRatesContainer" style="display: none;">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResortRatesModal"
                id="addResortRatesBtn">Add Rates</button>
            <table class=" table table-striped" id="resortRates">
                <thead>
                    <th scope="col">Tour Type</th>
                    <th scope="col">Time Range</th>
                    <th scope="col">Visitor Type</th>
                    <th scope="col">Price</th>
                    <th scope="col">Action</th>

                </thead>

                <tbody>
                    <?php
                    $selectRates = $conn->prepare("SELECT er.*, etr.* FROM entrancerates er
                    JOIN entrancetimeranges etr ON er.timeRangeID = etr.timeRangeID");
                    if ($selectRates->execute()) {
                        $rateResult = $selectRates->get_result();
                        while ($row = $rateResult->fetch_assoc()) {
                    ?>
                            <tr class="ratesdata">
                                <input type="hidden" name="entranceRatesID" class="entranceRateID"
                                    value="<?= $row['entranceRateID'] ?>">
                                <input type="hidden" name="timeRangeID" class="timeRangeID"
                                    value="<?= $row['entranceRateID'] ?>">
                                <td>
                                    <select id="tourType" name="tourType" class="form-select tourType" disabled>
                                        <option value="" disabled
                                            <?= htmlspecialchars($row['sessionType']) == "" ? "selected" : "" ?> selected>Tour
                                            Type</option>
                                        <option value="<?= htmlspecialchars($row['sessionType']) == "Day" ? "Day" : "" ?>"
                                            <?= htmlspecialchars($row['sessionType']) == "Day" ? "selected" : "" ?>>Day Swimming
                                        </option>
                                        <option value="<?= htmlspecialchars($row['sessionType']) == "Night" ? "Night" : "" ?>"
                                            <?= htmlspecialchars($row['sessionType']) == "Night" ? "selected" : "" ?>>Night
                                            Swimming</option>
                                        <option
                                            value="<?= htmlspecialchars($row['sessionType']) == "Overnight" ? "Overnight" : "" ?>"
                                            <?= htmlspecialchars($row['sessionType']) == "Overnight" ? "selected" : "" ?>>
                                            Overnight Swimming</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control timeRange" name="timeRange"
                                        value="<?= htmlspecialchars($row['time_range']) ?>" readonly>
                                </td>
                                <td>
                                    <select name="visitorType" class="form-select visitorType" disabled>
                                        <option value="" disabled
                                            <?= htmlspecialchars($row['ERcategory']) == "" ? "selected" : "" ?> selected>Visitor
                                            Type</option>
                                        <option value="adult"
                                            <?= htmlspecialchars($row['ERcategory']) == "Adult" ? "selected" : "" ?>>Adult
                                        </option>
                                        <option value="children"
                                            <?= htmlspecialchars($row['ERcategory']) == "Kids" ? "selected" : "" ?>>Children
                                        </option>
                                    </select>
                                </td>
                                <td><input type="text" class="form-control entrancePrice" name="entrancePrice"
                                        value="<?= htmlspecialchars($row['ERprice']) ?>" readonly></td>

                                <td>
                                    <div class="buttonContainer">
                                        <button class="btn btn-primary editRatesBtn" id="editPrimary"
                                            onclick="editRates(this)"><i class="fa-solid fa-pen-to-square"></i>Edit</button>
                                        <button class="btn btn-danger cancelRatesBtn" id="cancelDanger"
                                            onclick="cancelEditRates(this)" disabled><i
                                                class="fa-solid fa-delete-left"></i>Cancel</button>
                                    </div>

                                </td>
                            </tr>
                    <?php
                        }
                        $rateResult->free();
                        $selectRates->close();
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- For Event -->
        <div class="eventContainer" id="eventContainer" style="display: none;">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventServiceModal"
                id="eventAdd">Add a Service</button>
            <table class=" table table-striped" id="eventServices">
                <thead>
                    <th scope="col">Event Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Capacity</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Description</th>
                    <th scope="col">Image</th>
                    <th scope="col">Event Status</th>
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
                                <option value="" disabled selected>Select Status</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>
                        </td>
                        <td>
                            <div class="buttonContainer">
                                <button class="btn btn-primary" id="addEventService" onclick="add()">Add</button>
                                <button class="btn btn-primary" id="editEventService" onclick="edit()">Edit</button>
                                <button class="btn btn-danger cancelBtn" id="deleteEventService">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <!-- For Catering Food/Drink/Dessert -->
        <div class="cateringContainer" id="cateringContainer" style="display: none;">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                data-bs-target="#addCateringServiceModal" id="cateringAdd">Add a Service</button>
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
                        <td>
                            <select id="foodCategory" name="foodCategory" class="form-select" required>
                                <option value="" disabled selected>Category</option>
                                <option value="chicken">Chicken</option>
                                <option value="pork">Pork</option>
                                <option value="beef">Beef</option>
                                <option value="pasta">Pasta</option>
                                <option value="vegetables">Vegetables</option>
                                <option value="seafood">Seafood</option>
                                <option value="drinks">Drinks</option>
                                <option value="desserts">Desserts</option>
                            </select>
                        </td>


                        <td> <select id="foodAvailability" name="foodAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="unavailable" id="unavailable">Unavailable</option>

                            </select>
                        </td>
                        <td>
                            <div class="buttonContainer">
                                <button class="btn btn-primary" id="editCateringService" onclick="edit()">Edit</button>
                                <button class="btn btn-danger cancelBtn" id="deleteCateringService">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- FORM MODAL ADDING COTTAGE, ENTERTAINEMENT, EVENT HALL SERVICE-->
        <form action="../../Function/Admin/Services/addServices.php" id="addingServiceForm" method="POST"
            enctype="multipart/form-data">
            <!-- Modal -->
            <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addServiceModalLabel">Add a Service</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="input-container">
                                <label for="serviceName">Service Name</label>
                                <input type="text" class="form-control" id="serviceName" name="serviceName" required>
                            </div>
                            <div class="input-container">
                                <label for="servicePrice"> Service Price</label>
                                <input type="text" class="form-control" id="servicePrice" name="servicePrice" required>
                            </div>
                            <div class="input-container">
                                <label for="serviceCapacity">Service Capacity</label>
                                <input type="text" class="form-control" id="serviceCapacity" name="serviceCapacity">
                            </div>
                            <div class="input-container">
                                <label for="serviceMaxCapacity">Service Max Capacity</label>
                                <input type="text" class="form-control" id="serviceMaxCapacity"
                                    name="serviceMaxCapacity">
                            </div>
                            <div class="input-container">
                                <label for="serviceDuration">Service Duration</label>
                                <input type="text" class="form-control" id="serviceDuration" name="serviceDuration"
                                    placeholder="e.g, 22 hours">
                            </div>
                            <div class="input-container">
                                <label for="serviceDesc">Description</label>
                                <textarea class="form-control" name="serviceDesc" id="serviceDesc"> </textarea>
                            </div>

                            <div class="input-container">
                                <label for="serviceCategory">Service Category</label>
                                <select id="serviceCategory" name="serviceCategory" class="form-select" required>
                                    <option value="" disabled selected>Service Category</option>
                                    <?php
                                    $hotel = 'Hotel';
                                    $getCategory = $conn->prepare('SELECT * FROM resortservicescategories WHERE categoryName != ?');
                                    $getCategory->bind_param('s', $hotel);
                                    if ($getCategory->execute()) {
                                        $result =  $getCategory->get_result();
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                    ?>
                                                <option value="<?= htmlspecialchars($row['categoryID']) ?>" id="available">
                                                    <?= htmlspecialchars($row['categoryName']) ?></option>
                                    <?php
                                            }
                                        }
                                        $result->free();
                                        $getCategory->close();
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="input-container">
                                <label for="serviceImage">Service Image</label>
                                <input type="file" class="form-control" name="serviceImage" id="serviceImage">
                            </div>

                            <div class="input-container">
                                <label for="serviceAvailability">Availability</label>
                                <select id="serviceAvailability" name="serviceAvailability" class="form-select"
                                    required>
                                    <option value="" disabled selected>Select Availability</option>
                                    <?php

                                    $getAvailability = $conn->prepare('SELECT * FROM serviceavailability');
                                    if ($getAvailability->execute()) {
                                        $result = $getAvailability->get_result();
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                    ?>
                                                <option value="<?= htmlspecialchars($row['availabilityID']) ?>" id="available">
                                                    <?= htmlspecialchars($row['availabilityName']) ?></option>
                                    <?php
                                            }
                                        }
                                        $result->free();
                                        $getAvailability->close();
                                    }
                                    ?>
                                </select>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="saveService">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- </form> -->

        <!-- Modal for resort rates -->
        <form action="../../Function/Admin/Services/addServices.php" method="POST">
            <div class="modal fade" id="addResortRatesModal" tabindex="-1" aria-labelledby="addResortRatesModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addResortRatesModalLabel">Add a Resort Rate
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="input-container">
                                <label for="tourType">Tour Type</label>
                                <select id="tourType" name="tourType" class="form-select" required>
                                    <option value="" disabled selected>Tour Type</option>
                                    <?php
                                    $getTourType = $conn->prepare("SELECT timeRangeID, session_type FROM entrancetimeranges");
                                    if ($getTourType->execute()) {
                                        $tourTypeResult = $getTourType->get_result();
                                        if ($tourTypeResult->num_rows > 0) {


                                            while ($row = $tourTypeResult->fetch_assoc()) {
                                    ?>
                                                <option value="<?= htmlspecialchars($row['session_type']) ?>">
                                                    <?= htmlspecialchars($row['session_type']) ?></option>

                                    <?php
                                            }
                                        }
                                        $tourTypeResult->free();
                                        $getTourType->close();
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="input-container">
                                <label for="timeRange">Time Range</label>
                                <select id="timeRange" name="timeRange" class="form-select" required>
                                    <option value="" disabled selected>Tour Type</option>
                                    <?php
                                    $getTimeRange = $conn->prepare("SELECT timeRangeID, time_range FROM entrancetimeranges");
                                    if ($getTimeRange->execute()) {
                                        $timeRangeResult =  $getTimeRange->get_result();
                                        if ($timeRangeResult->num_rows > 0) {


                                            while ($row = $timeRangeResult->fetch_assoc()) {
                                    ?>
                                                <option value="<?= htmlspecialchars($row['timeRangeID']) ?>">
                                                    <?= htmlspecialchars($row['time_range']) ?></option>

                                    <?php
                                            }
                                        }
                                        $timeRangeResult->free();
                                        $getTimeRange->close();
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="input-container">
                                <label for="serviceCapacity">Visitor Type</label>
                                <select id="visitorType" name="visitorType" class="form-select" required>
                                    <option value="" disabled selected>Visitor Type</option>
                                    <option value="Adult">Adult</option>
                                    <option value="Kids">Children</option>
                                </select>
                            </div>
                            <div class="input-container">
                                <label for="entrancePrice">Price</label>
                                <input type="text" class="form-control" id="entrancePrice" name="entrancePrice">
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="saveRate">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- modal for event services -->
        <form action="../../Function/Admin/Services/addServices.php" method="POST">
            <div class="modal fade" id="addEventServiceModal" tabindex="-1" aria-labelledby="addEventServiceModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addEventServiceModalLabel">Add an Event
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="input-container">
                                <label for="eventName">Event Name </label>
                                <input type="text" class="form-control" id="eventName" name="eventName">
                            </div>

                            <div class="input-container">
                                <label for="eventPrice">Price</label>
                                <input type="text" class="form-control" id="eventPrice" name="eventPrice">
                            </div>

                            <div class="input-container">
                                <label for="eventCapacity">Event Capacity </label>
                                <input type="text" class="form-control" id="eventCapacity" name="eventCapacity">
                            </div>

                            <div class="input-container">
                                <label for="eventDuration">Event Duration</label>
                                <input type="text" class="form-control" id="eventDuration" name="eventDuration">
                            </div>

                            <div class="input-container">
                                <label for="eventDesc">Event Description</label>
                                <textarea class="form-control" id="eventDesc" name="eventDesc"></textarea>
                            </div>
                            <div class="input-container">
                                <label for="eventImage">Image</label>
                                <input type="file" class="form-control" id="eventImage" name="eventImage">
                            </div>

                            <div class="input-container">
                                <label for="eventAvailability">Event Status</label>
                                <select id="eventAvailability" name="eventAvailability" class="form-select" required>
                                    <option value="" disabled selected>Status</option>
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="saveRate">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- modal for catering services -->
        <form action="../../Function/Admin/Services/addServices.php" method="POST">
            <div class="modal fade" id="addCateringServiceModal" tabindex="-1" aria-labelledby="addCateringModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addCateringServiceModalLabel">Add Catering Option
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="input-container">
                                <label for="foodName">Food Name </label>
                                <input type="text" class="form-control" id="foodName" name="foodName">
                            </div>

                            <div class="input-container">
                                <label for="foodPrice">Price</label>
                                <input type="text" class="form-control" id="foodPrice" name="foodPrice">
                            </div>

                            <div class="input-container">
                                <label for="foodCategory">Food Category</label>
                                <select id="foodCategory" name="foodCategory" class="form-select" required>
                                    <option value="" disabled selected>Category</option>
                                    <option value="chicken">Chicken</option>
                                    <option value="pork">Pork</option>
                                    <option value="beef">Beef</option>
                                    <option value="pasta">Pasta</option>
                                    <option value="vegetables">Vegetables</option>
                                    <option value="seafood">Seafood</option>
                                    <option value="drinks">Drinks</option>
                                    <option value="desserts">Desserts</option>
                                </select>
                            </div>

                            <div class="input-container">
                                <label for="foodAvailability">Food Availability</label>
                                <select id="foodAvailability" name="foodAvailability" class="form-select" required>
                                    <option value="" disabled selected>Select Availability</option>
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="saveRate">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>




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

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous">
    </script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>

    <!-- Button Adding a service function -->
    <script>
        // console.log("Script loaded2");
        const addResortServiceBtn = document.getElementById('addResortServiceBtn');
        const addResortRatesBtn = document.getElementById('addResortRatesBtn');
        const modalAddServiceBtn = document.getElementById('saveService');
        const modalAddResortRatesBtn = document.getElementById('saveRate');
        const form = document.getElementById('addingServiceForm');
        let action = '';

        addResortServiceBtn.addEventListener('click', function() {
            action = 'addResortService';
            modalAddServiceBtn.setAttribute('name', action);
        });

        addResortRatesBtn.addEventListener('click', function() {
            action = 'addResortRates';
            modalAddResortRatesBtn.setAttribute('name', action);
        });
    </script>

    <!-- Table JS -->
    <script>
        // console.log("Script loaded1");
        $(document).ready(function() {
            $('#resortServices').DataTable({
                language: {
                    emptyTable: "No Services"
                },
                columnDefs: [{
                        width: '10%',
                        targets: 0
                    }, {
                        width: '10%',
                        targets: 1
                    }, {
                        width: '5%',
                        targets: 2
                    },
                    {
                        width: '5%',
                        targets: 3
                    }, {
                        width: '5%',
                        targets: 3
                    },
                    {
                        width: '15%',
                        targets: 5
                    },
                    {
                        width: '15%',
                        targets: 6
                    },
                    {
                        width: '15%',
                        targets: 7
                    }, {
                        width: '15%',
                        targets: 8
                    }

                ]
            });
            $('#resortRates').DataTable({
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

    <!-- Changing pages by category -->
    <script>
        // console.log("Script loaded");
        document.addEventListener("DOMContentLoaded", function() {

            const resortLink = document.getElementById("resort-link");
            const resortRatesLink = document.getElementById("resortRates-link");
            const eventLink = document.getElementById("event-link");
            const cateringLink = document.getElementById("catering-link");

            const resortContainer = document.getElementById("resortContainer");
            const resortRatesContainer = document.getElementById("resortRatesContainer");
            const eventContainer = document.getElementById("eventContainer");
            const cateringContainer = document.getElementById("cateringContainer");

            const backButton = document.getElementById("backArrowContainer");
            const serviceCategories = document.getElementById("serviceCategories");
            const headerText = document.getElementById("headerText");

            // console.log(resortLink, resortRatesLink, eventLink, cateringLink, backButton);

            function hideAllContainers() {
                resortContainer.style.display = "none";
                resortRatesContainer.style.display = "none";
                eventContainer.style.display = "none";
                cateringContainer.style.display = "none";
            }

            resortLink.addEventListener("click", function(e) {
                e.preventDefault();
                hideAllContainers();
                serviceCategories.style.display = "none";
                backButton.style.display = "block";
                resortContainer.style.display = "block";
                headerText.innerHTML = "Resort";
                document.body.style.backgroundColor = "whitesmoke";
            });

            resortRatesLink.addEventListener("click", function(e) {
                e.preventDefault();
                hideAllContainers();
                serviceCategories.style.display = "none";
                backButton.style.display = "block";
                resortRatesContainer.style.display = "block";
                headerText.innerHTML = "Resort Rates";
                document.body.style.backgroundColor = "whitesmoke";
            });

            eventLink.addEventListener("click", function(e) {
                e.preventDefault();
                hideAllContainers();
                serviceCategories.style.display = "none";
                backButton.style.display = "block";
                eventContainer.style.display = "block";
                headerText.innerHTML = "Event";
                document.body.style.backgroundColor = "whitesmoke";
            });

            cateringLink.addEventListener("click", function(e) {
                e.preventDefault();
                hideAllContainers();
                serviceCategories.style.display = "none";
                backButton.style.display = "block";
                cateringContainer.style.display = "block";
                headerText.innerHTML = "Catering";
                document.body.style.backgroundColor = "whitesmoke";
            });


            backButton.addEventListener("click", function() {
                hideAllContainers();
                backButton.style.display = "none";
                serviceCategories.style.display = "flex";
                headerText.innerHTML = "Services";
                document.body.style.backgroundColor = "#a1c8c7";
            });
        });
    </script>

    <!-- For editing, cancelling, saving a service -->
    <script src="../../Assets/JS/Services/resortFunc.js"></script>
    <script src="../../Assets/JS/Services/resortRateFunc.js"></script>



</body>

</html>