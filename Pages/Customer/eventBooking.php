<?php
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

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


$gcashDetails = '';
$resortInfoName = 'ContactNum';
$getContactDetails = $conn->prepare("SELECT resortInfoDetail FROM resortinfo WHERE resortInfoName = ?");
$getContactDetails->bind_param('s', $resortInfoName);
$getContactDetails->execute();
$result = $getContactDetails->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $contactNumber = $row['resortInfoDetail'];
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
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- flatpickr calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

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
                <div class="upperRow" id="upperRow">
                    <div class="card event-card" id="eventBookingCard">
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
                            <label for="additionalRequest" class="eventInfoLabel">Additional Request</label>
                            <textarea class="form-control w-100" id="purpose-additionalNotes" name="additionalRequest"
                                rows="2" placeholder="Optional"></textarea>
                        </div>

                        <div class="noteContainer">
                            <h6 class="eventInfoLabel">Note:</h6>
                            <ul>
                                <li> <i class="bi bi-info-circle-fill"></i> &nbsp;For any concerns or details regarding food and other services, contact us
                                    at <?= $contactNumber ?>.</li>
                                <li> <i class="bi bi-info-circle-fill"></i> &nbsp;If you already have a catering service in mind, you don’t need to select a dish.</li>
                                <li><i class="bi bi-info-circle-fill"></i> &nbsp;Kindly contact us first to confirm your food preference before adding it in the request field. </li>
                                <li> <i class="bi bi-info-circle-fill"></i> &nbsp;You can contact us on <a href="https://www.facebook.com/messages/t/100888189251567" target="_blank">Facebook</a>.</li>
                            </ul>
                        </div>
                    </div>


                    <div class="secondColumn">
                        <div id="calendar"></div>
                        <div class="packageDisplay" style="display: none;">
                            <div id="packageCardsContainer" class="container d-flex flex-wrap gap-3">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lowerRow" id="lowerRow">

                    <div class="card foodSelection-card" id="foodSelectionCard">

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

                    <div class="card additionalServices-card" id="additionalServicesCard">

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
            </div>
            <!--end ng container div-->

            <div class="eventButton-container">
                <button type="submit" class="btn btn-primary btn-md w-25" name="eventBN" id="bookNowBtn" disabled>Book
                    Now</button>
            </div>

        </div>
        <!--end ng event div-->

        <!-- Dish Modal -->
        <div class="modal  modal-lg" id="dishModal" tabindex="-1" aria-labelledby="exampleModalLabel">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-4 fw-bold" id="dishModalLabel">Select Dishes</h1>
                    </div>
                    <div class="note-container">
                        <h6 class="noteLabel">Catering Services Inclusions:</h6>
                        <ul>
                            <li>Basic design for tables, chair & stage</li>
                            <li>4 Dishes (vegetables is included)</li>
                            <li>w/ Rice & Drink/Juice</li>
                            <li>Dessert</li>
                        </ul>
                    </div>
                    <div class="modal-body dishMenu" id="dishMenuContainer">
                        <?php
                        $availableID = 1;
                        $getFoodItemQuery = $conn->prepare("SELECT `foodItemID`, `foodName`, `foodCategory`, `ageGroup` FROM `menuitem`WHERE availabilityID = ? ORDER BY ageGroup");
                        $getFoodItemQuery->bind_param("i", $availableID);
                        $getFoodItemQuery->execute();
                        $getFoodItemResult = $getFoodItemQuery->get_result();


                        $chickenCategory = [];
                        $porkCategory = [];
                        $beefCategory = [];
                        $pastaCategory = [];
                        $vegetablesCategory = [];
                        $seafoodCategory = [];
                        $dessertCategory = [];
                        $drinkCategory = [];
                        $fingerFoods = [];
                        if ($getFoodItemResult->num_rows > 0) {
                            while ($row = $getFoodItemResult->fetch_assoc()) {
                                $categoryName = $row['foodCategory'];
                                if ($categoryName === 'Chicken') {
                                    $chickenCategory[] = $row;
                                } elseif ($categoryName === 'Pork') {
                                    $porkCategory[] = $row;
                                } elseif ($categoryName === 'Beef') {
                                    $beefCategory[] = $row;
                                } elseif ($categoryName === 'Pasta') {
                                    $pastaCategory[] = $row;
                                } elseif ($categoryName === 'Vegetables') {
                                    $vegetablesCategory[] = $row;
                                } elseif ($categoryName === 'Seafood') {
                                    $seafoodCategory[] = $row;
                                } elseif ($categoryName === 'Drink') {
                                    $drinkCategory[] = $row;
                                } elseif ($categoryName === 'Dessert') {
                                    $dessertCategory[] = $row;
                                } elseif ($categoryName === 'Finger Foods Cocktail') {
                                    $fingerFoods[] = $row;
                                }
                            }
                        }

                        // error_log(print_r($dessertCategory, true));
                        ?>

                        <div class="adult-category container" id="adultMeal">
                            <h3 class="mealLabel">Adult Meal</h3>
                            <div class="foodContainer">
                                <div id="adultChickenContainer">
                                    <h4>Chicken</h4>

                                    <?php foreach ($chickenCategory as $item):
                                        if ($item['ageGroup'] === 'Adult'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                                <div id="adultPorkContainer">
                                    <h4>Pork</h4>
                                    <?php foreach ($porkCategory as $item):
                                        if ($item['ageGroup'] === 'Adult'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                                <div id="adultBeefContainer">
                                    <h4>Beef</h4>
                                    <?php foreach ($beefCategory as $item):
                                        if ($item['ageGroup'] === 'Adult'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                                <div id="adultPastaContainer">
                                    <h4>Pasta</h4>
                                    <?php foreach ($pastaCategory as $item):
                                        if ($item['ageGroup'] === 'Adult'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                                <div id="adultVeggieContainer">
                                    <h4>Vegetables</h4>
                                    <?php foreach ($vegetablesCategory as $item):
                                        if ($item['ageGroup'] === 'Adult'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                                <div id="adultSeafoodContainer">
                                    <h4>Seafood</h4>
                                    <?php foreach ($seafoodCategory as $item):
                                        if ($item['ageGroup'] === 'Adult'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="kid-category container" id="kidMeal" style="display: none;">
                            <h3 class="mealLabel">Kiddie Meal</h3>
                            <div class="foodContainer">
                                <div id="kidChickenContainer">
                                    <h4>Chicken</h4>
                                    <?php foreach ($chickenCategory as $item):
                                        if ($item['ageGroup'] === 'Child'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                                <div id="kidPorkContainer">
                                    <h4>Pork</h4>
                                    <?php foreach ($porkCategory as $item):
                                        if ($item['ageGroup'] === 'Child'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                                <div id="kidPastaContainer">
                                    <h4>Pasta</h4>
                                    <?php foreach ($pastaCategory as $item):
                                        if ($item['ageGroup'] === 'Child'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                                <div id="kidSeafoodContainer">
                                    <h4>Seafood</h4>
                                    <?php foreach ($seafoodCategory as $item):
                                        if ($item['ageGroup'] === 'Child'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                                <div id="kidFingerContainer">
                                    <h4>Finger Foods</h4>
                                    <?php foreach ($fingerFoods as $item):
                                        if ($item['ageGroup'] === 'Child'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>

                                <div id="dessertContainer">
                                    <h4>Desserts</h4>
                                    <?php foreach ($dessertCategory as $item):
                                        if ($item['ageGroup'] === 'Child'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            </div>
                        </div>


                        <div class="drink-dessert-category container" id="drinkDessert">
                            <h3 class="mealLabel">Drinks & Dessert</h3>
                            <div class="foodContainer">
                                <div id="drinkContainer">
                                    <h4>Drinks</h4>
                                    <?php foreach ($drinkCategory as $item):
                                        if ($item['ageGroup'] === 'Adult'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                                <div id="dessertContainer">
                                    <h4>Desserts</h4>
                                    <?php foreach ($dessertCategory as $item):
                                        if ($item['ageGroup'] === 'Adult'): ?>
                                            <div class="food-item-container">
                                                <input type="checkbox" class="food-item" name="foodSelections[<?= $item['foodItemID'] ?>][<?= $item['foodCategory'] ?>]" value="<?= $item['foodName'] ?>">&nbsp;<?= $item['foodName'] ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            </div>
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
                    </div>
                    <p class="note" id="additionalServiceNote" style="color: #0d6dfc">Before including additional
                        services from our partners,
                        <strong> please
                            make sure to contact them</strong> and discuss the details of the event.
                    </p>
                    <div class="customer-choice-container p-3" style="color:rgba(43, 155, 240, 1);">
                        <p class="warning-text">This option applies only when you’ve chosen to avail a partner service. Please select one if that’s the case.</p>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="customer-choice" value="proceed" <?= (!empty($formData['customer-choice']) ?? $formData['customer-choice'] === 'proceed') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="radioButton">
                                Still <strong>proceed </strong> with the event regardless of the partner’s decision.
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="customer-choice" value="cancel">
                            <label class="form-check-label" for="radioButton">
                                <strong>Cancel</strong> the event if the availed (chosen) service is declined.
                            </label>
                        </div>
                    </div>
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
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Full Calendar for Date display -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="../../Assets/JS/fullCalendar.js" type="text/javascript"> </script>

    <!-- Flatpickr for date input -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Format the date to Y-M-D h:i:s -->
    <script src="../../Assets/JS/formatDateTime.js" type="text/javascript"> </script>

    <!-- <script src="../../Assets/JS/EventJS/getFoodByCategory.js"></script> -->
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

        flatpickr('#eventStartTime', {
            enableTime: true,
            noCalendar: true,
            minTime: "06:00",
            maxTime: "17:00",
            dateFormat: "H:i",
            disableMobile: true,
        })
    </script>

    <!-- Event Hall-->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const date = document.getElementById('eventDate');
            const startTime = document.getElementById('eventStartTime');
            const venueSelect = document.getElementById('eventVenue');


            const sessionSelectedVenue =
                <?= isset($formData['eventVenue']) ? json_encode($formData['eventVenue']) : '""' ?>;

            venueSelect.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.textContent = 'Select event schedule first';
            defaultOption.disabled = true;
            defaultOption.selected = true;
            venueSelect.appendChild(defaultOption);

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

    <!-- Session Selected Food -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sessionFoodSelections = <?= isset($formData['foodSelections']) ? json_encode($formData['foodSelections']) : '[]' ?>;
            const sessionSelectedFoods = <?= isset($formData['selectedFoods']) ? json_encode($formData['selectedFoods']) : '[]' ?>;

            let selectedFoods = [];

            const checkboxes = document.querySelectorAll('.food-item');


            if (typeof sessionFoodSelections === "object" && sessionFoodSelections !== null) {
                Object.values(sessionFoodSelections).forEach(categoryObj => {
                    if (typeof categoryObj === "object" && categoryObj !== null) {
                        Object.values(categoryObj).forEach(foodName => {
                            selectedFoods.push(String(foodName));
                        });
                    }
                });
            }


            if (typeof sessionSelectedFoods === 'object' && sessionSelectedFoods !== null && Object.keys(sessionSelectedFoods).length > 0) {
                selectedFoods = Object.values(sessionSelectedFoods).map(String);
            }

            console.log(selectedFoods);

            checkboxes.forEach((checkbox) => {
                if (selectedFoods.includes(String(checkbox.value))) {
                    checkbox.checked = true;
                }
            });
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

            function getAvailablePartnerService() {
                const selectedDate = date.value;
                const selectedStartTime = startTime.value;

                if (!selectedDate || !selectedStartTime) return;

                const startDateTimeObj = new Date(`${selectedDate}T${selectedStartTime}`);
                const endDateTimeObj = new Date(
                    startDateTimeObj.getTime() + 5 * 60 * 60 * 1000
                ); // +5 hours

                const formattedStartDateTime = formatDateTime(startDateTimeObj);
                const formattedEndDateTime = formatDateTime(endDateTimeObj);

                fetch(
                        `../../Function/Booking/getPartnerService.php?startDate=${encodeURIComponent(
                    formattedStartDateTime
                    )}&endDate=${encodeURIComponent(formattedEndDateTime)}`
                    )
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error("Network Error");
                        }
                        return response.json();
                    })
                    .then((data) => {
                        if (data.error) {
                            Swal.fire({
                                title: "Error",
                                text: "Error: " + data.error,
                                icon: "error",
                            });
                            return;
                        }
                        mainContainer.innerHTML = "";
                        if (data.Categories && data.Categories.length > 0) {
                            data.Categories.forEach((category) => {
                                const wrapper = document.createElement("div");
                                wrapper.classList.add("photography");


                                const bpTypeContainer = document.createElement("div");
                                bpTypeContainer.classList.add("bpTypeContainer");

                                const categoryHeading = document.createElement("h6");
                                categoryHeading.classList.add("bpCategory", "fw-bold");
                                categoryHeading.innerText = category.eventCategory || "Category Name";

                                bpTypeContainer.appendChild(categoryHeading);
                                wrapper.appendChild(bpTypeContainer);

                                const partnerListContainer = document.createElement("div");
                                partnerListContainer.classList.add("partnerListContainer");

                                const checkbox = document.createElement("input");
                                checkbox.type = "checkbox";
                                checkbox.classList.add("form-check-input");
                                checkbox.name = `additionalServiceSelected[${category.partnershipServiceID}][selected]`;
                                checkbox.value = category.partnershipServiceID;

                                const inputPBName = document.createElement("input");
                                inputPBName.type = "hidden";
                                inputPBName.name = `additionalServiceSelected[${category.partnershipServiceID}][PBName]`;
                                inputPBName.value = category.PBName;

                                const inputPBPrice = document.createElement("input");
                                inputPBPrice.type = "hidden";
                                inputPBPrice.name = `additionalServiceSelected[${category.partnershipServiceID}][PBPrice]`;
                                inputPBPrice.value = category.PBPrice;

                                const inputServiceID = document.createElement("input");
                                inputServiceID.type = "hidden";
                                inputServiceID.name = `additionalServiceSelected[${category.partnershipServiceID}][partnershipServiceID]`;
                                inputServiceID.value = category.partnershipServiceID;

                                const label = document.createElement("label");
                                label.classList.add("form-check-label");
                                label.innerHTML = `${category.companyName} - ${
                                    category.PBName
                                } &mdash; ₱ ${Number(category.PBPrice).toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2,
                                })} &mdash; ${category.phoneNumber}`;

                                let selectedServiceIDs = [];

                                if (Array.isArray(sessionSelectedServices)) {
                                    selectedServiceIDs = sessionSelectedServices.map(String);
                                } else if (typeof sessionSelectedServices === "object" && sessionSelectedServices !== null) {
                                    selectedServiceIDs = Object.keys(sessionSelectedServices).map(String);
                                }

                                if (selectedServiceIDs.includes(String(category.partnershipServiceID))) {
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
                            const div = document.createElement("div");
                            div.classList.add("no-data-container");

                            const cardText = document.createElement("h3");
                            cardText.classList.add("card-text");
                            cardText.innerHTML = "No Additional Services Available";

                            div.appendChild(cardText);
                            mainContainer.appendChild(div);
                        }
                    })
                    .catch((error) => {
                        Swal.fire({
                            title: "Error",
                            text: error.Message,
                            icon: "error",
                        });
                        console.error("There was a problem with the fetch operation", error);
                    });
            }

            // console.log('Date value:', date.value);
            // console.log('StartTime value:', startTime.value);
        });
    </script>

    <!-- Auto select event -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const selectedEvent = params.get('event');
            const select = document.getElementById('eventType');
            if (selectedEvent) {
                if (select) {
                    select.value = selectedEvent;
                }
            };
            // console.log(selectedEvent);

            if (selectedEvent) {
                const url = new URL(window.location);
                url.search = '';
                history.replaceState({}, document.title, url.toString());
            }
            const kidsMeal = document.getElementById('kidMeal');
            const foodItem = document.querySelectorAll('.food-item');
            if (select.value) {
                if (select.value === 'Kids Party') {
                    kidsMeal.style.display = 'block';

                    foodItem.forEach((food) => {
                        if (foodItem.value === 'Chocolate fountain machine') {
                            foodItem.checked = true;
                        }
                    })
                }
                select.addEventListener('change', () => {
                    if (select.value === 'Kids Party') {
                        kidsMeal.style.display = 'block';
                    } else {
                        kidsMeal.style.display = 'none';
                    }
                    console.log(select.value);
                })
            }

        });
    </script>

    <!-- Fetching events for calendar  -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var disabledDates = []; // store dates to disable in Flatpickr

            // Initialize FullCalendar
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: '../../Function/fetchUserBookings.php',

                eventsSet: function(events) {
                    console.log('Fetched events:', events);

                    // Extract only the start dates (or adjust if you have ranges)
                    disabledDates = events.map(event => event.startStr);
                    console.log('Disabled dates:', disabledDates);

                    // Once we have the dates, initialize Flatpickr
                    initFlatpickr(disabledDates);
                },

                eventClick: function(info) {
                    window.location.href = "/Pages/Customer/Account/bookingHistory.php";
                },

                eventDidMount: function(info) {
                    if (info.event.allDay) {
                        const dateStr = info.event.startStr;
                        const dayCell = document.querySelector(`.fc-daygrid-day[data-date="${dateStr}"]`);
                        if (dayCell) {
                            let baseColor = info.event.backgroundColor || info.event.extendedProps.color || '#dc3545';
                            dayCell.style.backgroundColor = baseColor;
                            dayCell.style.color = '#000';
                        }
                        if (info.el) {
                            info.el.style.display = 'none';
                        }
                    }
                }
            });

            calendar.render();

            // Initialize Flatpickr after fetching disabled dates
            function initFlatpickr(dates) {
                flatpickr("#eventDate", {
                    dateFormat: "Y-m-d",
                    disable: dates,
                    minDate: "today",
                });
            }
        });
    </script>



    <!-- Sweetalert Message  -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const action = params.get('action');

        if (action === 'errorBooking') {
            Swal.fire({
                title: 'Error Booking',
                text: 'An error occured while booking. Try again later',
                icon: 'error',
            })
        } else if (action === 'NoSelectedChoice') {
            Swal.fire({
                title: 'Oops',
                text: 'Selection required! Choose whether to proceed or cancel the event before moving forward.',
                icon: 'warning',
                confirmButtonText: 'Okay',
            }).then((result) => {
                const additionalServicesModal = document.getElementById('additionalServicesModal');
                const modal = new bootstrap.Modal(additionalServicesModal);
                modal.show();

                // const container = document.querySelector('.customer-choice-container');
                // container.style.setProperty("border", "1px solid red", "important");

            })
        }
        // else if (action === 'exceedFoodCount') {
        //     Swal.fire({
        //         title: 'Oops',
        //         text: 'You can select a maximum of 4 dishes.',
        //         icon: 'warning',
        //         confirmButtonText: 'Okay',
        //     }).then((result) => {
        //         const dishModal = document.getElementById('dishModal');
        //         const modal = new bootstrap.Modal(dishModal);
        //         modal.show();
        //     })
        // }

        if (action) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>

</body>

</html>