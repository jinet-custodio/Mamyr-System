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
require '../../Function/notification.php';
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
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/services.css" />
    <link rel="stylesheet" href="../../Assets/CSS/Admin/sidebar.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
        integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body id="servicesBody">
    <div id="sidebar" class=" sidebar show sidebar-custom">
        <div class="sbToggle-container d-flex justify-content-center" id="sidebar-toggle">
            <button class="toggle-button" type="button" id="toggle-btn">
                <i class="bi bi-layout-sidebar"></i>
            </button>
        </div>
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place Logo" class="logo"
            id="sbLogo">
        <ul class="nav flex-column">
            <li class="nav-item" id="navLI">
                <a class="nav-link" href="adminDashboard.php">
                    <i class="bi bi-speedometer2"></i> <span class="linkText">Dashboard</span>
                </a>
            </li>
            <li class="nav-item" id="navLI">
                <a class="nav-link" href="booking.php">
                    <i class="bi bi-calendar-week"></i><span class="linkText"> Bookings</span>
                </a>
            </li>
            <li class="nav-item" id="navLI">
                <a class="nav-link" href="schedule.php">
                    <i class="bi bi-calendar-date"></i><span class="linkText">Schedule</span>
                </a>
            </li>
            <li class="nav-item" id="navLI">
                <a class="nav-link" href="roomList.php">
                    <i class="bi bi-door-open"></i> <span class="linkText">Rooms</span>
                </a>
            </li>
            <li class="nav-item active" id="navLI">
                <a class="nav-link" href="services.php">
                    <i class="bi bi-bell"></i> <span class="linkText">Services</span>
                </a>
            </li>
            <li class="nav-item" id="navLI">
                <a class="nav-link" href="transaction.php">
                    <i class="bi bi-credit-card-2-front"></i> <span class="linkText">Payments</span>
                </a>
            </li>
            <li class="nav-item" id="navLI">
                <a class="nav-link" href="displayPartnership.php">
                    <i class="bi bi-people"></i> <span class="linkText">Partnerships</span>
                </a>
            </li>
            <li class="nav-item" id="navLI">
                <a class="nav-link" href="reviews.php">
                    <i class="bi bi-list-stars"></i> <span class="linkText">Reviews</span>
                </a>
            </li>
            <li class="nav-item" id="navLI">
                <a class="nav-link" href="editWebsite/editWebsite.php">
                    <i class="bi bi-pencil-square"></i> <span class="linkText">Edit Website</span>
                </a>
            </li>
            <li class="nav-item" id="navLI">
                <a class="nav-link" href="auditLogs.php">
                    <i class="bi bi-clock-history"></i> <span class="linkText">Audit Logs</span>
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
                <h5 class="logoutText">Log Out</h5>
            </a>
        </section>
    </div>

    <main>
        <section class="booking-container">
            <section class="notification-toggler-container">
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

            <div class="headerContainer">
                <div class="backArrowContainer" id="backArrowContainer" style="display: none;">
                    <img src="../../Assets/Images/Icon/arrowBtnBlack.png" alt="Back Arrow" class="backArrow">
                </div>
                <!-- <h2 class="header text-center" id="headerText">Services</h2> -->
            </div>

            <section class="page-title-container">
                <h5 class="page-title" id="headerText">Services</h5>
            </section>



            <section id="serviceCategories">
                <button type="button" id="resort-link" class="categoryLink">
                    <div class="card category-card resort-category">
                        <img class="card-img-top" src="../../Assets/Images/amenities/poolPics/poolPic2.jpg"
                            alt="Resort">

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
                            <h5 class="category-title">SERVICE PRICING</h5>
                        </div>
                    </div>
                </button>

                <button type="button" id="catering-link" class="categoryLink">
                    <div class="card category-card event-category">
                        <img class="card-img-top" src="../../Assets/Images/BookNowPhotos/foodCoverImg2.jpg"
                            alt="Catering">
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
                        <!-- <th scope="col">Image</th> -->
                        <th scope="col">Availability</th>
                        <th scope="col">Action</th>
                    </thead>

                    <tbody>
                        <?php
                        $hotelCategoryID = 1;
                        $getResortServices = $conn->prepare("SELECT * FROM resortamenity WHERE RScategoryID !=  ?");

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
                                        <td><input type="text" class="form-control resortServiceCapacity"
                                                name="resortServiceCapacity" value="<?= htmlspecialchars($serviceCapacity) ?>"
                                                readonly></td>
                                        <td><input type="text" class="form-control resortServiceMaxCapacity"
                                                name="resortServiceMaxCapacity" value="<?= htmlspecialchars($serviceMaxCapacity) ?>"
                                                readonly></td>
                                        <td><input type="text" class="form-control resortServiceDuration"
                                                name="resortServiceDuration" value="<?= htmlspecialchars($serviceDuration) ?>"
                                                readonly></td>
                                        <td><textarea name="serviceDesc" readonly
                                                class="form-control"><?= htmlspecialchars($serviceDesc) ?></textarea></td>
                                        <!-- <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control resortServiceImage"
                                                value="<?= htmlspecialchars($serviceImageName) ?>" name="resortServiceImage"
                                                readonly>
                                            <button class="btn btn-outline-secondary editImageBtn" disabled type="button"><i
                                                    class="fa-solid fa-camera"></i></button>
                                        </div>
                                        <input type="file" class="form-control resortServiceImagePicker"
                                            name="resortServiceImagePicker" hidden>
                                    </td> -->
                                        <td>
                                            <select name="resortAvailability" class="form-select resortAvailability" disabled>
                                                <option value="" disabled <?= $serviceAvailability == "" ? "selected" : "" ?>>Select
                                                    Availability</option>
                                                <option value="1" <?= $serviceAvailability == "1" ? "selected" : "" ?>>Available
                                                </option>
                                                <option value="2" <?= $serviceAvailability == "2" ? "selected" : "" ?>>Occupied
                                                </option>
                                                <option value="3" <?= $serviceAvailability == "3" ? "selected" : "" ?>>Maintenance
                                                </option>
                                                <option value="4" <?= $serviceAvailability == "4" ? "selected" : "" ?>>Private
                                                </option>
                                                <option value="5" <?= $serviceAvailability == "5" ? "selected" : "" ?>>Not Available
                                                </option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="buttonContainer">
                                                <button class="btn btn-primary editBtn editResortService"
                                                    onclick="editResortService(this)" id="editPrimary" data-label="Edit"><i
                                                        class="fa-solid fa-pen-to-square"></i>Edit</button>
                                                <button class="btn btn-danger cancelBtn cancelResortService" disabled
                                                    id="cancelDanger" onclick="cancelResortService(this)"><i
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
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#addResortRatesModal" id="addResortRatesBtn">Add Rates</button>
                <table class=" table table-striped" id="resortRates">
                    <thead>
                        <th scope="col">Tour Type</th>
                        <th scope="col">Time Range</th>
                        <th scope="col">Visitor Type</th>
                        <th scope="col">Price</th>
                        <th scope="col">Availability</th>
                        <th scope="col">Action</th>

                    </thead>

                    <tbody>
                        <?php
                        $selectRates = $conn->prepare("SELECT er.*, etr.* FROM entrancerate er
                    JOIN entrancetimerange etr ON er.timeRangeID = etr.timeRangeID");
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
                                                <?= htmlspecialchars($row['sessionType']) == "" ? "selected" : "" ?> selected>
                                                Tour
                                                Type</option>
                                            <option value="<?= htmlspecialchars($row['sessionType']) == "Day" ? "Day" : "" ?>"
                                                <?= htmlspecialchars($row['sessionType']) == "Day" ? "selected" : "" ?>>Day
                                                Swimming
                                            </option>
                                            <option
                                                value="<?= htmlspecialchars($row['sessionType']) == "Night" ? "Night" : "" ?>"
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
                                                <?= htmlspecialchars($row['ERcategory']) == "" ? "selected" : "" ?> selected>
                                                Visitor
                                                Type</option>
                                            <option value="adult"
                                                <?= htmlspecialchars($row['ERcategory']) == "Adult" ? "selected" : "" ?>>Adult
                                            </option>
                                            <option value="children"
                                                <?= htmlspecialchars($row['ERcategory']) == "Kids" ? "selected" : "" ?>>Children
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control entrancePrice" name="entrancePrice"
                                            value="<?= htmlspecialchars($row['ERprice']) ?>" readonly>
                                    </td>
                                    <td>
                                        <select name="availability" class="form-select availability" disabled>
                                            <option value="" disabled
                                                <?= htmlspecialchars($row['availability']) == "" ? "selected" : "" ?> selected>
                                                Select Availability</option>
                                            <option value="Enabled"
                                                <?= htmlspecialchars($row['availability']) == "Enabled" ? "selected" : "" ?>>
                                                Enabled
                                            </option>
                                            <option value="Disabled"
                                                <?= htmlspecialchars($row['availability']) == "Disabled" ? "selected" : "" ?>>
                                                Disabled
                                            </option>
                                        </select>
                                    </td>
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

            <!-- For Service Pricing -->
            <div class="eventContainer" id="eventContainer" style="display: none;">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#addServicePricingModal" id="eventAdd">Add Service Pricing</button>
                <table class=" table table-striped" id="servicePricing">
                    <thead>
                        <th scope="col">Pricing Type</th>
                        <th scope="col">Price</th>
                        <th scope="col">Charge Type</th>
                        <th scope="col">Age Group</th>
                        <th scope="col">Notes</th>
                        <th scope="col">Action</th>
                    </thead>

                    <tbody>
                        <?php
                        $getServicePricingQuery = $conn->prepare("SELECT `pricingID`, `pricingType`, `price`, `chargeType`, `ageGroup`, `notes` FROM `servicepricing`");
                        if (!$getServicePricingQuery->execute()) {
                            error_log('Error: ' . $getServicePricingQuery->error);
                        }

                        $servicePricingResult = $getServicePricingQuery->get_result();
                        $data = [];
                        if ($servicePricingResult->num_rows > 0) {

                            while ($row = $servicePricingResult->fetch_assoc()) {
                                $data[] = [
                                    'pricingID' => (int) $row['pricingID'],
                                    'pricingType' => htmlspecialchars($row['pricingType'] ?? ''),
                                    'price' => $row['price'],
                                    'chargeType' => htmlspecialchars($row['chargeType'] ?? ''),
                                    'ageGroup' => !empty($row['ageGroup']) ? $row['ageGroup'] : 'Both',
                                    'notes' => htmlspecialchars($row['notes'] ?? 'N/A')
                                ];
                            }
                        }
                        ?>
                        <?php foreach ($data as $item): ?>
                            <tr id="service-pricing">
                                <input type="hidden" name="pricingID" value="<?= $item['pricingID'] ?>" class="pricingID">
                                <td>
                                    <select name="pricingType" class="form-select pricingType" disabled>
                                        <option value="" disabled <?= $item['pricingType'] == "" ? "selected" : "" ?>
                                            selected>
                                            Pricing Type</option>
                                        <option value="Per Head"
                                            <?= $item['pricingType'] == "Per Head" ? "selected" : "" ?>>Per
                                            Head</option>
                                        <option value="Per Hour"
                                            <?= $item['pricingType'] == "Per Hour" ? "selected" : "" ?>>Per
                                            Hour</option>
                                    </select>
                                </td>

                                <td>
                                    <input type="text" class="form-control servicePrice" name="servicePrice"
                                        value="<?= htmlspecialchars($item['price']) ?>" readonly>
                                </td>

                                <td>
                                    <select name="chargeType" class="form-select chargeType" disabled>
                                        <option value="" disabled <?= $item['chargeType'] == "" ? "selected" : "" ?>
                                            selected>
                                            Pricing Type</option>
                                        <option value="room" <?= $item['chargeType'] == "Room" ? "selected" : "" ?>>Room
                                        </option>
                                        <option value="food" <?= $item['chargeType'] == "Food" ? "selected" : "" ?>>Food
                                        </option>
                                        <option value="event" <?= $item['chargeType'] == "Event" ? "selected" : "" ?>>Event
                                        </option>
                                    </select>
                                </td>

                                <td> <select name="ageGroup" class="form-select ageGroup" disabled>
                                        <option value="" disabled <?= $item['ageGroup'] == "" ? "selected" : "" ?> selected>
                                            Age
                                            Group</option>
                                        <option value="adult" <?= $item['ageGroup'] == "Adult" ? "selected" : "" ?>>Adult
                                        </option>
                                        <option value="child" <?= $item['ageGroup'] == "Child" ? "selected" : "" ?>>Children
                                        </option>
                                        <option value="both" <?= $item['ageGroup'] == "Both" ? "selected" : "" ?>>Both
                                        </option>
                                    </select>
                                </td>

                                <td><textarea name="SPNotes" class="form-control SPNotes"
                                        readonly><?= $item['notes'] ?></textarea></td>

                                <td>
                                    <div class="buttonContainer">
                                        <button class="btn btn-primary editServicePricing"
                                            onclick="editServicePricing(this)">
                                            <i class="fa-solid fa-pen-to-square"></i>Edit
                                        </button>
                                        <button class="btn btn-danger cancelEditPricingBtn"
                                            onclick="cancelEditServicePricing(this)" disabled>
                                            <i class="fa-solid fa-delete-left"></i>Cancel
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
                        <!-- <th scope="col">Price</th> -->
                        <th scope="col">Category</th>
                        <th scope="col">Availability</th>
                        <th scope="col">Action</th>
                    </thead>

                    <?php
                    $getFoodQuery = $conn->prepare("SELECT mi.*, sa.availabilityName FROM menuitem mi
                LEFT JOIN  serviceavailability sa ON mi.availabilityID = sa.availabilityID");
                    if ($getFoodQuery->execute()) {
                        $foodResult = $getFoodQuery->get_result();
                        if ($foodResult->num_rows > 0) {
                            while ($row = $foodResult->fetch_assoc()) {
                    ?>
                                <tbody>
                                    <tr id="menuData">
                                        <input type="hidden" name="foodID" id="foodID" class="form-control foodID"
                                            value="<?= htmlspecialchars($row['foodItemID']) ?>">
                                        <td><input type="text" class="form-control foodName" name="foodName" id="foodName"
                                                value="<?= htmlspecialchars($row['foodName']) ?>" readonly></td>
                                        <td>
                                            <select id="foodCategory" name="foodCategory" class="form-select foodCategory" disabled>
                                                <option value="" disabled <?= empty($row['foodCategory']) ? 'selected' : '' ?>>
                                                    Category
                                                </option>
                                                <option value="chicken"
                                                    <?= strtolower($row['foodCategory']) === 'chicken' ? 'selected' : '' ?>>Chicken
                                                </option>
                                                <option value="pork"
                                                    <?= strtolower($row['foodCategory']) === 'pork' ? 'selected' : '' ?>>Pork
                                                </option>
                                                <option value="beef"
                                                    <?= strtolower($row['foodCategory']) === 'beef' ? 'selected' : '' ?>>Beef
                                                </option>
                                                <option value="pasta"
                                                    <?= strtolower($row['foodCategory']) === 'pasta' ? 'selected' : '' ?>>Pasta
                                                </option>
                                                <option value="vegetables"
                                                    <?= strtolower($row['foodCategory']) === 'vegetables' ? 'selected' : '' ?>>
                                                    Vegetables</option>
                                                <option value="seafood"
                                                    <?= strtolower($row['foodCategory']) === 'seafood' ? 'selected' : '' ?>>Seafood
                                                </option>
                                                <option value="drink"
                                                    <?= strtolower($row['foodCategory']) === 'drink' ? 'selected' : '' ?>>Drinks
                                                </option>
                                                <option value="dessert"
                                                    <?= strtolower($row['foodCategory']) === 'dessert' ? 'selected' : '' ?>>Desserts
                                                </option>
                                            </select>
                                        </td>


                                        <td>
                                            <select id="foodAvailability" name="foodAvailability"
                                                class="form-select foodAvailability" disabled>
                                                <option value="" disabled <?= empty($row['availabilityName']) ? 'selected' : '' ?>>
                                                    Select Availability</option>
                                                <option value="1" <?= $row['availabilityName'] === 'Available' ? 'selected' : '' ?>>
                                                    Available</option>
                                                <option value="5"
                                                    <?= $row['availabilityName'] === 'Unavailable' ? 'selected' : '' ?>>
                                                    Unavailable</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="buttonContainer">
                                                <button class="btn btn-primary editMenuItem" onclick="editMenuItem(this)"><i
                                                        class="fa-solid fa-pen-to-square"></i>Edit</button>
                                                <button class="btn btn-danger cancelBtn cancelEditItem"
                                                    onclick="cancelEditItem(this)" disabled><i
                                                        class="fa-solid fa-delete-left"></i>Cancel</button>
                                            </div>
                                        </td>
                                    </tr>
                        <?php
                            }
                        }
                    } else {
                        error_log("Error executing " . $getFoodQuery->error);
                    }
                        ?>
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
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="input-container">
                                    <label for="serviceName">Service Name</label>
                                    <input type="text" class="form-control" id="serviceName" name="serviceName"
                                        required>
                                </div>
                                <div class="input-container">
                                    <label for="servicePrice"> Service Price</label>
                                    <input type="text" class="form-control" id="servicePrice" name="servicePrice"
                                        required>
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
                                        $getCategory = $conn->prepare('SELECT * FROM resortservicescategory WHERE categoryName != ?');
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
                                <button type="submit" class="btn btn-primary" id="saveService"
                                    name="addResortService">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!-- </form> -->

            <!-- Modal for resort rates -->
            <form action="../../Function/Admin/Services/addServices.php" method="POST">
                <div class="modal fade" id="addResortRatesModal" tabindex="-1"
                    aria-labelledby="addResortRatesModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addResortRatesModalLabel">Add a Resort Rate
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="input-container">
                                    <label for="tourType">Tour Type</label>
                                    <select id="tourType" name="tourType" class="form-select" required>
                                        <option value="" disabled selected>Tour Type</option>
                                        <?php
                                        $getTourType = $conn->prepare("SELECT timeRangeID, session_type FROM entrancetimerange");
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
                                        $getTimeRange = $conn->prepare("SELECT timeRangeID, time_range FROM entrancetimerange");
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
                                <button type="submit" class="btn btn-primary" id="saveRate"
                                    name="addResortRates">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- modal for service pricing -->
            <form action="../../Function/Admin/Services/addServices.php" method="POST">
                <div class="modal fade" id="addServicePricingModal" tabindex="-1"
                    aria-labelledby="addServicePricingModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addServicePricingModalLabel">Add Service Pricing
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="input-container">
                                    <label for="pricingType">Pricing Type </label>
                                    <select name="pricingType" class="form-select" required>
                                        <option value="" disabled selected>Pricing Type</option>
                                        <option value="Per Head">Per Head</option>
                                        <option value="Per Hour">Per Hour</option>
                                    </select>
                                </div>

                                <div class="input-container">
                                    <label for="servicePrice">Price</label>
                                    <input type="text" maxlength="50" class="form-control" name="SPservicePrice">
                                </div>

                                <td>
                                    <label for="chargeType">Charge Type </label>
                                    <select name="chargeType" class="form-select" id="chargeType">
                                        <option value="" disabled selected>Pricing Type</option>
                                        <option value="Room">Room</option>
                                        <option value="Food">Food</option>
                                    </select>
                                </td>

                                <div class="input-container">
                                    <label for="ageGroup">Age Group</label>
                                    <select name="ageGroup" class="form-select" required>
                                        <option value="" disabled selected>Age Group</option>
                                        <option value="adult">Adult</option>
                                        <option value="children">Children</option>
                                        <option value="both">Both</option>
                                    </select>
                                </div>

                                <div class="input-container">
                                    <label for="eventDesc">Notes</label>
                                    <textarea name="SPNotes" class="form-control" maxlength="100"></textarea>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" name="addServicePrice">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- modal for catering services -->
            <form action="../../Function/Admin/Services/addServices.php" method="POST">
                <div class="modal fade" id="addCateringServiceModal" tabindex="-1"
                    aria-labelledby="addCateringModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addCateringServiceModalLabel">Add Catering Option
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="input-container">
                                    <label for="foodName">Food Name</label>
                                    <input type="text" class="form-control" id="foodName" name="foodName" required>
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
                                        <option value="drink">Drinks</option>
                                        <option value="dessert">Desserts</option>
                                    </select>
                                </div>

                                <div class="input-container">
                                    <label for="foodAvailability">Food Availability</label>
                                    <select id="foodAvailability" name="foodAvailability" class="form-select">
                                        <option value="" disabled selected>Select Availability</option>
                                        <?php
                                        $UnavailableName = 'Unavailable';
                                        $AvailableName = 'Available';
                                        $getAvailability = $conn->prepare('SELECT * FROM serviceavailability WHERE availabilityName = ? OR availabilityName = ?');
                                        $getAvailability->bind_param("ss", $UnavailableName, $AvailableName);
                                        if ($getAvailability->execute()) {
                                            $result = $getAvailability->get_result();
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                        ?>
                                                    <option value="<?= htmlspecialchars($row['availabilityID']) ?>">
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
                                <button type="submit" class="btn btn-primary" id="saveFood"
                                    name="addFoodItem">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </main>




    <!-- Notification Modal -->
    <?php include '../notificationModal.php' ?>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous">
    </script>

    <!-- Responsive sidebar -->
    <script src="../../Assets/JS/adminSidebar.js"> </script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>

    <!-- Table JS -->
    <script>
        // console.log("Script loaded1");
        $(document).ready(function() {
            $('#resortServices').DataTable({
                language: {
                    emptyTable: "No Services"
                },
                columnDefs: [{
                        targets: [0, 1, 2, 3, 4],
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return data;
                            }
                            var cell = $('#resortServices tbody tr').eq(meta.row).find('td').eq(meta
                                .col);
                            var input = cell.find('input');

                            return input.length ? input.val() : data;
                        }
                    },
                    {
                        targets: 5,
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return data;
                            }

                            var cell = $('#resortServices tbody tr').eq(meta.row).find('td').eq(meta
                                .col);
                            var textarea = cell.find('textarea');

                            return textarea.length ? textarea.val() : data;
                        }
                    },
                    {
                        targets: 6,
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return data;
                            }

                            var cell = $('#resortServices tbody tr').eq(meta.row).find('td').eq(meta
                                .col);
                            var select = cell.find('select');

                            return select.length ? select.find('option:selected').text() : data;
                        }
                    }
                ]
            });

            $('#resortRates').DataTable({
                language: {
                    emptyTable: "No Data"
                },
                columnDefs: [{
                        targets: [1, 3],
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return data;
                            }
                            var cell = $('#resortRates tbody tr').eq(meta.row).find('td').eq(meta
                                .col);
                            var input = cell.find('input');

                            return input.length ? input.val() : data;
                        }
                    },
                    {
                        targets: [0, 2, 4],
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return data;
                            }

                            var cell = $('#resortRates tbody tr').eq(meta.row).find('td').eq(meta
                                .col);
                            var select = cell.find('select');

                            return select.length ? select.find('option:selected').text() : data;
                        }
                    }
                ]
            });
            $('#servicePricing').DataTable({
                language: {
                    emptyTable: "No Data"
                },
                columnDefs: [{
                        targets: 1,
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return data;
                            }
                            var cell = $('#servicePricing tbody tr').eq(meta.row).find('td').eq(meta
                                .col);
                            var input = cell.find('input');

                            return input.length ? input.val() : data;
                        }
                    },
                    {
                        targets: 4,
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return data;
                            }

                            var cell = $('#servicePricing tbody tr').eq(meta.row).find('td').eq(meta
                                .col);
                            var textarea = cell.find('textarea');

                            return textarea.length ? textarea.val() : data;
                        }
                    },
                    {
                        targets: [0, 2, 3],
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return data;
                            }

                            var cell = $('#servicePricing tbody tr').eq(meta.row).find('td').eq(meta
                                .col);
                            var select = cell.find('select');

                            return select.length ? select.find('option:selected').text() : data;
                        }
                    }
                ]
            });
            $('#cateringServices').DataTable({
                language: {
                    emptyTable: "No Data"
                },
                columnDefs: [{
                        targets: 0,
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return data;
                            }
                            var cell = $('#cateringServices tbody tr').eq(meta.row).find('td').eq(
                                meta.col);
                            var input = cell.find('input');

                            return input.length ? input.val() : data;
                        }
                    },
                    {
                        targets: [1, 2],
                        render: function(data, type, row, meta) {
                            if (type === 'display') {
                                return data;
                            }

                            var cell = $('#cateringServices tbody tr').eq(meta.row).find('td').eq(
                                meta.col);
                            var select = cell.find('select');

                            return select.length ? select.find('option:selected').text() : data;
                        }
                    }
                ]
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

            });

            resortRatesLink.addEventListener("click", function(e) {
                e.preventDefault();
                hideAllContainers();
                serviceCategories.style.display = "none";
                backButton.style.display = "block";
                resortRatesContainer.style.display = "block";
                headerText.innerHTML = "Resort Rates";

            });

            eventLink.addEventListener("click", function(e) {
                e.preventDefault();
                hideAllContainers();
                serviceCategories.style.display = "none";
                backButton.style.display = "block";
                eventContainer.style.display = "block";
                headerText.innerHTML = "Service Pricing";

            });

            cateringLink.addEventListener("click", function(e) {
                e.preventDefault();
                hideAllContainers();
                serviceCategories.style.display = "none";
                backButton.style.display = "block";
                cateringContainer.style.display = "block";
                headerText.innerHTML = "Catering";

            });


            backButton.addEventListener("click", function() {
                hideAllContainers();
                backButton.style.display = "none";
                serviceCategories.style.display = "flex";
                headerText.innerHTML = "Services";

            });
        });
    </script>

    <!-- For editing, cancelling, saving a service -->
    <script src="../../Assets/JS/Services/resortFunc.js"></script>
    <script src="../../Assets/JS/Services/resortRateFunc.js"></script>
    <script src="../../Assets/JS/Services/cateringFunc.js"></script>
    <script src="../../Assets/JS/Services/servicePricingFunc.js"> </script>

    <?php include '../Customer/loader.php'; ?>
</body>

</html>