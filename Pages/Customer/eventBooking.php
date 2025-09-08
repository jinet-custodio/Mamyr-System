<?php
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
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
    <link rel="stylesheet" href="../../Assets/CSS/Customer/bookNow.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <!-- flatpickr calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body id="event-page">
    <!-- Event Booking -->
    <form action="eventBookingConfirmation.php" method="POST">
        <div class="event" id="event">
            <div class="backToSelection" id="backToSelection">
                <img src="../../Assets/Images/Icon/arrow.png" alt="back button" onclick="backToSelection()">
            </div>
            <div class="titleContainer">
                <h4 class="eventTitle" id="eventTitle">EVENT BOOKING</h4>
            </div>

            <div class="container-fluid event-container" id="eventContainer">
                <div class="card event-card" id="eventBookingCard" style="width: 40rem; flex-shrink: 0; ">
                    <div class="eventTypeContainer">
                        <label for="eventType" class="eventInfoLabel">Type of Event</label>
                        <select class="form-select" name="eventType" id="eventType" required>
                            <option value="" selected disabled>Choose here..</option>
                            <?php
                            $getEventCategoryQuery = $conn->prepare("SELECT * FROM eventcategory");
                            $getEventCategoryQuery->execute();
                            $getEventCategoryResult = $getEventCategoryQuery->get_result();

                            if ($getEventCategoryResult->num_rows > 0) {
                                while ($row = $getEventCategoryResult->fetch_assoc()) {
                                    // $categories[] = $row;

                            ?>
                                    <option value="<?= htmlspecialchars($row['categoryName']) ?>"><?= htmlspecialchars($row['categoryName']) ?></option>
                            <?php
                                }
                            }
                            $getEventCategoryResult->free();
                            $getEventCategoryQuery->close();
                            ?>

                        </select>
                    </div>

                    <div class="guestInfo">
                        <label for="guestNo" class="eventInfoLabel">Number of Guests</label>
                        <input type="number" class="form-control" name="guestNo" id="guestNo"
                            placeholder="Estimated Number of Guests" required>
                    </div>

                    <div class="eventSched">
                        <label for="eventDateTime" class="eventInfoLabel">Event Schedule</label>
                        <div class="eventBox">
                            <input type="datetime-local" class="form-control" name="eventDateTime" id="eventDateTime">
                            <i class=" fa-solid fa-calendar-days" style="color: #333333; "></i>
                        </div>
                    </div>

                    <div class="eventVenue">
                        <label for="eventVenue" class="eventInfoLabel" id="venueInfoLabel">Venue</label>
                        <select class="form-select" name="eventVenue" id="eventVenue" required>
                        </select>
                    </div>

                    <div class="eventStartTime">
                        <label for="eventStartTime" class="eventInfoLabel">Start Time</label>
                        <input type="time" class="form-control" name="eventStartTime" id="eventStartTime" required>
                    </div>

                    <div class="eventEndTime">
                        <label for="eventEndTime" class="eventInfoLabel">End Time</label>
                        <input type="time" class="form-control" name="eventEndTime" id="eventEndTime" required>
                    </div>

                    <div class="paymentMethod">
                        <label for="paymentMethod" class="eventInfoLabel">Payment Method</label>
                        <select class="form-select" name="paymentMethod" id="paymentMethod" required>
                            <option value="" disabled selected>Choose...</option>
                            <option value="GCash">Gcash</option>
                            <option value="Cash">Cash (Onsite Payment)</option>
                        </select>

                        <div class="noteContainer">
                            <p class="note">Note: For any concerns or details regarding food and other services, contact us at (0998) 962 4697.</p>
                        </div>
                    </div>

                    <div class="eventInfo">
                        <label for="additionalRequest" class="eventInfoLabel">Additional Notes</label>
                        <textarea class="form-control w-100" id="purpose-additionalNotes" name="additionalRequest"
                            rows="5" placeholder="Optional"></textarea>

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
                    <div class="card-body">
                        <img src="../../Assets/Images/BookNowPhotos/foodCoverImg.jpg" class="card-img-top"
                            id="foodSelectionCover" alt="Food Selection Cover">
                    </div>
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
                    <div class="card-body">
                        <img src="../../Assets/Images/BookNowPhotos/additionalServiceImg.jpg" class="card-img-top"
                            id="additionalServicesCover" alt="Additional Services Cover">
                    </div>
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

            <div class=" eventButton-container">
                <button type="submit" class="btn btn-primary btn-md w-25" name="eventBN" id="bookNowBtn" disabled>Book Now</button>
            </div>

        </div>
        <!--end ng event div-->

        <!-- Dish Modal -->
        <div class="modal fade modal-lg" id="dishModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
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
                        <button type="button" class="btn btn-primary" id="confirmDishBtn" data-bs-dismiss="modal">Confirm</button>
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
                    <div class="modal-body additionalService" id="additionalService">
                        <div class="photography">
                            <div class="bpTypeContainer">
                                <h6 class="bpCategory fw-bold">Photography/Videography</h6>
                            </div>
                            <div class="partnerListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" name="additionalServiceSelected[]" id="sw">
                                    <label class="form-check-label" for="sw">
                                        Shutter Wonders
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="2" name="additionalServiceSelected[]" id="cc">
                                    <label class="form-check-label" for="cc">
                                        Captured Creativity
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="foodCart">
                            <div class="bpTypeContainer">
                                <h6 class="bpCategory fw-bold">Food Cart</h6>
                            </div>
                            <div class="partnerListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="3" name="additionalServiceSelected[]" id="im">
                                    <label class="form-check-label" for="im">
                                        Issang Macchiato
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="4" name="additionalServiceSelected[]" id="mr">
                                    <label class="form-check-label" for="mr">
                                        Mango Royal
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="photoBooth">
                            <div class="bpTypeContainer">
                                <h6 class="bpCategory fw-bold">Photo Booth</h6>
                            </div>
                            <div class="partnerListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="5" name="additionalServiceSelected[]" id="ss">
                                    <label class="form-check-label" for="ss">
                                        Studios Studio
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="6" name="additionalServiceSelected[]" id="tri">
                                    <label class="form-check-label" for="tri">
                                        The Right Image
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="classicSnacks">
                            <div class="bpTypeContainer">
                                <h6 class="bpCategory fw-bold">Classic Snacks</h6>
                            </div>
                            <div class="partnerListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="7" name="additionalServiceSelected[]" id="di">
                                    <label class="form-check-label" for="di">
                                        Dirty Ice Cream (Sorbetes)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="8" name="additionalServiceSelected[]" id="sf">
                                    <label class="form-check-label" for="sf">
                                        Street Food
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="host">
                            <div class="bpTypeContainer">
                                <h6 class="bpCategory fw-bold">Host</h6>
                            </div>
                            <div class="partnerListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="9" name="additionalServiceSelected[]" id="hh">
                                    <label class="form-check-label" for="hh">
                                        The Hosting Hub
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="10" name="additionalServiceSelected[]" id="sh">
                                    <label class="form-check-label" for="sh">
                                        Stellar Hosts
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="lightsSounds">
                            <div class="bpTypeContainer">
                                <h6 class="bpCategory fw-bold">Lights and Sounds</h6>
                            </div>
                            <div class="partnerListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="11" name="additionalServiceSelected[]" id="lp">
                                    <label class="form-check-label" for="lp">
                                        Lightwave Productions
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="12" name="additionalServiceSelected[]" id="st">
                                    <label class="form-check-label" for="st">
                                        SoundBeam Technologies
                                    </label>
                                </div>
                            </div>
                        </div>
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
    <script src="../../Assets/JS/fullCalendar.js"></script>

    <!-- Flatpickr for date input -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


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
        flatpickr('#eventDateTime', {
            minDate: minDate,
            dateFormat: "Y-m-d",
        });
    </script>


    <!-- Guest Count & Food Count to enable the button for booknow-->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const guestNoInput = document.getElementById('guestNo');
            const eventVenueInput = document.getElementById('eventVenue');
            const button = document.getElementById('confirmDishBtn');
            const bookNowBtn = document.getElementById('bookNowBtn');

            let guestNoValue = 0;
            let eventVenueCapacity = '';

            let isGuestCountValid = false;
            let isFoodSelectionValid = false;

            guestNoInput.addEventListener('change', function() {
                guestNoValue = parseInt(guestNoInput.value);
                console.log(guestNoValue);
                checkCapacity();
            });

            eventVenueInput.addEventListener('change', function() {
                const selectedVenue = eventVenueInput.options[eventVenueInput.selectedIndex]
                eventVenueValue = selectedVenue.value;
                eventVenueCapacity = selectedVenue.dataset.capacity;
                checkCapacity();
            });

            function checkCapacity() {

                if (guestNoValue > eventVenueCapacity) {
                    Swal.fire({
                        title: 'Sorry',
                        text: `We're sorry. The resort can't accommodate ${guestNoValue} guests in the ${eventVenueValue}.`,
                        icon: 'info'
                    });
                    isGuestCountValid = false;
                } else {
                    isGuestCountValid = true;
                }
                updateBookNowButton();
            }

            function countSelected(categoryName) {
                const selected = document.querySelectorAll(`input[name="${categoryName}Selections[]"]:checked`);
                return selected.length;
            }


            button.addEventListener('click', function() {
                const mainDish = ['chicken', 'pork', 'pasta', 'beef', 'vegie', 'seafood'];
                const drinkCount = countSelected('drink');
                const dessertCount = countSelected('dessert');
                let mainDishCount = 0;
                mainDish.forEach(category => {
                    mainDishCount += countSelected(category);
                })

                let isValid = true;
                if (mainDishCount > 5) {
                    Swal.fire({
                        title: 'Sorry',
                        text: `You can select a maximum of 4 dishes.`,
                        icon: 'info'
                    });
                    isValid = false;
                }

                // if (mainDishCount == 0) {
                //     Swal.fire({
                //         title: 'Sorry',
                //         text: `You're required to select a dish.`,
                //         icon: 'info'
                //     });
                //     isValid = false;
                // }


                if (drinkCount > 2) {
                    Swal.fire({
                        title: 'Sorry',
                        text: `You may only select 1 drink.`,
                        icon: 'info'
                    });
                    isValid = false;
                }
                if (dessertCount > 3) {
                    Swal.fire({
                        title: 'Sorry',
                        text: `You can select up to 2 kinds of dessert.`,
                        icon: 'info'
                    });
                    isValid = false;
                }


                isFoodSelectionValid = isValid;
                updateBookNowButton();
            });

            function updateBookNowButton() {
                bookNowBtn.disabled = !(isGuestCountValid && isFoodSelectionValid)
            }
        })
    </script>

    <!-- Event Hall-->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            fetch(`../../Function/Booking/getEventCategory.php`)
                .then(response => {
                    if (!response.ok) throw new Error('Network Error');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert("Error: " + data.error);
                        return;
                    }

                    // const eventInfoLabel = document.querySelector(".eventInfoLabel");
                    // const eventTypeSelect = document.getElementById("eventType");

                    const venueInfoLabel = document.querySelector("#venueInfoLabel");
                    const venueSelect = document.getElementById("eventVenue");

                    venueSelect.innerHTML = '';

                    venueInfoLabel.innerHTML = 'Venue';

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
                        venueOptions.textContent = `${hall.RServiceName} - ${hall.RSmaxCapacity} pax`;
                        venueSelect.appendChild(venueOptions);
                    })

                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation', error);
                })
        });
    </script>

    <!-- For event food -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

                    function getMenuByCategory(menuContainerID, categories, categoryName, message) {

                        const container = document.getElementById(menuContainerID);
                        container.innerHTML = '';

                        if (categories.length > 0) {
                            categories.forEach(category => {
                                const wrapper = document.createElement('div');
                                wrapper.classList.add('form-check');

                                const input = document.createElement('input');
                                input.name = categoryName + 'Selections[]';
                                input.type = 'checkbox';
                                input.id = category.foodItemID;
                                input.value = category.foodName;
                                input.classList.add('form-check-input');

                                const label = document.createElement('label');
                                label.setAttribute('for', input.id);
                                label.textContent = category.foodName;
                                label.classList.add('form-check-label');

                                wrapper.appendChild(input);
                                wrapper.appendChild(label);
                                container.appendChild(wrapper);
                            });
                        } else {
                            const p = document.createElement('p');
                            p.classList.add('card-text');
                            p.textContent = message;
                            container.appendChild(p);
                        }
                    }

                    getMenuByCategory('chickenContainerA', data.chickenCategory, 'chicken',
                        'No Available Chicken Menu');
                    getMenuByCategory('porkContainerA', data.porkCategory, 'pork', 'No Available Pork Menu');
                    getMenuByCategory('pastaContainerA', data.pastaCategory, 'pasta',
                        'No Available Pasta Menu');
                    getMenuByCategory('beefContainerA', data.beefCategory, 'beef', 'No Available Beef Menu');
                    getMenuByCategory('vegieContainerA', data.vegieCategory, 'vegie',
                        'No Available Vegetables Menu');
                    getMenuByCategory('seafoodContainerA', data.seafoodCategory, 'seafood',
                        'No Available Seafood Menu');
                    getMenuByCategory('drinkContainer', data.drinkCategory, 'drink', 'No Available Drink Menu');
                    getMenuByCategory('dessertContainer', data.dessertCategory, 'dessert',
                        'No Available Dessert Menu');

                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation', error);
                })
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



</body>

</html>