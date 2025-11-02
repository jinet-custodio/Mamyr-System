<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

switch ($userRole) {
    case 1: //customer
        $role = "Customer";
        break;
    case 2:
        $role = "Business Partner";
        break;
    case 3:
        $role = "Admin";
        break;
    case 4:
        $role = "Partnership Applicant";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Book Now</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Customer/resortBooking.css">

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">

    <!-- Flatpickr calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Boxicons -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body id="resort-page">

    <!-- Resort Booking -->
    <form action="confirmBooking.php" method="POST" id="resortBookingForm">
        <div class="resort" id="resort">
            <button type="button" class="backToSelection btn btn-info" id="backToSelection">
                <img src="../../Assets/Images/Icon/arrowBtnWhite.png" alt="back button" />
            </button>
            <div class="titleContainer">
                <h4 class="resortTitle text-center" id="resortTitle">RESORT BOOKING</h4>
            </div>

            <div class="container">
                <div class="card resort-card" id="resortBookingCard" style="flex-shrink: 0;">
                    <h5 class="schedLabel">Schedule</h5>
                    <div class="scheduleForm">
                        <div class="scheduleInputContainer">
                            <input type="text" class="form-control w-95" id="resortBookingDate" name="resortBookingDate"
                                placeholder="Select booking date" required
                                value="<?php echo isset($_SESSION['resortFormData']['resortBookingDate']) ? htmlspecialchars(trim($_SESSION['resortFormData']['resortBookingDate'])) : ''; ?>" />
                            <i class="fa-solid fa-calendar" id="calendarIcon">
                            </i>
                        </div>
                        <select id="tourSelections" name="tourSelections" class="form-select" required>
                            <option value="" disabled selected>Select Preferred Tour</option>
                            <option value="Day" id="dayTour"
                                <?= (isset($_SESSION['resortFormData']['tourSelections']) && $_SESSION['resortFormData']['tourSelections'] === 'Day') ? 'selected' : '' ?>>
                                Day Tour</option>
                            <option value="Night" id="nightTour"
                                <?= (isset($_SESSION['resortFormData']['tourSelections']) && $_SESSION['resortFormData']['tourSelections'] === 'Night') ? 'selected' : '' ?>>
                                Night Tour</option>
                            <option value="Overnight" id="overnightTour"
                                <?= (isset($_SESSION['resortFormData']['tourSelections']) && $_SESSION['resortFormData']['tourSelections'] === 'Overnight') ? 'selected' : '' ?>>
                                Overnight Tour</option>
                        </select>
                    </div>

                    <h5 class="noOfPeopleLabel">Number of People</h5>
                    <div class="peopleForm">
                        <div class="input-container ">
                            <input type="number" class="form-control" min="0" placeholder="Adults" id="adultCount"
                                name="adultCount"
                                value="<?php echo isset($_SESSION['resortFormData']['adultCount']) ? htmlspecialchars(trim($_SESSION['resortFormData']['adultCount'])) : ''; ?>" />
                            <div class="info-container mt-1">
                                <i class="fa-solid fa-circle-info" style="color: #007BFF;"></i>
                                <p>Aged 14 and up</p>
                            </div>
                        </div>
                        <div class="input-container">
                            <input type="number" class="form-control" min="0" placeholder="Kids" id="childrenCount"
                                name="childrenCount"
                                value="<?php echo isset($_SESSION['resortFormData']['childrenCount']) ? htmlspecialchars(trim($_SESSION['resortFormData']['childrenCount'])) : ''; ?>" />
                            <div class="info-container mt-1">
                                <i class="fa-solid fa-circle-info" style="color: #007BFF;"></i>
                                <p>Aged 13 and below</p>
                            </div>
                        </div>

                        <div class="input-container">
                            <input type="number" class="form-control" min="0" placeholder="Toddler" id="toddlerCount"
                                name="toddlerCount"
                                value="<?php echo isset($_SESSION['resortFormData']['toddlerCount']) ? htmlspecialchars(trim($_SESSION['resortFormData']['toddlerCount'])) : ''; ?>" />
                            <div class="info-container mt-1">
                                <i class="fa-solid fa-circle-info" style="color: #007BFF;"></i>
                                <p>Height 3ft and below</p>
                            </div>
                        </div>
                    </div>

                    <div class="cottageRoomForm">
                        <div class="cottagesForm" id="cottages">
                            <h5 class="cottagesFormLabel" id="cottagesFormLabel">Cottage/s</h5>
                            <button type="button" class="btn btn-info text-white w-100" name="cottageBtn"
                                id="cottageBtn" data-bs-toggle="modal" data-bs-target="#cottageModal"> Choose
                                here</button>
                            <div id="selectedCottagesContainer" class="selected-container mt-2"></div>
                            <!-- Modal for cottages -->
                            <div class="modal" id="cottageModal">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Available Cottage/s</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body" id="cottageModalBody">
                                            <p class="modal-text"> <i class="fa-solid fa-circle-info"
                                                    style="color: rgb(15, 127, 255);"></i> You can select more than one
                                                cottage</p>
                                            <div id="cottagesContainer">
                                                <p class="text-center"> Choose your preferred booking date and tour first!</p>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary"
                                                data-bs-dismiss="modal">Okay</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="roomNumbers" id="rooms" style="display: none;">
                            <h5 class="roomLabel" id="roomLabel">Hotel Room</h5>
                            <button type="button" class="btn btn-info text-white w-100" name="hotelBtn" id="hotelBtn"
                                data-bs-toggle="modal" data-bs-target="#hotelRoomModal"> Choose
                                here...</button>
                            <div id="selectedRoomsContainer" class="selected-container mt-2"></div>
                            <!-- Modal for hotel rooms -->
                            <div class="modal" id="hotelRoomModal">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Available Hotels</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="modal-text"> <i class="fa-solid fa-circle-info"
                                                    style="color: rgb(15, 127, 255);"></i> You can select more than one
                                                hotel room</p>
                                            <div id="roomsContainer">
                                                <p class="text-center"> Choose your preferred booking date and tour first!</p>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary"
                                                data-bs-dismiss="modal">Okay</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="entertainmentForm">
                        <h5 class="entertainmentFormLabel" id="entertainmentFormLabel">Additional Services:</h5>
                        <button type="button" class="btn btn-info text-white w-100" name="entertainmentBtn"
                            id="entertainmentBtn" data-bs-toggle="modal" data-bs-target="#entertainmentModal">
                            Choose here...</button>
                        <div id="selectedEntertainmentContainer" class="selected-container mt-2"></div>
                        <!-- Modal for hotel rooms -->
                        <div class="modal modal-fullscreen-sm-down" id="entertainmentModal">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Available Additional Services</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="entertainmentContainer">
                                            <p class="text-center"> Choose your preferred booking date and tour first!</p>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary"
                                            data-bs-dismiss="modal">Okay</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="summary mt-3" id="selected-cottage-room" style="border: 1px solid red;">
                        <ul id="selection-list"></ul>
                    </div> -->

                    <h5 class="purposeLabel">Purpose for Booking/Additional Notes</h5>
                    <textarea class="form-control w-100" id="purpose-additionalNotes" maxlength="150"
                        name="additionalRequest" rows="3" placeholder="Optional"></textarea>

                    <div class="mt-auto button-container">
                        <button type="button" class="btn btn-primary btn-md w-100" id="bookRatesBTN"
                            name="bookRates">Book Now</button>
                    </div>

                    <div class="additional-info-container">
                        <ul>
                            <li style="color: #0076d1ff;">
                                <i class="fa-solid fa-circle-info" style="color: #37a5fff1;"></i>&nbsp;
                                The resort staff will double check the number of people on the day of the scheduled
                                booking
                            </li>
                            <li style="color: #0076d1ff;">
                                <i class="fa-solid fa-circle-info" style="color: #37a5fff1;"></i>&nbsp;
                                Children’s height will be measured on the day of the scheduled booking to verify if they
                                are 3 feet tall or below.
                            </li>
                            <li style="color: #0076d1ff;">
                                <i class="fa-solid fa-circle-info" style="color: #37a5fff1;"></i>&nbsp;
                                Payment for cottages or rooms is required and must be made through the resort’s GCash
                                account.
                            </li>

                        </ul>
                    </div>
                </div>


                <div class="entrance-rates">
                    <div class="card rates">

                        <h1 class="text-center">Entrance Fee</h1>

                        <div class="card-body">
                            <?php
                            $getEntranceRates = $conn->prepare("SELECT er.*, etr.timeRangeID AS primaryID, etr.time_range AS timeRange FROM entrancerate er
                            JOIN entrancetimerange etr ON er.timeRangeID = etr.timeRangeID
                            ORDER BY er.sessionType, etr.time_range, er.ERcategory");
                            $getEntranceRates->execute();
                            $getEntranceRatesResult = $getEntranceRates->get_result();

                            if ($getEntranceRatesResult->num_rows > 0) {
                                $groupedData = [];
                                while ($row = $getEntranceRatesResult->fetch_assoc()) {
                                    $sessionType = $row['sessionType'];
                                    $timeRange = $row['timeRange'];
                                    $category = $row['ERcategory'];
                                    $price = $row['ERprice'];

                                    $key = $sessionType . '|' . $timeRange;
                                    $groupedData[$key][] = [
                                        'category' => $category,
                                        'price' => $price
                                    ];
                                }
                                foreach ($groupedData as $key => $entries) {
                                    list($sessionType, $timeRange) = explode('|', $key);
                            ?>
                                    <div class="data-container">
                                        <h5><strong><?= htmlspecialchars($sessionType) ?> Tour</strong>|
                                            <?= htmlspecialchars($timeRange) ?> </h5>
                                        <?php
                                        foreach ($entries as $entry) {
                                        ?>
                                            <p><strong><?= htmlspecialchars($entry['category']) ?></strong> -
                                                ₱ <?= htmlspecialchars($entry['price']) ?></p>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                <?php
                                }
                            } else {
                                ?>
                                <h1 class="error-display">No data to display</h1>
                            <?php
                            }
                            ?>
                        </div>
                    </div>


                    <div class="card cottagesEntertainment">
                        <div class="cottagesNotes">
                            <div class="card-body cottage">
                                <h1>Cottages</h1>
                                <?php
                                $cottageCategoryID = 2;
                                $rowNumber = 1;
                                $getCottageQuery = $conn->prepare("SELECT *
                                                                        FROM (
                                                                            SELECT *, ROW_NUMBER() OVER (PARTITION BY RScapacity ORDER BY RSprice ASC) AS rn
                                                                            FROM resortamenity
                                                                            WHERE RScategoryID = ?
                                                                        ) AS sub
                                                                        WHERE rn = ?;
                                                                        ");
                                $getCottageQuery->bind_param("ii", $cottageCategoryID, $rowNumber);
                                $getCottageQuery->execute();
                                $getCottageQueryResult =  $getCottageQuery->get_result();
                                if ($getCottageQueryResult->num_rows > 0) {
                                    while ($row = $getCottageQueryResult->fetch_assoc()) {
                                        $description = $row['RSdescription'];
                                        $price = $row['RSprice'];
                                ?>
                                        <p> ₱ <?= htmlspecialchars(number_format($price, 0)) ?> pesos
                                            <?= htmlspecialchars(strtolower($description)) ?> </p>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <p>No Cottages to display</p>
                                <?php
                                }
                                ?>
                            </div>

                            <div class="card-body note">
                                <h1 class="card-title">NOTE:</h1>
                                <ul>
                                    <li>Food is allowed except alcoholic drink and soft drinks. It`s available and
                                        affordable in our convenience store inside.</li>
                                    <li>Appropriate swimming attire is required.</li>
                                    <li>3ft below are free in entrance fee</li>
                                </ul>
                            </div>
                        </div>

                        <div class="entertainmentContainer">
                            <div class="card-body videoke">
                                <h1> Videoke</h1>
                                <?php
                                $entertainmentName = 'Videoke %';
                                $categoryID = 3;
                                $getVideoke = $conn->prepare("SELECT * FROM resortamenity WHERE  RScategoryID = ? AND  RServiceName LIKE ? LIMIT 1");
                                $getVideoke->bind_param("is",  $categoryID, $entertainmentName);
                                $getVideoke->execute();
                                $getVideokeResult =  $getVideoke->get_result();
                                if ($getVideokeResult->num_rows > 0) {
                                    while ($row = $getVideokeResult->fetch_assoc()) {
                                        $price = $row['RSprice'];
                                ?>

                                        <p><?= htmlspecialchars(number_format($price, 0)) ?> pesos per rent </p>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <p>None</p>
                                <?php
                                }
                                ?>

                            </div>

                            <div class="card-body massage">
                                <h1> Massage Chair</h1>
                                <?php
                                $entertainmentName = 'Massage Chair';
                                $categoryID = 3;
                                $getVideoke = $conn->prepare("SELECT * FROM resortamenity WHERE RServiceName = ? AND RScategoryID = ?");
                                $getVideoke->bind_param("si", $entertainmentName, $categoryID);
                                $getVideoke->execute();
                                $getVideokeResult =  $getVideoke->get_result();
                                if ($getVideokeResult->num_rows > 0) {
                                    while ($row = $getVideokeResult->fetch_assoc()) {
                                        $duration = $row['RSduration'];
                                        $price = $row['RSprice'];
                                ?>

                                        <p><?= htmlspecialchars(number_format($price, 0)) ?> pesos for
                                            <?= htmlspecialchars($duration) ?></p>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <p>None</p>
                                <?php
                                }
                                ?>

                            </div>

                            <div class="card-body billiard">
                                <h1> Billiard</h1>
                                <?php
                                $entertainmentName = 'Billiard';
                                $categoryID = 3;
                                $getVideoke = $conn->prepare("SELECT * FROM resortamenity WHERE RServiceName = ? AND RScategoryID = ?");
                                $getVideoke->bind_param("si", $entertainmentName, $categoryID);
                                $getVideoke->execute();
                                $getVideokeResult =  $getVideoke->get_result();
                                if ($getVideokeResult->num_rows > 0) {
                                    while ($row = $getVideokeResult->fetch_assoc()) {
                                        $duration = $row['RSduration'];
                                        $price = $row['RSprice'];
                                ?>
                                        <p><?= htmlspecialchars(number_format($price, 0)) ?> pesos for
                                            <?= htmlspecialchars($duration) ?> </p>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <p>None</p>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Phone Number Modal -->
    <form action="../../Function/getPhoneNumber.php" method="POST">
        <div class="modal fade" id="phoneNumberModal" tabindex=" -1"
            aria-labelledby="phoneNumberModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="phoneNumberModalLabel">Required Phone Number</h5>
                    </div>
                    <div class="modal-body">
                        <p class="text-center">Phone number is required before booking please enter your phone
                            number
                        </p>
                        <input type="tel" name="phoneNumber" id="phoneNumber" class="form-control w-100 mt-2"
                            placeholder="+63 9XX XXX XXXX" pattern="^(?:\+63|0)9\d{9}$"
                            title="e.g., +639123456789 or 09123456789" required>
                        <input type="hidden" name="page" value="resortBooking.php">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" name="submitPhoneNumber">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>


    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Flatpickr for date input -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Functions -->
    <script>
        document.getElementById("backToSelection").addEventListener("click", function() {
            window.location.href = "bookNow.php";
        });
    </script>

    <!-- Calendar -->
    <script>
        const calIcon = document.getElementById("calendarIcon");
        //resort calendar
        flatpickr('#resortBookingDate', {
            // enableTime: true,
            minDate: new Date().setDate(new Date().getDate() + 1),
            dateFormat: "Y-m-d",
        });
    </script>

    <!-- Fetch Info -->
    <script>
        const cottageSelectionsSession = <?= isset($_SESSION['resortFormData']['cottageOptions'])
                                                ? json_encode($_SESSION['resortFormData']['cottageOptions'])
                                                : '[]'
                                            ?>;
        const addOnsServicesSession =
            <?= isset($_SESSION['resortFormData']['addOnsServices']) ? json_encode($_SESSION['resortFormData']['addOnsServices']) : '[]' ?>;

        const roomSelectionSession =
            <?= isset($_SESSION['resortFormData']['roomOptions']) ? json_encode($_SESSION['resortFormData']['roomOptions']) : '[]' ?>;
        // console.log(roomSelectionSession);
        document.addEventListener("DOMContentLoaded", function() {
            const dateInput = document.getElementById('resortBookingDate');
            const tourInput = document.getElementById('tourSelections');
            const form = document.querySelector('form');
            if (dateInput && !dateInput.value) {
                Swal.fire({
                    icon: 'info',
                    title: 'Select your choice of date',
                    text: 'Please pick a booking date to continue',
                    confirmButtonText: 'OK'
                }).then(() => {
                    tourInput.style.border = '2px solid red'
                    dateInput.style.border = '2px solid red';
                    form.removeAttribute('aria-hidden');
                    dateInput.focus();
                })
            };
        });

        const startDate = document.getElementById('resortBookingDate');
        const tourSelect = document.getElementById('tourSelections');

        const adultCount = document.getElementById('adultCount');
        const kidsCount = document.getElementById('childrenCount');

        function getTotalPax() {
            const kids = parseInt(kidsCount.value) || 0;
            const adults = parseInt(adultCount.value) || 0;
            return kids + adults;
        }


        function fetchAmenities() {
            const selectedDate = startDate.value;
            const selectedTour = tourSelect.value;

            if (!selectedDate || !selectedTour) return;

            fetch(
                    `../../Function/Booking/getAvailableAmenities.php?date=${encodeURIComponent(selectedDate)}&tour=${encodeURIComponent(selectedTour)}`
                )
                .then(response => {
                    if (!response.ok) throw new Error("Network error");
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert("Error: " + data.error);
                        return;
                    }
                    const cottageContainer = document.getElementById('cottagesContainer');
                    const roomSection = document.getElementById('rooms');
                    const roomsContainer = document.getElementById('roomsContainer');
                    const entertainmentContainer = document.getElementById('entertainmentContainer');

                    cottageContainer.innerHTML = '';
                    roomsContainer.innerHTML = '';
                    entertainmentContainer.innerHTML = '';
                    roomSection.style.display = 'none';

                    function getCottages() {
                        data.cottages.forEach(cottage => {
                            const wrapper = document.createElement('div');
                            wrapper.classList.add('checkbox-item');

                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'cottageOptions[]';
                            checkbox.value = cottage.RServiceName;
                            checkbox.id = `${cottage.RServiceName}`;
                            checkbox.dataset.capacity = cottage.RScapacity;

                            const label = document.createElement('label');
                            label.setAttribute('for', checkbox.id);
                            label.textContent = `${cottage.RServiceName} - (${cottage.RScapacity} pax)`;

                            const cottageSelections = cottageSelectionsSession.map(String);
                            if (cottageSelections.includes(String(cottage.RServiceName))) {
                                checkbox.checked = true;
                            }

                            wrapper.appendChild(checkbox);
                            wrapper.appendChild(label);

                            cottageContainer.appendChild(wrapper);
                        });
                    };

                    function getRooms() {
                        roomSection.style.display = 'block';

                        data.rooms.forEach(room => {
                            const wrapper = document.createElement('div');
                            wrapper.classList.add('checkbox-item');

                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'roomOptions[]';
                            checkbox.value = room.RServiceName;
                            checkbox.id = `${room.RServiceName}`;
                            checkbox.dataset.capacity = room.RScapacity;

                            const label = document.createElement('label');
                            label.setAttribute('for', checkbox.id);
                            label.innerHTML =
                                `<strong>${room.RServiceName} </strong> for ₱${Number(room.RSprice).toLocaleString()}.00 - Good for ${room.RScapacity} pax`;

                            const roomSelection = roomSelectionSession.map(String);
                            if (roomSelection.includes(String(room.RServiceName))) {
                                checkbox.checked = true;
                            }

                            wrapper.appendChild(checkbox);
                            wrapper.appendChild(label);
                            roomsContainer.appendChild(wrapper);
                        });
                    }

                    // Show cottages for Day/Night
                    if (selectedTour === 'Day' || selectedTour === 'Night') {
                        getCottages();
                    }

                    // Show rooms for Overnight
                    if (selectedTour === 'Overnight') {
                        getRooms();
                        getCottages();
                    }

                    // Show entertainment for all
                    if (data.entertainments && data.entertainments.length > 0) {
                        //  entertainmentLabel.innerHTML = "Additional Services";
                        data.entertainments.forEach(ent => {
                            const wrapper = document.createElement('div');
                            wrapper.classList.add('checkbox-item');

                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'entertainmentOptions[]';
                            checkbox.value = ent.RServiceName;
                            checkbox.id = `ent-${ent.RServiceName}`;

                            const label = document.createElement('label');
                            label.setAttribute('for', checkbox.id);
                            label.textContent =
                                `${ent.RServiceName} - ₱${Number(ent.RSprice).toLocaleString()}.00`;

                            const addOnsServices = addOnsServicesSession.map(String);
                            if (addOnsServices.includes(String(ent.RServiceName))) {
                                checkbox.checked = true;
                            }

                            wrapper.appendChild(checkbox);
                            wrapper.appendChild(label);
                            entertainmentContainer.appendChild(wrapper);
                        });
                    }
                })
                .catch(error => {
                    // console.error(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch amenities. Please try again.',
                    });
                });
        }


        document.addEventListener("DOMContentLoaded", () => {
            if (startDate && startDate.value || tourSelect && tourSelect.value) {
                fetchAmenities();
                startDate.style.border = '1px solid rgb(223, 226, 230)';
                tourSelect.style.border = '1px solid rgb(223, 226, 230)';
            }

            // console.log("startDate.value at DOMContentLoaded:", startDate?.value);

        });

        if (startDate) {
            startDate.addEventListener("change", () => {
                fetchAmenities();
                startDate.style.border = '1px solid rgb(223, 226, 230)';
            });
            fetchAmenities();
            startDate.style.border = '1px solid rgb(223, 226, 230)';

        }
        if (tourSelect) {
            tourSelect.addEventListener('change', function() {
                fetchAmenities();
                tourSelect.style.border = '1px solid rgb(223, 226, 230)';
            });
            tourSelect.style.border = '1px solid rgb(223, 226, 230)';
            fetchAmenities();
        }
        const bookRatesBTN = document.getElementById('bookRatesBTN')


        bookRatesBTN.addEventListener("click", function() {
            // e.preventDefault();

            let totalCapacity = 0;
            const totalPax = getTotalPax();

            const cottageSelected = document.querySelectorAll('input[name="cottageOptions[]"]:checked');
            cottageSelected.forEach(item => {
                totalCapacity += parseInt(item.dataset.capacity) || 0;
            });

            let roomSelectedCount = 0;
            let roomTotalCapacity = 0;
            const roomSelected = document.querySelectorAll('input[name="roomOptions[]"]:checked');
            roomSelected.forEach(item => {
                roomTotalCapacity += parseInt(item.dataset.capacity) || 0;
                roomSelectedCount++;
            });

            let isValid = true;
            if (tourSelect.value === 'Overnight' && roomSelectedCount === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops!',
                    text: 'Room is required. Please select a room(s).',
                });
                isValid = false;
            }

            if (totalPax === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops',
                    text: 'Please enter the number of guests.',
                });
                isValid = false;
            }

            if (tourSelect.value === 'Night' || tourSelect.value === 'Day') {
                if (totalCapacity === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops',
                        text: 'Select a cottage(s) or room(s)',
                    });
                    isValid = false;
                }
                if (totalPax > totalCapacity) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops',
                        text: 'The number of guests exceeds the capacity of the selected cottage(s) or room(s). Please adjust your selection.',
                    });
                    isValid = false;
                }
            }

            bookRatesBTN.type = isValid ? 'submit' : 'button';
        });
    </script>

    <!-- For displaying text for hotel and cottages -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            function renderSelectedItems(containerId, label, items) {
                const container = document.getElementById(containerId);
                if (!container) return;

                container.innerHTML = ""; // clear previous items
                if (items.length === 0) return;

                const wrapper = document.createElement("div");
                wrapper.classList.add("selected-inline");

                const labelEl = document.createElement("span");
                labelEl.classList.add("selected-label-inline");
                labelEl.textContent = label + " ";

                wrapper.appendChild(labelEl);

                items.forEach(item => {
                    const tag = document.createElement("span");
                    tag.classList.add("selected-tag");
                    tag.textContent = item;
                    wrapper.appendChild(tag);
                });

                container.appendChild(wrapper);
            }

            const cottageModal = document.getElementById('cottageModal');
            const cottageOkayBtn = cottageModal.querySelector('.modal-footer .btn-primary');
            cottageOkayBtn.addEventListener('click', () => {
                const selectedCottages = Array.from(document.querySelectorAll('input[name="cottageOptions[]"]:checked'))
                    .map(el => el.value);
                renderSelectedItems('selectedCottagesContainer', 'Selected Cottages/:', selectedCottages);
            });

            const hotelModal = document.getElementById('hotelRoomModal');
            const hotelOkayBtn = hotelModal.querySelector('.modal-footer .btn-primary');
            hotelOkayBtn.addEventListener('click', () => {
                const selectedRooms = Array.from(document.querySelectorAll('input[name="roomOptions[]"]:checked'))
                    .map(el => el.value);
                renderSelectedItems('selectedRoomsContainer', 'Selected Hotel Room/s:', selectedRooms);
            });

            const entertainmentModal = document.getElementById('entertainmentModal');
            const entertainmentOkayBtn = entertainmentModal.querySelector('.modal-footer .btn-primary');
            entertainmentOkayBtn.addEventListener('click', () => {
                const selectedEntertainment = Array.from(document.querySelectorAll('input[name="entertainmentOptions[]"]:checked'))
                    .map(el => el.value);
                renderSelectedItems('selectedEntertainmentContainer', 'Selected Additional Service/s:', selectedEntertainment);
            });

        });
    </script>




    <script>
        //* For not allowing letters
        const phoneNumber = document.getElementById('phoneNumber');

        phoneNumber.addEventListener('keypress', function(e) {
            if (!/[0-9+]/.test(e.key)) {
                e.preventDefault();
            }
        })

        // * For Messages Popup

        const param = new URLSearchParams(window.location.search);
        const paramValue = param.get('action');
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        switch (paramValue) {
            case 'errorBooking':
                Swal.fire({
                    icon: 'error',
                    text: 'An error occurred. Please try again.',
                    title: 'Oops'
                })
                break;
            case 'phoneNumber':
                Swal.fire({
                    icon: 'info',
                    text: 'Phone number is required!',
                    title: 'Oops',
                    confirmButtonText: 'Okay'
                }).then((result) => {
                    const phoneNumberModal = document.getElementById('phoneNumberModal');
                    const modal = new bootstrap.Modal(phoneNumberModal);
                    modal.show();
                });
                break;
            case 'phoneAdded':
                Toast.fire({
                    text: "Your phone number has been submitted successfully. You may now proceed with booking.",
                    icon: "success"
                });
                break;
            default:
                const cleanUrl = window.location.origin + window.location.pathname;
                history.replaceState({}, document.title, cleanUrl);
                break;
        }

        if (paramValue) {
            const cleanUrl = window.location.origin + window.location.pathname;
            history.replaceState({}, document.title, cleanUrl);

        }
    </script>


</body>

</html>