<?php
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

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

function getEventCategory($conn)
{
    $getEventCategoryQuery = $conn->prepare("SELECT * FROM eventcategory");
    $getEventCategoryQuery->execute();
    $getEventCategoryResult = $getEventCategoryQuery->get_result();
    $categories = [];
    if ($getEventCategoryResult->num_rows > 0) {
        while ($row = $getEventCategoryResult->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    return $categories;
    $getEventCategoryResult->free();
    $getEventCategoryQuery->close();
}


function getPartnershipType($conn)
{
    $getPartnershipTypeQuery = $conn->prepare("SELECT * FROM partnershiptype");
    $getPartnershipTypeQuery->execute();
    $getPartnershipTypeResult = $getPartnershipTypeQuery->get_result();
    $categories = [];
    if ($getPartnershipTypeResult->num_rows > 0) {
        while ($row =  $getPartnershipTypeResult->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    return $categories;
    $getPartnershipTypeResult->free();
    $getPartnershipTypeQuery->close();
}


$formData = $_SESSION['eventFormData'] ?? [];
// echo '<pre>';
// print_r($formData);
// echo '</pre>';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Book Now</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Customer/eventBooking.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <!-- flatpickr calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

    <!-- Bootstrap CSS Link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Boxicons -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body id="event-page">
    <!-- Event Booking -->
    <form action="eventBookingConfirmation.php" method="POST">
        <div class="event" id="event">
            <div class="backToSelection" id="backToSelection">
                <img src="../../Assets/Images/Icon/arrowBtnBlue.png" alt="back button" onclick="backToSelection()">
            </div>
            <div class="titleContainer">
                <h4 class="eventTitle text-center" id="eventTitle">EVENT BOOKING</h4>
            </div>

            <div class="container-fluid event-container" id="eventContainer">
                <div class="card event-card" id="eventBookingCard" style="width: 40rem; flex-shrink: 0; ">

                    <!-- For Event Types -->
                    <div class="eventTypeContainer">
                        <label for="eventType" class="eventInfoLabel">Type of Event</label>
                        <select class="form-select" name="eventType" id="eventType" required>
                            <option value="" disabled <?= empty($formData['eventType'] ?? '') ? 'selected' : '' ?>>
                                Choose here..</option>
                            <?php
                            $eventCategory = getEventCategory($conn);
                            foreach ($eventCategory as $category) {
                                $eventType =  isset($formData['eventType']) ?  $formData['eventType'] : '';
                                $isSelected = (htmlspecialchars($category['categoryName']) === $eventType) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($category['categoryName']) ?>" <?= $isSelected ?>>
                                    <?= htmlspecialchars($category['categoryName']) ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div class="paymentMethod">
                        <label for="paymentMethod" class="eventInfoLabel">Payment Method</label>
                        <select class="form-select" name="paymentMethod" id="paymentMethod" required>
                            <option value="" disabled <?= empty($formData['paymentMethod'] ?? '') ? 'selected' : '' ?>>
                                Choose...</option>
                            <option value="GCash"
                                <?= (isset($formData['paymentMethod']) && $formData['paymentMethod'] === 'GCash') ? 'selected' : '' ?>>
                                Gcash</option>
                            <option value="Cash"
                                <?= (isset($formData['paymentMethod']) && $formData['paymentMethod'] === 'Cash') ? 'selected' : '' ?>>
                                Cash (Onsite Payment)</option>
                        </select>
                    </div>


                    <div class="eventSched">
                        <label for="eventDate" class="eventInfoLabel">Event Schedule</label>
                        <div class="eventBox">
                            <input type="date" class="form-control" name="eventDate" id="eventDate"
                                value="<?= !empty($formData['eventDate']) ? $formData['eventDate'] : '' ?>">
                            <i class=" fa-solid fa-calendar-days" style="color: #333333; "></i>
                        </div>
                    </div>

                    <div class="eventStartTime">
                        <label for="eventStartTime" class="eventInfoLabel">Start Time</label>
                        <input type="time" class="form-control" name="eventStartTime" id="eventStartTime" required
                            value="<?= !empty($formData['eventStartTime']) ? $formData['eventStartTime'] : '' ?>">
                    </div>

                    <!-- <div class="eventEndTime">
                        <label for="eventEndTime" class="eventInfoLabel">End Time</label>
                        <input type="time" class="form-control" name="eventEndTime" id="eventEndTime" required>
                    </div> -->

                    <div class="eventVenue">
                        <label for="eventVenue" class="eventInfoLabel" id="venueInfoLabel">Venue</label>
                        <select class="form-select" name="eventVenue" id="eventVenue" required>
                        </select>
                    </div>

                    <div class="guestInfo">
                        <label for="guestNo" class="eventInfoLabel">Number of Guests</label>
                        <input type="number" min="1" step="1" class="form-control" name="guestNo" id="guestNo"
                            value="<?= isset($formData['guestNo']) ? htmlspecialchars($formData['guestNo']) : '' ?>"
                            placeholder="Estimated Number of Guests" required>

                    </div>

                    <div class="eventInfo">
                        <label for="additionalRequest" class="eventInfoLabel">Additional Notes</label>
                        <textarea class="form-control w-100" id="purpose-additionalNotes" name="additionalRequest"
                            rows="5" placeholder="Optional"></textarea>
                    </div>

                    <div class="noteContainer">
                        <p class="note">Note: For any concerns or details regarding food and other services, contact us
                            at (0998) 962 4697.</p>
                    </div>
                </div>

                <div class="secondColumn">
                    <div id="calendar"></div>
                    <div class="packageDisplay" style="display: none;">
                        <div id="packageCardsContainer" class="container d-flex flex-wrap gap-3">
                        </div>
                    </div>
                </div>

                <!-- <div class="card foodSelection-card" id="foodSelectionCard">
                    <h1>testing</h1>
                </div> -->

                <div class="card foodSelection-card" id="foodSelectionCard" style="width:40rem;">

                    <img src="../../Assets/Images/BookNowPhotos/foodCoverImg.jpg" class="card-img-top"
                        id="foodSelectionCover" alt="Food Selection Cover">

                    <div class="card-body ">
                        <h5 class="card-title fw-bold text-center">Dish Selection</h5>
                        <p class="card-text mt-3 text-center">Choose from a variety of catering options to suit your
                            event’s needs.
                            Select dishes that will delight your guests and complement your celebration.</p>
                        <button type="button" class="btn btn-primary mt-3 w-100" data-bs-toggle="modal"
                            data-bs-target="#dishModal">Open Menu</button>
                    </div>
                </div>

                <div class="card additionalServices-card" id="additionalServicesCard" style="width:40rem;">

                    <img src="../../Assets/Images/BookNowPhotos/additionalServiceImg.jpg" class="card-img-top"
                        id="additionalServicesCover" alt="Additional Services Cover">

                    <div class="card-body">
                        <h5 class="card-title fw-bold text-center">Additional Services</h5>
                        <p class="card-text mt-3 text-center">Explore our range of additional services to elevate your
                            event. From
                            photography to hosting, choose what best suits your needs and adds a special touch to
                            your celebration.</p>

                        <button type="button" class="btn btn-primary mt-3 w-100" data-bs-toggle="modal"
                            data-bs-target="#additionalServicesModal">View Services</button>
                    </div>
                </div>
            </div>
            <!--end ng container div-->

            <div class="eventButton-container">
                <button type="submit" class="btn btn-primary btn-md w-25" name="eventBN" id="bookNowBtn" disabled>Book
                    Now</button>
            </div>

        </div>
        <!--end ng event div-->

        <!-- Dish Modal -->
        <div class="modal fade modal-lg" id="dishModal" tabindex="-1" aria-labelledby="exampleModalLabel">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-4 fw-bold" id="dishModalLabel">Select Dishes</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="note-container">
                        <ul>
                            <li>You can select a maximum of 4 dishes.</li>
                            <li>You may only select 1 drink.</li>
                            <li>You can select up to 2 kinds of dessert.</li>
                        </ul>
                    </div>
                    <div class="modal-body dishMenu" id="dishMenu">
                        <div class="chicken">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Chicken</h6>
                            </div>
                            <div class="dishListContainer" id="chickenContainerA"></div>
                        </div>
                        <div class="pasta">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Pasta</h6>
                            </div>
                            <div class="dishListContainer" id="pastaContainerA"></div>
                        </div>
                        <div class="pork">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Pork</h6>
                            </div>
                            <div class="dishListContainer" id="porkContainerA"></div>
                        </div>
                        <div class="veg">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Vegetables</h6>
                            </div>
                            <div class="dishListContainer" id="vegieContainerA"></div>
                        </div>
                        <div class="beef">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Beef</h6>
                            </div>
                            <div class="dishListContainer" id="beefContainerA"></div>
                        </div>
                        <div class="seafood">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Seafood</h6>
                            </div>
                            <div class="dishListContainer" id="seafoodContainerA"> </div>
                        </div>
                        <div class="drinks">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Drinks</h6>
                            </div>
                            <div class="dishListContainer" id="drinkContainer"></div>
                        </div>
                        <div class="dessert">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Desserts</h6>
                            </div>
                            <div class="dishListContainer" id="dessertContainer"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="confirmDishBtn"
                            data-bs-dismiss="modal">Confirm</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- BP Modal -->
        <div class="modal fade modal-lg" id="additionalServicesModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-4 fw-bold" id="additionalServicesModalLabel">Business Partners</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <p class="note" id="additionalServiceNote" style="color: #0d6dfc">Before including additional
                        services from our partners,
                        <strong> please
                            make sure to contact them</strong> and discuss the details of the event.
                    </p>
                    <div class="modal-body additionalService" id="additionalService">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>

    <!-- Full Calendar for Date display -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="../../Assets/JS/fullCalendar.js" type="text/javascript"> </script>

    <!-- Flatpickr for date input -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Format the date to Y-M-D h:i:s -->
    <script src="../../Assets/JS/formatDateTime.js" type="text/javascript"> </script>

    <script src="../../Assets/JS/EventJS/getFoodByCategory.js"></script>

    <!-- Guest Count & Food Count to enable the button for booknow-->
    <script src="../../Assets/JS/EventJS/countingGuestFood.js"> </script>

    <!--Back Functions -->
    <script>
        function backToSelection() {
            location.href = "bookNow.php"
        };
    </script>

    <!-- Calendar -->
    <script>
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 8);

        //event calendar
        flatpickr('#eventDate', {
            minDate: minDate,
            dateFormat: "Y-m-d",
        });
    </script>

    <!-- Event Hall-->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const date = document.getElementById('eventDate');
            const startTime = document.getElementById('eventStartTime');
            const venueSelect = document.getElementById('eventVenue');


            const sessionSelectedVenue =
                <?= isset($formData['eventVenue']) ? json_encode($formData['eventVenue']) : '""' ?>;

            function getAvailableVenue() {
                const selectedDate = date.value;
                const selectedStartTime = startTime.value;

                if (!selectedDate || !selectedStartTime) return;

                const startDateTimeObj = new Date(`${selectedDate}T${selectedStartTime}`);
                const endDateTimeObj = new Date(startDateTimeObj.getTime() + 5 * 60 * 60 * 1000);

                const formattedStartDateTime = formatDateTime(startDateTimeObj);
                const formattedEndDateTime = formatDateTime(endDateTimeObj);

                // console.log(formattedStartDateTime);
                // console.log(formattedEndDateTime);

                fetch(
                        `../../Function/Booking/getEventVenue.php?startDate=${encodeURIComponent(formattedStartDateTime)}&endDate=${encodeURIComponent(formattedEndDateTime)}`
                    )
                    .then(response => {
                        if (!response.ok) throw new Error('Network Error');
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            alert("Error: " + data.error);
                            return;
                        }

                        // const venueSelect = document.getElementById("eventVenue");
                        venueSelect.innerHTML = '';

                        const venueOption = document.createElement('option')
                        venueOption.value = "";
                        venueOption.disabled = true;
                        venueOption.selected = true;
                        venueOption.textContent = "Choose...";
                        venueSelect.appendChild(venueOption);

                        data.Halls.forEach(hall => {
                            const venueOptions = document.createElement('option');
                            venueOptions.value = hall.RServiceName;
                            venueOptions.dataset.capacity = hall.RSmaxCapacity;
                            venueOptions.textContent =
                                `${hall.RServiceName} - ${hall.RSmaxCapacity} pax`;

                            if (hall.RServiceName === sessionSelectedVenue) {
                                venueOptions.selected = true;
                            }

                            venueSelect.appendChild(venueOptions);
                        })

                        venueSelect.dispatchEvent(new Event('change'));

                    })
                    .catch(error => {
                        console.error('There was a problem with the fetch operation', error);
                        alert("Failed to load available venues. Please try again later.");
                    });
            }

            if (date && startTime) {
                date.addEventListener("change", getAvailableVenue);
                startTime.addEventListener("change", getAvailableVenue);
                getAvailableVenue();
            }
        });
    </script>

    <!-- For event food -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sessionSelectedChicken =
                <?= isset($formData['chickenSelections']) ? json_encode($formData['chickenSelections']) : '[]' ?>;
            const sessionSelectedPork =
                <?= isset($formData['porkSelections']) ? json_encode($formData['porkSelections']) : '[]' ?>;
            const sessionSelectedPasta =
                <?= isset($formData['pastaSelections']) ? json_encode($formData['pastaSelections']) : '[]' ?>;
            const sessionSelectedBeef =
                <?= isset($formData['beefSelections']) ? json_encode($formData['beefSelections']) : '[]' ?>;
            const sessionSelectedVegie =
                <?= isset($formData['vegieSelections']) ? json_encode($formData['vegieSelections']) : '[]' ?>;
            const sessionSelectedSeafood =
                <?= isset($formData['seafoodSelections']) ? json_encode($formData['seafoodSelections']) : '[]' ?>;
            const sessionSelectedDrink =
                <?= isset($formData['drinkSelections']) ? json_encode($formData['drinkSelections']) : '[]' ?>;
            const sessionSelectedDessert =
                <?= isset($formData['dessertSelections']) ? json_encode($formData['dessertSelections']) : '[]' ?>;

            const sessionFoodIDs = <?= isset($formData['foodIDs']) ? json_encode($formData['foodIDs']) : '[]' ?>;


            fetch('../../Function/Booking/getAvailableFood.php')
                .then(response => {
                    if (!response.ok) throw new Error('Network Error');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert("Error: " + data.error);
                        return;
                    }

                    getMenuByCategory('chickenContainerA', data.chickenCategory, 'chicken',
                        'No Available Chicken Menu', sessionSelectedChicken, sessionFoodIDs);
                    getMenuByCategory('porkContainerA', data.porkCategory, 'pork', 'No Available Pork Menu',
                        sessionSelectedPork, sessionFoodIDs);
                    getMenuByCategory('pastaContainerA', data.pastaCategory, 'pasta',
                        'No Available Pasta Menu', sessionSelectedPasta, sessionFoodIDs);
                    getMenuByCategory('beefContainerA', data.beefCategory, 'beef', 'No Available Beef Menu',
                        sessionSelectedBeef, sessionFoodIDs);
                    getMenuByCategory('vegieContainerA', data.vegieCategory, 'vegie',
                        'No Available Vegetables Menu', sessionSelectedVegie, sessionFoodIDs);
                    getMenuByCategory('seafoodContainerA', data.seafoodCategory, 'seafood',
                        'No Available Seafood Menu', sessionSelectedSeafood, sessionFoodIDs);
                    getMenuByCategory('drinkContainer', data.drinkCategory, 'drink', 'No Available Drink Menu',
                        sessionSelectedDrink, sessionFoodIDs);
                    getMenuByCategory('dessertContainer', data.dessertCategory, 'dessert',
                        'No Available Dessert Menu', sessionSelectedDessert, sessionFoodIDs);

                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation', error);
                })
        });
    </script>

    <!-- Fetch Partner service -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const date = document.getElementById('eventDate');
            const startTime = document.getElementById('eventStartTime');
            const mainContainer = document.getElementById('additionalService');
            const sessionSelectedServices =
                <?= isset($formData['additionalServiceSelected']) ? json_encode($formData['additionalServiceSelected']) : '[]' ?>;

            function showDefaultText() {
                const div = document.createElement('div');
                div.classList.add('no-data-container');

                const cardText = document.createElement('h5');
                cardText.classList.add('card-text');
                cardText.innerHTML = 'Choose the date and time';

                div.appendChild(cardText);
                mainContainer.appendChild(div);
            }

            date.addEventListener('change', getAvailablePartnerService);
            startTime.addEventListener('change', getAvailablePartnerService);

            if (date.value === '' && startTime.value === '') {
                showDefaultText();
            } else {

                getAvailablePartnerService();
            }

            // console.log('Date value:', date.value);
            // console.log('StartTime value:', startTime.value);


            function getAvailablePartnerService() {

                const selectedDate = date.value;
                const selectedStartTime = startTime.value;

                if (!selectedDate || !selectedStartTime) return;

                const startDateTimeObj = new Date(`${selectedDate}T${selectedStartTime}`);
                const endDateTimeObj = new Date(startDateTimeObj.getTime() + 5 * 60 * 60 * 1000); // +5 hours

                const formattedStartDateTime = formatDateTime(startDateTimeObj);
                const formattedEndDateTime = formatDateTime(endDateTimeObj);

                fetch(
                        `../../Function/Booking/getPartnerService.php?startDate=${encodeURIComponent(formattedStartDateTime)}&endDate=${encodeURIComponent(formattedEndDateTime)}`
                    )
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network Error');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            Swal.fire({
                                title: 'Error',
                                text: 'Error: ' + data.error,
                                icon: 'error'
                            });
                            return;
                        }
                        mainContainer.innerHTML = '';
                        if (data.Categories && data.Categories.length > 0) {
                            data.Categories.forEach(category => {
                                const wrapper = document.createElement('div');
                                wrapper.classList.add('photography');

                                // Create and append category label
                                const bpTypeContainer = document.createElement('div');
                                bpTypeContainer.classList.add('bpTypeContainer');

                                const categoryHeading = document.createElement('h6');
                                categoryHeading.classList.add('bpCategory', 'fw-bold');
                                categoryHeading.innerText = category.eventCategory ||
                                    'Category Name'; // fallback if undefined

                                bpTypeContainer.appendChild(categoryHeading);
                                wrapper.appendChild(bpTypeContainer);


                                const partnerListContainer = document.createElement('div');
                                partnerListContainer.classList.add('partnerListContainer');

                                const checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.classList.add('form-check-input');
                                checkbox.name =
                                    `additionalServiceSelected[${category.partnershipServiceID}][selected]`;
                                checkbox.value = category.partnershipServiceID;


                                const inputPBName = document.createElement('input');
                                inputPBName.type = 'hidden';
                                inputPBName.name =
                                    `additionalServiceSelected[${category.partnershipServiceID}][PBName]`;
                                inputPBName.value = category.PBName;

                                const inputPBPrice = document.createElement('input');
                                inputPBPrice.type = 'hidden';
                                inputPBPrice.name =
                                    `additionalServiceSelected[${category.partnershipServiceID}][PBPrice]`;
                                inputPBPrice.value = category.PBPrice;

                                const inputServiceID = document.createElement('input');
                                inputServiceID.type = 'hidden';
                                inputServiceID.name =
                                    `additionalServiceSelected[${category.partnershipServiceID}][partnershipServiceID]`;
                                inputServiceID.value = category.partnershipServiceID;


                                const label = document.createElement('label');
                                label.classList.add('form-check-label');
                                label.innerHTML =
                                    `${category.companyName} - ${category.PBName} &mdash; ₱ ${Number(category.PBPrice).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} &mdash; ${category.phoneNumber}`;

                                const selectedServiceIDs = Object.keys(sessionSelectedServices).map(
                                    String);

                                if (selectedServiceIDs.includes(String(category
                                        .partnershipServiceID))) {
                                    checkbox.checked = true;
                                }


                                partnerListContainer.appendChild(checkbox);
                                partnerListContainer.appendChild(inputPBName);
                                partnerListContainer.appendChild(inputPBPrice);
                                partnerListContainer.appendChild(inputServiceID);
                                partnerListContainer.appendChild(label);

                                wrapper.appendChild(partnerListContainer);
                                mainContainer.appendChild(wrapper);
                            });
                        } else {
                            const div = document.createElement('div');
                            div.classList.add('no-data-container');

                            const cardText = document.createElement('h3');
                            cardText.classList.add('card-text');
                            cardText.innerHTML = 'No Additional Services Available';

                            div.appendChild(cardText);
                            mainContainer.appendChild(div);
                        }


                    }).catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: error.Message,
                            icon: 'error'
                        })
                        console.error('There was a problem with the fetch operation', error);
                    })
            };
        });
    </script>

    <!-- Auto select event -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const selectedEvent = params.get('event');

            if (selectedEvent) {
                const select = document.getElementById('eventType');
                if (select) {
                    select.value = selectedEvent;
                }
            };
            // console.log(selectedEvent);

            if (params) {
                const url = new URL(window.location);
                url.search = '';
                history.replaceState({}, document.title, url.toString());
            }
        });
    </script>

    <!-- Sweetalert Message  -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('action');

        if (paramValue === 'errorBooking') {
            Swal.fire({
                title: 'Error Booking',
                text: 'An error occured while booking. Try again later',
                icon: 'error',
            })
        }
    </script>

</body>

</html>