<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
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
    <link rel="stylesheet" href="../../Assets/CSS/Customer/hotelBooking.css">

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">

    <!-- Flatpickr calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- BoxIcon -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body id="hotel-page">

    <!-- Hotel Booking -->
    <form action="confirmBooking.php" method="POST">
        <div class="hotel" id="hotel">
            <div class="backToSelection" id="backToSelection">
                <img src="../../Assets/Images/Icon/arrowBtnBlue.png" alt="back button" onclick="backToSelection()">
            </div>
            <div class="titleContainer">
                <h4 class="hotelTitle text-center" id="hotelTitle">HOTEL BOOKING</h4>
            </div>
            <div class="container-fluid" id="hotelContainerFluid">
                <div class="hotelIconsContainer">
                    <div class="availabilityIcons">
                        <div class="availabilityIcon" id="availableRooms">
                            <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 2"
                                class="avail">
                            <p>Available</p>
                        </div>
                        <div class="availabilityIcon" id="unavailableRooms">
                            <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png" alt="Rate Picture 3"
                                class="avail">
                            <p>Not Available</p>
                        </div>
                    </div>

                    <div class="hotelIconContainer mt-3">
                        <?php
                        $hotelCategoryID = 1;
                        $getAllHotelQuery = $conn->prepare("SELECT RServiceName, RSduration, RSAvailabilityID FROM resortamenity 
                                                                WHERE RScategoryID = ? 
                                                                GROUP BY RServiceName
                                                                ORDER BY CAST(SUBSTRING(RServiceName, LOCATE(' ', RServiceName) + 1) AS UNSIGNED)");
                        $getAllHotelQuery->bind_param("i", $hotelCategoryID);
                        $getAllHotelQuery->execute();
                        $getAllHotelResult = $getAllHotelQuery->get_result();
                        if ($getAllHotelResult->num_rows > 0) {
                            $i = 1;
                            while ($row = $getAllHotelResult->fetch_assoc()) {
                                $isAvailable = ($row['RSAvailabilityID'] == 1);
                                $iconPath = $isAvailable
                                    ? "../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png"
                                    : "../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png";
                                $roomName = htmlspecialchars($row['RServiceName']);
                                $availabilityStatus = $isAvailable ? 'available' : 'unavailable';
                        ?>
                                <div class="hotelIconWithCaption" style="display: inline-block; text-align: center;"
                                    data-availability="<?= $availabilityStatus ?>">
                                    <a href="#<?= trim($row['RServiceName']) ?>"
                                        data-duration="<?= htmlspecialchars($row['RSduration']) ?>">
                                        <img src="<?= $iconPath ?>" alt="<?= $roomName ?>" class="hotelIcon"
                                            id="hotelIcon<?= $i ?>">
                                    </a>
                                    <p class="roomCaption"> <?= $roomName ?></p>
                                </div>
                            <?php
                                $i++;
                            }
                        } else {
                            ?>
                            <p class="text-center m-auto">No room services found.</p>
                        <?php
                        }
                        ?>
                    </div>

                    <div>
                        <a href="ratesAndHotelRooms.php" class="btn btn-primary btn-md w-100" id="amenitiesHR"> Take me
                            to Hotel Rooms and
                            Rates</a>
                    </div>
                </div>

                <div class="card hotel-card" id="hotelBookingCard" style="width: 40rem; flex-shrink: 0; ">
                    <div class="days-time-container">
                        <div class="arrivalTime">
                            <h5 class="arrivalTimeLabel">Time arrival</h5>
                            <div class="input-group">
                                <input type="time" name="arrivalTime" id="arrivalTime" class="form-control"
                                    placeholder="Select Arrival Time"
                                    value="<?php echo isset($_SESSION['hotelFormData']['arrivalTime']) ? htmlspecialchars(trim($_SESSION['hotelFormData']['arrivalTime'])) : ''; ?>">
                            </div>
                        </div>
                        <!-- <div class="days-count">
                            <h5 class="containerLabel">Number of days</h5>
                            <input type="number" class="form-control" name="daysCount" id="daysCount" required
                                placeholder="Days Count"
                                value="<?php echo isset($_SESSION['hotelFormData']['daysCount']) ? htmlspecialchars(trim($_SESSION['hotelFormData']['daysCount'])) : ''; ?>" min="1">
                        </div> -->
                    </div>
                    <div class="checkInOut">
                        <div class="checkIn-container">
                            <h5 class="containerLabel">Check-In Date</h5>
                            <div style="display: flex;align-items:center;width:100%">
                                <input type="text" class="form-control" name="checkInDate" id="checkInDate" required
                                    placeholder="Select Date and Time"
                                    value="<?php echo isset($_SESSION['hotelFormData']['checkInDate']) ? htmlspecialchars(trim($_SESSION['hotelFormData']['checkInDate'])) : ''; ?>">
                                <!--<i class="fa-solid fa-calendar" id="hotelCheckinIcon" style="margin-left: -2vw;font-size:1.2vw;"> </i>-->
                            </div>
                        </div>
                        <div class="checkOut-container">
                            <h5 class="containerLabel">Check-Out Date</h5>
                            <div style="display: flex;align-items:center;">
                                <input type="text" class="form-control" name="checkOutDate" id="checkOutDate" required
                                    placeholder="Date and Time"
                                    value="<?php echo isset($_SESSION['hotelFormData']['checkOutDate']) ? htmlspecialchars(trim($_SESSION['hotelFormData']['checkOutDate'])) : ''; ?>">
                                <!-- <input type="hidden" name="checkOutDate" id="checkOutDateHidden"> -->
                                <!-- <i class="fa-solid fa-calendar" id="hotelCheckoutIcon" style="margin-left: -2vw;font-size:1.2vw;"> </i> -->
                            </div>
                        </div>

                    </div>

                    <div class="hotelPax">
                        <h5 class="noOfPeopleHotelLabel">Number of People</h5>
                        <div class="hotelPeopleForm">
                            <div class="input-container ">
                                <input type="number" class="form-control" placeholder="Adults" id="adultCount"
                                    name="adultCount" required min="0"
                                    value="<?php echo isset($_SESSION['hotelFormData']['adultCount']) ? htmlspecialchars(trim($_SESSION['hotelFormData']['adultCount'])) : ''; ?>" />
                                <div class="info-container mt-1">
                                    <i class="fa-solid fa-circle-info" style="color: #007BFF;"></i>
                                    <p>Ages 14 and up</p>
                                </div>
                            </div>
                            <div class="input-container">
                                <input type="number" class="form-control" placeholder="Kids" id="childrenCount"
                                    name="childrenCount" min="0"
                                    value="<?php echo isset($_SESSION['hotelFormData']['childrenCount']) ? htmlspecialchars(trim($_SESSION['hotelFormData']['childrenCount'])) : ''; ?>" />
                                <div class="info-container mt-1">
                                    <i class="fa-solid fa-circle-info" style="color: #007BFF;"></i>
                                    <p>Ages 4 to 13</p>
                                </div>
                            </div>
                            <div class="input-container">
                                <input type="number" class="form-control" placeholder="Toddler/Infant"
                                    id="toddlerCount" name="toddlerCount" min="0"
                                    value="<?php echo isset($_SESSION['hotelFormData']['toddlerCount']) ? htmlspecialchars(trim($_SESSION['hotelFormData']['toddlerCount'])) : ''; ?>" />
                                <div class="info-container mt-1">
                                    <i class="fa-solid fa-circle-info" style="color: #007BFF;"></i>
                                    <p>Ages 3 and below</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="hotelRooms">
                        <h5 class="hotelRooms-title">Room Number</h5>
                        <button type="button" class="btn btn-info text-black w-100" name="hotelSelectionBtn"
                            id="hotelSelectionBtn" data-bs-toggle="modal" data-bs-target="#hotelRoomModal">
                            Choose your room</button>

                        <!-- Modal for hotel rooms -->
                        <div class="modal modal-lg" id="hotelRoomModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Available Hotel Rooms</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="hotelDisplaySelection">
                                        <p class="text-center">Choose date and time</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary"
                                            data-bs-dismiss="modal">Okay</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="paymentTimeDiv">
                        <div class="paymentMethod">
                            <h5 class="payment-title">Payment Method</h5>
                            <div class="input-group">
                                <select class="form-select" name="paymentMethod" id="paymentMethod" required>
                                    <option value="" disabled
                                        <?= !isset($_SESSION['hotelFormData']['paymentMethod']) ? 'selected' : '' ?>>
                                        Choose...</option>
                                    <option value="GCash"
                                        <?= (isset($_SESSION['hotelFormData']['paymentMethod']) && $_SESSION['hotelFormData']['paymentMethod'] === 'GCash') ? 'selected' : '' ?>>
                                        GCash</option>
                                    <option value="Cash"
                                        <?= (isset($_SESSION['hotelFormData']['paymentMethod']) && $_SESSION['hotelFormData']['paymentMethod'] === 'Cash') ? 'selected' : '' ?>>
                                        Cash</option>
                                </select>

                            </div>
                        </div>
                    </div>

                    <div class="additional-info-container">
                        <ul style="list-style: none;">
                            <li style="color: #0076d1ff;">
                                <i class="fa-solid fa-circle-info" style="color: #37a5fff1;"></i>
                                &nbsp;If the maximum pax exceeded, extra guest is charged
                                ₱250 per head
                            </li>
                            <li style="color: #0076d1ff;">
                                <i class="fa-solid fa-circle-info" style="color: #37a5fff1;"></i>
                                &nbsp;Children 3 years old and below are free
                            </li>
                            <li style="color: #0076d1ff;">
                                <i class="fa-solid fa-circle-info" style="color: #37a5fff1;"></i>
                                &nbsp;Any request for an additional hour of stay must be arranged directly with the resort.
                            </li>
                            <li style="color: #0076d1ff;">
                                <i class="fa-solid fa-circle-info" style="color: #37a5fff1;"></i>
                                <?php
                                $chargeType = 'Room';
                                $pricingType = 'Per Hour';
                                $getPerHourRate = $conn->prepare("SELECT `pricingID` , `price`, `notes` FROM `servicepricing` WHERE `chargeType` = ? AND `pricingType` = ?");
                                $getPerHourRate->bind_param('ss', $chargeType, $pricingType);
                                if ($getPerHourRate->execute()) {
                                    $result = $getPerHourRate->get_result();
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                    }
                                }
                                ?>
                                &nbsp; The rate for each additional hour will be <?= $row['notes'] ?? ($price . ' ' . $pricingType) ?>
                            </li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-success" name="hotelBooking" id="hotelBooking">Book
                        Now</button>
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


    <!-- Full Calendar for Date display -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>

    <!-- Flatpickr for date input -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Functions -->
    <script>
        function backToSelection() {
            location.href = "bookNow.php"
        };
    </script>

    <!-- Calendar -->
    <script>
        const calIcon = document.getElementById("calendarIcon");

        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 2);

        //hotel calendar
        flatpickr('#checkInDate', {
            enableTime: true,
            minDate: minDate,
            dateFormat: "Y-m-d h:i K",
            // minTime: "06:00",
            // maxTime: "22:00",
            disableMobile: true,
        });


        flatpickr('#checkOutDate', {
            enableTime: true,
            minDate: minDate,
            dateFormat: "Y-m-d h:i K",
            // minTime: "06:00",
            // maxTime: "22:00",
            disableMobile: true,
        });

        flatpickr('#arrivalTime', {
            enableTime: true,
            noCalendar: true,
            minTime: "06:00",
            maxTime: "22:00",
            dateFormat: "H:i",
            disableMobile: true,
        });
    </script>


    <!-- Hotel check-in check-out  -->
    <script>
        const checkInInput = document.getElementById('checkInDate');
        const checkOutInput = document.getElementById('checkOutDate');
        // const daysCountInput = document.getElementById('daysCount');

        checkInInput.addEventListener('change', () => {
            const selectedValue = '22 hours';
            const checkInDate = new Date(checkInInput.value);
            const addHours = parseInt(selectedValue);
            if (!isNaN(checkInDate.getTime()) && !isNaN(addHours)) {
                const checkOutDate = new Date(checkInDate.getTime() + addHours * 60 * 60 * 1000);

                const year = checkOutDate.getFullYear();
                const month = String(checkOutDate.getMonth() + 1).padStart(2, '0');
                const day = String(checkOutDate.getDate()).padStart(2, '0');
                const hours = String(checkOutDate.getHours()).padStart(2, '0');
                const minutes = String(checkOutDate.getMinutes()).padStart(2, '0');

                const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}`;
                checkOutInput.value = formattedDate;
            }
        });
    </script>


    <!-- Get the available hotel/room depends on the customer selected date -->
    <script>
        function formatDate(date) {
            const y = date.getFullYear().toString();
            const m = (date.getMonth() + 1).toString().padStart(2, '0');
            const d = date.getDate().toString().padStart(2, '0');
            const h = date.getHours().toString().padStart(2, '0');
            const i = date.getMinutes().toString().padStart(2, '0');
            return `${y}${m}${d} ${h}${i}`;
        }

        hotelSelectionSession = <?= isset($_SESSION['hotelFormData']) ? json_encode($_SESSION['hotelFormData']['hotelSelections']) : '[]' ?>;

        document.addEventListener("DOMContentLoaded", function() {
            const checkInDate = document.getElementById('checkInDate');
            const checkOutDate = document.getElementById('checkOutDate');

            if (checkInDate && !checkInDate.value) {
                Swal.fire({
                    icon: 'info',
                    title: 'Select your choice of date',
                    text: 'Please pick a booking date to continue',
                    confirmButtonText: 'OK'
                }).then(() => {
                    checkInDate.style.border = '2px solid red';
                    checkInDate.focus();
                });
            }
        });


        function fetchAvailableRooms() {
            const checkInDateValue = checkInDate.value;
            const checkOutDateValue = checkOutDate.value;
            const hoursSelectedValue = "22 hours";

            const checkInDateObj = new Date(checkInDateValue);
            const checkOutDateObj = new Date(checkOutDateValue);

            const formattedCheckIn = formatDate(checkInDateObj);
            const formattedCheckOut = formatDate(checkOutDateObj);

            if (!formattedCheckIn || !hoursSelectedValue) return;
            fetch(
                    `../../Function/Booking/getAvailableAmenities.php?hotelCheckInDate=${encodeURIComponent(formattedCheckIn)}&hotelCheckOutDate=${encodeURIComponent(formattedCheckOut)}&duration=${encodeURIComponent(hoursSelectedValue)}`
                )
                .then(response => {
                    if (!response.ok) throw new Error('Network Problem');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert("Error: " + data.error);
                        return;
                    }

                    const hotelRoomContainer = document.getElementById("hotelDisplaySelection");
                    hotelRoomContainer.innerHTML = '';

                    data.hotels.forEach(hotel => {
                        const wrapper = document.createElement('div');
                        wrapper.classList.add('checkbox-item');

                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.name = 'hotelSelections[]';
                        checkbox.value = hotel.RServiceName;
                        checkbox.id = hotel.RServiceName;
                        checkbox.dataset.capacity = hotel.RScapacity;


                        const content = document.createElement('div');
                        content.classList.add('content');
                        const label = document.createElement('label');
                        label.setAttribute('for', checkbox.id);
                        label.textContent =
                            `${hotel.RServiceName} good for ${hotel.RScapacity} pax (₱${Number(hotel.RSprice).toLocaleString()}.00)`;

                        const img = document.createElement('img');
                        img.classList.add('hotel-image');
                        img.src = `../../Assets/Images/Services/Hotel/${hotel.RSimageData}`;
                        img.alt = `${hotel.RServiceName} image`;
                        img.style.width = "200px";

                        const hotelSelection = hotelSelectionSession.map(String);

                        if (hotelSelectionSession.includes(String(hotel.RServiceName))) {
                            checkbox.checked = true;
                        }

                        content.appendChild(checkbox);
                        content.appendChild(label);
                        wrapper.appendChild(img);
                        wrapper.appendChild(content);
                        hotelRoomContainer.appendChild(wrapper);
                    })

                }).catch(error => {
                    console.error(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch hotels. Please try again.',
                    });
                });
        }

        document.addEventListener("DOMContentLoaded", () => {
            if (checkInDate && checkInDate.value) {
                fetchAvailableRooms();
                checkInDate.style.border = '1px solid rgb(223, 226, 230)';
            }
        });

        if (checkInDate) {
            checkInDate.addEventListener("change", () => {
                fetchAvailableRooms();
                checkInDate.style.border = checkInDate.value ? '' : '2px solid red';
            });
        }
        if (checkOutDate) {
            checkOutDate.addEventListener("change", fetchAvailableRooms);
        }

        const hotelBookingBtn = document.getElementById('hotelBooking');

        hotelBookingBtn.addEventListener("click", function() {

            let totalCapacity = 0;

            const hotelSelected = document.querySelectorAll('input[name="hotelSelections[]"]:checked');
            hotelSelected.forEach(item => {
                totalCapacity += parseInt(item.dataset.capacity) || 0;
            });

            if (totalCapacity === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops',
                    text: 'Select a cottage(s) or room(s)',
                });
                hotelBookingBtn.type = 'button';
            } else {
                hotelBookingBtn.type = 'submit';
            }
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

        document.addEventListener('DOMContentLoaded', () => {
            const param = new URLSearchParams(window.location.search);
            const paramValue = param.get('action');

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
                default:
                    const cleanUrl = window.location.origin + window.location.pathname;
                    history.replaceState({}, document.title, cleanUrl);
                    break;
            }

            if (paramValue) {
                const cleanUrl = window.location.origin + window.location.pathname;
                history.replaceState({}, document.title, cleanUrl);

            }
        });
    </script>


</body>

</html>