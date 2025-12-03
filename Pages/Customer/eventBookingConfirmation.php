<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
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
    <title>Mamyr Resort and Events Place - Event Booking Confirmation</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Customer/eventBookingConfirmation.css">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
        integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <?php
    $query = $conn->prepare("SELECT firstName, middleInitial, lastName FROM user WHERE userID = ? AND userRole = ?");
    $query->bind_param('ii', $userID, $userRole);
    $query->execute();
    $result =  $query->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $firstName = trim(ucfirst($data['firstName']));
        $lastName = trim(ucfirst($data['lastName']));
        $middleInitial = trim(ucfirst($data['middleInitial'] ?? ''));

        $customerName = ucfirst($firstName) . " " .
            ucfirst($middleInitial) . ". " .
            ucfirst($lastName);
    }
    ?>


    <?php


    function getMenuItem($items, $query)
    {
        $menuItems = [];
        foreach ($items as $item) {
            $itemName = trim($item);
            $query->bind_param('s',  $itemName);
            if ($query->execute()) {
                $result = $query->get_result();
                if ($result->num_rows > 0) {
                    while ($data = $result->fetch_assoc()) {
                        $menuItems[] = [
                            'foodItemID' => $data['foodItemID'],
                            'foodName'   => $data['foodName']
                            // 'foodPrice' => $data['foodPrice']
                        ];
                    }
                } else {
                    error_log("No data");
                }
            } else {
                error_log("Error: " . $query->error);
            }
        }

        return $menuItems;
    }

    if (isset($_POST['eventBN'])) {

        $_SESSION['eventFormData'] = $_POST;
        print_r($_SESSION['eventFormData']);
        $eventType = mysqli_real_escape_string($conn, $_POST['eventType']);
        $guestNo = intval($_POST['guestNo']);
        $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);


        $preferencesList = [];
        $allergenList = [];

        if (!empty($_POST['foodPreferences'])) {

            foreach ($_POST['foodPreferences'] as $itemKey => $categories) {
                foreach ($categories as $category => $value) {
                    $value = trim($value);

                    if ($value === "") continue;

                    if ($category === 'preference') {
                        $preferencesList[] = $value;
                    }

                    if ($category === 'allergen')
                        $allergenList[] = $value;
                }
            }
        }

        $formattedPreferences = !empty($preferencesList)
            ? implode(", ", $preferencesList)
            : "None";

        $formattedAllergens = !empty($allergenList)
            ? implode(", ", $allergenList)
            : "None";

        $additionalRequest = !empty($_POST['additionalRequest'])
            ? trim(mysqli_real_escape_string($conn, $_POST['additionalRequest']))
            : "N/A";

        $finalFullNotes =
            "Preferences: " . $formattedPreferences . "\n" .
            "Allergens: " . $formattedAllergens . "\n" .
            "Additional Request: " . $additionalRequest;

        //Date and time
        $eventDate = mysqli_real_escape_string($conn, $_POST['eventDate']);
        $eventStartTime = mysqli_real_escape_string($conn, $_POST['eventStartTime']);

        $startDateObj = new DateTime("$eventDate $eventStartTime");


        $endDateObj = clone $startDateObj;


        $endDateObj->modify('+5 hours');


        $startDate = $startDateObj->format('Y-m-d H:i:s');
        $endDate = $endDateObj->format('Y-m-d H:i:s');

        $formattedEventDate = $startDateObj->format('F d, Y');
        $formattedEventTime = $startDateObj->format('g:i A') . " to " . $endDateObj->format('g:i A');

        // Food 
        $foodSelections = $_POST['foodSelections'] ?? [];
        echo ("<h1> Selection </h1> <br>");
        print_r($foodSelections);
        $targetCategory = 'Vegetables';
        $drinkCategory = 'Drink';
        $dessertCategory = 'Dessert';
        $drinkCount = 0;
        $dessertCount = 0;
        $totalFoodCount = count($foodSelections);


        if ($totalFoodCount <= 6 && $totalFoodCount != 0) {
            $foundVeggie = false;

            foreach ($foodSelections as $foodID => $items) {
                foreach ($items as $category => $name) {
                    switch ($category) {
                        case $targetCategory:
                            $foundVeggie = true;
                            break;
                        case $drinkCategory:
                            $drinkCount++;
                            break;
                        case $dessertCategory:
                            $dessertCount++;
                            break;
                    }
                }
            }

            if (!$foundVeggie) {
                header("Location: eventBooking.php?action=noSelectedVegie");
                exit();
            }

            if ($drinkCount == 0 || $dessertCount == 0) {
                header("Location: eventBooking.php?action=noDrinkOrDessert");
                exit();
            }
        } elseif ($totalFoodCount == 0) {
            $foodSelections = [];
        } else {
            header("Location: eventBooking.php?action=exceedFoodCount");
            exit();
        }
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';

        //Venue
        $eventVenue = mysqli_real_escape_string($conn, $_POST['eventVenue']);
        $getVenuePrice = $conn->prepare('SELECT * FROM `resortamenity` WHERE RServiceName = ?');
        $getVenuePrice->bind_param('s', $eventVenue);
        if ($getVenuePrice->execute()) {
            $result = $getVenuePrice->get_result();
            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();
                $venueID = $data['resortServiceID'];
                $venuePrice = $data['RSprice'];
                $venueCapacity = $data['RScapacity'];
                $venueDescription = !empty($data['RSdescription']) ? explode('. ', $data['RSdescription']) : [];
            }
        }

        //Business Partner Service
        $additionalServiceSelected = [];

        if (!empty($_POST['additionalServiceSelected'])) {
            foreach ($_POST['additionalServiceSelected'] as $id => $service) {
                // Only keep if checkbox was actually selected
                if (isset($service['selected'])) {
                    $additionalServiceSelected[$id] = [
                        'selected' => trim($service['selected']),
                        'PBName' => trim($service['PBName'] ?? ''),
                        'PBPrice' => trim($service['PBPrice'] ?? ''),
                        'partnershipServiceID' => trim($service['partnershipServiceID'] ?? ''),
                    ];
                }
            }
        }

        if (!empty($additionalServiceSelected)) {
            $customerChoice = mysqli_real_escape_string($conn, $_POST['customer-choice']);

            if (empty($customerChoice)) {
                header('Location: ../../../Pages/Customer/eventBooking.php?action=NoSelectedChoice');
            }

            if ($customerChoice === 'proceed') {
                $customerChoiceMessage = 'You decided to proceed with the event booking regardless of the chosen service provider\'s decision.';
            } elseif ($customerChoice === 'cancel') {
                $customerChoiceMessage = 'You decided to cancel the event booking if the partner service you availed declines.';
            }
        }

        // $getFreeServiceQuery = $conn->prepare("SELECT RServiceName, resortServiceID FROM resortamenity WHERE RSavailabilityID = 4");
        // $getFreeServiceQuery->execute();
        // $result = $getFreeServiceQuery->get_result();
        // $row = $result->fetch_assoc();

        // $freeRoom = $row['RServiceName'];

        // var_dump($additionalServiceSelected);
    }

    // echo '<pre>';
    // print_r($_posiadditionalServiceSelected);
    // echo '</pre>';
    ?>

    <header class="headerSection">
        <div class="backToSelection" id="backToSelection">
            <a href="eventBooking.php" class="back-btn"><img src="../../Assets/Images/Icon/arrowBtnBlue.png"
                    alt="Back Button Image"></a>
        </div>
        <div class="titleContainer">
            <h1 class="eventTitle" id="eventTitle">EVENT BOOKING</h1>
        </div>
    </header>
    <form action="../../Function/Booking/eventBooking.php" method="POST">
        <main class="card mainCard mb-4">
            <section class="event-details-container">
                <h4 id="top-title">Event Details</h4>
                <div class="event-details">
                    <div class="input-container">
                        <label for="customerName">Customer Name: </label>
                        <input type="text" class="form-control" name="customerName"
                            value="<?= htmlspecialchars($customerName) ?>" readonly>
                    </div>
                    <div class="input-container">
                        <label for="eventType">Type of Event</label>
                        <input type="text" class="form-control" name="eventType"
                            value="<?= htmlspecialchars($eventType) ?>">
                    </div>

                    <div class="input-container">
                        <label for="paxNumber">Number of People</label>
                        <input type="text" class="form-control" name="paxNumber"
                            value="<?= htmlspecialchars($guestNo) ?> people">
                        <input type="hidden" name="guestNo" value="<?= $guestNo ?>">
                    </div>
                    <div class="input-container">
                        <label for="eventVenue">Venue of Event</label>
                        <input type="text" class="form-control" name="eventVenue"
                            value="<?= htmlspecialchars($eventVenue) ?>">
                    </div>
                </div>
            </section>

            <section class="venue-details">
                <div class="textareaContainer">
                    <div class="input-container">
                        <p>Venue Description</p>
                        <textarea rows="3" class="form-control" name="venueDescription" id="venueDescription"
                            readonly><?= !empty($venueDescription) ? htmlspecialchars(ucwords(implode("\n", $venueDescription))) : 'N/A' ?></textarea>
                    </div>

                    <div class="input-container additionalRequestPart">
                        <p>Additional Request</p>
                        <textarea rows="3" class="form-control" name="additionalRequest"
                            id="additionalRequest"><?= !empty($additionalRequest) ?  htmlspecialchars(ucfirst($finalFullNotes)) : '' ?></textarea>
                    </div>
                </div>
            </section>

            <section class="date-container">
                <h4> Date & Time</h4>
                <div class="date-details">
                    <div class="input-container">
                        <label for="formattedEventDate">Event Date </label>
                        <input type="text" class="form-control" name="formattedEventDate"
                            value="<?= htmlspecialchars($formattedEventDate) ?>">
                    </div>
                    <div class="input-container">
                        <label for="eventTime">Event Time </label>
                        <input type="text" class="form-control" name="eventTime"
                            value="<?= htmlspecialchars($formattedEventTime) ?>">
                    </div>
                </div>
            </section>


            <section class="food-main-container">
                <h4>Food, Drinks & Dessert</h4>
                <div class="food-container">
                    <?php
                    $selectedFoodCount = 0;

                    if (!empty($foodSelections)) {
                        $groupedFoods = [];

                        foreach ($foodSelections as $id => $items) {
                            foreach ($items as $category => $name) {
                                $groupedFoods[$category][] = [
                                    'id' => $id,
                                    'name' => $name
                                ];
                            }
                        }

                        foreach ($groupedFoods as $category => $foods) { ?>
                            <div class="card p-2 food-info">
                                <h5 class="foodCategory"><?= htmlspecialchars(ucfirst($category)) ?></h5>
                                <?php foreach ($foods as $food) {
                                    $selectedFoodCount++; ?>
                                    <div class="food-name">
                                        <label class="foodName"><?= htmlspecialchars($food['name']) ?></label>
                                        <input type="hidden" name="selectedFoods[<?= $food['id'] ?>]" value="<?= htmlspecialchars($food['name']) ?>">
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                    <?php } else { ?>
                        <p class="text-center">No menu selected.</p>
                    <?php } ?>
                </div>
            </section>


            <section class="additional-container">
                <h4>Additional Services</h4>
                <div id="additionals">
                    <?php
                    $additionalServicePrice = 0;
                    if (!empty($additionalServiceSelected)) { ?>

                        <?php foreach ($additionalServiceSelected as $id => $service) {
                            $additionalServicePrice += $service['PBPrice'] ?>
                            <div class="form-group">
                                <label><?= htmlspecialchars(ucfirst($service['PBName'])) ?> &mdash;
                                    ₱<?= number_format($service['PBPrice'], 2) ?></label>
                                <input type="hidden" name="additionalServiceSelected[<?= $id ?>]" value="<?= htmlspecialchars($service['partnershipServiceID']) ?>"
                                    class="form-control">
                            </div>
                            <div class="customer-chocie-container">
                                <p style="color: rgba(2, 10, 20, 0.6);"><?= $customerChoiceMessage ?> </p>
                                <input type="hidden" name="customer-choice" value="<?= $customerChoice ?>">
                            </div>
                        <?php } ?>
                </div>
            </section>
        <?php } else { ?>
            <p class="text-center">No additional service selected.</p>
        <?php } ?>


        <?php
        $chargeType = 'Food';
        $pricingType = 'Per Head';
        $pricePerHead = 0;
        $getPricingID = $conn->prepare("SELECT pricingID, price FROM `servicepricing` WHERE chargeType = ? AND pricingType = ?");
        $getPricingID->bind_param('ss',  $chargeType, $pricingType);
        if ($getPricingID->execute()) {
            $result =  $getPricingID->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                $pricingID = intval($row['pricingID']);
                $pricePerHead = (float) $row['price'] ?? 0;
            }
        }

        $totalFoodPrice = 0;
        if ($selectedFoodCount > 0) {
            $totalFoodPrice = $guestNo * $pricePerHead;
        }
        $totalCost = $totalFoodPrice + $venuePrice + $additionalServicePrice;
        $downpaymentPrice = $totalCost * 0.3;
        ?>


        <!-- <section class="additionalServices">

        </section> -->

        <section class="payment-container">
            <h4>Payment Details</h4>

            <div class="payment-details-container">
                <div class="input-container">
                    <label for="paymentMethod"> Payment Method</label>
                    <input type="text" class="form-control" name="paymentMethod"
                        value="<?= htmlspecialchars($paymentMethod) ?>">
                </div>
                <div class="input-container">
                    <label for="eventVenuePrice">Venue Price</label>
                    <input type="text" class="form-control" name="eventVenuePrice"
                        value="<?= !empty($venuePrice) ? '₱' . htmlspecialchars(number_format($venuePrice, 2)) : 'None' ?>">
                </div>
                <div class="input-container">
                    <label for="totalFoodPrice">Menu Price</label>
                    <input type="text" class="form-control" name="totalFoodPrice"
                        value="<?= !empty($totalFoodPrice) ? '₱' . htmlspecialchars(number_format($totalFoodPrice, 2)) : 'None' ?>">
                </div>
                <div class="input-container">
                    <label for="additionalServicePrice">Additional Service Price</label>
                    <input type="text" class="form-control" name="additionalServicePrice"
                        value="<?= !empty($additionalServicePrice) ? '₱' . htmlspecialchars(number_format($additionalServicePrice, 2)) : 'None' ?>">
                </div>
                <div class="input-container">
                    <label for="downpayment">(Tentative) Downpayment (30%): </label>
                    <input type="text" class="form-control" name="downpayment"
                        value="₱<?= htmlspecialchars(number_format($downpaymentPrice, 2)) ?>">
                </div>
                <div class="input-container">
                    <label for="totalCost">Total Cost (Tentative)</label>
                    <input type="text" class="form-control" name="totalCost"
                        value="₱<?= htmlspecialchars(number_format($totalCost, 2)) ?>">
                </div>
            </div>
        </section>


        <section class="notes-container">


            <p><i class="fa-solid fa-circle-info fa-flip" style="color: #74C0FC;"></i>The admin will verify the
                number of guests and the selected menu to calculate the total cost.</p>
            <p><i class="fa-solid fa-circle-info fa-flip" style="color: #74C0FC;"></i>There will be a changes in
                downpayment amount once the total bill is computed</p>
            <p><i class="fa-solid fa-circle-info fa-flip" style="color: #74C0FC;"></i>Full payment must be given to
                the admin before the start of the event.</p>
            <p><i class="fa-solid fa-circle-info fa-flip" style="color: #74C0FC;"></i>Cooking and sticking
                decoration that will damage the venue are prohibited.</p>
        </section>

        <div class="hidden-inputs" style="display: none;">

            <input type="hidden" name="eventDate" value="<?= $eventDate ?>">
            <input type="hidden" name="eventStartTime" value="<?= $eventStartTime ?>">
            <input type="hidden" name="startDate" value="<?= $startDate ?>">
            <input type="hidden" name="endDate" value="<?= $endDate ?>">
            <input type="hidden" name="venueID" value="<?= $venueID ?>">
            <input type="hidden" name="pricingID" value="<?= $pricingID ?>">
        </div>

        <div class="button-container my-3">
            <button type="submit" class="btn btn-primary loaderTrigger" name="eventBook">Book Now</button>
        </div>
        </main>
    </form>

    <?php include 'loader.php'; ?>
    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>